<?php

namespace App\Models\Xfxs;

use App\Models\Base;

class XfxsOrder extends Base
{
    const DB_TABLE = 'xfxs_order';
    protected $table = 'xfxs_order';

    protected $fillable = [
        'id', 'live_num', 'pay_type', 'activity_tag', 'kun_said', 'refund_no', 'is_live_order_send',
        'ordernum', 'status', 'type', 'user_id', 'relation_id', 'cost_price', 'price', 'twitter_id', 'coupon_id', 'ip',
        'os_type', 'live_id', 'reward_type', 'reward', 'service_id', 'reward_num', 'pay_time', 'start_time', 'end_time',
        'pay_price', 'city', 'vip_order_type', 'send_type', 'send_user_id', 'remark', 'sales_id', 'sales_bind_id','protect_user_id','live_admin_id',
    ];
}
