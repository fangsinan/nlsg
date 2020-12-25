<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Http\Controllers\Admin\V4;

use App\Http\Controllers\ControllerBackend;
use App\Servers\AfterSalesServers;
use Illuminate\Http\Request;

/**
 * 售后模块
 *
 * @author wangxh
 */
class AfterSalesController extends ControllerBackend
{

    /**
     * 售后列表和详情
     * @api {get} /api/admin_v4/after_sales/list 售后列表和详情
     * @apiVersion 4.0.0
     * @apiName /api/admin_v4/after_sales/list
     * @apiGroup  后台-售后
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/after_sales/list
     * @apiDescription 售后列表和详情
     * @apiSuccess {string} service_num 售后订单
     * @apiSuccess {string} user_info 用户信息
     * @apiSuccess {string} os_type 订单来源(1安卓 2ios 3微信 )
     * @apiSuccess {string} pay_type 支付渠道(1 微信端 2app微信 3app支付宝 4ios)
     * @apiSuccess {string} info_order 售前单号信息
     * @apiSuccess {string} type  申请类型( 1退款 2退货)
     * @apiSuccess {string} reason_id 申请原因(1,商品问题,2客服问题,3物流问题,4其他问题
     * @apiSuccess {string} description 申请描述
     * @apiSuccess {string} picture 凭证
     * @apiSuccess {string} created_at 时间
     */
    public function list(Request $request)
    {
        $servers = new AfterSalesServers();
        $data = $servers->getList($request->input());
        return $this->getRes($data);
    }

    /**
     *审核,鉴定
     * @api {get} /api/admin_v4/after_sales/status_change 审核,鉴定
     * @apiVersion 4.0.0
     * @apiName /api/admin_v4/after_sales/status_change
     * @apiGroup  后台-售后
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/after_sales/status_change
     * @apiDescription 审核,鉴定
     * @apiParam {number} id id
     * @apiParam {string=check,identify} flag check审核,identify鉴定
     * @apiParam {number=1,2} [value]  审核时传,1通过 2拒绝
     * @apiParam {number} [return_address_id] 退货地址id
     *
     */
    public function statusChange(Request $request)
    {
        if (empty($this->user['id'] ?? 0)) {
            return $this->error(0, '未登录');
        }
        $model = new AfterSalesServers();
        $data = $model->statusChange($request->input(), $this->user['id']);
        return $this->getRes($data);
    }

}
