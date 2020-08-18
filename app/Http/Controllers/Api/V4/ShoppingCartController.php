<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ShoppingCart;

class ShoppingCartController extends Controller
{

    /**
     * 添加,编辑
     * @api {post} /api/v4/shopping_cart/create 添加,编辑
     * @apiVersion 1.0.0
     * @apiName /api/v4/shopping_cart/create
     * @apiGroup shopping_cart
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/shopping_cart/create
     * @apiDescription 添加,编辑
     * @apiParam {string} goods_id 商品id
     * @apiParam {string} sku_number sku
     * @apiParam {string} num 数量
     * @apiParam {string=replace,add} flag 添加方式(replace:覆盖数量  add:累计数量)
     * @apiParam {string} [inviter] 邀请人
     *
     * @apiParamExample {json} Request-Example:
     * {
     * "goods_id":98,
     * "sku_number":"1835913656",
     * "num":666,
     * "id":1,
     * "flag":"replace",
     * "inviter":168934
     * }
     * @apiSuccessExample {json} Request-Example:
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": {
     * "code": true,
     * "msg": "成功"
     * }
     * }
     */
    public function create(Request $request)
    {
        $params = $request->input();
        $model = new ShoppingCart();
        $data = $model->create($params, $this->user['id']);
        if (($data['code'] ?? true) === false) {
            $ps = ($this->show_ps ? (($data['ps'] ?? false) ? (':' . $data['ps']) : '') : '');
            return $this->error(0, $data['msg'] . $ps);
        } else {
            return $this->success($data);
        }
    }

    /**
     * 购物车列表
     * @api {get} /api/v4/shopping_cart/get_list 购物车列表
     * @apiVersion 4.0.0
     * @apiName /api/v4/shopping_cart/get_list
     * @apiGroup  shopping_cart
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/shopping_cart/get_list
     * @apiDescription 购物车列表
     * @apiSuccess {string[]} id list列表  invalid_list失效商品
     * @apiSuccess {string} id 购物车id
     * @apiSuccess {string} goods_id
     * @apiSuccess {string} sku_number
     * @apiSuccess {string} num
     * @apiSuccess {string} goods_name
     * @apiSuccess {string} goods_subtitle
     * @apiSuccess {string} original_price
     * @apiSuccess {string} price
     * @apiSuccess {string} sku_list
     *
     * @apiSuccessExample {json} Request-Example:
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": {
     * "list": [
     * {
     * "id": 4,
     * "goods_id": 98,
     * "sku_number": 1835913656,
     * "num": 110,
     * "invalid": 0,
     * "goods_name": "王琨专栏学习套装",
     * "goods_subtitle": "王琨老师专栏年卡1张+《琨说》珍藏版",
     * "original_price": "399.00",
     * "price": "254.15",
     * "sku_list": {
     * "id": 1369,
     * "goods_id": 98,
     * "sku_number": "1835913656",
     * "picture": "/wechat/mall/goods/8873_1545796221.png",
     * "original_price": "399.00",
     * "price": "299.00",
     * "stock": 457,
     * "status": 1,
     * "sku_value_list": [
     * {
     * "id": 241,
     * "sku_id": 1369,
     * "key_name": "规格",
     * "value_name": "王琨专栏学习套装"
     * }
     * ]
     * }
     * }
     * ],
     * "invalid_list": [
     * {
     * "id": 5,
     * "goods_id": 476,
     * "sku_number": 1654630825,
     * "num": 2,
     * "invalid": 1,
     * "goods_name": "藏在地图里的成语（全四册）",
     * "goods_subtitle": "一套与地图相结合的成语把知识断点连成一片海",
     * "original_price": "136.00",
     * "price": "101.72",
     * "sku_list": {
     * "id": 1936,
     * "goods_id": 476,
     * "sku_number": "1654630825",
     * "picture": "/nlsg/goods/20191227135102333850.jpg",
     * "original_price": "136.00",
     * "price": "119.68",
     * "stock": 99,
     * "status": 1,
     * "sku_value_list": [
     * {
     * "id": 383,
     * "sku_id": 1936,
     * "key_name": "规格",
     * "value_name": "藏在地图里的成语（全四册）"
     * }
     * ]
     * }
     * }
     * ]
     * }
     * }
     */
    public function getList()
    {
        $model = new ShoppingCart();
        $data = $model->getList($this->user);
        return $this->success($data);
    }

    /**
     * 修改状态
     * @api {put} /api/v4/shopping_cart/status_change 修改状态
     * @apiVersion 1.0.0
     * @apiName /api/v4/shopping_cart/status_change
     * @apiGroup shopping_cart
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/shopping_cart/status_change
     * @apiDescription 修改状态
     * @apiParam {string=del} flag 状态(删除)
     * @apiParam {number} id id(如  1或者1,2,3 或者[1,2,3])
     *
     * @apiParamExample {json} Request-Example:
     * {
     * "flag":"del",
     * "id":1
     * }
     * @apiSuccessExample {json} Request-Example:
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": {
     * "code": true,
     * "msg": "成功"
     * }
     * }
     */
    public function statusChange(Request $request)
    {
        $flag = $request->input('flag', '');
        if (empty($flag)) {
            return $this->error(0, '参数错误');
        } else {
            $flag = strtolower($flag);
        }
        $id = $request->input('id', 0);
        if (empty($id)) {
            return $this->error(0, '参数错误');
        }

        $model = new ShoppingCart();
        $data = $model->statusChange($id, $flag, $this->user['id']);
        if (($data['code'] ?? true) === false) {
            $ps = ($this->show_ps ? (($data['ps'] ?? false) ? (':' . $data['ps']) : '') : '');
            return $this->error(0, $data['msg'] . $ps);
        } else {
            return $this->success($data);
        }
    }

}
