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
class CacheTools
{

    //获取缓存时间  单位秒
    public static function getExpire($flag)
    {
        if (0) {
            $sort_expire = 300;
            $normal_expire = 3600;
            $long_expire = 86400;
        } else {
            $sort_expire = 60;
            $normal_expire = 120;
            $long_expire = 180;
        }

        switch (strtolower($flag)) {
            case 'goods_sp_list_expire':
            case 'sku_value':
            case 'get_list':
            case 'mall_banner_list':
            case 'home_group_list':
            case 'mall_order_token':
                $expire = $normal_expire;
                break;
            case 'mall_comment_list':
            case 'coupon_rule_list':
            case 'goods_price_expire':
            case 'set_kill_list':
            case 'post_info':
                $expire = $sort_expire;
                break;
            case 'goods_category_list':
            case 'area_list':
            case 'freight_template_list':
            case 'freight_template':
                $expire = $long_expire;
                break;
            default :
                $expire = 0;
        }

        return $expire;
    }
}
