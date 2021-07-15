<?php


namespace App\Http\Controllers\Api\V4;


use App\Http\Controllers\Controller;
use App\Servers\ImDocServers;
use Illuminate\Http\Request;

class ImDocController extends Controller
{
    /**
     * @api {post} api/v4/im_doc/add 添加文案
     * @apiVersion 4.0.0
     * @apiName  list
     * @apiGroup 社群文案
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/im_doc/add
     * @apiDescription 社群文案
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
        $data = $servers->add($request->input(),$this->user['user_id']);
        return $this->getRes($data);
    }

    /**
     * @api {get} api/v4/im_doc/list 文案列表
     * @apiVersion 4.0.0
     * @apiName  list
     * @apiGroup 社群文案
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/im_doc/list
     * @apiDescription 社群文案
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
     * @apiName  list
     * @apiGroup 社群文案
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/im_doc/change_status
     * @apiParam {number} id id
     * @apiParam {string=del} flag 动作(del:删除)
     * @apiDescription 社群文案
     */
    public function changeStatus(Request $request)
    {
        $servers = new ImDocServers();
        $data = $servers->changeStatus($request->input(),$this->user['user_id']);
        return $this->getRes($data);
    }
}
