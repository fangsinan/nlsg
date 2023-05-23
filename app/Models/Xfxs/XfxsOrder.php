<?php

namespace App\Models\Xfxs;

use App\Models\Base;
use App\Models\MallAddress;
use App\Models\Textbook;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class XfxsOrder extends Base
{
    const DB_TABLE = 'xfxs_order';
    protected $table = 'xfxs_order';

    protected $fillable = [
        'type', 'vip_order_type', 'relation_id', 'live_id', 'user_id', 'status', 'pay_time',
        'cost_price', 'price', 'coupon_id', 'start_time', 'cancel_time', 'close_time', 'end_time',
        'ordernum', 'pay_price', 'city', 'ip', 'pay_type', 'os_type', 'test', 'share_code',
        'twitter_id', 'bind_twitter_id', 'live_twitter_id', 'activity_tag', 'kun_said',
        'send_type', 'send_user_id', 'reward', 'reward_num', 'reward_type', 'service_id',
        'is_shill', 'shill_job_price', 'shill_refund_sum', 'is_refund', 'refund_no',
        'refund_at', 'is_live_order_send', 'remark', 'profit_ordernum', 'wx_profit_ordernum',
        'sales_id', 'sales_bind_id', 'is_ascription', 'ascription_time', 'is_deal', 'pay_check',
        'express_info_id', 'textbook_id', 'address_id', 'protect_user_id', 'channel_show',
        'can_refund', 'full_payment', 'offline_status', 'live_admin_id',
    ];

    public function addressInfo(): HasOne
    {
        return $this->hasOne(MallAddress::class,'id','address_id');
    }
    public function textbookInfo(): HasOne
    {
        return $this->hasOne(Textbook::class,'id','textbook_id');
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
