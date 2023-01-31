<?php
namespace App\Models\Bliss;

use App\Models\Base;
use App\Models\Order;
use App\Models\User;

/**
 * 订单表
 */
class OrderModel extends Base
{
    const DB_TABLE = 'xfxs_order';
    protected $table = 'xfxs_order';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

}
