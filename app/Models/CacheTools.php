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
        $sort_exprice = 300;
        $normal_exprice = 3600;
        $long_exprice = 86400;
        switch (strtolower($flag)) {
            case 'goods_sp_list_exprie':
            case 'sku_value':
            case 'get_list':
            case 'mall_banner_list':
            case 'home_group_list':
                $exprie = $normal_exprice;
                break;
            case 'mall_comment_list':
            case 'coupon_rule_list':
            case 'goods_price_exprie':
            case 'set_kill_list':
                $exprie = $sort_exprice;
                break;
            case 'goods_category_list':
            case 'area_list':
            case 'freight_template_list':
                $exprie = $long_exprice;
                break;
            default :
                $exprie = 0;
        }

        return $exprie;
    }

    //coupon_rule_list 优惠券规则列表
}
