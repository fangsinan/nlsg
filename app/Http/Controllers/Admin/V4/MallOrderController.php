<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Http\Controllers\Admin\V4;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\servers\MallOrderServers;

/**
 * Description of MallOrderController
 *
 * @author wangxh
 */
class MallOrderController extends Controller {

    //todo 列表
    public function list(Request $request) {
        $servers = new MallOrderServers();
        $data = $servers->getList($request->input());
        if (($data['code'] ?? true) === false) {
            $ps = ($this->show_ps ? (($data['ps'] ?? false) ? (':' . $data['ps']) : '') : '');
            return $this->error(0, $data['msg'] . $ps);
        } else {
            return $this->success($data);
        }
    }

    //todo 发货
    public function send(Request $request) {
        
    }

}
