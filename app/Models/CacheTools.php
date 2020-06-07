<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;

use Illuminate\Support\Facades\DB;

/**
 * Description of CacheTools
 *
 * @author wangxh
 */
class CacheTools {

    //获取缓存时间  单位秒
    public static function getExpire($flag) {
        $sort_exprice = 10;
        $normal_exprice = 10;
        $long__exprice = 86400;
        switch (strtolower($flag)) {
            case 'goods_sp_list_exprie':
            case 'sku_value':
            case 'get_list':
                $exprie = $normal_exprice;
                break;
            case 'mall_comment_list':
            case 'coupon_rule_list':
            case 'goods_price_exprie':
                $exprie = $sort_exprice;
                break;
            default :
                $exprie = 0;
        }

        return $exprie;
    }

}
