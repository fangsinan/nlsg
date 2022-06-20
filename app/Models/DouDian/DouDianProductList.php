<?php

namespace App\Models\DouDian;

use App\Models\Base;

class DouDianProductList extends Base
{
    protected $table = 'nlsg_dou_dian_product_list';

    protected $fillable = [
        'product_id', 'status', 'check_status', 'market_price', 'discount_price',
        'img', 'name', 'create_time', 'update_time', 'out_product_id','dou_dian_type',
    ];

}
