<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class MallOrderDetails extends Base {

    protected $table = 'nlsg_mall_order_detail';

    public function goodsInfo() {
        return $this->hasOne('App\Models\MallGoods', 'id', 'goods_id')
                        ->select(['name', 'subtitle', 'picture', 'id']);
    }

    public function skuInfo()
    {
        return $this->hasOne('App\Models\MallSku', 'sku_number', 'sku_number')
            ->select(['id','sku_number','picture','stock','status','erp_goods_no','erp_spec_no']);
    }

}
