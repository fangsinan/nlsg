<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;

use Illuminate\Support\Facades\DB;

/**
 * Description of CouponRuleSub
 *
 * @author wangxh
 */
class CouponRuleSub extends Base {

    protected $table = 'nlsg_coupon_rule_sub';

    public function goods_list() {
        return $this->hasOne('App\Models\MallGoods', 'id', 'goods_id')
                        ->where('status', '=', 2)
                        ->select(['id', 'name']);
    }

}
