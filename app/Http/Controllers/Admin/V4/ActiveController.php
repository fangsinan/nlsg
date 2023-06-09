<?php


namespace App\Http\Controllers\Admin\V4;

use App\Http\Controllers\ControllerBackend;
use App\Servers\ActiveServers;
use Illuminate\Http\Request;

class ActiveController extends ControllerBackend
{
    /**
     * 活动列表和详情
     * @api {get} /api/admin_v4/active/list 活动列表和详情
     * @apiVersion 4.0.0
     * @apiName /api/admin_v4/active/list
     * @apiGroup  后台-活动
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/active/list
     * @apiDescription 活动列表和详情
     */
    public function list(Request $request)
    {
        $servers = new ActiveServers();
        $data = $servers->list($request->input());
        return $this->getRes($data);
    }

    /**
     * 活动列表和详情
     * @api {post} /api/admin_v4/active/add 添加编辑
     * @apiVersion 4.0.0
     * @apiName /api/admin_v4/active/add
     * @apiGroup  后台-活动
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/active/add
     * @apiDescription 添加编辑
     */
    public function add(Request $request)
    {
        $servers = new ActiveServers();
        $data = $servers->add($request->input());
        return $this->getRes($data);
    }

    /**
     * 添加模块和绑定商品
     * @api {post} /api/v4/active/binding 添加模块和绑定商品
     * @apiVersion 1.0.0
     * @apiName /api/v4/active/binding
     * @apiGroup 后台-活动
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/active/binding
     * @apiDescription 添加模块和绑定商品
     * @apiParam {string[]} data 提交数据
     *
     * @apiParamExample {json} Request-Example:
     * {
     * "active_id": 4,
     * "module_list": [
     * {
     * "title": "板块1",
     * "goods_list": [1,2,3,4,5]
     * },
     * {
     * "title": "板块2",
     * "goods_list": [1,2,3,4,5]
     * }
     * ]
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
    public function binding(Request $request)
    {
        $servers = new ActiveServers();
        $data = $servers->binding($request->input());
        return $this->getRes($data);
    }

    /**
     * 修改状态
     * @api {put} /api/admin_v4/active/status_change 修改状态
     * @apiVersion 4.0.0
     * @apiName /api/admin_v4/active/status_change
     * @apiGroup  后台-活动
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/active/status_change
     * @apiDescription 修改状态
     */
    public function statusChange(Request $request)
    {
        $servers = new ActiveServers();
        $data = $servers->statusChange($request->input());
        return $this->getRes($data);
    }

}
