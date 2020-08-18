<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{

    use AuthorizesRequests,
        DispatchesJobs,
        ValidatesRequests;

    protected $page_per_page = 50;
    protected $show_ps = true;
    public $user;

    public function __construct()
    {
        $this->user = auth('api')->user();
        if ($this->user) {
            $this->user = $this->user->toArray();
        }
    }

    protected function success($data = [], $flag = 0)
    {
        $result = [
            'code' => 200,
            'msg' => '成功',
            'now' => time(),
            'data' => $data
        ];
        return response()->json($result);
    }

    protected function error($code, $msg = '')
    {
        $result = [
            'code' => $code,
            'msg' => $msg,
            'now' => time(),
            'data' => ''
        ];
        return response()->json($result);
    }

    protected function notLogin(){
        $result = [
            'code' => 401,
            'msg' => '未登录',
            'now' => time(),
            'data' => ''
        ];
        return response()->json($result);
    }
}
