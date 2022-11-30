<?php

namespace App\Http\Controllers;

use App\Models\BackendUserAuthLog;
use App\Models\ConfigModel;
use App\Models\Role;
use App\Models\User;
use App\Servers\V5\BackendUserToken;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Route;

class ControllerBackend extends BaseController
{

    use AuthorizesRequests,
        DispatchesJobs,
        ValidatesRequests;

    protected $page_per_page = 20;
    protected $show_ps = true;
    public $user;

    public function __construct(Request $request)
    {

        $token     = $request->header('authorization');
        $url_token = $request->input('token', '');
        if (empty($token) && !empty($url_token)) {
            $request->headers->set('Authorization', 'Bearer ' . $url_token);
        }

        $this->user = auth('backendApi')->user();

        $route = Route::current();
        $url_2 = explode('/', $route->uri);
        $url_2 = array_slice($url_2, -2);
        $url_2 = '/' . trim(implode('/', $url_2), '/');

        if ($url_2 !== '/auth/login') {
            $cache_token = BackendUserToken::getToken($this->user['id']);
            if ($cache_token) {
                $header_token = $request->header('authorization');
                $header_token = str_replace('Bearer ', '', $header_token);

                if ($cache_token !== $header_token) {
                    $class       = new \stdClass();
                    $class->code = 401;
                    $class->msg  = '登录已过期,请重试.';
                    $class->data = '';
                    echo json_encode($class);
                    exit;
                }
            }
            BackendUserToken::refreshToken($this->user['id'] ?? 0);
        }

        if ($this->user) {
            $this->user            = $this->user->toArray();
            $this->user['user_id'] = User::query()->where('phone', '=', $this->user['username'])->value('id');

//            $url = substr($route->uri, 12);

            //临时添加,解决直播后台和普通后台域名前缀长度不一致问题

            BackendUserAuthLog::query()
                ->insertOrIgnore(
                    [
                        'admin_id'     => $this->user['user_id'],
                        'log_time_str' => date('Y-m-d H:i'),
                        'ip'           => $this->getIp($request),
                        'uri'          => $url_2,
                    ]
                );

            if (1 === $this->user['role_id']) {
                return true;
            }

            $roleModel       = new Role();
            $roleAuthNodeMap = $roleModel->getRoleAuthNodeMap($this->user['role_id']);

            $pass_url = ConfigModel::getData(55);
            $pass_url = explode(',', $pass_url);

            if (!in_array($url_2, $pass_url) && !in_array($url_2, $roleAuthNodeMap)) {
                $class       = new \stdClass();
                $class->code = 1000;
                $class->msg  = '没有权限';
                $class->data = '';
                echo json_encode($class);
                exit;
            }
        }
    }

    public function getIp(Request $request)
    {
        $request::setTrustedProxies($request->getClientIps(), \Illuminate\Http\Request::HEADER_X_FORWARDED_FOR);
        return $request->getClientIp();
    }

    protected function success($data = [], $flag = 0, $msg = '成功')
    {
        $result = [
            'code' => 200,
            'msg'  => $msg,
            'now'  => time(),
            'data' => $data
        ];
        return response()->json($result);
    }

    protected function error($code, $msg = '', $data = '')
    {
        $result = [
            'code' => $code,
            'msg'  => $msg,
            'now'  => time(),
            'data' => $data
        ];
        return response()->json($result);
    }

    protected function getRes($data)
    {
        if (($data['code'] ?? true) === false) {
            $ps         = ($this->show_ps ? (($data['ps'] ?? false) ? (':' . $data['ps']) : '') : '');
            $temp       = new class {
            };
            $temp->code = false;
            $temp->msg  = $data['msg'];
            return $this->error(0, $data['msg'] . $ps, $temp);
        } else {
            $msg = '成功';
            if (is_array($data) && isset($data['msg']) && !empty($data['msg'])) {
                $msg = $data['msg'];
            } elseif (is_object($data) && isset($data->msg) && !empty($data->msg)) {
                $msg = $data->msg;
            }
            return $this->success($data, 0, $msg);
        }
    }
}
