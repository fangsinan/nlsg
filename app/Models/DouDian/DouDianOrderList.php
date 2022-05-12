<?php

namespace App\Models\DouDian;

use App\Models\Base;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DouDianOrderList extends Base
{

    protected $table = 'nlsg_dou_dian_order_list';

    protected $fillable = [
        'order_id', 'parent_order_id', 'create_time', 'update_time', 'sku_id',
        'product_id', 'goods_type', 'out_sku_id', 'supplier_id',
        'item_num', 'receive_type',
        'after_sale_info_status', 'after_sale_info_type', 'after_sale_info_refund_status'
    ];

    public function productInfo(): HasOne {
        return $this->hasOne(DouDianProductList::class, 'product_id', 'product_id');
    }

    public function skuInfo(): HasOne {
        return $this->hasOne(DouDianSkuList::class, 'id', 'sku_id');
    }

}
