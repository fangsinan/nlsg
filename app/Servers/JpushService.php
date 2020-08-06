<?php

namespace App\Servers;

use JPush\Client as JPushClient;

class JpushService
{
    protected $appkey;
    protected $secret;

    //初始化
    public function __construct()
    {
        $this->appkey = config('jpush.app_key');
        $this->secret = config('jpush.master_secret');
    }

    /**
     * 给特定用户发通知
     * @param $title  标题
     * @param $content  内容
     * @param $alias  别名
     * @param  array  $params  扩展字段
     * @param  string  $ios_badge  ios 角标数
     * @param  array  $platform  推送设备
     * @return array|bool
     */
    public function pushUser($regid, $alert, array $notification = [])
    {

        // 初始化
        $client = new JPushClient($this->appkey, $this->secret);
        $payload = $client->push()
            ->setPlatform(['ios', 'android'])
            ->addAllAudience()
            //                ->addRegistrationId($regid)
            ->addAlias($regid)
            ->iosNotification($alert, $notification)
            ->androidNotification($alert, $notification)
            ->options([
                'apns_production' => false
            ]);

        try {
            $payload->send();
            Log::channel('jpush')->info('推送成功');
        } catch (\JPush\Exceptions\APIConnectionException $e) {
            // try something here
            Log::channel('jpush')->info('连接失败，:'.$e->getMessage());
        } catch (\JPush\Exceptions\APIRequestException $e) {
            // try something here
            Log::channel('jpush')->info('推送失败，:'.$e->getMessage());
        }


    }

    /**
     * 给全部用户发通知
     * @param $title  标题
     * @param $content  内容
     * @return array|bool
     */
    public function pushAllUser($alert, array $notification = [])
    {

        $client = new JPushClient($this->appkey, $this->secret);
        $payload = $client->push()
            ->addAllAudience()
            ->setPlatform('all')
            ->message($alert, $notification)
            ->iosNotification($alert, $notification)
            ->androidNotification($alert, $notification)
            ->options([
                'apns_production' => false
            ]);

        try {
            $payload->send();
            Log::channel('jpush')->info('推送成功');
        } catch (\JPush\Exceptions\APIConnectionException $e) {
            Log::channel('jpush')->info('连接失败，原因:'.$e->getMessage());
        } catch (\JPush\Exceptions\APIRequestException $e) {
            Log::channel('jpush')->info('推送失败，原因:'.$e->getMessage());
        }

    }

}
