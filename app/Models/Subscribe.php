<?php

namespace App\Models;


use Illuminate\Support\Facades\DB;

class Subscribe extends Base
{
    protected $table = 'nlsg_subscribe';


    public function UserInfo (){
        return $this->belongsTo('App\Models\User','user_id');
    }
    protected $fillable = ['user_id','pay_time','type','order_id','status',
        'start_time','end_time', 'relation_id', 'service_id','channel_works_list_id' ];




    /**
     * $user_id  登录者用户
     * $target_id  目标id  1为专栏的id  2作品id ....
     * type 1 专栏  2作品 3直播  4会员 5线下产品  6讲座
     * */
    static function isSubscribe ($user_id=0,$target_id=0,$type=0){
        $is_sub = 0;


        //会员都免费
        $level = User::getLevel($user_id);
        if($type!=3 && $level) return 1;

        if($user_id && $target_id && $type ){
            $where = ['type' => $type, 'user_id' => $user_id,];
            //处理专栏的关注信息
            if( !in_array($type,[1,2,3,4,5,6]) ){
                return 0;
            }

            $where['relation_id'] = $target_id;
            if( in_array($type,[3,5]) ){  //直播永久有效不需 判断end_time
                $sub_data = Subscribe::where($where)
                    ->first();
            }else{
                $sub_data = Subscribe::where($where)
                    ->where('end_time', '>', date('Y-m-d H:i:s'))
                    ->first();
            }

            if( $sub_data ){
                $is_sub = 1;
            }

            //特殊 作品和讲座的情况下需要校验是否订阅专栏
            if($is_sub==0 && in_array($type,[2,6])){
                switch ($type) {
                    case 1:
                        $result = Column::find($target_id);
                        break;
                    case 2:
                        $result = Works::find($target_id);
                        break;
                    case 3:
                        $result = Live::find($target_id);
                        break;
                    case 6:
                        $result = Column::find($target_id);
                        break;
                }

                $id = Column::select('id')->where( [ 'user_id'=> $result['user_id'],'type'=> 1] )->first();

                $sub_data = Subscribe::where([
                    'type' => 1,  //专栏
                    'user_id' => $user_id,
                    'relation_id' => $id['id'],
                    ])->where('end_time', '>', date('Y-m-d H:i:s'))->first();
                if($sub_data){
                    $is_sub = 1;
                }
            }
        }
        return $is_sub;
    }

    //用户补充订阅方法
    static public function appendSub($user_id, $team = 1)
    {

        if (!is_array($user_id)){
            $user_id = explode(',',$user_id);
        }

        if (empty($user_id)){
            return ['code'=>false,'msg'=>'用户错误'];
        }

        //2是作品表 6是讲座表
        switch (intval($team)) {
            case 1:
                $works_list = [
                    ['type' => 2, 'id' => 566],
                    ['type' => 6, 'id' => 379],
                    ['type' => 6, 'id' => 438],
                ];
                break;
            default:
                $works_list = [];
        }

        if (empty($works_list)) {
            return ['code' => false, 'msg' => '课程信息错误'];
        }

        $add_data = [];
        $now_date = date('Y-m-d H:i:s');
        $end_date = date('Y-m-d 23:59:59', strtotime('+10 years'));

        $update_res = true;
        DB::beginTransaction();

        foreach ($user_id as $v) {
            foreach ($works_list as $wlv) {
                $check = Subscribe::where('user_id', '=', $v)
                    ->where('relation_id', '=',$wlv['id'])
                    ->where('type', '=', $wlv['type'])
                    ->where('status', '=', 1)
                    ->where('end_time', '>=', $now_date)
                    ->first();

                if (empty($check)){
                    $temp_data = [];
                    $temp_data['type'] = $wlv['type'];
                    $temp_data['user_id'] = $v;
                    $temp_data['relation_id'] = $wlv['id'];
                    $temp_data['pay_time'] = $now_date;
                    $temp_data['start_time'] = $now_date;
                    $temp_data['end_time'] = $end_date;
                    $temp_data['status'] = 1;
                    $temp_data['give'] = 3;
                    $add_data[] = $temp_data;
                }else{
//                    $temp_update_res = Subscribe::whereId($check->id)
//                        ->update([
//                            'end_time'=>date('Y-m-d 23:59:59',strtotime("$check->end_time +1 years")),
//                        ]);
//                    if ($temp_update_res === false){
//                        $update_res = false;
//                    }
                }
            }
        }
        if (empty($add_data)){
            $add_res = true;
        }else{
            $add_res = DB::table('nlsg_subscribe')->insert($add_data);
        }

        if ($add_res){
            DB::commit();
            return ['code'=>true,'msg'=>'成功'];
        }else{
            DB::rollBack();
            return ['code'=>false,'msg'=>'失败'];
        }
    }

}
