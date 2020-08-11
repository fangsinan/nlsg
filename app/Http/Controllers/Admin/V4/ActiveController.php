<?php


namespace App\Http\Controllers\Admin\V4;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\servers\ActiveServers;

class ActiveController extends Controller
{
    public function list(Request $request)
    {
        $servers = new ActiveServers();
        $data = $servers->list($request->input());
        if (($data['code'] ?? true) === false) {
            $ps = ($this->show_ps ? (($data['ps'] ?? false) ? (':' . $data['ps']) : '') : '');
            return $this->error(0, $data['msg'] . $ps);
        } else {
            return $this->success($data);
        }

    }

    public function add(Request $request)
    {
        $servers = new ActiveServers();
        $data = $servers->add($request->input());
        if (($data['code'] ?? true) === false) {
            $ps = ($this->show_ps ? (($data['ps'] ?? false) ? (':' . $data['ps']) : '') : '');
            return $this->error(0, $data['msg'] . $ps);
        } else {
            return $this->success($data);
        }
    }

}
