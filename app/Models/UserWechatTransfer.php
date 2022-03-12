<?php
/**
 * Created by PhpStorm.
 * User: nlsg2017
 * Date: 2019/6/25
 * Time: 2:04 PM
 */


namespace App\Models;

class UserWechatTransfer extends Base
{
    const DB_TABLE = 'nlsg_user_wechat_transfer';
    protected $table = 'nlsg_user_wechat_transfer';

    const STATUS_FINISH=1;//已完成
    const STATUS_WAIT=2; //等待接替

}
