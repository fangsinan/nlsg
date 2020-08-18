<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use App\Models\User;

class AuthController extends Controller
{

    /**
     * @api {get} api/v4/auth/login  登录
     * @apiVersion 4.0.0
     * @apiName  login
     * @apiGroup Auth
     *
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/auth/login
     * @apiParam  {string} phone  手机号
     * @apiParam  {number} code   验证码
     * 
     * @apiSuccess {string} token token
     *
     * @apiSuccessExample  Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "code": 200,
     *       "msg" : '成功',
     *       "data": {
     *       "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9 .eyJpc3MiOiJodHRwOlwvXC92NC5jb21cL2FwaVwvdjRcL2F1dGhcL2xvZ2luIiwiaWF0IjoxNTk0ODgzMzk4LCJleHAiOjE1OTQ4ODY5OTgsIm5iZiI6MTU5NDg4MzM5OCwianRpIjoic1FKYnFnRU5UM0hRYWJjSyIsInN1YiI6MSwicHJ2IjoiMjNiZDVjODk0OWY2MDBhZGIzOWU3MDFjNDAwODcyZGI3YTU5NzZmNyJ9.ke8ARBD6p9Rv1yTnhQxjIvle_zFN5mI_zzTQUBhSgwI"
     *       }
     *     }
     *
     */

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

        $user = User::where('phone', $phone)->first();
        if ( ! $user) {
            $list = User::create([
                'phone' => $phone
            ]);
            $user = User::find($list->id);
        }
        $token = auth('api')->login($user);;
        $data = [
            'token' => $token
        ];
        return success($data);
    }

    /**
     * @api {get} api/v4/auth/logout 退出
     * @apiVersion 4.0.0
     * @apiName  logout
     * @apiGroup Auth
     *
     * @apiSuccessExample 成功响应:
     *   {
     *      "code": 200,
     *      "msg" : '成功',
     *      "data": {
     *
     *       }
     *   }
     *
     */
    public function logout()
    {
        auth('api')->logout();
        return success();
    }

    /**
     * @api {get} api/v4/auth/wechat 微信授权
     * @apiVersion 4.0.0
     * @apiName  wechat
     * @apiGroup Auth
     * @apiParam code 授权码
     *
     * @apiSuccess {String} token   token
     *
     * @apiSuccessExample 成功响应:
     *   {
     *      "code": 200,
     *      "msg" : '成功',
     *      "data": {
     *
     *       }
     *   }
     *
     */
    public function wechat(Request $request)
    {
        $code = $request->input('code');
        if ( ! $code) {
            return $this->error(1000, 'code不能为空');
        }

        $res = $this->getRequest('https://api.weixin.qq.com/sns/oauth2/access_token', [
            'appid'      => env('WECHAT_OFFICIAL_ACCOUNT_APPID'),
            'secret'     => env('WECHAT_OFFICIAL_ACCOUNT_SECRET'),
            'code'       => $code,
            'grant_type' => 'authorization_code'
        ]);
        if ( ! $res) {
            return $this->error(401, 授权失败);
        }
        $list = $this->getRequest('https://api.weixin.qq.com/sns/userinfo', [
            'access_token' => $res['access_token'],
            'openid'       => $res['openid'],
        ]);
        if ( ! $list) {
            return $this->error(400, '获取用户信息失败');
        }

        $unionid = $request->input('unionid');
        $user = User::where('unionid', $unionid)->first();
        if ( ! $user) {
            $user = User::create([
                'nickname' => '微信',
                'sex'      => '1',
                'province' => '北京市',
                'city'     => '海淀区',
                'headimg'  => '/wechat/works/headimg/3833/2017110823004219451.png'
            ]);
        }

        $token = auth('api')->login($user);;
        $data = [
            'nickname' => $user->nickname,
            'sex'      => $user->sex,
            'province' => $user->province,
            'city'     => $user->city,
            'token'    => $token
        ];
        return $this->success($data);

    }

    /**
     * @api {get} api/v4/auth/sms 发送验证码
     * @apiVersion 4.0.0
     * @apiName  sendSms
     * @apiGroup Auth
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/auth/sendsms
     *
     * @apiParam {number} phone 手机号
     * @apiSuccessExample  Success-Response:
     * HTTP/1.1 200 OK
     * {
     *   "code": 200,
     *   "msg" : '成功',
     *   "data": {
     *
     *    }
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
            $result = $easySms->send($phone, [
                'template' => 'SMS_70300075',
                'data'     => [
                    'code' => $code,
                ],
            ], ['aliyun']);

            Redis::setex($phone, 60*60*24, $code);
            return success();
        } catch (\Overtrue\EasySms\Exceptions\NoGatewayAvailableException $exception) {
            $message = $exception->getResults();
            return $message;
        }

    }

    /**
     * 处理微信返回的数据
     * @param $url
     * @param $query
     * @return bool|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getRequest($url, $query)
    {
        $client = new Client();
        $res = $client->request('GET', $url, [
            'query' => $query
        ]);
        if ($res->getStatusCode() != 200) {
            return false;
        }

        return json_decode($res->getBody()->getContents());
    }

}
