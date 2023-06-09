<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Http\Controllers\Admin\V4;

use App\Http\Controllers\ControllerBackend;
use App\Servers\MallOrderServers;
use Illuminate\Http\Request;

/**
 * Description of MallOrderController
 *
 * @author wangxh
 */
class MallOrderController extends ControllerBackend
{

    /**
     * 订单列表和详情
     * @api {get} /api/admin_v4/mall_order/list 订单列表和详情
     * @apiVersion 4.0.0
     * @apiName /api/admin_v4/mall_order/list
     * @apiGroup  后台-订单管理
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/mall_order/list
     * @apiDescription 可申请售后订单和商品列表
     * @apiParam {number=0,1} flag 0列表,1详情
     * @apiParam {number} [page] 页数,默认1
     * @apiParam {number} [size] 条数,默认10
     * @apiParam {string} [ordernum] 订单编号
     * @apiParam {string} [created_at] 订单时间范围(2020-01-01,2022-02-02)
     * @apiParam {string} [pay_time] 支付时间范围
     * @apiParam {string} [pay_type] 支付渠道(1微信端 2app微信 3app支付宝 4ios)
     * @apiParam {string} [os_type] 客户端(客户端:1安卓 2ios 3微信 )
     * @apiParam {string} [phone] 账号
     * @apiParam {string} [nickname] 昵称
     * @apiParam {string} [goods_name] 品名
     * @apiParam {string} [status] 状态(参考前端订单接口文档)
     * @apiParam {string='normal','flash_sale','group_buy'} order_type 订单类型:普通,秒杀,团购
     *
     * @apiSuccessExample {json} Request-Example:
     * {
     * "code": 200,
     * "msg": "成功",
     * "data":{}
     * }
     */
    public function list(Request $request)
    {
        $servers = new MallOrderServers();
        $data = $servers->getList($request->input());
        return $this->getRes($data);
    }

    public function listNew(Request $request){
        $servers = new MallOrderServers();
        $data = $servers->listNew($request->input());
        return $this->getRes($data);
    }

    public function makeGroupSuccess(Request $request){
        $servers = new MallOrderServers();
        $data = $servers->makeGroupSuccess($request->input());
        return $this->getRes($data);
    }

    /**
     * 发货
     * @api {post} /api/admin_v4/mall_order/send 发货
     * @apiVersion 4.0.0
     * @apiName /api/admin_v4/mall_order/send
     * @apiGroup  后台-订单管理
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/mall_order/send
     * @apiDescription 发货
     * @apiParam {strint} express_id 快递公司id
     * @apiParam {strint} num 快递单号
     * @apiParam {strint} order_id 订单id
     * @apiParam {strint} order_detail_id 订单详情id
     *
     * @apiParamExample {json} Request-Example:
     * [
     * {
     * "express_id": 2,
     * "num": "YT4538526006366",
     * "order_id": 9526,
     * "order_detail_id": 10323
     * },
     * {
     * "express_id": 2,
     * "num": "YT4506367161457",
     * "order_id": 9526,
     * "order_detail_id": 10324
     * }
     * ]
     *
     * @apiSuccess {number} id id
     * @apiSuccessExample {json} Request-Example:
     * {
     * "code": 200,
     * "msg": "成功",
     * "data":{}
     * }
     */
    public function send(Request $request)
    {
        $servers = new MallOrderServers();
        $data = $servers->send($request->input('list', ''));
        return $this->getRes($data);
    }

    //商城服务信息列表
    public function tos(Request $request)
    {

    }
}
