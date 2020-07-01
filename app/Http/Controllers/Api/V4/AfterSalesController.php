<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MallRefundRecord;

/**
 * Description of AfterSalesController
 *
 * @author wangxh
 */
class AfterSalesController extends Controller {

    //todo 可申请售后订单和商品列表
    public function list(Request $request) {
        $user = ['id' => 168934, 'level' => 4, 'is_staff' => 1];
        if (empty($user['id'] ?? 0)) {
            return $this->error(0, '未登录');
        }
        $params = $request->input();
        $model = new MallRefundRecord();
        $data = $model->list($params, $user);
        if (($data['code'] ?? true) === false) {
            $ps = ($this->show_ps ? (($data['ps'] ?? false) ? (':' . $data['ps']) : '') : '');
            return $this->error(0, $data['msg'] . $ps);
        } else {
            return $this->success($data);
        }
    }

    //todo 申请售后    
    public function createOrder(Request $request) {
        $user = ['id' => 168934, 'level' => 4, 'is_staff' => 1];
        if (empty($user['id'] ?? 0)) {
            return $this->error(0, '未登录');
        }
        $params = $request->input();
        $model = new MallRefundRecord();
        $data = $model->createOrder($params, $user);
        if (($data['code'] ?? true) === false) {
            $ps = ($this->show_ps ? (($data['ps'] ?? false) ? (':' . $data['ps']) : '') : '');
            return $this->error(0, $data['msg'] . $ps);
        } else {
            return $this->success($data);
        }
    }

    //todo 售后详情
    public function orderInfo(Request $request) {
        $user = ['id' => 168934, 'level' => 4, 'is_staff' => 1];
        if (empty($user['id'] ?? 0)) {
            return $this->error(0, '未登录');
        }
        $params = $request->input();
        $model = new MallRefundRecord();
        $data = $model->orderInfo($params, $user);
        if (($data['code'] ?? true) === false) {
            $ps = ($this->show_ps ? (($data['ps'] ?? false) ? (':' . $data['ps']) : '') : '');
            return $this->error(0, $data['msg'] . $ps);
        } else {
            return $this->success($data);
        }
    }

    //todo 删除,取消等
    public function statusChange(Request $request) {
        $user = ['id' => 168934, 'level' => 4, 'is_staff' => 1];
        if (empty($user['id'] ?? 0)) {
            return $this->error(0, '未登录');
        }
        $params = $request->input();
        $model = new MallRefundRecord();
        $data = $model->statusChange($params, $user);
        if (($data['code'] ?? true) === false) {
            $ps = ($this->show_ps ? (($data['ps'] ?? false) ? (':' . $data['ps']) : '') : '');
            return $this->error(0, $data['msg'] . $ps);
        } else {
            return $this->success($data);
        }
    }

    //todo 寄回商品
}
