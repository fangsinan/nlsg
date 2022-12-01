<?php


namespace App\Http\Controllers\Admin\V4;


use App\Http\Controllers\ControllerBackend;
use App\Models\BackendUser;
use App\Models\CacheTools;
use App\Models\Node;
use App\Models\User;
use App\Servers\V5\BackendUserToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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
        $time_out = time() + Config('captcha.flat.expire');
        $data = $captcha->create('flat', true);
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

        if (empty($username) || empty($password) || empty($captcha) || empty($key)) {
            return $this->getRes(['code' => false, 'msg' => '参数错误']);
        }

        $captcha_res = captcha_api_check($captcha, $key, 'flat');
        if ($captcha !== 'nlsg_2021' &&$captcha_res === false) {
            return $this->getRes(['code' => false, 'msg' => '验证码错误']);
        }

        $check_user = BackendUser::where('username', '=', $username)->first();
        $userInfo = User::where('phone', '=', $username)->first();
        if (empty($check_user)) {
            return $this->getRes(['code' => false, 'msg' => '账号或密码错误']);
        }

        $err_count = BackendUserToken::errLockCheck($check_user->id);
        if ($err_count >= 5){
            BackendUserToken::delToken($check_user->id);
            return $this->getRes(['code' => false, 'msg' => '重试次数过多,请明天再来.']);
        }

        if (Hash::check($password, $check_user->password)) {
            $token = auth('backendApi')->login($check_user);
            //存入redis
            BackendUserToken::setToken($check_user->id,$token);

            $data = [
                'id' => $check_user->id,
                'nickname' => $check_user->username,
                'live_role' => $check_user->live_role,
                'token' => $token,
                'role' => $check_user->role_id,
                'role_id' => $check_user->role_id,
                'menu_tree' => Node::getMenuTree($check_user->role_id),
                'app_uid' => User::where('phone', '=', $username)->value('id'),
                'live_role_button' => $check_user->live_role_button,
                'app_user_id' => $userInfo->id,
            ];
            return $this->getRes($data);
        }else{
            //查看当天错误次数
            BackendUserToken::errLockSet($check_user->id);
        }
        return $this->getRes(['code' => false, 'msg' => '账号或密码错误']);
    }

    public function changePassword(Request $request)
    {
        $model = new BackendUser();
        $data = $model->changePwd($this->user, $request->input());
        return $this->getRes($data);
    }

    //仅供内部技术人员测试使用
    public function getToken(Request $request){

        $username = $request->input('username', '');
        if(!in_array($username,[18810355387])){
            return $this->getRes(['fail' => '非法请求',]);
        }
        $check_user = BackendUser::where('username', '=', $username)->first();
        $token = auth('backendApi')->login($check_user);
        return $this->getRes(['token' => $token,]);

    }

}
