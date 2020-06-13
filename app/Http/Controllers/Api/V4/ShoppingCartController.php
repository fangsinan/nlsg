<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ShoppingCart;

class ShoppingCartController extends Controller {

    /**
     * 添加,编辑
     * @api {post} /api/V4/shopping_cart/create 添加,编辑
     * @apiVersion 1.0.0
     * @apiName /api/V4/shopping_cart/create
     * @apiGroup shopping_cart
     * @apiSampleRequest /api/V4/shopping_cart/create
     * @apiDescription 添加,编辑
     * @apiParam {string} goods_id 商品id
     * @apiParam {string} sku_number sku
     * @apiParam {string} num 数量
     * @apiParam {string} [inviter] 邀请人
     *
     * @apiParamExample {json} Request-Example:
      {
      "goods_id":98,
      "sku_number":"1835913656",
      "num":666,
      "id":1,
      "inviter":168934
      }
     * @apiSuccessExample {json} Request-Example:
     * {
      "code": 200,
      "msg": "成功",
      "data": {
      "code": true,
      "msg": "成功"
      }
      }
     */
    public function create(Request $request) {
        $params = $request->input();
        $user = ['id' => 168934, 'level' => 4, 'is_staff' => 1];
        if (empty($user['id'] ?? 0)) {
            return $this->error('未登录');
        }
        $model = new ShoppingCart();
        $data = $model->create($params, $user['id']);
        if (($data['code'] ?? true) === false) {
            return $this->error($data['msg']);
        } else {
            return $this->success($data);
        }
    }

    //todo

    /**
     * 收货地址列表
     * @api {get} /api/V4/shopping_cart/get_list 收货地址列表
     * @apiVersion 4.0.0
     * @apiName /api/V4/shopping_cart/get_list
     * @apiGroup  shopping_cart
     * @apiSampleRequest /api/V4/shopping_cart/get_list
     * @apiDescription 收货地址列表,字段说明见创建接口
     * @apiSuccessExample {json} Request-Example:
     * 
     */
    public function getList() {
        $user = ['id' => 168934, 'level' => 4, 'is_staff' => 1];
        if (empty($user['id'] ?? 0)) {
            return $this->error('未登录');
        }
        $model = new ShoppingCart();
        $data = $model->getList($user['id']);
        return $this->success($data);
    }

    /**
     * 修改状态
     * @api {put} /api/V4/shopping_cart/status_change 修改状态
     * @apiVersion 1.0.0
     * @apiName /api/V4/shopping_cart/status_change
     * @apiGroup shopping_cart
     * @apiSampleRequest /api/V4/shopping_cart/status_change
     * @apiDescription 修改状态
     * @apiParam {string=del} flag 状态(删除)
     * @apiParam {number} id id
     *
     * @apiParamExample {json} Request-Example:
      {
      "flag":"del",
      "id":1
      }
     * @apiSuccessExample {json} Request-Example:
     * {
      "code": 200,
      "msg": "成功",
      "data": {
      "code": true,
      "msg": "成功"
      }
      }
     */
    public function statusChange(Request $request) {
        $user = ['id' => 168934, 'level' => 4, 'is_staff' => 1];
        if (empty($user['id'] ?? 0)) {
            return $this->error('未登录');
        }
        $flag = $request->input('flag', '');
        if (empty($flag)) {
            return $this->error('参数错误');
        } else {
            $flag = strtolower($flag);
        }
        $id = $request->input('id', 0);
        if (empty($id)) {
            return $this->error('参数错误');
        }

        $model = new ShoppingCart();
        $data = $model->statusChange($id, $flag, $user['id']);
        if (($data['code'] ?? true) === false) {
            return $this->error($data['msg']);
        } else {
            return $this->success($data);
        }
    }

}
