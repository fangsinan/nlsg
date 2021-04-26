<?php

namespace App\Http\Controllers;

use App\Models\Auth;
use App\Models\User;
use App\Models\VipUser;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class ControllerBackend extends BaseController
{

    use AuthorizesRequests,
        DispatchesJobs,
        ValidatesRequests;

    protected $page_per_page = 20;
    protected $show_ps = true;
    public $user;

    public function __construct()
    {
        $this->user = auth('backendApi')->user();
        if ($this->user) {
//            $route = Route::current();
//            $url   = substr($route->uri, 12);
//            $authModel = new Auth();
//            $flag = $authModel->authCheck($url, $this->user['role_id']);
//
//            if (!$flag) {
//                return error(1000, '无操作权限');
//            }
            $this->user = $this->user->toArray();
            $this->user['user_id'] = User::where('phone','=',$this->user['username'])->value('id');
        }
    }

    public function getIp(Request $request){
        $request::setTrustedProxies($request->getClientIps(), \Illuminate\Http\Request::HEADER_X_FORWARDED_FOR);
        return $request->getClientIp();
    }

    protected function success($data = [], $flag = 0, $msg = '成功')
    {
        $result = [
            'code' => 200,
            'msg' => $msg,
            'now' => time(),
            'data' => $data
        ];
        return response()->json($result);
    }

    protected function error($code, $msg = '', $data = '')
    {
        $result = [
            'code' => $code,
            'msg' => $msg,
            'now' => time(),
            'data' => $data
        ];
        return response()->json($result);
    }

    protected function getRes($data)
    {
        if (($data['code'] ?? true) === false) {
            $ps = ($this->show_ps ? (($data['ps'] ?? false) ? (':' . $data['ps']) : '') : '');
            $temp = new class {
            };
            $temp->code = false;
            $temp->msg = $data['msg'];
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
