<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class OrderRefundLog extends Base
{

    protected $table = 'nlsg_order_refund_log';

    public function orderChild()
    {
        return $this->hasOne('App\Models\Order', 'ordernum', 'ordernum');
    }
}
