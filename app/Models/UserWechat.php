<?php
/**
 * Created by PhpStorm.
 * User: nlsg2017
 * Date: 2019/6/25
 * Time: 2:04 PM
 */


namespace App\Models;

class UserWechat extends Base
{
    const DB_TABLE = 'nlsg_user_wechat';
    protected $table = 'nlsg_user_wechat';

    const TRANSFER_STATUS_FAIL=-1;//分配失败
    const TRANSFER_STATUS_FINISH=1;//接替完毕
    const TRANSFER_STATUS_WAIT=2; //等待接替

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id')
//            ->where('unionid','<>','')
            ;
    }

    public function source_staff(){
        return $this->belongsTo(UserWechatName::class, 'source_follow_user_userid', 'follow_user_userid');
    }


    public function follow_staff(){
        return $this->belongsTo(UserWechatName::class, 'follow_user_userid', 'follow_user_userid');
    }


    public function user_orders(){
        return $this->hasMany(Order::class,'user_id','user_id')
            ->whereIn('relation_id',[5,6,7])
            ->where('type',14)
            ->where('status',1);
    }

}
