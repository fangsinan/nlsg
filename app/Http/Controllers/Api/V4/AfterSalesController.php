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
class AfterSalesController extends Controller
{

    /**
     * 可申请售后订单和商品列表
     * @api {get} /api/v4/after_sales/goods_list 可申请售后订单和商品列表
     * @apiVersion 4.0.0
     * @apiName /api/v4/after_sales/goods_list
     * @apiGroup  afterSales
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/after_sales/goods_list
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
     * @apiSuccess {number=1,0} is_pass 1:失效
     * @apiSuccessExample {json} Request-Example:
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": [
     * {
     * "ordernum": "2006280016893465633601",
     * "order_id": 9560,
     * "order_detail_id": 10367,
     * "goods_id": 160,
     * "sku_number": "1904221194",
     * "goods_name": "少有人走的路",
     * "subtitle": "武志红 张德芬 胡茵梦等名人大咖推荐",
     * "receipt_at": "2020-07-02 14:47:22",
     * "num": 2,
     * "sku_value": [
     * {
     * "key_name": "少有人走的路",
     * "value_name": "勇敢地面对谎言"
     * }
     * ],
     * "is_pass": 0
     * }
     * ]
     * }
     */
    public function goodsList(Request $request)
    {
        $params = $request->input();
        $model = new MallRefundRecord();
        $data = $model->goodsList($params, $this->user);
        return $this->getRes($data);
    }

    /**
     * 申请售后
     * @api {post} /api/v4/after_sales/create_order 申请售后
     * @apiVersion 4.0.0
     * @apiName /api/v4/after_sales/create_order
     * @apiGroup  afterSales
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/after_sales/create_order
     * @apiDescription 申请售后
     *
     * @apiParam {number=1,2} type  1退款2退货
     * @apiParam {number} order_id order_id
     * @apiParam {number} order_detail_id 订单详情id
     * @apiParam {number} [num] 退货的申请数量
     * @apiParam {number} reason_id 理由id
     * @apiParam {string} picture 图片(字符串,数组)
     * @apiParam {string} description 用户退货描述
     */
    public function createOrder(Request $request)
    {
        $params = $request->input();
        $model = new MallRefundRecord();
        $data = $model->createOrder($params, $this->user);
        return $this->getRes($data);
    }

    /**
     * 售后列表
     * @api {get} /api/v4/after_sales/list 售后列表
     * @apiVersion 4.0.0
     * @apiName /api/v4/after_sales/list
     * @apiGroup  afterSales
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/after_sales/list
     * @apiDescription 售后列表
     * @apiParam {number} [page] 页数,默认1
     * @apiParam {number} [size] 条数,默认10
     * @apiParam {number} [status] 状态(全部0,待审核10,待寄回20,待鉴定30,待退款40,已完成:60,已驳回:70,已取消99)
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
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": [
     * {
     * "id": 2,
     * "service_num": "2007030016893462686832",
     * "order_id": 9560,
     * "order_detail_id": 10367,
     * "type": 2,
     * "num": 1,
     * "cost_price": "10.00",
     * "refe_price": "0.00",
     * "price": "0.00",
     * "status": 99,
     * "user_cancel": 0,
     * "user_cancel_time": null,
     * "goods_list": [
     * {
     * "goods_id": 160,
     * "name": "少有人走的路",
     * "subtitle": "武志红 张德芬 胡茵梦等名人大咖推荐",
     * "picture": "/wechat/mall/goods/7700_1532401324.png",
     * "num": 1,
     * "price": "10.00"
     * }
     * ]
     * }
     * ]
     * }
     */
    public function list(Request $request)
    {
        $params = $request->input();
        $model = new MallRefundRecord();
        $data = $model->list($params, $this->user);
        return $this->getRes($data);
    }

    /**
     * 售后详情和进度条
     * @api {get} /api/v4/after_sales/order_info 售后详情
     * @apiVersion 4.0.0
     * @apiName /api/v4/after_sales/order_info
     * @apiGroup  afterSales
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/after_sales/order_info
     * @apiDescription 售后详情
     * @apiParam {number} id id
     * @apiParam {number=1,0} only_bar 是否只返回进度条(1是0否)
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
     * @apiSuccess {string[]} express_info 寄回物流信息
     * @apiSuccess {string[]} progress_bar 进度条
     * @apiSuccessExample {json} Request-Example:
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": {
     * "id": 2,
     * "service_num": "2007030016893462686832",
     * "order_id": 9560,
     * "order_detail_id": 10367,
     * "type": 2,
     * "num": 1,
     * "cost_price": "10.00",
     * "refe_price": "0.00",
     * "price": "0.00",
     * "status": 99,
     * "user_cancel": 1,
     * "user_cancel_time": "2020-07-08 13:51:57",
     * "created_at": "2020-07-03 17:24:46",
     * "return_address_id": 10,
     * "picture": "",
     * "pass_at": "2020-07-24 15:46:52",
     * "check_at": "2020-07-23 15:47:01",
     * "receive_at": "2020-07-12 15:47:06",
     * "succeed_at": "2020-07-25 15:47:10",
     * "reason_id": 0,
     * "description": null,
     * "is_check_reject": 0,
     * "check_reject_at": "2020-07-23 15:47:21",
     * "check_remark": "",
     * "is_authenticate_reject": 0,
     * "authenticate_reject_at": null,
     * "authenticate_remark": "",
     * "express_info_id": 1,
     * "goods_list": [
     * {
     * "goods_id": 160,
     * "name": "少有人走的路",
     * "subtitle": "武志红 张德芬 胡茵梦等名人大咖推荐",
     * "picture": "/wechat/mall/goods/7700_1532401324.png",
     * "num": 1,
     * "price": "10.00"
     * }
     * ],
     * "express_name": "",
     * "refund_address": {
     * "id": 10,
     * "name": "退货点1",
     * "admin_name": "啊哈哈",
     * "admin_phone": "20349024",
     * "province": 110000,
     * "city": 110105,
     * "area": 0,
     * "details": "朝阳路85号",
     * "province_name": "北京",
     * "city_name": "朝阳",
     * "area_name": ""
     * },
     * "progress_bar": [
     * {
     * "time": "2020-07-25 15:47",
     * "status": "退款完毕文本"
     * },
     * {
     * "time": "1970-01-01 08:00",
     * "status": "鉴定待退款文本"
     * },
     * {
     * "time": "2020-07-24 15:46",
     * "status": "通过,寄回文本"
     * },
     * {
     * "time": "2020-07-03 17:24",
     * "status": "提交申请"
     * }
     * ],
     * "express_info": {
     * "id": 1,
     * "history": {
     * "number": "YT4538526006366",
     * "type": "yto",
     * "typename": "圆通速递",
     * "logo": "https://api.jisuapi.com/express/static/images/logo/80/yto.png",
     * "list": [
     * {
     * "time": "2020-05-24 13:23:02",
     * "status": "客户签收人: 周一派送急件电联18513793888 已签收  感谢使用圆通速递，期待再次为您服务 如有疑问请联系：18513793888，投诉电话：010-53579888"
     * },
     * {
     * "time": "2020-05-22 15:38:58",
     * "status": "【浙江省金华市永康市公司】 已收件 取件人: 00773969 (15268689991)"
     * }
     * ],
     * "deliverystatus": 3,
     * "issign": 1
     * }
     * }
     * }
     * }
     */
    public function orderInfo(Request $request)
    {
        $params = $request->input();
        $model = new MallRefundRecord();
        $data = $model->orderInfo($params, $this->user);
        return $this->getRes($data);
    }

    /**
     * 修改状态
     * @api {put} /api/v4/after_sales/status_change 修改状态
     * @apiVersion 1.0.0
     * @apiName /api/v4/after_sales/status_change
     * @apiGroup afterSales
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/after_sales/status_change
     * @apiDescription 修改状态
     * @apiParam {string=stop,del} flag 状态(取消,删除)
     * @apiParam {number} id id
     *
     * @apiParamExample {json} Request-Example:
     * {
     * "flag":"stop",
     * "id":2
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
        $model = new MallRefundRecord();
        $data = $model->statusChange($id, $flag, $this->user['id']);
        return $this->getRes($data);
    }

    /**
     * 寄回商品
     * @api {put} /api/v4/after_sales/refund_post 寄回商品
     * @apiVersion 1.0.0
     * @apiName /api/v4/after_sales/refund_post
     * @apiGroup afterSales
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/after_sales/refund_post
     * @apiDescription 寄回商品
     * @apiParam {number} id id
     * @apiParam {number} express_id 快递公司id
     * @apiParam {string} express_num  快递单号
     *
     * @apiParamExample {json} Request-Example:
     * {
     * "express_id":1,
     * "express_num":"42134234242",
     * "id":2
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
    public function refundPost(Request $request)
    {
        $params = $request->input();
        $model = new MallRefundRecord();
        $data = $model->refundPost($params, $this->user);
        return $this->getRes($data);
    }

    /**
     * 售后原因列表
     * @api {get} /api/v4/after_sales/reason_list 售后原因列表
     * @apiVersion 1.0.0
     * @apiName /api/v4/after_sales/reason_list
     * @apiGroup afterSales
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/after_sales/reason_list
     * @apiDescription 售后原因列表
     * @apiSuccessExample {json} Request-Example:
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": [
     * {
     * "id": 1,
     * "value": "不喜欢/不想要",
     * "status": 1
     * },
     * {
     * "id": 2,
     * "value": "颜色/图案/款式等不符",
     * "status": 1
     * },
     * {
     * "id": 3,
     * "value": "包装/商品破损/污渍",
     * "status": 1
     * },
     * {
     * "id": 4,
     * "value": "少件/漏发",
     * "status": 1
     * },
     * {
     * "id": 5,
     * "value": "发票问题",
     * "status": 1
     * },
     * {
     * "id": 6,
     * "value": "卖家发错货",
     * "status": 1
     * },
     * {
     * "id": 7,
     * "value": "退运费",
     * "status": 1
     * }
     * ]
     * }
     */
    public function reasonList()
    {
        $res = \App\Models\ConfigModel::getData(15);
        $res = json_decode($res, true);
        foreach ($res as $k => $v) {
            if ($v['status'] != 1) {
                unset($res[$k]);
            }
        }
        return $this->success($res);
    }

}
