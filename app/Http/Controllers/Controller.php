<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{

    use AuthorizesRequests,
        DispatchesJobs,
        ValidatesRequests;

    protected $page_per_page = 10;
    protected $show_ps = true;
    public $user;

    public function __construct()
    {
        $this->user = auth('api')->user();
        //$this->user = User::find(168934);
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

    protected function error($code, $msg = '',$data='')
    {
        $result = [
            'code' => $code,
            'msg' => $msg,
            'now' => time(),
            'data' => $data
        ];
        return response()->json($result);
    }

    protected function getRes($data){
        if (($data['code'] ?? true) === false) {
            $ps = ($this->show_ps ? (($data['ps'] ?? false) ? (':' . $data['ps']) : '') : '');
            return $this->error(0, $data['msg'] . $ps);
        } else {
            return $this->success($data);
        }
    }
}
