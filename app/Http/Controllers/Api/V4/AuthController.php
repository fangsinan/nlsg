<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class AuthController extends Controller
{

    /**
     * @api {get} api/v4/user/login 登录
     * @apiVersion 4.0.0
     * @apiName  phone 手机号
     * @apiName  code  验证码
     * @apiGroup Api
     *
     * @apiSuccess {String} token   token
     *
     * @apiSuccessExample 成功响应:
     *   {
     *      "code": 200,
     *      "msg" : '成功',
     *      "data": {
     *          'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ'
     *       }
     *   }
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
        return $this->success($data);
    }

    /**
     * @api {get} api/v4/auth/logout 退出
     * @apiVersion 4.0.0
     * @apiGroup Api
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
    public function logout()
    {
        auth('api')->logout();
        return $this->success();
    }

    /**
     * @api {get} api/v4/auth/wechat 微信授权
     * @apiVersion 4.0.0
     * @apiName  code  授权码
     * @apiGroup Api
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
            'appid' => env('WECHAT_OFFICIAL_ACCOUNT_APPID'),
            'secret' => env('WECHAT_OFFICIAL_ACCOUNT_SECRET'),
            'code' => $code,
            'grant_type' => 'authorization_code'
        ]);
        if ( ! $res) {
            return $this->error(401, 授权失败);
        }
        $list = $this->getRequest('https://api.weixin.qq.com/sns/userinfo', [
            'access_token' => $res['access_token'],
            'openid' => $res['openid'],
        ]);
        if ( ! $list) {
            return $this->error(400, '获取用户信息失败');
        }

        $unionid = $request->input('unionid');
        $user = User::where('unionid', $unionid)->first();
        if ( ! $user) {
            $user = User::create([
                'nickname' => '微信',
                'sex' => '1',
                'province' => '北京市',
                'city' => '海淀区',
                'headimg' => '/wechat/works/headimg/3833/2017110823004219451.png'
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
     * @api {post} api/v4/user/sendSms 发送验证码
     * @apiVersion 4.0.0
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
            $result   = $easySms->send( $phone, [
                'template' => 'SMS_70300075',
                'data'     => [
                    'code' => $code,
                ],
            ], ['aliyun'] );

            Redis::setex($phone, 60, $code);
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
