<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasOne;

class OfflineProductsOrderLog extends Base
{
    protected $table = 'nlsg_offline_products_order_log';

    protected $fillable = [
        'admin_id',
        'order_id',
        'remark',
        'log_date',
        'full_payment',
        'offline_status',
    ];


    public function adminInfo(): HasOne
    {
        return $this->hasOne(BackendUser::class, 'id', 'admin_id');
    }

}
