<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class LiveLogin extends Model
{
    protected $table = 'nlsg_live_login';

    public function clear()
    {

//        $live_list = Live::query()->pluck('id')->toArray();
        $live_list = DB::table('nlsg_live_online_user')->groupBy('live_id')
            ->where('live_id','=',139)
            ->select(['live_id'])->get();

        foreach ($live_list as $v) {
            $sql = "SELECT * from (
SELECT GROUP_CONCAT(id) as ids,flag,count(*) as counts from (
SELECT id,user_id,live_id,live_son_flag,online_time_str,
       CONCAT(online_time_str,'-',live_id,'-',user_id,'-',live_son_flag) as flag
from nlsg_live_online_user where live_id = $v->live_id
) as a GROUP BY flag) as b where counts > 1";
            $list = DB::select($sql);

            if (!empty($list)){
                foreach ($list as $vv){
                    $ids = explode(',',$vv->ids);
                    array_shift($ids);
                    DB::table('nlsg_live_online_user')->whereIn('id',$ids)->delete();
                }
            }

        }


    }
}
