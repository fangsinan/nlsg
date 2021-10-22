<?php

namespace App\Http\Controllers\Admin\V4;

use App\Http\Controllers\ControllerBackend;
use App\Servers\ImDocFolderServers;
use Illuminate\Http\Request;

class ImDocFolderController extends ControllerBackend
{

    /**
     * @api {post} api/admin_v4/im_doc_folder/add_doc 添加文案
     * @apiVersion 4.0.0
     * @apiName  api/admin_v4/im_doc_folder/add_doc
     * @apiGroup 后台-社群文案v2
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/im_doc_folder/add_doc
     * @apiDescription 添加文案
     *
     * @apiParam {number} folder_id 文件夹id
     * @apiParam {number=1,2,3} type 类型(1商品 2附件 3文本)
     * @apiParam {number} type_info 详细类型(类型 11:讲座 12课程 13商品 14会员 15直播 16训练营 17外链  18线下课 19听书 21音频 22视频 23图片 24文件 31文本)
     * @apiParam {number} [obj_id]  目标id(当type=1时需要传)
     * @apiParam {string} content   内容或名称(type=1如果是商品类型传商品的标题,外链类型传网址)
     * @apiParam {string} [subtitle]   副标题(外链类型传网址说明名称)
     * @apiParam {string} cover_img   封面图片(type=1必穿)
     * @apiParam {string} [media_id]  媒体id(type=2时必传,如果是图片,可逗号拼接多个)
     */
    public function addDoc(Request $request)
    {
        $servers = new ImDocFolderServers();
        $data = $servers->addDoc($request->input(), $this->user['id']);
        return $this->getRes($data);
    }

    /**
     * @api {get} api/admin_v4/im_doc_folder/list 文件夹列表
     * @apiVersion 4.0.0
     * @apiName  api/admin_v4/im_doc_folder/list
     * @apiGroup 后台-社群文案v2
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/im_doc_folder/list
     * @apiDescription 文件夹列表
     */
    public function list(Request $request)
    {
        $servers = new ImDocFolderServers();
        $data = $servers->list($request->input(), $this->user['id']);
        return $this->getRes($data);
    }

    /**
     * @api {post} api/admin_v4/im_doc_folder/add 添加文件夹
     * @apiVersion 4.0.0
     * @apiName  api/admin_v4/im_doc_folder/add
     * @apiGroup 后台-社群文案v2
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/im_doc_folder/add
     * @apiDescription 添加文件夹
     *
     * @apiParam {string} folder_name 文件夹名称
     * @apiParam {number} pid 上级文件夹id,顶级0
     */
    public function add(Request $request)
    {
        $servers = new ImDocFolderServers();
        $data = $servers->add($request->input(), $this->user['id']);
        return $this->getRes($data);
    }

    /**
     * @api {put} api/admin_v4/im_doc_folder/change_status 修改文件夹状态(删除,移动,复制)
     * @apiVersion 4.0.0
     * @apiName  api/admin_v4/im_doc_folder/change_status
     * @apiGroup 后台-社群文案v2
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/im_doc_folder/change_status
     * @apiDescription 修改文件夹状态(删除,移动,复制)
     *
     * @apiParam {number} id 文件夹id
     * @apiParam {string=del,remove,copy} flag 动作(删除,移动,复制)
     * @apiParam {number} [pid] 目标id (remove时需要,pid为目标文件夹id)
     */
    public function changeStatus(Request $request)
    {
        $servers = new ImDocFolderServers();
        $data = $servers->changeStatus($request->input(), $this->user['id']);
        return $this->getRes($data);
    }

    /**
     * @api {put} api/admin_v4/im_doc_folder/change_doc_status 修改文案状态(删除,移动)
     * @apiVersion 4.0.0
     * @apiName  api/admin_v4/im_doc_folder/change_doc_status
     * @apiGroup 后台-社群文案v2
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/im_doc_folder/change_doc_status
     * @apiDescription 修改文案状态(删除,移动)
     *
     * @apiParam {number} id 文案
     * @apiParam {string=del,remove} flag 动作(删除,移动)
     * @apiParam {number} folder_id 文案所属文件夹id
     * @apiParam {number} [pid] 目标id (remove时需要,pid为目标文件夹id)
     */
    public function changeDocStatus(Request $request)
    {
        $servers = new ImDocFolderServers();
        $data = $servers->changeDocStatus($request->input(), $this->user['id']);
        return $this->getRes($data);
    }

    /**
     * @api {get} api/admin_v4/im_doc_folder/folder_doc_List 文件夹下所有文案列表
     * @apiVersion 4.0.0
     * @apiName  api/admin_v4/im_doc_folder/folder_doc_List
     * @apiGroup 后台-社群文案v2
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/im_doc_folder/folder_doc_List
     * @apiDescription 文件夹下所有文案列表
     * @apiParam {number} folder_id 文件夹id
     */
    public function folderDocList(Request $request)
    {
        $servers = new ImDocFolderServers();
        $data = $servers->folderDocList($request->input(), $this->user['id']);
        return $this->getRes($data);
    }

    /**
     * @api {post} api/admin_v4/im_doc_folder/add_job 添加文案发送任务
     * @apiVersion 4.0.0
     * @apiName  api/admin_v4/im_doc_folder/add_job
     * @apiGroup 后台-社群文案v2
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/im_doc_folder/add_job
     * @apiDescription 添加文案发送任务
     *
     * @apiParam {num} folder_id 所选文件夹
     * @apiParam {string} group_id 群组id(字符串拼接和数组均可)
     * @apiParam {number=1,2} job_type 任务类型(1定时 2立刻)
     * @apiParam {string[]} list 文案的时间
     * @apiParam {number} list.folder_id 文案所属文件夹id
     * @apiParam {number} list.doc_id 文案id
     * @apiParam {number} list.job_time 定时任务时间
     * @apiParamExample {json} Request-Example:
     * {
     * "folder_id": 1,
     * "group_id": "51,52,53",
     * "job_type": 1,
     * "list": [
     * {
     * "folder_id": 1,
     * "doc_id": 611,
     * "job_time": "2021-10-21 12:00:00"
     * },
     * {
     * "folder_id": 1,
     * "doc_id": 610,
     * "job_time": "2021-10-21 12:00:10"
     * },
     * {
     * "folder_id": 1,
     * "doc_id": 613,
     * "job_time": "2021-10-21 12:01:00"
     * }
     * ]
     * }
     */
    public function addJob(Request $request)
    {
        $servers = new ImDocFolderServers();
        $data = $servers->addJob($request->input(), $this->user['id']);
        return $this->getRes($data);
    }


    /**
     * @api {get} api/admin_v4/im_doc_folder/job_list 文案发送任务列表
     * @apiVersion 4.0.0
     * @apiName  api/admin_v4/im_doc_folder/job_list
     * @apiGroup 后台-社群文案v2
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/im_doc_folder/job_list
     * @apiDescription 文案发送任务列表
     *
     * @apiParam {num} folder_id 所选文件夹
     * @apiParam {string} group_id 群组id(字符串拼接和数组均可)
     * @apiParam {number=1,2} job_type 任务类型(1定时 2立刻)
     * @apiParam {string[]} list 文案的时间
     * @apiParam {number} list.folder_id 文案所属文件夹id
     * @apiParam {number} list.doc_id 文案id
     * @apiParam {number} list.job_time 定时任务时间
     */
    public function jobList(Request $request)
    {
        $servers = new ImDocFolderServers();
        $data = $servers->jobList($request->input(), $this->user['id']);
        return $this->getRes($data);
    }

    /**
     * @api {put} api/admin_v4/im_doc_folder/change_job_status 修改任务状态(删除,取消)
     * @apiVersion 4.0.0
     * @apiName  api/admin_v4/im_doc_folder/change_job_status
     * @apiGroup 后台-社群文案v2
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/im_doc_folder/change_job_status
     * @apiDescription 修改任务状态(删除,取消)
     *
     * @apiParam {string=job,job_info} job_flag 目标类型(job:任务 job_info:任务里的文案)
     * @apiParam {string=del,off} flag 动作(删除,移动)
     * @apiParam {number} job_id 任务id
     * @apiParam {number} [job_info_id] 任务文案id
     */
    public function changeJobStatus(Request $request)
    {
        $servers = new ImDocFolderServers();
        $data = $servers->changeJobStatus($request->input(), $this->user['id']);
        return $this->getRes($data);
    }


}
