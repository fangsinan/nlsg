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
     * @apiParam {string=0,1,2} [status] 群状态
     *
     * @apiSuccess {string} id 群id
     * @apiSuccess {string} group_id 腾讯群id
     * @apiSuccess {string} owner_account 群组用户id
     * @apiSuccess {string} type 类型(群组类型 陌生人社交群（Public）,好友工作群（Work）,临时会议群（Meeting）,直播群（AVChatRoom）)
     * @apiSuccess {string} name 群名
     * @apiSuccess {string} status 状态(1正常 2解散)
     * @apiSuccess {string} created_at 创建时间
     * @apiSuccess {string} owner_phone 群组账号
     * @apiSuccess {string} owner_id 群组id
     * @apiSuccess {string} owner_nickname 群组昵称
     * @apiSuccess {string} member_num 群人数
     * @apiSuccess {string} is_top 是否置顶(1是 0否)
     * @apiSuccessExample {json} Request-Example:
     * {
     * "id": 56,
     * "group_id": "@TGS#2ICPIJJHB",
     * "operator_account": 211172,
     * "owner_account": 211172,
     * "type": "Public",
     * "name": "房思楠、鬼见愁、邢成",
     * "status": 1,
     * "created_at": "2021-07-22 15:31:45",
     * "owner_phone": "15650701817",
     * "owner_id": 211172,
     * "owner_nickname": "房思楠",
     * "group_count": 0,
     * "is_top": 1
     * }
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
