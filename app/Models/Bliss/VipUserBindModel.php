<?php
namespace App\Models\Bliss;

use App\Models\Base;
use App\Models\Order;
use App\Models\User;

/**
 * 新会员关系保护数据
 */
class VipUserBindModel extends Base
{
    const DB_TABLE = 'nlsg_vip_user_bind';
    protected $table = 'nlsg_vip_user_bind';

}
