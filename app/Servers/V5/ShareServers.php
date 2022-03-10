<?php

namespace App\Servers\V5;

use EasyWeChat\Factory;
use Symfony\Component\Cache\Adapter\RedisAdapter;

class ShareServers
{
 // 更新access_token缓存
 public static function SetTicket(){
    
    $app = Factory::officialAccount([
        'app_id' => 'wxe24a425adb5102f6',
        'secret' => '2ded804b74f99ae2f342423dd7952620',
        'response_type' => 'array',
        'cache' => "redis"
    ]);
    // 创建缓存实例
    $cache = new RedisAdapter(app('redis')->connection()->client());
    $app->rebind('cache', $cache);
    $app->jssdk->getTicket();
}

}
