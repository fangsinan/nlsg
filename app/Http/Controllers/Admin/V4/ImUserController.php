<?php


namespace App\Http\Controllers\Admin\V4;


use App\Http\Controllers\ControllerBackend;
use App\Servers\ImGroupServers;
use App\Servers\ImUserServers;
use Illuminate\Http\Request;

class ImUserController extends ControllerBackend
{
    /**
     * @api {post} api/admin_v4/im_user/list 用户列表
     * @apiVersion 4.0.0
     * @apiName  api/admin_v4/im_user/list
     * @apiGroup 后台-用户列表与信息
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/im_user/list
     * @apiDescription 用户列表
     */
    public function list(Request $request)
    {
        $servers = new ImUserServers();
        $data = $servers->list($request->input(), $this->user['user_id']);
        return $this->getRes($data);
    }
}
