<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MallOrder;

class MallOrderController extends Controller {

    //todo 预下单
    public function prepareCreateOrder(Request $request) {
        $params = $request->input();
        $user = ['id' => 168934, 'level' => 4, 'is_staff' => 1];

        //1626220663,1627866674,1734814569
        //1835913656,1654630825,1626220663
        $params = [
            'from_cart' => 1, //1表示是购物车  0不是
            'sku' => '1612728266,1835913656,1654630825,1626220663', //如果是购物车,可能是多条
            'goods_id' => 209,
            'buy_num' => 1,
            'inviter' => 211172, //推荐人
            'post_type' => 1, //1邮寄 2自提
            'coupon_goods_id' => '7', //优惠券id
            'coupon_freight_id' => '10',
            'address_id' => 0
        ];

        if (empty($user['id'] ?? 0)) {
            return $this->error('未登录');
        }
        $model = new MallOrder();
        $data = $model->prepareCreateOrder($params, $user);
        if (($data['code'] ?? true) === false) {
            $ps = ($this->show_ps ? (($data['ps'] ?? false) ? (':' . $data['ps']) : '') : '');
            return $this->error(0, $data['msg'] . $ps);
        } else {
            return $this->success($data);
        }
    }

    //todo 下单
    public function createOrder(Request $request) {
        $params = $request->input();
        $user = ['id' => 168934, 'level' => 4, 'is_staff' => 1];

        //1626220663,1627866674,1734814569
        //1835913656,1654630825,1626220663
        $params = [
            'from_cart' => 1, //1表示是购物车  0不是
            'sku' => '1612728266,1835913656,1654630825,1626220663', //如果是购物车,可能是多条
            'goods_id' => 209,
            'buy_num' => 1,
            'inviter' => 211172, //推荐人
            'post_type' => 1, //1邮寄 2自提
            'coupon_goods_id' => '7', //优惠券id
            'coupon_freight_id' => '10',
            'address_id' => 2814
        ];

        if (empty($user['id'] ?? 0)) {
            return $this->error('未登录');
        }
        $model = new MallOrder();
        $data = $model->createOrder($params, $user);
        if (($data['code'] ?? true) === false) {
            $ps = ($this->show_ps ? (($data['ps'] ?? false) ? (':' . $data['ps']) : '') : '');
            return $this->error(0, $data['msg'] . $ps);
        } else {
            return $this->success($data);
        }
    }

}
