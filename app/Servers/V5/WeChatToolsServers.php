<?php

namespace App\Servers\V5;

use Illuminate\Support\Facades\Http;
use Predis\Client;

class WeChatToolsServers
{
    //小程序
    const XCX_APPID     = 'wxff27413cdd2db48c';
    const XCX_APPSECRET = '391b29e9f74e02f8ebb5a59bbf8cff4b';
    const XCX_URL       = 'https://api.weixin.qq.com/cgi-bin/token';
    private $xcx_access_token;

    public function getUrlLink($params)
    {
//        $this->XcxAccessTokenJob();

        dd($this->xcx_access_token);
    }

    public function __construct()
    {
        $this->xcx_access_token = $this->XcxAccessTokenJob();
    }

    public function XcxAccessTokenJob($flag = 0)
    {
        $key_name = 'xcx_access_token';

        $redis_config = [
            'url'      => 'r-2zeyu6aw5wozyw1ot2pd.redis.rds.aliyuncs.com',
            'host'     => 'r-2zeyu6aw5wozyw1ot2pd.redis.rds.aliyuncs.com',
            'password' => 'NLSG2020*beijin*0906BJ',
            'port'     => '6379',
//            'database' => 0,
        ];
        $Redis        = new Client($redis_config);
        $rr = $Redis->set($key_name, 'test',100);
        dd($rr);
//        if ($flag === 0) {
//            $token = $Redis->get($key_name);
//            if (!$token) {
//                $res   = $this->getXcxAccessToken();
//                $token = $res['access_token'];
//                $Redis->set($key_name, $token, $res['expires_in']);
//            }
//            return $token;
//        } else {
//            //刷新
//            $res   = $this->getXcxAccessToken();
//            $Redis->set($key_name, $res['access_token'], $res['expires_in']);
//            return true;
//        }
    }

    private function getXcxAccessToken()
    {
        $params = [
            'grant_type' => 'client_credential',
            'appid'      => self::XCX_APPID,
            'secret'     => self::XCX_APPSECRET,
        ];

        $res = Http::get(self::XCX_URL, $params);
        return json_decode($res, true);
    }


}
