<?php
namespace App\Models\Bliss;

use App\Models\Base;
use App\Models\Order;
use App\Models\User;

/**
 * 收益消息
 */
class ProfitModel extends Base
{
    const DB_TABLE = 'xfxs_profit';
    protected $table = 'xfxs_profit';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }
}
