<?php

namespace App\Models\DouDian;

use App\Models\Base;

class DouDianOrderStatus extends Base
{
    protected $table = 'nlsg_dou_dian_order_status';

    protected $fillable = [
        'type','key','value'
    ];
}
