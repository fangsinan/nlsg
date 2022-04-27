<?php

namespace App\Http\Controllers;

use App\Models\VipUser;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Cache;


class Controller extends BaseController
{

    use AuthorizesRequests,
        DispatchesJobs,
        ValidatesRequests;

    protected $page_per_page = 20;
    protected $show_ps = false;
    public $user;
    public $ip;

    public function __construct()
    {
        $this->user = auth('api')->user();

        if ($this->user) {
            $this->user = $this->user->toArray();
            $this->user['true_level'] = 0;
            if (!empty($this->user['level']) && !empty($this->user['expire_time']) && $this->user['expire_time'] > date('Y-m-d H:i:s')) {
                $this->user['true_level'] = $this->user['level'];
            }
            $this->user['level'] = $this->user['true_level'];
            $this->user['new_vip'] = VipUser::newVipInfo($this->user['id']);
        }else{
//            exit(json_encode(['msg'=>'没有登陆','code'=>401]));
            return response()->json(['msg' => '没有登录','code'=> 401]);
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
