<?php


namespace App\Servers;

use Illuminate\Support\Facades\Cache;
use Predis\Client;

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
            case 2:
                self::clearWorks();
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

    protected static function clearWorks(){
        Cache::forget('vip_works_list');
    }

    //coupon_rule_list  商品优惠券的缓存

    //获取微信的access_token
    public static function wechatAccessToken(){
        $redis_config  = [
            'url' => '8.140.167.113',
            'host' => '8.140.167.113',
            'password' => 'NLSG2020*beijin*0906BJ',
            'port' => '6379',
            'database' => 0,
        ];
        $Redis = new Client($redis_config);
        $Redis->select(1);

        return $Redis->get('swoole_wechat_access_token');
    }
}
