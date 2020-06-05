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
        switch (strtolower($flag)) {
            case 'goods_price_exprie':
                $exprie = 10;
                break;
            case 'goods_sp_list_exprie':
                $exprie = 10;
                break;
            case 'coupon_rule_list':
                $exprie = 10;
                break;
            case 'mall_comment_list':
                $exprie = 10;
                break;
            case 'sku_value':
                $exprie = 3600;
                break;
            default :
                $exprie = 0;
        }

        return $exprie;
    }

    public static function getLastSqlBegin() {
        return DB::connection()->enableQueryLog();
    }

    public static function getLastSql() {
        return DB::getQueryLog();
    }

}
