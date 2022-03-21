<?php
/**
 * Created by PhpStorm.
 * User: nlsg2017
 * Date: 2019/6/25
 * Time: 2:04 PM
 */


namespace App\Models;

class UserWechatTransferLog extends Base
{
    const DB_TABLE = 'nlsg_user_wechat_transfer_log';
    protected $table = 'nlsg_user_wechat_transfer_log';


    public function handover_user(){
        return $this->belongsTo(UserWechatName::class, 'handover_userid', 'follow_user_userid');
    }

    public function takeover_user(){
        return $this->belongsTo(UserWechatName::class, 'takeover_userid', 'follow_user_userid');
    }

}
