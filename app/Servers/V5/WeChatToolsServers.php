<?php

namespace App\Servers\V5;

use App\Models\ConfigModel;
use App\Models\XcxUrlLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class WeChatToolsServers
{
    //小程序
    const XCX_URL               = 'https://api.weixin.qq.com/cgi-bin/token';
    const XCX_SHOP_URL_LINK_URL = 'https://api.weixin.qq.com/wxa/generate_urllink';
    private $xcx_shop_access_token;
    private $xcx_access_token;

    public function getUrlLink($params)
    {
        $flag       = $params['flag'] ?? '';
        $product_id = $params['productId'] ?? '';
        switch ($flag) {
            case 'shop':
                if (empty($product_id) || !is_numeric($product_id)) {
                    $post_params = [
                        'path'  => '',
                        'query' => '',
                    ];
                } else {
                    $post_params = [
                        'path'  => '__plugin__/wx34345ae5855f892d/pages/productDetail/productDetail',
                        'query' => 'productId=' . intval($product_id),
                    ];
                }
                $res = Http::post(
                    self::XCX_SHOP_URL_LINK_URL . '?access_token=' . $this->xcx_shop_access_token,
                    $post_params
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
            case 'xcx':
                if (empty($product_id) || !is_numeric($product_id)) {
                    $post_params = [
                        'path'  => '',
                        'query' => '',
                    ];
                } else {
                    $post_params = [
                        'path'  => '/pages/webview/webview',
                        'query' => '',
                    ];
                }
                $res = Http::post(
                    self::XCX_SHOP_URL_LINK_URL . '?access_token=' . $this->xcx_access_token,
                    $post_params
                );
                $res = json_decode($res, true);
                if ($res['errcode'] !== 0) {
                    return ['code' => false, 'msg' => '失败,请重试.'];
                }
                //如果存在则counts+1 否则创建
                XcxUrlLog::query()->updateOrCreate(
                    ['type' => 2, 'date' => date('Y-m-d')],
                    ['counts' => DB::raw('counts+1')]
                );
                return $res;
            default:
                return ['code' => false, 'msg' => '参数错误'];
        }
    }

    public function __construct()
    {

        $this->xcx_access_token      = $this->XcxAccessTokenJob();
        $this->xcx_shop_access_token = $this->XcxShopAccessTokenJob();
    }

    public function XcxAccessTokenJob()
    {
        $data = ConfigModel::getData(74, 1);
        if (empty($data)) {
            $token      = '';
            $expires_in = 0;
        } else {
            $data       = json_decode($data, true);
            $token      = $data['token'];
            $expires_in = $data['expires_in'];
        }
        $now = time();
        if (empty($token) || ($expires_in - 600) < $now) {

            $job_flag = ConfigModel::getData(75, 1);
            if ($job_flag !== '1') {
                return '';
            }
            //刷新
            $get_token  = $this->getXcxAccessToken();
            $token      = $get_token['access_token'];
            $expires_in = $get_token['expires_in'] + $now;

            ConfigModel::query()->where('id', '=', 74)
                ->update([
                             'value' => json_encode([
                                                        'token'           => $token,
                                                        'expires_in'      => $expires_in,
                                                        'expires_in_date' => date('Y-m-d H:i:s'),
                                                    ])
                         ]);
        }
        return $token;
    }

    public function XcxShopAccessTokenJob()
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
        if (empty($token) || ($expires_in - 600) < $now) {

            $job_flag = ConfigModel::getData(73, 1);
            if ($job_flag !== '1') {
                return '';
            }
            //刷新
            $get_token  = $this->getXcxShopAccessToken();
            $token      = $get_token['access_token'];
            $expires_in = $get_token['expires_in'] + $now;

            ConfigModel::query()->where('id', '=', 72)
                ->update([
                             'value' => json_encode([
                                                        'token'           => $token,
                                                        'expires_in'      => $expires_in,
                                                        'expires_in_date' => date('Y-m-d H:i:s'),
                                                    ])
                         ]);
        }
        return $token;
    }

    //微信小商店的token
    private function getXcxShopAccessToken()
    {
        $params = [
            'grant_type' => 'client_credential',
            'appid'      => config('env.XCX_SHOP_APPID'),
            'secret'     => config('env.XCX_SHOP_APPSECRET'),
        ];

        $res = Http::get(self::XCX_URL, $params);
        return json_decode($res, true);
    }

    //小程序的token
    private function getXcxAccessToken()
    {
        $params = [
            'grant_type' => 'client_credential',
            'appid'      => config('env.XCX_APPID'),
            'secret'     => config('env.XCX_APPSECRET'),
        ];

        $res = Http::get(self::XCX_URL, $params);
        return json_decode($res, true);
    }


}
