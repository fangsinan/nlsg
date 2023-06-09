<?php


namespace App\Http\Controllers\Admin\V4;


use App\Http\Controllers\ControllerBackend;
use App\Servers\ImDocServers;
use Illuminate\Http\Request;

class ImDocController extends ControllerBackend
{

    /**
     * @api {post} api/admin_v4/im_doc/group_list 群列表
     * @apiVersion 4.0.0
     * @apiName  api/admin_v4/im_doc/group_list
     * @apiGroup 后台-社群文案
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/im_doc/group_list
     * @apiDescription 群列表
     */
    public function groupList(Request $request)
    {
        $servers = new ImDocServers();
        $data = $servers->groupList($request->input(), $this->user['user_id']);
        return $this->getRes($data);
    }

    /**
     * @api {post} api/admin_v4/im_doc/add 添加文案
     * @apiVersion 4.0.0
     * @apiName  api/admin_v4/im_doc/add
     * @apiGroup 后台-社群文案
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/im_doc/add
     * @apiDescription 添加文案
     *
     * @apiParam {number=1,2,3} type 类型(1商品 2附件 3文本)
     * @apiParam {number} type_info 详细类型(类型 11:讲座 12课程 13商品 14会员 15直播 16训练营 17外链  18线下课 19听书 21音频 22视频 23图片 24文件 31文本)
     * @apiParam {number} [obj_id]  目标id(当type=1时需要传)
     * @apiParam {string} content   内容或名称(type=1如果是商品类型传商品的标题,外链类型传网址)
     * @apiParam {string} [subtitle]   副标题(外链类型传网址说明名称)
     * @apiParam {string} cover_img   封面图片(type=1必穿)
     * @apiParam {string} [media_id]  媒体id(type=2时必传,如果是图片,可逗号拼接多个)
     *
     *
     * @apiParamExample {json} Request-Example:
     *[
     * {
     * "type": 1,
     * "type_info": 11,
     * "obj_id": 448,
     * "content": "44节科学探索课，开启孩子自然科学之门",
     * "cover_img": "nlsg/authorpt/20201229114832542932.png",
     * "subtitle": "浩瀚宇宙、海洋世界、恐龙时代、昆虫家族，精美视频动画展现前沿的科学知识，让孩子爱上自然科学",
     * "status": 1
     * },
     * {
     * "type": 1,
     * "type_info": 16,
     * "obj_id": 517,
     * "content": "30天亲子训练营",
     * "cover_img": "wechat/works/video/184528/8105_1527070171.png",
     * "subtitle": "",
     * "status": 1
     * },
     * {
     * "type": 2,
     * "type_info": 21,
     * "content": "文件ing.mp3",
     * "file_url": "https://1253639599.vod2.myqcloud.com/32a152b3vodgzp1253639599/f63da4f95285890780889058541/aaodecBf5FAA.mp3",
     * "file_size": 4426079,
     * "format": "mp3",
     * "second": 275,
     * "file_md5": "34131545324543",
     * "status": 1
     * },
     * {
     * "type": 2,
     * "type_info": 22,
     * "content": "视频.mp4",
     * "file_url": "https://cos.ap-shanghai.myqcloud.com/240b-shanghai-030-shared-08-1256635546/751d-1400536432/a4d8-425232/345e2a389fe32d62fedad3d6d2150110.mp4",
     * "file_size": 1247117,
     * "format": "mp4",
     * "second": 7,
     * "file_md5": "3413154532454311",
     * "cover_img": "https://cos.ap-shanghai.myqcloud.com/240b-shanghai-030-shared-08-1256635546/751d-1400536432/a4d8-425232/643665ba437cf198a9961f85795d8474.jpg?imageMogr2/",
     * "img_size": 277431,
     * "img_width": 720,
     * "img_height": 1600,
     * "img_format": "jpg",
     * "img_md5": "14436454",
     * "status": 1
     * },
     * {
     * "type": 3,
     * "type_info": 31,
     * "content": "nihao"
     * }
     * ]
     */
    public function add(Request $request)
    {
        $servers = new ImDocServers();
        $data = $servers->add($request->input(), $this->user['user_id']);
        return $this->getRes($data);
    }

    /**
     * @api {get} api/admin_v4/im_doc/list 文案列表
     * @apiVersion 4.0.0
     * @apiName  api/admin_v4/im_doc/list
     * @apiGroup 后台-社群文案
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/im_doc/list
     * @apiDescription 文案列表
     */
    public function list(Request $request)
    {
        $servers = new ImDocServers();
        $data = $servers->list($request->input());
        return $this->getRes($data);
    }

    /**
     * @api {put} api/admin_v4/im_doc/change_status 文案状态修改
     * @apiVersion 4.0.0
     * @apiName  api/admin_v4/im_doc/change_status
     * @apiGroup 后台-社群文案
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/im_doc/change_status
     * @apiParam {number} id id
     * @apiParam {string=del} flag 动作(del:删除)
     * @apiDescription 文案状态修改
     */
    public function changeStatus(Request $request)
    {
        $servers = new ImDocServers();
        $data = $servers->changeStatus($request->input(), $this->user['user_id']);
        return $this->getRes($data);
    }

    /**
     * @api {post} api/admin_v4/im_doc/job_add 添加发送任务
     * @apiVersion 4.0.0
     * @apiName  api/admin_v4/im_doc/job_add
     * @apiGroup 后台-社群文案
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/im_doc/job_add
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
        $data = $servers->addSendJob($request->input(), $this->user['user_id']);
        return $this->getRes($data);
    }

    /**
     * @api {post} api/admin_v4/im_doc/job_list 发送任务列表
     * @apiVersion 4.0.0
     * @apiName  api/admin_v4/im_doc/job_list
     * @apiGroup 后台-社群文案
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/im_doc/job_list
     * @apiParam {number=1,2,3} send_obj_type 发送目标类型(1群组 2个人 3标签)
     * @apiParam {number} send_obj_id 发送目标id
     * @apiParam {number=1,2,3} [doc_type] 文案类型(1商品 2附件 3文本)
     * @apiParam {number} [doc_type_info] 文案类型(类型 11:讲座 12课程 13商品 14会员 15直播 16训练营21音频 22视频 23图片 31文本)
     * @apiParam {number=0,1,2,3,4} [is_done] 发送结果(1待发送  2发送中 3已完成 4无任务)
     */
    public function sendJobList(Request $request)
    {
        $servers = new ImDocServers();
        $data = $servers->sendJobList($request->input());
        return $this->getRes($data);
    }

    /**
     * @api {put} api/admin_v4/im_doc/change_job_status 发送任务状态修改
     * @apiVersion 4.0.0
     * @apiName  api/admin_v4/im_doc/change_job_status
     * @apiGroup 后台-社群文案
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/im_doc/change_job_status
     * @apiParam {number} id 任务id
     * @apiParam {string=on,off,del} flag 动作
     * @apiDescription 发送任务状态修改
     */
    public function changeJobStatus(Request $request)
    {
        $servers = new ImDocServers();
        $data = $servers->changeJobStatus($request->input(), $this->user['user_id']);
        return $this->getRes($data);
    }

    /**
     * @api {get} api/admin_v4/im_doc/category 分类
     * @apiVersion 4.0.0
     * @apiName  api/admin_v4/im_doc/category
     * @apiGroup 后台-社群文案
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/im_doc/category
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
     * @api {get} api/admin_v4/im_doc/category/product 分类筛选的商品列表
     * @apiVersion 4.0.0
     * @apiName  api/admin_v4/im_doc/category/product
     * @apiGroup 后台-社群文案
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/im_doc/category/product
     * @apiDescription 分类筛选的商品列表
     *
     * @apiParam {number} category_id 分类id 0为全部
     * @apiParam {number} type   类型  1.精品课 2 讲座 3 商品 4 直播 5训练营 6幸福360 7线下课
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

    public function test()
    {
        $servers = new ImDocServers();
        $data = $servers->sendGroupDocMsgJob();
//        $data = $servers->test();
        return $this->getRes($data);
    }
}
