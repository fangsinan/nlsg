<?php

namespace App\Servers;

use App\Models\Subscribe;
use App\Models\VipWorksList;
use Illuminate\Support\Facades\DB;

class VipWorksListServers
{
    public function appendSub(){
        $work = VipWorksList::query()
            ->where('status','=',1)
            ->where('sub_job','=',0)
            ->first();

        if (empty($work)){
            return true;
        }

        if ($work->type === 1){
            //专栏表
            $sub['type'] = 6;
        }elseif ($work->type === 2){
            //作品表
            $sub['type'] = 2;
        }else{
            return true;
        }

        $sub['relation_id'] = $work->works_id;

        $already_sub = DB::table('nlsg_vip_user as v')
            ->join('nlsg_subscribe as s','v.user_id','=','s.user_id')
            ->where('s.type','=',$sub['type'])
            ->where('s.relation_id','=',$sub['relation_id'])
            ->where('s.status','=',1)
            ->where('s.is_del','=',0)
            ->where('v.status','=',1)
            ->where('v.is_default','=',1)
            ->pluck('v.user_id')
            ->toArray();

        $already_sub = implode(',',$already_sub);

        $sql = 'SELECT user_id,username,`level`,start_time,expire_time,is_open_360,time_begin_360,time_end_360,
floor((UNIX_TIMESTAMP(expire_time) - UNIX_TIMESTAMP(start_time)) / 31536000 ) as l_1,
floor((UNIX_TIMESTAMP(time_end_360) - UNIX_TIMESTAMP(time_begin_360)) / 31536000 ) as l_2
from nlsg_vip_user where status = 1 and is_default = 1
and (`level` = 1 or (`level` = 2 and is_open_360 = 1))';

        if (!empty($already_sub)){
            $sql .= ' and user_id not in ('.$already_sub.')';
        }

        $list = DB::select($sql);

        $now_data = date('Y-m-d H:i:s');

        $add_data = [];

        foreach ($list as $v) {
                $temp_data = [];
                $temp_data['type'] = $sub['type'];
                $temp_data['user_id'] = $v->user_id;
                $temp_data['relation_id'] = $sub['relation_id'];
                $temp_data['pay_time'] = $now_data;
                if ($v->level == 1) {
                    $temp_data['start_time'] = $v->start_time;
                    $temp_data['end_time'] = $v->expire_time;
                } else {
                    $temp_data['start_time'] = $v->time_begin_360;
                    $temp_data['end_time'] = $v->time_end_360;
                }
                $temp_data['status'] = 1;
                $temp_data['give'] = 3;
                $add_data[] = $temp_data;
        }

        $add_data = array_chunk($add_data,500);
        DB::beginTransaction();

        $insert_res = true;
        foreach ($add_data as $ad){
           $temp_res =  DB::table('nlsg_subscribe')->insert($ad);
           if (!$temp_res){
               $insert_res = false;
               break;
           }
        }
        if (!$insert_res){
            DB::rollBack();
        }

        $work->sub_job = 1;
        $temp_res = $work->save();
        if (!$temp_res){
            DB::rollBack();
        }

        DB::commit();
        return true;

    }
}
