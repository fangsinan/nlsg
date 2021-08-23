<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use App\Models\ConfigModel;
use App\Models\Coupon;
use App\Models\UserInvite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use App\Models\User;
use AppleSignIn\ASDecoder;
use GuzzleHttp\Client;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Ecdsa\Sha256;
use Lcobucci\JWT\Signer\Key;
use PHPUnit\Util\Exception;
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
     * @apiParam  {number} [inviter]   推荐人id
     *
     * @apiSuccess {string} token token
     *
     * @apiSuccessExample  Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "code": 200,
     *       "msg" : '成功',
     *       "data": {
     *       "token": "eyJ0eXAiOiJKV1QiLCJhbGv1yTnhQxjIvle_zFN5mI_zzTQUBhSgwI"
     *       }
     *     }
     *
     */

    public function login(Request $request)
    {

        $phone = $request->input('phone');
        $code = $request->input('code');
        $is_invite = $request->input('is_invite');
        $user_id = $request->input('user_id');
        $inviter = $request->input('inviter', 0);
        $ref = $request->input('ref', 0);

        $sclass = new \StdClass();
        if (!$phone) {
            return error(400, '手机号不能为空', $sclass);
        }
        if (!$code) {
            return error(400, '验证码不能为空', $sclass);
        }

        $dont_check_phone = ConfigModel::getData(35, 1);
        $dont_check_phone = explode(',', $dont_check_phone);
        if (in_array($phone, $dont_check_phone)) {
            if (intval($code) !== 6666) {
                return error(400, '验证码错误', $sclass);
            }
        } else {
            $res = Redis::get($phone);
            if (!$res) {
                return error(400, '验证码已过期', $sclass);
            }
            if ($code !== $res) {
                return error(400, '验证码错误', $sclass);
            }
        }

        //新注册用户发送优惠券
        $model = new Coupon();
        $user = User::where('phone', $phone)->first();

        if (!$user) {
            $list = User::create([
                'phone' => $phone,
                'inviter' => $inviter,
                'login_flag' => ($inviter == 0) ? 0 : 1,
                'nickname' => substr_replace($phone, '****', 3, 4),
                'ref' => $ref
            ]);
            $user = User::find($list->id);

            //新人优惠券
            $model->giveCoupon($list->id, ConfigModel::getData(41));

            if ($is_invite && $user_id) {
                //邀请人优惠券
                $model->giveCoupon($user_id, ConfigModel::getData(42));
                UserInvite::create([
                    'from_uid' => $user_id,
                    'to_uid' => $list->id,
                    'type' => 1
                ]);
            }

        } else {
            if ($user->login_flag == 1) {
                User::where('id', '=', $user->id)->update(['login_flag' => 2]);
            }
        }

        Redis::del($phone);
        $token = auth('api')->login($user);

        //判断是否过期
        $time = strtotime(date('Y-m-d', time())) + 86400;
        if (in_array($user->level, [3, 4, 5]) && $user->expire_time > $time) {
            $user->level = $user->level;
        } else {
            $user->level = 0;
        }
        $data = $this->get_data($user,$token);

//        $data = [
//            'id' => $user->id,
//            'token' => $token,
//            'nickname' => $user->nickname,
//            'headimg' => $user->headimg ?? '',
//            'phone' => $user->phone,
//            'level' => $user->level,
//            'sex' => $user->sex,
//            'children_age' => 10,//$user->children_age,
//            'push'  => $user->push
//        ];
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
    //获取微信绑定信息，如已绑定返回登录信息，未绑定跳转绑定手机号
    public function wechat(Request $request)
    {
        $input = $request->all();
        $user = User::where('unionid', $input['unionid'])->first();
        if (!$user) {
            return error(1000, '微信还未绑定', (object)[]);
        }

        $token = auth('api')->login($user);
//        $data = [
//            'id' => $user->id,
//            'phone' => $user->phone ?? '',
//            'token' => $token
//        ];
        //判断是否过期
        $time = strtotime(date('Y-m-d', time())) + 86400;
        if (in_array($user->level, [3, 4, 5]) && $user->expire_time > $time) {
            $user->level = $user->level;
        } else {
            $user->level = 0;
        }
        $data = $this->get_data($user,$token);
//        $data = [
//            'id' => $user->id,
//            'token' => $token,
//            'nickname' => $user->nickname,
//            'headimg' => $user->headimg ?? '',
//            'phone' => $user->phone,
//            'level' => $user->level,
//            'sex' => $user->sex,
//            'children_age' => 10,//$user->children_age,
//        ];
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
        $code = $input['code'];

        if (!$phone) {
            return error(1000, '手机号不能为空');
        }
        if (!$code) {
            return error(1000, '验证码不能为空');
        }

        $dont_check_phone = ConfigModel::getData(35, 1);
        $dont_check_phone = explode(',', $dont_check_phone);
        if (in_array($phone, $dont_check_phone) || $phone =='18600179874' ) {
            if (intval($code) !== 6666) {
                return error(1000, '验证码错误');
            }
        } else {
            $res = Redis::get($phone);
            if (!$res) {
                return error(1000, '验证码已过期');
            }
            if ($code !== $res) {
                return error(1000, '验证码错误');
            }
        }

        Redis::del($phone);
        $is_wx = 0;
        if($input['unionid']){
            $is_wx = 1;
        }
        $data = [
            'nickname' => $input['nickname'] ?? '',
            'sex' => $input['sex'] == '男' ? 1 : 2,
            'province' => $input['province'],
            'city' => $input['city'],
            'unionid' => $input['unionid'] ?? '',
            'wxopenid' => $input['wx_openid'] ?? '',
            'headimg' => $input['headimg'] ?? '',
            'is_wx' => $is_wx
        ];
        $user = User::where('phone', $phone)->first();
        if ($user) {
            User::where('phone', $phone)->update($data); //如果有手机号，以前有微信覆盖
        } else {
            $data['phone'] = $phone;
            $res = User::create($data);
            if ($res) {
                $user = User::find($res->id);
            }
        }
        $token = auth('api')->login($user);
        $arra = $this->get_data($user,$token);
//        $arra = [
//            'id' => $user->id,
//            'token' => $token,
//            'sex' => $user->sex,
//            'children_age' => 10,//$user->children_age,
//        ];
        return success($arra);


    }





    /**
     * 静默授权注册登录
     */
    public function channel_bind(Request $request)
    {
        $input = $request->all();
        $arra = [];
        if(!empty($input['unionid'])){ //渠道新版本
            $user = User::where('unionid', $input['unionid'])->first();
            if (empty($user)) {

                $time = time();
                $redis_phone = 'phone_' . date('Ymd', $time);

                $key = 'phone_lock';
                self::lock($key); //加锁
                $num = Redis::get($redis_phone);
                if (empty($num) || $num <= 0) {
                    $num = 1;
                }
                Redis::setex($redis_phone, 86400, $num + 1);
                self::unlock($key); //释放锁

                $str_num = str_pad($num, 7, "0", STR_PAD_LEFT);
                $phone = date('ymd', $time) . $str_num; //6+7

                $data = [
                    'nickname' => (!empty($input['nickname'])) ?$input['nickname']:'nlsg' . rand(100000, 999999),
                    'phone' => $phone,
                    'isNoLogin' => 1,
                    'wxopenid' => $input['wx_openid'],
                    'unionid' => $input['unionid'],
                    'is_wx' => 1,
                ];
                $res = User::create($data);
                if ($res) {
                    $user = User::find($res->id);
                    if (empty($user)) {
                        return success($arra);
                    }
                }
            } else {
                $phone = $user->phone;
            }
        }else {
            if (empty($input['wx_openid'])) {
                return success($arra);
            }
            $user = User::where('wxopenid', $input['wx_openid'])->first();
            if ($input['wx_openid'] && empty($user)) {

                $time = time();
                $redis_phone = 'phone_' . date('Ymd', $time);

                $key = 'phone_lock';
                self::lock($key); //加锁
                $num = Redis::get($redis_phone);
                if (empty($num) || $num <= 0) {
                    $num = 1;
                }
                Redis::setex($redis_phone, 86400, $num + 1);
                self::unlock($key); //释放锁

                $str_num = str_pad($num, 7, "0", STR_PAD_LEFT);
                $phone = date('ymd', $time) . $str_num; //6+7

                $data = [
                    'nickname' => 'nlsg' . rand(100000, 999999),
                    'phone' => $phone,
                    'isNoLogin' => 1,
                    'wxopenid' => $input['wx_openid']
                ];
                $res = User::create($data);
                if ($res) {
                    $user = User::find($res->id);
                    if (empty($user)) {
                        return success($arra);
                    }
                }
            } else {
                $phone = $user->phone;
            }
        }

        $token = auth('api')->login($user);
        $arra = [
            'id' => $user->id,
            'token' => $token,
            'phone' => $phone,
            'sex' => 0,
            'children_age' => 10,//$user->children_age,
        ];
        return success($arra);

    }

    /**
     * 李总免登陆绑定手机手机号
     */
    public function sub_phone(Request $request)
    {
        $input = $request->all();
        $phone = $input['phone'];
        $code = $input['code'];
        $user_id = $input['user_id']; //用户id

        if (!$phone) {
            return error(1000, '手机号不能为空');
        }
        if (!$code) {
            return error(1000, '验证码不能为空');
        }
        if (!$user_id) {
            return error(1000, '用户id不能为空');
        }

        $dont_check_phone = ConfigModel::getData(35, 1);
        $dont_check_phone = explode(',', $dont_check_phone);
        if (in_array($phone, $dont_check_phone) || $phone =='18600179874' ) {
            if (intval($code) !== 6666) {
                return error(1000, '验证码错误');
            }
        } else {
            $res = Redis::get($phone);
            if (!$res) {
                return error(1000, '验证码已过期');
            }
            if ($code !== $res) {
                return error(1000, '验证码错误');
            }
        }
        Redis::del($phone);
        $user = User::where('phone', $phone)->first();
        if (!$user) {
            User::where('id', $user_id)->update(['phone'=>$phone]);
        } else { //号码已存在
            return error(1000, '此手机号已存在');
        }
        $arra = [
            'id' => $user_id,
            'phone' => $phone,
        ];
        return success($arra);

    }

    public static function lock(string $key): string
    {
        $redis = new Redis();
        $key .= '_lock';
        $value = '';
        $curTime = time();
        while (true) { // 独占式抢锁
            $value = microtime(true).mt_rand(1000, 9999);
            if ($redis::setnx($key, $value)) { // 获取到锁 当key不存在时设置key
                // 锁过期时间5秒
                $redis::expire($key, 5);
                break;
            } else  {
                if (time() - $curTime >= 10) { // 超过10秒抛异常
                    throw new Exception('lock error', 1);
                    break;
                }
                $time = $redis::ttl($key);
                if(($time / 2) > 0){
                    sleep($time / 2);
                }
            }
        }
        return $value;
    }

    /**
     * 释放分布式锁
     * @param string $key
     * @return bool
     */
    public static function unlock(string $key): bool
    {
        $redis = new Redis();
        $key .= '_lock';
        $redis::del($key);

        return true;
    }

    /**
     * @api {get} api/v4/auth/wechat_info 微信授权
     * @apiVersion 4.0.0
     * @apiName  wechat_info
     * @apiGroup Auth
     * @apiParam code 授权码
     * @apiParam type 1 获取openid 默认1  0 微信信息
     *
     * @apiSuccess {String} openid   openid
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
    public function wechatInfo(Request $request)
    {
        $code = $request->input('code');
        $type = $request->input('type', 1);
        if (!$code) {
            return $this->error(1000, 'code不能为空');
        }

        $res = $this->getRequest('https://api.weixin.qq.com/sns/oauth2/access_token', [
            'appid' => config('env.WECHAT_OFFICIAL_ACCOUNT_APPID'),
            'secret' => config('env.WECHAT_OFFICIAL_ACCOUNT_SECRET'),
            'code' => $code,
            'grant_type' => 'authorization_code'
        ]);
        if (!$res) {
            return $this->error(401, '授权失败');
        }

        if ($type == 1) {
            return $this->success(['openid' => $res->openid]); //获取code  再获取openid
        }
        $list = $this->getRequest('https://api.weixin.qq.com/sns/userinfo', [
            'access_token' => $res->access_token,
            'openid' => $res->openid,
        ]);
        if (!$list) {
            return $this->error(400, '获取用户信息失败');
        }
        $data = [
            'unionid' => $list->unionid,
            'nickname' => $list->nickname,
            'openid' => $res->openid,
            'headimg' => $list->headimgurl,
            'sex' => $list->sex,
            'province' => $list->province, //北京
            'city' => $list->city, //朝阳区
            'country' => $list->country, //中国
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
        if(!preg_match('/^1[3456789]\d{9}$/', $phone)){
            return $this->error(400, '手机号格式错误');
        }

        //自己人不发验证码
        $dont_check_phone = ConfigModel::getData(35, 1);
        $dont_check_phone = explode(',', $dont_check_phone);
        if (in_array($phone, $dont_check_phone)) {
            return success();
        } else {
            $easySms = app('easysms');

            try {
                if ($phone =='18600179874'){
                    $code = 6666;
                } else {
                    $code = rand(1000, 9999);
                }
                $result = $easySms->send($phone, [
                    'template' => 'SMS_200714195',
                    'data' => [
                        'code' => $code,
                    ],
                ], ['aliyun']);

                Redis::setex($phone, 60 * 5, $code);
                return success();
            } catch (\Overtrue\EasySms\Exceptions\NoGatewayAvailableException $exception) {
                $message = $exception->getResults();
                return $this->error(400, '验证码发送错误,请一分钟后重试');
            }
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

    public function apple(Request $request)
    {
        $input = $request->all();
        $user = User::where('appleid', $input['user'])->first();
//        if (!$user) {
//            return error(1000, '苹果还未绑定');
//        }

        $token = auth('api')->login($user);
//        $data = [
//            'id' => $user->id,
//            'phone' => $user->phone ?? '',
//            'token' => $token
//        ];

        //判断是否过期
        $time = strtotime(date('Y-m-d', time())) + 86400;
        if (in_array($user->level, [3, 4, 5]) && $user->expire_time > $time) {
            $user->level = $user->level;
        } else {
            $user->level = 0;
        }
        $data = $this->get_data($user,$token);
//        $data = [
//            'id' => $user->id,
//            'token' => $token,
//            'nickname' => $user->nickname,
//            'headimg' => $user->headimg ?? '',
//            'phone' => $user->phone,
//            'level' => $user->level,
//            'sex' => $user->sex,
//            'children_age' => 10,//$user->children_age,
//        ];
        return success($data);
    }
    //登录返回字段
    function get_data($user,$token){

        return  [
            'id' => $user->id,
            'token' => $token,
            'nickname' => $user->nickname,
            'headimg' => $user->headimg ?? '',
            'phone' => $user->phone,
            'level' => $user->level,
            'sex' => $user->sex,
            'is_community_admin' => $user->is_community_admin,
            'children_age' => 10,//$user->children_age,
        ];
    }

    // JWT 验证
    public function jwtApple(Request $request)
    {

        $phone = $request->input('phone');
        $appleid = $request->input('user');
        $email = $request->input('email') ?? '';
        $fullName = $request->input('fullName') ?? '';
        $authorizationCode = $request->input('authorizationCode');
        $identityToken = $request->input('identityToken');
        if(empty($identityToken) || empty($appleid)){
            return error(1000, '参数错误');
        }
        $appleSignInPayload = ASDecoder::getAppleSignInPayload($identityToken);
        $isValid = $appleSignInPayload->verifyUser($appleid);

         //当 $isValid 为 true 时验证通过，后续逻辑根据需求编写
        if ($isValid === true) {
            $user = User::where('appleid', $appleid)->first();
            if (!$user) {
                $rand =  uniqid();
                $list = User::create([
                    'nickname'=> '苹果用户'. $rand,
                    'phone'   => '苹果用户'. $rand,
                    'appleid' => $appleid ?? ''
                ]);
                $user = User::find($list->id);
            }

            $token = auth('api')->login($user);
            $data = [
                'id' => $user->id,
                'phone' => $user->phone ?? '',
                'token' => $token
            ];
            return success($data);
        } else {
            return error(1000, '验证失败');
        }

    }

    public function checkPhone(Request $request)
    {
        $phone = strval($request->input('phone', ''));
        if (empty($phone) || !is_numeric($phone) || strlen($phone) !== 11) {
            return $this->getRes(['code' => false, 'msg' => '号码错误']);
        }

        if (0) {
            $header = substr($phone, 0, 3);
            $list = [
                '130', '131', '132', '133', '134', '135', '136', '137', '138', '139',
                '150', '151', '152', '153', '155', '156', '157', '158', '159',
                '176', '177', '178',
                '180', '181', '182', '183', '184', '185', '186', '187', '188', '189',
            ];

            if (in_array($header, $list)) {
                return $this->getRes(['code' => true, 'msg' => '正确']);
            } else {
                return $this->getRes(['code' => false, 'msg' => '号码错误']);
            }
        } else {
            $g = "/^1[34578]\d{9}$/";
            $g2 = "/^19[0126789]\d{8}$/";
            $g3 = "/^166\d{8}$/";

            if (preg_match($g, $phone)) {
                return $this->getRes(['code' => true, 'msg' => '正确']);
            } else if (preg_match($g2, $phone)) {
                return $this->getRes(['code' => true, 'msg' => '正确']);
            } else if (preg_match($g3, $phone)) {
                return $this->getRes(['code' => true, 'msg' => '正确']);
            }
            return $this->getRes(['code' => false, 'msg' => '号码错误']);
        }

    }

    public function module(Request $request)
    {


        $version = $request->input('version', '');//1 获取是否有提交信息  2修改
        $config_version = ConfigModel::getData(52);
        $switchAll = [
            //精确版本
//            '4.1.0' => [
//                'money_switch' => '0',//app赚钱开关   0关闭  1开启
//                'Vip_Switch' => '0',//提现开关   0关闭  1开启
//                'vipCode' => '0', //钻石兑换码
//                'worksCode' => '0',//课程兑换码
//            ],
            $config_version => [
                'money_switch' => '0',//app赚钱开关   0关闭  1开启
                'Vip_Switch' => '0',//提现开关   0关闭  1开启
                'vipCode' => '0', //钻石兑换码
                'worksCode' => '0',//课程兑换码
            ],
            'default' => [
                'money_switch' => '1',//app赚钱开关   0关闭  1开启
                'Vip_Switch' => '1',//提现开关   0关闭  1开启
                'vipCode' => '1',
                'worksCode' => '1',
            ],

        ];

        $res_switchAll = $switchAll[$version] ?? $switchAll['default'];

        return success($res_switchAll);
    }

    //收集用户信息
    public function checkWx(Request $request){
        $uid = $request->input('user_id')??0;//
        $wx_openid = $request->input('wx_openid');
        $user = User::find($uid);
        if( empty($user['wxopenid']) ){
            User::where('id', '=', $uid)->update(['wxopenid' => $wx_openid]);
        }
        return success();
    }




    /**
     * @api {post} api/v4/auth/visitorLogin  游客登录
     * @apiVersion 4.0.0
     * @apiName  visitorLogin
     * @apiGroup Auth
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/auth/visitorLogin
     *
     * @apiParam {number} unionid  设备号
     * @apiParam {number} sig  sig
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

    public function visitorUser(Request $request){
        $unionid = $request->input('unionid');
        $timestamp = $request->input('timestamp');
        $sig = $request->input('sig');
        $version = $request->input('version');
        $nicke = substr($unionid, 0, 5);


        //如果开关关闭  直接返回
        $version_config = ConfigModel::getData(52);
        if($version_config == $version){
            return error(0, 'version error');
        }
        if(empty($unionid)){
            return error(0, 'unionid error');
        }
        //如果密串错误  直接返回

        if( !( strtoupper(MD5($unionid.'_'.$timestamp."_"."NLSG")) == strtoupper($sig)) ){
            return error(0, 'sig error');
        }
        $rand = substr(uniqid(), -5);
        $user = User::where("phones",'like',"游客$nicke"."_%")
                    ->where(['unionid'=>$unionid])->first();
        if(empty($user)){
            $list = User::create([
                'phone' => '游客'. $nicke."_".$rand,
                'unionid' => $unionid,
                'nickname' => '游客'. $nicke."_".$rand,
            ]);
            $user = User::find($list->id);

        }


        $token = auth('api')->login($user);
        $res = $this->get_data($user,$token);
        return  success($res);
    }

}
