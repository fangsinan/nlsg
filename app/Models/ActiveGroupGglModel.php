<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ActiveGroupGglModel extends Base
{

    protected $table = 'nlsg_active_group_goods_lit';

    public function bindingGoodsInfo()
    {
        return $this->hasOne('App\Models\MallGoods', 'id', 'goods_id')
            ->select(['id','name','subtitle','picture','original_price','price','status']);
    }
}
