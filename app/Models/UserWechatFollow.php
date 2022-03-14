<?php
/**
 * Created by PhpStorm.
 * User: nlsg2017
 * Date: 2019/6/25
 * Time: 2:04 PM
 */


namespace App\Models;

class UserWechatFollow extends Base
{
    const DB_TABLE = 'nlsg_user_wechat_follow';
    protected $table = 'nlsg_user_wechat_follow';

    const STATUS_ING=1;//跟进中
    const STATUS_END=2;//跟进结束

}
