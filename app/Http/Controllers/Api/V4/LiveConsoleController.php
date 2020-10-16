<?php


namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use App\Models\LiveConsole;
use Illuminate\Http\Request;

class LiveConsoleController extends Controller
{
    /**
     * 创建直播
     * @api {post} /api/v4/live_console/add 创建直播
     * @apiVersion 4.0.0
     * @apiName /api/v4/live_console/add
     * @apiGroup  我的直播
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/live_console/add
     * @apiDescription 创建直播
     * @apiParam {string} title 直播间名称
     * @apiParam {string} describe 简介
     * @apiParam {string} cover_img 封面
     * @apiParam {string} price 价格
     * @apiParam {number=1,0} is_free 是否免费  1免费0收费
     * @apiParam {number=1,0} is_show 是否公开  1公开
     * @apiParam {string} password  密码
     * @apiParam {number=1,0} can_push 能否推广 1能
     * @apiParam {string} helper 助手手机号,可多条
     * @apiParam {string} msg 公告
     * @apiParam {string} content 内容介绍
     * @apiParam {string[]} list 直播时间列表
     * @apiParam {string} list.begin_at 开始时间
     * @apiParam {string} list.length 持续时长
     *
     * @apiParamExample {json} Request-Example:
     * {
     * "title": "直播间名称11",
     * "describe": "简介",
     * "cover_img": "封面.jpg",
     * "price": 10,
     * "is_free": 0,
     * "is_show": 1,
     * "password": "652635",
     * "can_push": 1,
     * "helper": "1522222222",
     * "msg": "直播预约公告",
     * "content": "直播内容介绍",
     * "list": [
     * {
     * "begin_at": "2020-09-25 20:30:00",
     * "length": 1.5
     * },
     * {
     * "begin_at": "2020-10-25 20:30:00",
     * "length": 2
     * },
     * {
     * "begin_at": "2020-10-20 20:30:00",
     * "length": 1.5
     * },
     * {
     * "begin_at": "2020-10-21 20:30:00",
     * "length": 2.2
     * }
     * ]
     * }
     */
    public function add(Request $request)
    {
        $params = $request->input();
        $model = new LiveConsole();
        $data = $model->add($params, $this->user['id']);
        return $this->success($data);
    }

    /**
     * 检查助手手机号
     * @api {post} /api/v4/live_console/check_helper 检查助手手机号
     * @apiVersion 4.0.0
     * @apiName /api/v4/live_console/check_helper
     * @apiGroup  我的直播
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/live_console/check_helper
     * @apiDescription 检查助手手机号
     */
    public function checkHelper(Request $request)
    {
        $params = $request->input();
        $model = new LiveConsole();
        $data = $model->checkHelper($params, $this->user['id']);
        return $this->success($data);
    }

    /**
     * 修改状态
     * @api {put} /api/v4/live_console/change_status 修改状态
     * @apiVersion 4.0.0
     * @apiName /api/v4/live_console/change_status
     * @apiGroup  我的直播
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/live_console/change_status
     * @apiDescription 修改状态
     * @apiParam {number} id 直播间id
     * @apiParam {string=del,off} flag 操作
     */
    public function changeStatus(Request $request)
    {
        $params = $request->input();
        $model = new LiveConsole();
        $data = $model->changeStatus($params, $this->user['id']);
        return $this->success($data);
    }
}
