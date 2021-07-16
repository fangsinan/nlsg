<?php


namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use App\Servers\ImDocServers;
use Illuminate\Http\Request;

class ImDocController extends Controller
{
    /**
     * @api {post} api/admin_v4/im_doc/add (废弃)添加文案
     * @apiVersion 4.0.0
     * @apiName  api/v4/im_doc/add
     * @apiGroup 社群文案
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/im_doc/add
     * @apiDescription (废弃)添加文案
     *
     * @apiParam {number=1,2,3} type 类型(1商品 2附件 3文本)
     * @apiParam {number} type_info 详细类型(类型 11:讲座 12课程 13商品 14会员 15直播 16训练营 21音频 22视频 23图片 31文本)
     * @apiParam {number} [obj_id]  目标id(当type=1时需要传)
     * @apiParam {string} content   内容或名称
     * @apiParam {string} [file_url]  附件地址,当type=2时需要传
     *
     */
    public function add(Request $request)
    {
        $servers = new ImDocServers();
        $data = $servers->add($request->input(), $this->user['id']);
        return $this->getRes($data);
    }

    /**
     * @api {post} api/admin_v4/im_doc/add_for_app 添加文案
     * @apiVersion 4.0.0
     * @apiName  api/v4/im_doc/add_for_app
     * @apiGroup 社群文案
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/im_doc/add_for_app
     * @apiDescription 添加文案
     *
     * @apiParam {number=1,2,3} type 类型(1商品 2附件 3文本)
     * @apiParam {number} type_info 详细类型(类型 11:讲座 12课程 13商品 14会员 15直播 16训练营 21音频 22视频 23图片 31文本)
     * @apiParam {number} [obj_id]  目标id(当type=1时需要传)
     * @apiParam {string} content   内容或名称
     * @apiParam {string} [file_url]  附件地址,当type=2时需要传
     *
     */
    public function addForApp(Request $request){
        $servers = new ImDocServers();
        $params = $request->input();
        $params['for_app'] = 1;
        $data = $servers->add($params, $this->user['id']);
        return $this->getRes($data);
    }

    /**
     * @api {get} api/v4/im_doc/list (废弃)文案列表
     * @apiVersion 4.0.0
     * @apiName  api/v4/im_doc/list
     * @apiGroup 社群文案
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/im_doc/list
     * @apiDescription 文案列表
     */
    public function list(Request $request)
    {
        $servers = new ImDocServers();
        $data = $servers->list($request->input());
        return $this->getRes($data);
    }

    /**
     * @api {put} api/v4/im_doc/change_status 文案状态修改
     * @apiVersion 4.0.0
     * @apiName  api/v4/im_doc/change_status
     * @apiGroup 社群文案
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/im_doc/change_status
     * @apiParam {number} id id
     * @apiParam {string=del} flag 动作(del:删除)
     * @apiDescription 文案状态修改
     */
    public function changeStatus(Request $request)
    {
        $servers = new ImDocServers();
        $data = $servers->changeStatus($request->input(), $this->user['id']);
        return $this->getRes($data);
    }

    /**
     * @api {post} api/v4/im_doc/job_add 添加发送任务
     * @apiVersion 4.0.0
     * @apiName  api/v4/im_doc/job_add
     * @apiGroup 社群文案
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/im_doc/job_add
     * @apiParam {number} doc_id 文案id
     * @apiParam {number=1,2} send_type 发送时间类型(1立刻 2定时)
     * @apiParam {string} [send_at] 定时时间
     * @apiParam {string[]} info 对象列表
     * @apiParam {string=1,2,3} info.send_obj_type 目标对象类型(1群组 2个人 3标签)
     * @apiParam {string} info.send_obj_id 目标id
     * @apiDescription 添加发送任务
     * @apiParamExample {json} Request-Example:
     * {
     * "doc_id": 1,
     * "send_type": 1,
     * "send_at": "",
     * "info": [
     * {
     * "type": 1,
     * "list": [
     * 1,
     * 2,
     * 3
     * ]
     * }
     * ]
     * }
     */
    public function addSendJob(Request $request)
    {
        $servers = new ImDocServers();
        $data = $servers->addSendJob($request->input(), $this->user['id']);
        return $this->getRes($data);
    }

    /**
     * @api {post} api/v4/im_doc/job_list (废弃)发送任务列表
     * @apiVersion 4.0.0
     * @apiName  api/v4/im_doc/job_list
     * @apiGroup 社群文案
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/im_doc/job_list
     * @apiParam {number=1,2,3} doc_type 文案类型(1商品 2附件 3文本)
     * @apiParam {number} doc_type_info 文案类型(类型 11:讲座 12课程 13商品 14会员 15直播 16训练营21音频 22视频 23图片 31文本)
     * @apiParam {number=0,1,2,3,4} is_done 发送结果(1待发送  2发送中 3已完成 4无任务)
     * @apiParam {number=1,2,3} send_obj_type 发送目标类型(1群组 2个人 3标签)
     * @apiParam {number} send_obj_id 发送目标id
     */
    public function sendJobList(Request $request)
    {
        $servers = new ImDocServers();
        $data = $servers->sendJobList($request->input());
        return $this->getRes($data);
    }

    public function sendJobListForApp(Request $request)
    {
        $servers = new ImDocServers();
        $data = $servers->sendJobListForApp($request->input());
        return $this->getRes($data);
    }

    /**
     * @api {put} api/v4/im_doc/change_job_status 发送任务状态修改
     * @apiVersion 4.0.0
     * @apiName  api/v4/im_doc/change_job_status
     * @apiGroup 社群文案
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/im_doc/change_job_status
     * @apiParam {number} id 任务id
     * @apiParam {string=on,off,del} flag 动作
     * @apiDescription 发送任务状态修改
     */
    public function changeJobStatus(Request $request)
    {
        $servers = new ImDocServers();
        $data = $servers->changeJobStatus($request->input(), $this->user['id']);
        return $this->getRes($data);
    }

    /**
     * @api {get} api/v4/im_doc/category 分类
     * @apiVersion 4.0.0
     * @apiName  api/v4/im_doc/category
     * @apiGroup 社群文案
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/im_doc/category
     * @apiDescription 分类的列表
     *
     * @apiParam {number} category_id 分类id 0为全部
     * @apiParam {number} type   类型  1.精品课 2 讲座 3 商品 4 直播 5训练营 6幸福360
     *
     * @apiSuccessExample  Success-Response:
     * HTTP/1.1 200 OK
     * {
     *   "code": 200,
     *   "msg" : '成功',
     *   "data": {
     *
     *    }
     * }
     */
    public function getCategory()
    {
        $servers = new ImDocServers();
        $data = $servers->getCategory();
        return $this->getRes($data);
    }

    /**
     * @api {get} api/v4/im_doc/category/product 分类筛选的商品列表
     * @apiVersion 4.0.0
     * @apiName  api/v4/im_doc/category/product
     * @apiGroup 社群文案
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/im_doc/category/product
     * @apiDescription 分类筛选的商品列表
     *
     * @apiParam {number} category_id 分类id 0为全部
     * @apiParam {number} type   类型  1.精品课 2 讲座 3 商品 4 直播 5训练营 6幸福360
     *
     * @apiSuccessExample  Success-Response:
     * HTTP/1.1 200 OK
     * {
     *   "code": 200,
     *   "msg" : '成功',
     *   "data": {
     *
     *    }
     * }
     */
    public function getCategoryProduct(Request $request)
    {
        $servers = new ImDocServers();
        $data = $servers->getCategoryProduct($request->input());
        return $this->getRes($data);
    }
}
