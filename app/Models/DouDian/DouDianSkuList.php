<?php

namespace App\Models\DouDian;

use App\Models\Base;

class DouDianSkuList extends Base
{
    protected $table = 'nlsg_dou_dian_sku_list';

    protected $fillable = [
        'id', 'product_id',
        'spec_detail_id1', 'spec_detail_id2', 'spec_detail_id3',
        'spec_detail_name1', 'spec_detail_name2', 'spec_detail_name3',
        'price', 'settlement_price', 'spec_id', 'create_time', 'update_time',
    ];
}
