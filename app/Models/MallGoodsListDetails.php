<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

/**
 * Description of MallGoodsListDetails
 *
 * @author wangxh
 */
class MallGoodsListDetails extends Base {
    protected $fillable = ['list_id','goods_id'];
    protected $table = 'nlsg_mall_goods_list_details';

}
