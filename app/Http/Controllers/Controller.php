<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController {

    use AuthorizesRequests,
        DispatchesJobs,
        ValidatesRequests;

    protected $page_per_page = 50;
    protected $show_ps = true;
    public $user;

    public function __construct() {
        $this->user = auth('api')->user();
        if ($this->user) {
            $this->user = $this->user->toArray();
        }
    }

    protected function success($data = [], $flag = 0) {
        $result = [
            'code' => 200,
            'msg' => '成功',
            'data' => $data
        ];
        if ($flag == 1) {
            $result = json_decode(json_encode($result, JSON_FORCE_OBJECT));
        }
        return response()->json($result);
    }

    protected function error($code, $msg = '') {
        $result = [
            'code' => $code,
            'msg' => $msg,
            'data' => ''
        ];
        return response()->json($result);
    }

}
