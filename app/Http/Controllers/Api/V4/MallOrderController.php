<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use App\Models\MallOrder;

class MallOrderController extends Controller {

    //todo 预下单
    public function prepareCreateOrder() {
        $params = $request->input();
        $user = ['id' => 168934, 'level' => 4, 'is_staff' => 1];
        if (empty($user['id'] ?? 0)) {
            return $this->error('未登录');
        }
        $model = new MallOrder();
        $data = $model->prepareCreateOrder($params, $user['id']);
        if (($data['code'] ?? true) === false) {
            return $this->error($data['msg']);
        } else {
            return $this->success($data);
        }
    }

    //todo 下单
    public function createOrder() {
        
    }

}
