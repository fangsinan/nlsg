<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;

use Illuminate\Support\Facades\DB;

/**
 * Description of MallOrderChild
 *
 * @author wangxh
 */
class MallOrderChild extends Base {

    protected $table = 'nlsg_mall_order_child';

    public function expressInfo() {
        return $this->hasOne('App\Models\ExpressInfo', 'id', 'express_info_id')
                        ->select(['id', 'history','express_id']);
    }

}
