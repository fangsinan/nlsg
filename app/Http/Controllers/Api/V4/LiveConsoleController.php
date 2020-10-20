<?php


namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use App\Models\Live;
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
     * @apiParam {string} playback_price 回放价格
     * @apiParam {string} twitter_money 分校金额
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
        return $this->getRes($data);
    }

    /**
     * 检查助手手机号
     * @api {post} /api/v4/live_console/check_helper 检查助手手机号
     * @apiVersion 4.0.0
     * @apiName /api/v4/live_console/check_helper
     * @apiGroup  我的直播
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/live_console/check_helper
     * @apiDescription 检查助手手机号
     * @apiParam {string} helper 手机号,可多条
     */
    public function checkHelper(Request $request)
    {
        $params = $request->input();
        $model = new LiveConsole();
        $data = $model->checkHelper($params, $this->user['id']);
        return $this->getRes($data);
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
        return $this->getRes($data);
    }

    /**
     * 详情
     * @api {get} /api/v4/live_console/info 详情
     * @apiVersion 4.0.0
     * @apiName /api/v4/live_console/info
     * @apiGroup  我的直播
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/live_console/info
     * @apiDescription 详情
     * @apiParam {number} id 直播间id
     *
     * @apiSuccess {string} title 名称
     * @apiSuccess {string} describe 简介
     * @apiSuccess {string} cover_img 封面
     * @apiSuccess {number} status 状态( 1:待审核  2:已取消 3:已驳回  4:通过)
     * @apiSuccess {string} msg 公告
     * @apiSuccess {string} content 直播内容介绍
     * @apiSuccess {string} reason 驳回原因
     * @apiSuccess {string} check_time 驳回或通过时间
     * @apiSuccess {number} price 价格
     * @apiSuccess {number} playback_price 回放价格
     * @apiSuccess {number} is_finish 当status=4的时候  is_finish=1表示已结束 0表示待直播
     * @apiSuccess {string} helper 助手
     * @apiSuccess {number} is_free 是否免费
     * @apiSuccess {number} is_show 是否公开
     * @apiSuccess {number} can_push 是否退光
     * @apiSuccess {string[]} statistics 相关统计
     * @apiSuccess {string[]} info_list 场次列表
     * @apiSuccess {string} info_list.begin_at 开始时间
     * @apiSuccess {number} info_list.length 时长
     * @apiSuccessExample {json} Request-Example:
     * {
     * "code": 200,
     * "msg": "成功",
     * "now": 1602818012,
     * "data": {
     * "id": 223,
     * "title": "直播间名称11",
     * "describe": "简介",
     * "cover_img": "封面.jpg",
     * "status": 2,
     * "msg": "直播预约公告",
     * "content": "直播内容介绍",
     * "reason": "",
     * "check_time": null,
     * "price": "10.00",
     * "helper": "18624078563,18500065188,15081920892",
     * "is_free": 0,
     * "is_show": 1,
     * "can_push": 1,
     * "info_list": [
     * {
     * "id": 339,
     * "begin_at": "2020-10-20 20:30:00",
     * "end_at": "2020-10-20 22:00:00",
     * "length": 1.5,
     * "live_pid": 223
     * },
     * {
     * "id": 340,
     * "begin_at": "2020-10-21 20:30:00",
     * "end_at": "2020-10-21 22:42:00",
     * "length": 2.2,
     * "live_pid": 223
     * },
     * {
     * "id": 341,
     * "begin_at": "2020-10-25 20:30:00",
     * "end_at": "2020-10-25 22:30:00",
     * "length": 2,
     * "live_pid": 223
     * }
     * ]
     * }
     * }
     */
    public function info(Request $request)
    {
        $id = $request->input('id', 0);
        $model = new LiveConsole();
        $data = $model->info($id, $this->user['id']);
        return $this->getRes($data);
    }

    /**
     * 列表
     * @api {get} /api/v4/live_console/list 列表
     * @apiVersion 4.0.0
     * @apiName /api/v4/live_console/list
     * @apiGroup  我的直播
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/live_console/list
     * @apiDescription 列表
     * @apiParam {number=1,2,3,4} list_flag 列表类型(1待审核 2已取消 3待直播 4已结束)
     * @apiParam {number} [page] page
     * @apiParam {number} [size] size
     *
     * @apiSuccess {string} status 直播状态(1:待审核,2:已取消,3:已驳回,4:通过)
     * @apiSuccessExample {json} Request-Example:
     *{
     * "code": 200,
     * "msg": "成功",
     * "now": 1602838648,
     * "data": [
     * {
     * "id": 223,
     * "title": "直播间名称11",
     * "describe": "简介",
     * "cover_img": "封面.jpg",
     * "status": 2,
     * "msg": "直播预约公告",
     * "content": "直播内容介绍",
     * "reason": "",
     * "check_time": null,
     * "price": "10.00",
     * "helper": "18624078563,18500065188,15081920892",
     * "is_free": 0,
     * "is_show": 1,
     * "can_push": 1,
     * "nickname": "chandler",
     * "end_at": "2020-10-25 22:30:00",
     * "all_pass_flag": 0,
     * "list_flag": 2,
     * "info_list": [
     * {
     * "id": 339,
     * "begin_at": "2020-10-20 20:30:00",
     * "end_at": "2020-10-20 22:00:00",
     * "length": 1.5,
     * "live_pid": 223,
     * "playback_url": ""
     * },
     * {
     * "id": 340,
     * "begin_at": "2020-10-21 20:30:00",
     * "end_at": "2020-10-21 22:42:00",
     * "length": 2.2,
     * "live_pid": 223,
     * "playback_url": ""
     * },
     * {
     * "id": 341,
     * "begin_at": "2020-10-25 20:30:00",
     * "end_at": "2020-10-25 22:30:00",
     * "length": 2,
     * "live_pid": 223,
     * "playback_url": ""
     * }
     * ]
     * }
     * ]
     * }
     *
     **/
    public function list(Request $request)
    {
        $params = $request->input();
        $model = new LiveConsole();
        //$data = $model->list($params, $this->user['id']);
        $data = $model->listNew($params, $this->user['id']);
        return $this->getRes($data);
    }


    /**
     * 开始,结束直播
     * @api {put} /api/v4/live_console/change_info_status 开始,结束直播
     * @apiVersion 4.0.0
     * @apiName /api/v4/live_console/change_info_status
     * @apiGroup  直播画面页
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/live_console/change_info_status
     * @apiDescription 开始,结束直播
     * @apiParam {number} live_id 直播期数id
     * @apiParam {number} live_info_id 直播场次id
     * @apiParam {string=on,finish} flag 操作(开始,结束)
     */
    public function changeInfoState(Request $request)
    {
        $params = $request->input();
        $model = new LiveConsole();
        $data = $model->changeInfoState($params, $this->user['id']);
        return $this->getRes($data);
    }

}
