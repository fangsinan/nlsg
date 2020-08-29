<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Http\Controllers\Admin\V4;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Servers\AfterSalesServers;

/**
 * 售后模块
 *
 * @author wangxh
 */
class AfterSalesController extends Controller
{

    /**
     * 售后列表和详情
     * @api {get} /api/admin_v4/after_sales/list 售后列表和详情
     * @apiVersion 4.0.0
     * @apiName /api/admin_v4/after_sales/list
     * @apiGroup  后台-售后
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/after_sales/list
     * @apiDescription 售后列表和详情
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
     * @apiParam {number=1,2} [value]  鉴定时传,1通过 2拒绝
     *
     */
    public function statusChange(Request $request)
    {
        $this->user['id'] = 168934;
        if (empty($this->user['id'] ?? 0)) {
            return $this->error(0, '未登录');
        }

        $model = new AfterSalesServers();
        $data = $model->statusChange($request->input(), $this->user['id']);
        return $this->getRes($data);
    }

}
