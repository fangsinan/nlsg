<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Description of MallCategory
 *
 * @author wangxh
 */
class OrderZero extends Base
{

    protected $table = 'nlsg_order_zero';

    protected $fillable = [
        'id', 'relation_id','user_id','status','pay_time', 'start_time', 'end_time',
        'ordernum', 'ip', 'pay_type',  'os_type','twitter_id', 'remark', 'live_admin_id',
    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function twitter()
    {
        return $this->belongsTo(User::class, 'twitter_id', 'id');
    }

    public function relationLiveInfo(): HasOne
    {
        return $this->hasOne(Live::class,'id','relation_id');
    }

    public function fromLiveInfo(): HasOne
    {
        return $this->hasOne(Live::class,'id','live_id');
    }
}
