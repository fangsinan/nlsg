<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ExpressCompany;

/**
 * Description of ExpressController
 *
 * @author wangxh
 */
class ExpressController extends Controller {

    public function getPostInfo(Request $request) {
        $user = ['id' => 168934, 'level' => 4, 'is_staff' => 1];
        if (empty($user['id'] ?? 0)) {
            return $this->error(0, '未登录');
        }

        $params['order_id'] = $request->input('order_id', 0);
        $params['express_id'] = $request->input('express_id', 0);
        $params['express_num'] = $request->input('express_num', 0);
        $params['type'] = $request->input('type', 0);

        $model = new ExpressCompany();
        $data = $model->getPostInfo($params, $user);
        if (($data['code'] ?? true) === false) {
            $ps = ($this->show_ps ? (($data['ps'] ?? false) ? (':' . $data['ps']) : '') : '');
            return $this->error(0, $data['msg'] . $ps);
        } else {
            return $this->success($data);
        }
    }

}
