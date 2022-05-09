<?php

namespace App\Models;

class DouDianOrderList extends Base
{

    protected $table = 'nlsg_dou_dian_order_list';

    protected $fillable = [
        'order_id', 'parent_order_id', 'create_time', 'update_time', 'sku_id',
        'product_id', 'goods_type', 'out_sku_id', 'supplier_id',
        'item_num', 'receive_type',
    ];

}
