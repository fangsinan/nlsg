<?php


namespace App\Servers;

use Illuminate\Support\Facades\Cache;

class CacheServers
{
    /**
     * @param $type (1:商品缓存)
     */
    public static function clear($type)
    {
        switch (intval($type)) {
            case 1:
                self::clearGoods();
                break;
        }
    }

    //添加编辑商品的缓存
    protected static function clearGoods()
    {
        Cache::tags(['goods_list', 'goods_sp_list_1', 'goods_price'])->flush();
        Cache::forget('home_group_list');
        Cache::forget('set_kill_list');
    }


    //coupon_rule_list  商品优惠券的缓存

}
