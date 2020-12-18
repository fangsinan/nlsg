<?php


namespace App\Http\Controllers\Admin\V4;


use App\Http\Controllers\Controller;
use App\Http\Controllers\ControllerBackend;
use App\Models\BackendUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Mews\Captcha\Captcha;

class AuthController extends ControllerBackend
{

    /**
     * 验证码
     * @api {get} /api/admin_v4/auth/captcha 验证码
     * @apiVersion 4.0.0
     * @apiName /api/admin_v4/auth/captcha
     * @apiGroup  后台-登陆
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/auth/captcha
     * @apiDescription 验证码
     * @apiSuccess {string} key 验证码key
     * @apiSuccess {number} expire 验证码过期时间戳,过期需刷新,验证错误需刷新
     */
    public function captcha(Captcha $captcha)
    {
        $time_out = time() + Config('captcha.default.expire');
        $data = $captcha->create('default', true);
        $data['expire'] = $time_out;
        return $this->getRes($data);
    }

    /**
     * 登陆
     * @api {post} /api/admin_v4/auth/login 登陆
     * @apiVersion 4.0.0
     * @apiName /api/admin_v4/auth/login
     * @apiGroup  后台-登陆
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/auth/login
     * @apiParam {string} username 账号
     * @apiParam {string} password 密码
     * @apiParam {string} captcha 验证码
     * @apiParam {string} key 验证码key
     * @apiDescription 登陆
     */
    public function login(Request $request)
    {
        $username = $request->input('username', '');
        $password = $request->input('password', '');
        $captcha = $request->input('captcha', '');
        $key = $request->input('key', '');

        if (empty($username) || empty($password) || empty($captcha) || empty($key)){
            return $this->getRes(['code'=>false,'msg'=>'参数错误']);
        }

        $captcha_res = captcha_api_check($captcha, $key);
        if ($captcha_res === false) {
            return $this->getRes(['code' => false, 'msg' => '验证码错误']);
        }

        $check_user = BackendUser::where('username','=',$username)->first();
        if (empty($check_user)){
            return $this->getRes(['code' => false, 'msg' => '账号或密码错误']);
        }
        if(Hash::check($password,$check_user->password)){
            $token = auth('backendApi')->login($check_user);;
            $data = [
                'id' => $check_user->id,
                'nickname' => $check_user->username,
                'token' => $token
            ];
            return $this->getRes($data);
        }else{
            return $this->getRes(['code' => false, 'msg' => '账号或密码错误']);
        }

    }


}
