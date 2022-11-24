<?php


namespace App\Http\Controllers\Admin\V4;


use App\Http\Controllers\ControllerBackend;
use App\Servers\VipServers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VipController extends ControllerBackend
{

    /**
     * 列表与详情
     * @param Request $request
     * @return JsonResponse
     * @api {get} /api/admin_v4/vip/list 列表与详情
     * @apiVersion 4.0.0
     * @apiName /api/admin_v4/vip/list
     * @apiGroup  后台-VIP
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/vip/list
     * @apiDescription 列表与详情
     * @apiParam {number=0,1,2} [level] 会员级别(0全部,1是360,2是经销商)
     * @apiParam {string} [username] 账号
     *
     * @apiSuccess {string[]} new_level 账号的当前会员信息
     * @apiSuccess {string[]} open_history 开通记录
     * @apiSuccess {string} assign_count 配额总数
     * @apiSuccess {string} assign_history 配额记录
     */
    public function list(Request $request): JsonResponse {
        $servers = new VipServers();
        $data    = $servers->list($request->input(), $this->user['role_id'] ?? 0);
        return $this->getRes($data);
    }

    public function change360ExpireTime(Request $request): JsonResponse {
        $servers = new VipServers();
        $data    = $servers->change360ExpireTime($request->input(), $this->user['id'] ?? 0);
        return $this->getRes($data);
    }

    /**
     * 360兑换码配额修改
     * @param Request $request
     * @return JsonResponse
     * @api {get} /api/admin_v4/vip/assign 兑换码配额修改
     * @apiVersion 4.0.0
     * @apiName /api/admin_v4/vip/assign
     * @apiGroup  后台-VIP
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/vip/assign
     * @apiDescription 兑换码配额修改
     *
     * @apiParam {string} user_id 用户id
     * @apiParam {string} vip_id 用户vip_id
     * @apiParam {string} num 数量
     * @apiParam {string=1,2} status 状态(1生效 2失效)
     * @apiParam {string=edit,add} flag 添加或修改
     * @apiParam {string} assign_history_id 历史记录的id
     */
    public function assign(Request $request): JsonResponse {
        $servers = new VipServers();
        $data    = $servers->assign($request->input(), $this->user['id'] ?? 0);
        return $this->getRes($data);
    }

    /**
     * 开通360或钻石
     * @param Request $request
     * @return JsonResponse
     * @api {get} /api/admin_v4/vip/create_vip 开通360或钻石
     * @apiVersion 4.0.0
     * @apiName /api/admin_v4/vip/create_vip
     * @apiGroup  后台-VIP
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/vip/create_vip
     * @apiDescription 开通360或钻石
     *
     * @apiParam {string} [parent] 上级账号(添加360时可选)
     * @apiParam {string} phone 开通账号
     * @apiParam {string} [send_money] 是否生成收益(添加360时可用.1生成,0不生成)
     * @apiParam {string=1,2} flag 类型(1是360 , 2是钻石)
     *
     * @apiSuccess {string[]} success_msg 操作信息
     *
     */
    public function createVip(Request $request) {
        $servers = new VipServers();
        $data    = $servers->createVip($request->input(), $this->user['id'] ?? 0);
        return $this->getRes($data);
    }

    //todo 划转收益或关系

}
