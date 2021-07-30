<?php

namespace App\Http\Controllers\Admin\V4;

use App\Http\Controllers\ControllerBackend;
use App\Servers\ImQuickReplyServers;
use Illuminate\Http\Request;

class ImQuickReply extends ControllerBackend
{
     /**
      * @api {get} api/admin_v4/im_quick_reply/list 快捷回复列表
      * @apiVersion 4.0.0
      * @apiName  api/admin_v4/im_doc/list
      * @apiGroup 后台-快捷回复
      * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/im_doc/list
      * @apiDescription 快捷回复列表
      */
    public function list(Request $request)
    {
        $servers = new ImQuickReplyServers();
        $data = $servers->list($request->input(), $this->user['user_id']);
        return $this->getRes($data);
    }

    /**
     * @api {post} api/admin_v4/im_quick_reply/add 添加快捷回复
     * @apiVersion 4.0.0
     * @apiName  api/admin_v4/im_doc/add
     * @apiGroup 后台-快捷回复
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/im_doc/add
     * @apiDescription 添加快捷回复
     *
     * @apiParam {string} content 内容
     */
    public function add(Request $request)
    {
        $servers = new ImQuickReplyServers();
        $data = $servers->add($request->input(), $this->user['user_id']);
        return $this->getRes($data);
    }

    /**
     * @api {put} api/admin_v4/im_quick_reply/change_status 快捷回复状态修改
     * @apiVersion 4.0.0
     * @apiName  api/admin_v4/im_doc/change_status
     * @apiGroup 后台-快捷回复
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/im_doc/change_status
     * @apiDescription 快捷回复状态修改
     * @apiParam {number} id id
     * @apiParam {string=del} flag 动作(del:删除)
     */
    public function changeStatus(Request $request)
    {
        $servers = new ImQuickReplyServers();
        $data = $servers->changeStatus($request->input(), $this->user['user_id']);
        return $this->getRes($data);
    }

}
