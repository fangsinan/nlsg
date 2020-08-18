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
class ExpressController extends Controller
{

    /**
     * 快递进度查询
     * @api {get} /api/v4/post/get_info 快递进度查询
     * @apiVersion 4.0.0
     * @apiName /api/v4/post/get_info
     * @apiGroup  express
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/post/get_info
     * @apiDescription 快递进度查询
     * @apiParam {string} express_id 快递公司id
     * @apiParam {string} express_num 快递单号
     *
     *
     * @apiSuccess {string[]} list 进度
     * @apiSuccess {string} list.time 时间
     * @apiSuccess {string} list.status 进展
     *
     * @apiSuccessExample {json} Request-Example:
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": {
     * "number": "YT4538526006366",
     * "type": "yto",
     * "typename": "圆通速递",
     * "logo": "https://api.jisuapi.com/express/static/images/logo/80/yto.png",
     * "list": [
     * {
     * "time": "2020-05-24 13:23:02",
     * "status": "客户签收人: 周一派送急件电联18513793888 已签收  感谢使用圆通速递，期待再次为您服务 如有疑问请联系：18513793888，投诉电话：010-53579888"
     * }
     * ],
     * "deliverystatus": 3,
     * "issign": 1
     * }
     * }
     */
    public function getPostInfo(Request $request)
    {
//        if (empty($this->user['id'] ?? 0)) {
//            return $this->notLogin();
//        }

        $params['express_id'] = $request->input('express_id', 0);
        $params['express_num'] = $request->input('express_num', 0);

        $model = new ExpressCompany();
        $data = $model->getPostInfo($params);
        if (($data['code'] ?? true) === false) {
            $ps = ($this->show_ps ? (($data['ps'] ?? false) ? (':' . $data['ps']) : '') : '');
            return $this->error(0, $data['msg'] . $ps);
        } else {
            return $this->success($data);
        }
    }

    /**
     * 快递公司列表
     * @api {get} /api/v4/post/company_list 快递公司列表
     * @apiVersion 4.0.0
     * @apiName /api/v4/post/company_list
     * @apiGroup  express
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/post/company_list
     * @apiDescription 快递公司列表
     * @apiSuccessExample {json} Request-Example:
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": [
     * {
     * "id": 3,
     * "name": "圆通"
     * },
     * {
     * "id": 1,
     * "name": "顺丰"
     * },
     * {
     * "id": 2,
     * "name": "韵达"
     * },
     * {
     * "id": 4,
     * "name": "京东"
     * }
     * ]
     * }
     */
    public function companyList()
    {
//        if (empty($this->user['id'] ?? 0)) {
//            return $this->notLogin();
//        }
        $model = new ExpressCompany();
        $data = $model->companyList();
        if (($data['code'] ?? true) === false) {
            $ps = ($this->show_ps ? (($data['ps'] ?? false) ? (':' . $data['ps']) : '') : '');
            return $this->error(0, $data['msg'] . $ps);
        } else {
            return $this->success($data);
        }
    }

}
