<?php

namespace App\Servers\V5;

use App\Models\ConfigModel;
use App\Models\XcxUrlLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class WeChatToolsServers
{
    //小程序
    const XCX_APPID             = 'wxff27413cdd2db48c';
    const XCX_APPSECRET         = '391b29e9f74e02f8ebb5a59bbf8cff4b';
    const XCX_URL               = 'https://api.weixin.qq.com/cgi-bin/token';
    const XCX_SHOP_URL_LINK_URL = 'https://api.weixin.qq.com/wxa/generate_urllink';
    private $xcx_access_token;

    public function getUrlLink($params)
    {
        $flag = $params['flag'] ?? '';
        switch ($flag) {
            case 'shop':
                $res = Http::post(
                    self::XCX_SHOP_URL_LINK_URL . '?access_token=' . $this->xcx_access_token,
                    [
                        'path'  => '',
                        'query' => '',
                    ]
                );
                $res = json_decode($res, true);
                if ($res['errcode'] !== 0) {
                    return ['code' => false, 'msg' => '失败,请重试.'];
                }
                //如果存在则counts+1 否则创建
                XcxUrlLog::query()->updateOrCreate(
                    ['type' => 1, 'date' => date('Y-m-d')],
                    ['counts' => DB::raw('counts+1')]
                );
                return $res;
            default:
                return ['code' => false, 'msg' => '参数错误'];
        }
    }

    public function __construct()
    {
        $this->xcx_access_token = $this->XcxAccessTokenJob();
    }

    public function XcxAccessTokenJob()
    {
        $data = ConfigModel::getData(72, 1);
        if (empty($data)) {
            $token      = '';
            $expires_in = 0;
        } else {
            $data       = json_decode($data, true);
            $token      = $data['token'];
            $expires_in = $data['expires_in'];
        }
        $now = time();

        if (empty($token) || $expires_in < ($now - 600)) {
            $job_flag = ConfigModel::getData(73, 1);
            if ($job_flag !== '1') {
                return '';
            }

            //刷新
            $get_token  = $this->getXcxAccessToken();
            $token      = $get_token['access_token'];
            $expires_in = $get_token['expires_in'] + $now;

            ConfigModel::query()->where('id', '=', 72)
                ->update([
                             'value' => json_encode([
                                                        'token'      => $token,
                                                        'expires_in' => $expires_in
                                                    ])
                         ]);
        }

        return $token;


//        $key_name = 'xcx_access_token';
//
//        $redis_config = [
//            'url'      => 'r-2zeyu6aw5wozyw1ot2pd.redis.rds.aliyuncs.com',
//            'host'     => 'r-2zeyu6aw5wozyw1ot2pd.redis.rds.aliyuncs.com',
//            'password' => 'NLSG2020*beijin*0906BJ',
//            'port'     => '6379',
////            'database' => 0,
//        ];
//        $Redis        = new Client($redis_config);
//        $rr = $Redis->set($key_name, 'test',100);
//        dd($rr);
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
