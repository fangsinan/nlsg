<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

/**
 * Description of MallGoodsList
 *
 * @author wangxh
 */
class MallGoodsList extends Model {

    protected $table = 'nlsg_mall_goods_list';

    public function goods_list() {
        return $this
                ->hasMany('App\Models\MallGoodsListDetails', 'list_id', 'id')
                ->select(['list_id','goods_id']);
    }
}
