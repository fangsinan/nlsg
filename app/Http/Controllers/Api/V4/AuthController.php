<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use App\Models\User;
use AppleSignIn\ASDecoder;
use GuzzleHttp\Client;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Ecdsa\Sha256;
use Lcobucci\JWT\Signer\Key;
use function GuzzleHttp\headers_from_lines;

class AuthController extends Controller
{

    /**
     * @api {POST} api/v4/auth/login  登录
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
        $inviter = $request->input('inviter', 0);

        if (!$phone) {
            return $this->error(400, '手机号不能为空');
        }
        if (!$code) {
            return $this->error(400, '验证码不能为空');
        }

        $res = Redis::get($phone);
        if (!$res) {
            return $this->error(400, '验证码已过期');
        }

        if ($code !== $res) {
            return $this->error(400, '验证码错误');
        }

        $user = User::where('phone', $phone)->first();

        if (!$user) {
            $list = User::create([
                'phone' => $phone,
                'inviter' => $inviter,
                'login_flag' => ($inviter == 0) ? 0 : 1,
                'nickname' => substr_replace($phone, '****', 3, 4)
            ]);
            $user = User::find($list->id);
        } else {
            if ($user->login_flag == 1) {
                User::where('id', '=', $user->id)->update(['login_flag' => 2]);
            }
        }

        Redis::del($phone);
        $token = auth('api')->login($user);
        $data = [
            'id' => $user->id,
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

    public function wechat(Request $request)
    {
        $input = $request->all();
        $user = User::where('unionid', $input['unionid'])->first();
        if (!$user) {
            return  error(1000, '微信还未绑定', (object)[]);
        }

        $token = auth('api')->login($user);
        $data = [
            'id'    => $user->id,
            'phone' => $user->phone ?? '',
            'token' => $token
        ];
        return success($data);
    }

    /**
     *  微信登录开关
     */
    public function switch()
    {
        $data = [
            'wechat' => 1
        ];
        return success($data);

    }


    /**
     * 绑定手机号
     *
     */
    public function bind(Request $request)
    {
        $input = $request->all();
        $phone = $input['phone'];
        $code  = $input['code'];

        if ( ! $phone) {
            return error(1000, '手机号不能为空');
        }
        if ( ! $code) {
            return error(1000, '验证码不能为空');
        }

        $res = Redis::get($phone);
        if ( ! $res) {
            return error(1000, '验证码已过期');
        }
        if ($code !== $res) {
            return error(1000, '验证码错误');
        }
        Redis::del($phone);

        $data = [
            'nickname' => $input['nickname'] ?? '',
            'sex'      => $input['sex'] == '男' ? 1 : 2,
            'province' => $input['province'],
            'city'     => $input['city'],
            'headimg'  => $input['headimg'] ?? '',
            'unionid'  => $input['unionid'] ?? '',
            'is_wx'    => 1
        ];
        $user = User::where('phone', $phone)->first();
        if ($user) {
            User::where('phone', $phone)->update($data);
        } else {
            $data['phone'] = $phone;
            $res = User::create($data);
            if ($res) {
                $user = User::find($res->id);
            }
        }
        $token = auth('api')->login($user);
        $arra =[
            'id'    => $user->id,
            'token' => $token
        ];
        return success($arra);
       

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
    public function wechat2(Request $request)
    {
        $code = $request->input('code');
        if (!$code) {
            return $this->error(1000, 'code不能为空');
        }

        $res = $this->getRequest('https://api.weixin.qq.com/sns/oauth2/access_token', [
            'appid' => env('WECHAT_OFFICIAL_ACCOUNT_APPID'),
            'secret' => env('WECHAT_OFFICIAL_ACCOUNT_SECRET'),
            'code' => $code,
            'grant_type' => 'authorization_code'
        ]);
        if (!$res) {
            return $this->error(401, 授权失败);
        }
        $list = $this->getRequest('https://api.weixin.qq.com/sns/userinfo', [
            'access_token' => $res['access_token'],
            'openid' => $res['openid'],
        ]);
        if (!$list) {
            return $this->error(400, '获取用户信息失败');
        }

        $unionid = $request->input('unionid');
        $user = User::where('unionid', $unionid)->first();
        if (!$user) {
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
            'sex' => $user->sex,
            'province' => $user->province,
            'city' => $user->city,
            'token' => $token
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
        if (!$phone) {
            return $this->error(400, '手机号不能为空');
        }

        $easySms = app('easysms');
        try {

            $code = rand(1000, 9999);
            $result = $easySms->send($phone, [
                'template' => 'SMS_70300075',
                'data' => [
                    'code' => $code,
                ],
            ], ['aliyun']);

            Redis::setex($phone, 60 * 5, $code);
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

     // JWT 验证
    public function jwtApple(Request $request) {
        
        $phone   = $request->input('phone');
        $appleid = $request->input('user');
        $email = $request->input('email') ?? '';
        $fullName = $request->input('fullName') ?? '';
        $authorizationCode = $request->input('authorizationCode');

        $identityToken     = $request->input('identityToken');

        $appleSignInPayload = ASDecoder::getAppleSignInPayload($identityToken);

        $isValid = $appleSignInPayload->verifyUser($appleid);

        // 当 $isValid 为 true 时验证通过，后续逻辑根据需求编写
        if ($isValid === true) {
            $user = User::where('phone', $phone)->first();
            if (!$user) {
                $user = User::create([
                    'appleid' => $appleid ?? ''
                ]);
            }

            $token = auth('api')->login($user);
            $data = [
                'id'    => $user->id,
                'phone' => $user->phone ?? '',
                'token' => $token
            ];
            return success($data);
        } else {
            return error(1000, '验证失败');
        }

    }

}
