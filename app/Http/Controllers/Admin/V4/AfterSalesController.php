<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Http\Controllers\Admin\V4;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\servers\AfterSalesServers;

/**
 * Description of AfterSalesController
 *
 * @author wangxh
 */
class AfterSalesController extends Controller {

    //todo 售后列表和详情
    public function list(Request $request) {
        $servers = new AfterSalesServers();
        $data = $servers->getList($request->input());
        if (($data['code'] ?? true) === false) {
            $ps = ($this->show_ps ? (($data['ps'] ?? false) ? (':' . $data['ps']) : '') : '');
            return $this->error(0, $data['msg'] . $ps);
        } else {
            return $this->success($data);
        }
    }

    //todo 审核,鉴定
    public function statusChange(Request $request) {
        if (empty($this->user['id'] ?? 0)) {
            return $this->error(0, '未登录');
        }

        $model = new AfterSalesServers();
        $data = $model->statusChange($request->input(), $this->user['id']);
        if (($data['code'] ?? true) === false) {
            $ps = ($this->show_ps ? (($data['ps'] ?? false) ? (':' . $data['ps']) : '') : '');
            return $this->error(0, $data['msg'] . $ps);
        } else {
            return $this->success($data);
        }
    }

}
