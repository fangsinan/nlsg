<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

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
            $ten_expire = 600;
            $long_expire = 86400;
        } else {
            $sort_expire = 60;
            $normal_expire = 120;
            $ten_expire = 600;
            $long_expire = 3600;
        }

        switch (strtolower($flag)) {
            case 'goods_sp_list_expire':
            case 'sku_value':
            case 'get_list':
            case 'mall_banner_list':
            case 'home_group_list':
            case 'order_token':
            case 'works_index':
                $expire = $normal_expire;
                break;
            case 'mall_comment_list':
            case 'coupon_rule_list':
            case 'goods_price_expire':
            case 'set_kill_list':
            case 'post_info':
            case 'fyr_list':
            case 'vip_works_list':
            case 'channel_works_list':
            case 'index_recommend_live':
                $expire = $sort_expire;
                break;
            case 'goods_category_list':
            case 'area_list':
            case 'freight_template_list':
            case 'freight_template':
            case 'index_works_category':
                $expire = $long_expire;
                break;

            case 'index_recommend':
            case 'index_rank_data':
            case 'live_index_list':
                $expire = $ten_expire;
                break;
            default :
                $expire = 0;
        }

        return $expire;
    }

    /**
     * 订单令牌
     * @param $uid
     * @param $order_type 订单类型 1普通2秒杀3拼团
     * @param $flag 操作:set,check,del
     * @param string $key
     * @return int|string
     */
    public static function orderToken($uid, $order_type, $flag, $key = '')
    {
        $cache_key_name = 'order_token';
        switch ($flag) {
            case 'set':
                $expire_num = CacheTools::getExpire($cache_key_name);
                $cache_name = $uid . Str::random(16) . $order_type;
                Cache::tags($cache_key_name)->put($cache_name, 1, $expire_num);
                return $cache_name;
            case 'check':
                if (empty($key)) {
                    return 0;
                }
                $check = Cache::tags($cache_key_name)->has($key);
                if ($check) {
                    return 1;
                } else {
                    return 0;
                }
                break;
            case 'del':
                Cache::tags($cache_key_name)->forget($key);
                return 1;
            default:
                return 0;
        }

    }
}
