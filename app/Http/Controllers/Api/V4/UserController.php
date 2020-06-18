<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use GuzzleHttp\Client;

class UserController extends Controller
{
    /**
     * @api {post} api/v4/user/sendSms 发送验证码
     * @apiVersion 1.0.0
     * @apiName  sendEms
     * @apiGroup User
     *
     * @apiParam {string} phone 手机号
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     *{
     * "code": 200,
     * "msg": ok,
     * "result": { }
     * }
     */

    public function sendSms(Request $request)
    {
        $phone = $request->input('phone');
        if ( ! $phone) {
            return $this->error(400, '手机号不能为空');
        }

        $easySms = app('easysms');
        try {

            $code = rand(1000, 9999);
//            $result   = $easySms->send( $phone, [
//                'template' => 'SMS_70300075',
//                'data'     => [
//                    'code' => $code,
//                ],
//            ], ['aliyun'] );

            Redis::setex($phone, 60, $code);
        } catch (\Overtrue\EasySms\Exceptions\NoGatewayAvailableException $exception) {
            $message = $exception->getResults();
            return $message;
        }

    }

    public function login(Request $request)
    {
        $phone = $request->input('phone');
        $code = $request->input('code');

        if ( ! $phone) {
            return $this->error(400, '手机号不能为空');
        }
        if ( ! $code) {
            return $this->error(400, '验证码不能为空');
        }

        $res = Redis::get($phone);
        if ( ! $res) {
            return $this->error(400, '验证码已过期');
        }

        if ($code !== $res) {
            return $this->error(400, '验证码错误');
        }
    }

    /**
     * 微信授权
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function wechat(Request $request)
    {
        $code = $request->input('code');
        if (!$code){
            return $this->error(1000, 'code不能为空');
        }

        $res = $this->getRequest('https://api.weixin.qq.com/sns/oauth2/access_token', [
            'appid'    => env('WECHAT_OFFICIAL_ACCOUNT_APPID'),
            'secret'   => env('WECHAT_OFFICIAL_ACCOUNT_SECRET'),
            'code'     => $code,
            'grant_type' => 'authorization_code'
        ]);

        $user = $this->getRequest('https://api.weixin.qq.com/sns/userinfo', [
            'access_token' => $res['access_token'],
            'openid'       => $res['openid'],
        ]);


    }

    /**
     * 处理微信返回的数据
     * @param $url
     * @param $query
     * @return bool|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public  function getRequest($url, $query)
    {
        $client = new Client();
        $res = $client->request('GET', $url,[
            'query' => $query
        ]);
        if($res->getStatusCode()!=200){
            return false;
        }

        return json_decode($res->getBody()->getContents());
    }






}
