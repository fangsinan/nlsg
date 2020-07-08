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
 * 售后相关
 *
 * @author wangxh
 */
class AfterSalesController extends Controller {

    /**
     * 可申请售后订单和商品列表
     * @api {get} /api/V4/after_sales/goods_list 可申请售后订单和商品列表
     * @apiVersion 4.0.0
     * @apiName /api/V4/after_sales/goods_list
     * @apiGroup  afterSales
     * @apiSampleRequest /api/V4/after_sales/goods_list
     * @apiDescription 可申请售后订单和商品列表
     * @apiParam {number} [page] 页数,默认1
     * @apiParam {number} [size] 条数,默认10
     * @apiParam {number} [order_detail_id] 订单详情id
     * 
     * @apiSuccess {number} ordernum 订单编号
     * @apiSuccess {number} order_id 订单id
     * @apiSuccess {number} order_detail_id 订单详情id
     * @apiSuccess {number} goods_id 商品id
     * @apiSuccess {number} sku_number sku
     * @apiSuccess {number} goods_name 品名
     * @apiSuccess {number} subtitle 副标题
     * @apiSuccess {number} num 可申请数量
     * @apiSuccess {number} sku_value 规格信息
     * @apiSuccessExample {json} Request-Example:
      {
      "code": 200,
      "msg": "成功",
      "data": [
      {
      "ordernum": "2006280016893465633601",
      "order_id": 9560,
      "order_detail_id": 10367,
      "goods_id": 160,
      "sku_number": "1904221194",
      "goods_name": "少有人走的路",
      "subtitle": "武志红 张德芬 胡茵梦等名人大咖推荐",
      "receipt_at": "2020-07-02 14:47:22",
      "num": 2,
      "sku_value": [
      {
      "key_name": "少有人走的路",
      "value_name": "勇敢地面对谎言"
      }
      ],
      "is_pass": 0
      }
      ]
      }
     */
    public function goodsList(Request $request) {
        $user = ['id' => 168934, 'level' => 4, 'is_staff' => 1];
        if (empty($user['id'] ?? 0)) {
            return $this->error(0, '未登录');
        }
        $params = $request->input();
        $model = new MallRefundRecord();
        $data = $model->goodsList($params, $user);
        if (($data['code'] ?? true) === false) {
            $ps = ($this->show_ps ? (($data['ps'] ?? false) ? (':' . $data['ps']) : '') : '');
            return $this->error(0, $data['msg'] . $ps);
        } else {
            return $this->success($data);
        }
    }

    /**
     * 申请售后
     * @api {post} /api/V4/after_sales/create_order 申请售后
     * @apiVersion 4.0.0
     * @apiName /api/V4/after_sales/create_order
     * @apiGroup  afterSales
     * @apiSampleRequest /api/V4/after_sales/create_order
     * @apiDescription 申请售后
     * 
     * @apiParam {number=1,2} type  1退款2退货
     * @apiParam {number} order_id order_id
     * @apiParam {number} order_detail_id 订单详情id
     * @apiParam {number} [num] 退货的申请数量
     */
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

    /**
     * 售后列表
     * @api {get} /api/V4/after_sales/list 售后列表
     * @apiVersion 4.0.0
     * @apiName /api/V4/after_sales/list
     * @apiGroup  afterSales
     * @apiSampleRequest /api/V4/after_sales/list
     * @apiDescription 售后列表
     * @apiParam {number} [page] 页数,默认1
     * @apiParam {number} [size] 条数,默认10
     * @apiParam {number} [status] 状态(全部0,待审核10,待寄回20,待鉴定30,待退款40,已完成:60,已取消99)
     * 
     * @apiSuccess {number} id id
     * @apiSuccess {number} service_num 服务单号
     * @apiSuccess {number} order_id 订单id
     * @apiSuccess {number} type 类型(1退款,2退货)
     * @apiSuccess {number} refe_price 预计退款金额
     * @apiSuccess {number} price 实际退款金额
     * @apiSuccess {number} status 状态
     * @apiSuccess {string[]} goods_list 商品列表
     * @apiSuccess {number} goods_list.goods_id 商品id
     * @apiSuccess {number} goods_list.name 品名
     * @apiSuccess {number} goods_list.subtitle 副标题
     * @apiSuccess {number} goods_list.picture 图片
     * @apiSuccess {number} goods_list.num 数量
     * @apiSuccess {number} goods_list.price 单价
     * @apiSuccessExample {json} Request-Example:
      {
      "code": 200,
      "msg": "成功",
      "data": [
      {
      "id": 2,
      "service_num": "2007030016893462686832",
      "order_id": 9560,
      "order_detail_id": 10367,
      "type": 2,
      "num": 1,
      "cost_price": "10.00",
      "refe_price": "0.00",
      "price": "0.00",
      "status": 99,
      "user_cancel": 0,
      "user_cancel_time": null,
      "goods_list": [
      {
      "goods_id": 160,
      "name": "少有人走的路",
      "subtitle": "武志红 张德芬 胡茵梦等名人大咖推荐",
      "picture": "/wechat/mall/goods/7700_1532401324.png",
      "num": 1,
      "price": "10.00"
      }
      ]
      }
      ]
      }
     */
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

    /**
     * 售后详情
     * @api {get} /api/V4/after_sales/order_info 售后详情
     * @apiVersion 4.0.0
     * @apiName /api/V4/after_sales/order_info
     * @apiGroup  afterSales
     * @apiSampleRequest /api/V4/after_sales/order_info
     * @apiDescription 售后详情
     * @apiParam {number} id id
     * 
     * @apiSuccess {number} id id
     * @apiSuccess {string} service_num 售后单号
     * @apiSuccess {number} order_id 订单id
     * @apiSuccess {number} order_detail_id 订单详情id
     * @apiSuccess {number} type 类型(1退款2退货)
     * @apiSuccess {number} user_cancel 1用户取消
     * @apiSuccess {number} user_cancel_time 用户取消时间
     * @apiSuccess {string} created_at 提交时间
     * @apiSuccess {string} picture 图片
     * @apiSuccess {string} pass_at 审核通过时间
     * @apiSuccess {string} check_at 验货时间
     * @apiSuccess {string} receive_at 收货时间
     * @apiSuccess {string} succeed_at succeed_at
     * @apiSuccess {number} reason_id 售后原因id
     * @apiSuccess {string} description 买家退货描述
     * @apiSuccess {number} is_check_reject 1:审核拒绝 2:审核通过
     * @apiSuccess {number} check_reject_at 审核拒绝时间
     * @apiSuccess {string} check_remark 审核备注
     * @apiSuccess {number} is_authenticate_reject
     * @apiSuccess {number} authenticate_reject_at 鉴定时间
     * @apiSuccess {string} authenticate_remark 鉴定备注
     * @apiSuccess {number} express_id 寄回快递公司id
     * @apiSuccess {number} express_num 寄回快递单号
     * @apiSuccess {string} express_name 寄回快递公司名称
     * @apiSuccess {string[]} goods_list 商品列表
     * @apiSuccess {string[]} refund_address 售后点信息
     * @apiSuccessExample {json} Request-Example:
     * {
      "id": 2,
      "service_num": "2007030016893462686832",
      "order_id": 9560,
      "order_detail_id": 10367,
      "type": 2,
      "num": 1,
      "cost_price": "10.00",
      "refe_price": "0.00",
      "price": "0.00",
      "status": 99,
      "user_cancel": 0,
      "user_cancel_time": null,
      "created_at": "2020-07-03 17:24:46",
      "return_address_id": 10,
      "picture": "",
      "pass_at": null,
      "check_at": null,
      "receive_at": null,
      "succeed_at": null,
      "reason_id": 0,
      "description": null,
      "is_check_reject": 0,
      "check_reject_at": null,
      "check_remark": "",
      "is_authenticate_reject": 0,
      "authenticate_reject_at": null,
      "authenticate_remark": "",
      "express_id": 0,
      "express_num": "",
      "goods_list": [
      {
      "goods_id": 160,
      "name": "少有人走的路",
      "subtitle": "武志红 张德芬 胡茵梦等名人大咖推荐",
      "picture": "/wechat/mall/goods/7700_1532401324.png",
      "num": 1,
      "price": "10.00"
      }
      ],
      "express_name": "",
      "refund_address": {
      "id": 10,
      "name": "退货点1",
      "admin_name": "啊哈哈",
      "admin_phone": "20349024",
      "province": 110000,
      "city": 110105,
      "area": 0,
      "details": "朝阳路85号",
      "province_name": "北京",
      "city_name": "朝阳",
      "area_name": ""
      }
      }
     */
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

    /**
     * 修改状态
     * @api {put} /api/V4/after_sales/status_change 修改状态
     * @apiVersion 1.0.0
     * @apiName /api/V4/after_sales/status_change
     * @apiGroup afterSales
     * @apiSampleRequest /api/V4/after_sales/status_change
     * @apiDescription 修改状态
     * @apiParam {string=stop,del} flag 状态(取消,删除)
     * @apiParam {number} id id
     *
     * @apiParamExample {json} Request-Example:
      {
      "flag":"stop",
      "id":2
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
            return $this->error(0, '未登录');
        }
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
        $model = new MallRefundRecord();
        $data = $model->statusChange($id, $flag, $user['id']);
        if (($data['code'] ?? true) === false) {
            $ps = ($this->show_ps ? (($data['ps'] ?? false) ? (':' . $data['ps']) : '') : '');
            return $this->error(0, $data['msg'] . $ps);
        } else {
            return $this->success($data);
        }
    }

    /**
     * 寄回商品
     * @api {put} /api/V4/after_sales/refund_post 寄回商品
     * @apiVersion 1.0.0
     * @apiName /api/V4/after_sales/refund_post
     * @apiGroup afterSales
     * @apiSampleRequest /api/V4/after_sales/refund_post
     * @apiDescription 寄回商品
     * @apiParam {number} id id
     * @apiParam {number} express_id 快递公司id
     * @apiParam {string} express_num  快递单号
     *
     * @apiParamExample {json} Request-Example:
      {
      "express_id":1,
      "express_num":"42134234242",
      "id":2
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
    public function refundPost(Request $request) {
        $user = ['id' => 168934, 'level' => 4, 'is_staff' => 1];
        if (empty($user['id'] ?? 0)) {
            return $this->error(0, '未登录');
        }
        $params = $request->input();
        $model = new MallRefundRecord();
        $data = $model->refundPost($params, $user);
        if (($data['code'] ?? true) === false) {
            $ps = ($this->show_ps ? (($data['ps'] ?? false) ? (':' . $data['ps']) : '') : '');
            return $this->error(0, $data['msg'] . $ps);
        } else {
            return $this->success($data);
        }
    }

}
