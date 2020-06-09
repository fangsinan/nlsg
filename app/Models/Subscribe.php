<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Subscribe extends Authenticatable
{
    protected $table = 'nlsg_subscribe';

    /**
     * $user_id  登录者用户
     * $target_id  目标id  1为专栏的老师id  2作品id ....
     * type 1 专栏  2作品 3直播  4会员 5线下产品
     * */
    static function isSubscribe($user_id=0,$target_id=0,$type=0){
        $is_sub = 0;

        //会员都免费
        $level = User::getLevel($user_id);
        if($level) return 1;

        if($user_id && $target_id && $type ){
            $where = ['type' => $type, 'user_id' => $user_id,];
            //处理专栏的关注信息
//            if($type == 1){
//                $where['column_id'] = $target_id;
//            }else if($type == 2){
//                $where['works_id'] = $target_id;
//            }else if($type == 3){
//                $where['live_id'] = $target_id;
//            }else{
//                //type 类型错误直接返回0
//                return 0;
//            }
            if( !in_array($type,[1,2,3]) ){
                return 0;
            }

            $where['relation_id'] = $target_id;
            $sub_data = Subscribe::where($where)->first();
            if( $sub_data ){
                $is_sub = 1;
            }
        }
        return $is_sub;
    }

}
