<?php

namespace App\Models;

use App\Models\Xfxs\XfxsOrder;

class XfxsOrderErpList  extends Base
{
    protected $table = 'xfxs_order_erp_list';

    protected $fillable = [
        'order_id', 'flag', 'created_at', 'updated_at'
    ];

    public function orderInfo() {
        return $this->hasOne(XfxsOrder::class, 'id', 'order_id');
    }

}
