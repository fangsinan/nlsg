<?php

namespace App\Servers\V5;

use App\Models\WorksInfo;
use App\Servers\LiveConsoleServers;
use EasyWeChat\Factory;
use Predis\Client;
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

    /**
     * 获取token校验信息
     * access_token是公众号的全局唯一接口调用凭据
     * access_token的存储至少要保留512个字符空间。access_token的有效期目前为2个小时  刷新时公众平台后台会保证在5分钟内，新老access_token都可用
     * 此token和获取用户信息token不是同一个，此token用于调用其他接口如分享接口等   用户信息token用于处理支付
     * @return mixed
     */
    public static function SetAccessToken()
    {

        try {

            $redisConfig = config('database.redis.default');
            $Redis = new Client($redisConfig);
            $Redis->select(2);

            $app_id = 'wxe24a425adb5102f6';
            $app_secret = '2ded804b74f99ae2f342423dd7952620';
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $app_id . '&secret=' . $app_secret;

            $rst = WorksInfo::curlPost($url);

            if ($rst) {
                $rstJson = json_decode($rst);

                if (isset($rstJson->access_token)) {
                    //写入redis
                    $Redis->setex('crontab_wechat_access_token',7200,$rstJson->access_token);//设置redis缓存
                    LiveConsoleServers::LogIo('accesstoken','wechat','1');
                } else {
                    //写入报错日志
                    LiveConsoleServers::LogIo('accesstoken','wechat_error','WechatToken：access_token null');
                }
            }
        } catch (\Exception $e) {
            LiveConsoleServers::LogIo('accesstoken','wechat_error','WechatToken：' . $e->getMessage());
        }


    }

}
