<?php


namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use App\Models\Live;
use App\Models\LiveConsole;
use App\Models\LiveForbiddenWords;
use App\Models\LiveNotice;
use App\Models\LivePush;
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


    /*****************************直播画面页部分***************************************/
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


    /**
     * 推送消息-添加(修改)
     * @api {post} /api/v4/live_console/push_msg_to_live 推送消息-添加(修改)
     * @apiVersion 4.0.0
     * @apiName /api/v4/live_console/push_msg_to_live
     * @apiGroup  直播画面页
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/live_console/push_msg_to_live
     * @apiDescription 推送消息-添加(修改)
     * @apiParam {number} live_id 直播期数id
     * @apiParam {number} ive_info_id 直播场次id
     * @apiParam {number=1,2,3,4,6,7,8} type 类型( 1专栏 2精品课 3商品 4 线下产品门票类 6新会员 7:讲座 8:听书)
     * @apiParam {number} gid 目标id(type=6时,1是360)
     * @apiParam {string} time 推送时间(2020-01-01 01:00)
     */
    public function pushMsgToLive(Request $request)
    {
        $params = $request->input();
        $model = new LivePush();
        $data = $model->add($params, $this->user['id']);
        return $this->getRes($data);
    }

    /**
     * 推送消息-列表
     * @api {get} /api/v4/live_console/push_msg_list 推送消息-列表
     * @apiVersion 4.0.0
     * @apiName /api/v4/live_console/push_msg_list
     * @apiGroup  直播画面页
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/live_console/push_msg_list
     * @apiDescription 推送消息-列表
     * @apiParam {number} [page] page
     * @apiParam {number} [size] size
     * @apiParam {number} [id] id(获取单条)
     * @apiParam {number} live_id live_id
     * @apiParam {number} live_info_id live_info_id
     *
     * @apiSuccess {number} id 推送id
     * @apiSuccess {number} live_id 直播id
     * @apiSuccess {number} live_info_id 場次id
     * @apiSuccess {number} push_type 商品類型
     * @apiSuccess {number} push_gid 目標id
     * @apiSuccess {number} click_num 點擊數
     * @apiSuccess {number} close_num 关闭数
     * @apiSuccess {number} is_push 是否推送 0已取消
     * @apiSuccess {string} push_at 预设推送时间
     * @apiSuccess {number} is_done 是否完成(1完成)
     * @apiSuccess {string} done_at 完成时间
     * @apiSuccess {number} is_self 是不是自己的(自己的能编辑删除)
     * @apiSuccess {string} order_count 单量
     * @apiSuccess {string} money_count 收益
     * @apiSuccess {string[]} info 目标信息
     *
     * @apiSuccessExample {json} Request-Example:
     * {
     * "code": 200,
     * "msg": "成功",
     * "now": 1603272248,
     * "data": [
     * {
     * "id": 9,
     * "live_id": 224,
     * "live_info_id": 346,
     * "push_type": 8,
     * "push_gid": 553,
     * "click_num": 0,
     * "close_num": 0,
     * "is_push": 0,
     * "push_at": "2020-10-21 14:50",
     * "is_self": 1,
     * "info": {
     * "id": 553,
     * "title": "孩子，把你的手给我",
     * "subtitle": "《孩子把你的手给我》的作者是海姆·G.吉诺特，此书是畅高居美国各大图书排行榜榜首。",
     * "cover_img": "/nlsg/works/20191118162916177457.png",
     * "price": "0.00",
     * "with_type": 8
     * },
     * "order_count": "暂无单",
     * "money_count": "¥暂无"
     * }
     * ]
     * }
     **/
    public function pushMsgList(Request $request)
    {
        $params = $request->input();
        $model = new LivePush();
        $data = $model->list($params, $this->user['id']);
        return $this->getRes($data);
    }

    /**
     * 推送消息-状态修改
     * @api {put} /api/v4/live_console/change_push_msg_state 推送消息-状态修改
     * @apiVersion 4.0.0
     * @apiName /api/v4/live_console/change_push_msg_state
     * @apiGroup  直播画面页
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/live_console/change_push_msg_state
     * @apiDescription 推送消息-状态修改
     * @apiParam {number} id 推送记录id
     * @apiParam {string=on,del} flag 操作(取消,删除)
     */
    public function changePushMsgState(Request $request)
    {
        $params = $request->input();
        $model = new LivePush();
        $data = $model->changeState($params, $this->user['id']);
        return $this->getRes($data);
    }

    /**
     * 公告和笔记-添加
     * @api {post} /api/v4/live_notice/add 公告和笔记-添加
     * @apiVersion 4.0.0
     * @apiName /api/v4/live_notice/add
     * @apiGroup  直播画面页
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/live_notice/add
     * @apiDescription 公告和笔记-添加
     * @apiParam {number} live_id 直播期数id
     * @apiParam {number} ive_info_id 直播场次id
     * @apiParam {number=1,2} type 类型(1公告 2笔记)
     * @apiParam {string} content 内容(最多300字)
     * @apiParam {number} [length] 公告的持续时长(秒)
     * @apiParam {string} [send_at] 推送时间,不传默认为下一分钟
     */
    public function createLiveNotice(Request $request)
    {
        $params = $request->input();
        $model = new LiveNotice();
        $data = $model->add($params, $this->user['id']);
        return $this->getRes($data);
    }

    /**
     * 公告和笔记-列表
     * @api {get} /api/v4/live_notice/list 公告和笔记-列表
     * @apiVersion 4.0.0
     * @apiName /api/v4/live_notice/list
     * @apiGroup  直播画面页
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/live_notice/list
     * @apiDescription 公告和笔记-列表
     * @apiParam {number} [page] page
     * @apiParam {number} [size] size
     * @apiParam {number} [id] id(获取单条)
     * @apiParam {number} live_id live_id
     * @apiParam {number} live_info_id live_info_id
     * @apiParam {number=1,2} [type] 类型(1公告 2笔记)
     *
     * @apiSuccess {number} id 推送id
     * @apiSuccess {number} live_id 直播id
     * @apiSuccess {number} live_info_id 場次id
     * @apiSuccess {number} content 内容
     * @apiSuccess {number} length 时长(秒)
     * @apiSuccess {number} is_send 是否推送 0已取消
     * @apiSuccess {string} send_at 预设推送时间
     * @apiSuccess {number} is_done 是否完成(1完成)
     * @apiSuccess {string} done_at 完成时间
     * @apiSuccess {number} is_self 是不是自己的(自己的能编辑删除)
     *
     * @apiSuccessExample {json} Request-Example:
     *{
     * "code": 200,
     * "msg": "成功",
     * "now": 1603350636,
     * "data": [
     * {
     * "id": 350,
     * "live_id": 224,
     * "live_info_id": 346,
     * "content": "笔记法撒旦飞洒",
     * "length": 300,
     * "send_at": "2020-10-22 14:55:00",
     * "is_send": 1,
     * "is_done": 0,
     * "done_at": null,
     * "is_self": 1
     * }
     * ]
     * }
     */
    public function liveNoticeList(Request $request)
    {
        $params = $request->input();
        $model = new LiveNotice();
        $user_id=0;
        if(isset($this->user['id']) && !empty($this->user['id'])){
            $user_id=$this->user['id'];
        }
        $data = $model->list($params, $user_id);
        return $this->getRes($data);
    }

    /**
     * 公告和笔记-修改状态
     * @api {put} /api/v4/live_notice/change_state 公告和笔记-修改状态
     * @apiVersion 4.0.0
     * @apiName /api/v4/live_notice/change_state
     * @apiGroup  直播画面页
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/live_notice/change_state
     * @apiDescription 公告和笔记-修改状态
     * @apiParam {number} id 推送记录id
     * @apiParam {string=off,del} flag 操作(取消,删除)
     */
    public function changeLiveNoticeState(Request $request)
    {
        $params = $request->input();
        $model = new LiveNotice();
        $data = $model->changeState($params, $this->user['id']);
        return $this->getRes($data);
    }

    /**
     * 禁言
     * @api {post} /api/v4/live_forbid/add 禁言
     * @apiVersion 4.0.0
     * @apiName /api/v4/live_forbid/add
     * @apiGroup  直播画面页
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/live_forbid/add
     * @apiDescription 禁言
     * @apiParam {number} live_id 直播期数id
     * @apiParam {number} ive_info_id 直播场次id
     * @apiParam {number} user_id 目标任务id(全体就是0)
     * @apiParam {string=on,off} flag on开启禁言,off关闭禁言
     */
    public function forbid(Request $request){
        $params = $request->input();
        $model = new LiveForbiddenWords();
        $data = $model->add($params, $this->user['id']);
        return $this->getRes($data);
    }

}
