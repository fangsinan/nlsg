<?php

namespace App\Http\Controllers\Live\V4;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {

    }

    /**
     * @api {get} api/live_v4/order/list 订单列表和详情
     * @apiVersion 4.0.0
     * @apiName  order/list
     * @apiGroup 直播后台-订单列表和详情
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/live_v4/order/list
     * @apiDescription  订单列表和详情
     *
     * @apiParam {number} page 分页
     * @apiParam {number} size 条数
     * @apiParam {number} [id] 单条详情传id获取
     * @apiParam {strint} [ordernum] 订单编号
     * @apiParam {strint} [created_at] 订单时间范围(2020-01-01,2022-02-02)
     * @apiParam {strint} [pay_type] 支付渠道(1微信端 2app微信 3app支付宝 4ios)
     * @apiParam {strint} [os_type] 客户端(客户端:1安卓 2ios 3微信 )
     * @apiParam {strint} [phone] 账号
     * @apiParam {strint} [title] 直播标题
     * @apiParam {number=9,10,14,15,16} [type] 订单类型(9精品课,10直播,14线下产品,15讲座,16新vip)
     *
     *
     * @apiSuccess {string[]} goods 商品信息
     * @apiSuccess {string[]} pay_record 支付信息
     * @apiSuccess {string[]} pay_record_detail 收益信息,当指定id时返回
     * @apiSuccess {string[]} live 所属直播信息
     * @apiSuccess {string[]} user 购买者信息
     * @apiSuccess {string} id 订单id
     * @apiSuccess {string} type 订单类型(9精品课,10直播,14线下产品,15讲座,16新vip)
     * @apiSuccess {string} price 商品价格
     * @apiSuccess {string} pay_price 支付金额
     * @apiSuccess {string} pay_type 支付渠道
     * @apiSuccess {string} os_type 客户端
     * @apiSuccessExample  Success-Response:
     * [
     * {
     * "id": 167376,
     * "type": 10,
     * "relation_id": "0",
     * "pay_time": "2020-04-30 15:05:16",
     * "price": "99.00",
     * "user_id": 313125,
     * "pay_price": "99.00",
     * "pay_type": 0,
     * "ordernum": "202004301505044830",
     * "live_id": 17,
     * "os_type": 3,
     * "goods": {
     * "goods_id": 0,
     * "title": "数据错误",
     * "subtitle": "",
     * "cover_img": "",
     * "detail_img": "",
     * "price": "价格数据错误"
     * },
     * "pay_record": {
     * "ordernum": "202004301505044830",
     * "price": "99.00",
     * "type": 1,
     * "created_at": "2020-04-30 15:05:16"
     * },
     * "pay_record_detail": {
     * "id": 27001,
     * "type": 10,
     * "ordernum": "202004301505044830",
     * "user_id": 234586,
     * "user": {
     * "id": 234586,
     * "phone": "15305396370",
     * "nickname": "慧宇教育-王秀翠"
     * }
     * },
     * "live": {
     * "id": 17,
     * "title": "经营家庭和孩子的秘密——发现婚姻的小幸福，成就育儿的大智慧",
     * "describe": "王琨老师本人视频直播课，帮助你拥有幸福的婚姻、成为智慧的父母、培养优秀的孩子！",
     * "begin_at": "2021-01-21 19:00:00",
     * "cover_img": "/live/liveinfo30/20200121.png"
     * },
     * "user": {
     * "id": 313125,
     * "phone": "15042623555",
     * "nickname": "清然一平常心"
     * }
     * }
     * ]
     */
    public function list(Request $request)
    {
        $model = new Order();
        $data = $model->orderInLive($request->input());
        return $this->getRes($data);
    }
}
