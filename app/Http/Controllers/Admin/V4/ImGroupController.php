<?php


namespace App\Http\Controllers\Admin\V4;


use App\Http\Controllers\ControllerBackend;
use App\Servers\ImDocServers;
use App\Servers\ImGroupServers;
use Illuminate\Http\Request;

class ImGroupController extends ControllerBackend
{
    /**
     * @api {post} api/admin_v4/im_group/statistics 群列表统计信息
     * @apiVersion 4.0.0
     * @apiName  api/admin_v4/im_group/statistics
     * @apiGroup 后台-社群
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/im_group/statistics
     * @apiDescription 群列表统计信息
     */
    public function statistics(Request $request){
        $servers = new ImGroupServers();
        $data = $servers->statistics($request->input(), $this->user['user_id']);
        return $this->getRes($data);
    }

    /**
     * @api {post} api/admin_v4/im_group/list 群列表
     * @apiVersion 4.0.0
     * @apiName  api/admin_v4/im_group/list
     * @apiGroup 后台-社群
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/im_group/list
     * @apiDescription 群列表
     * @apiParam {string=time_asc,time_desc} [ob] 排序
     * @apiParam {string} [name] 群名
     */
    public function list(Request $request){
        $servers = new ImGroupServers();
        $data = $servers->groupList($request->input(), $this->user['user_id']);
        return $this->getRes($data);
    }

    public function changeStatus(Request $request){
        $servers = new ImGroupServers();
        $data = $servers->changeStatus($request->input(), $this->user['user_id']);
        return $this->getRes($data);
    }

}
