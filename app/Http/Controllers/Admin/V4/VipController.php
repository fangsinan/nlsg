<?php


namespace App\Http\Controllers\Admin\V4;


use App\Http\Controllers\ControllerBackend;
use App\Servers\VipServers;
use Illuminate\Http\Request;

class VipController extends ControllerBackend
{

    /**
     * 列表与详情
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
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
    public function list(Request $request)
    {
        $servers = new VipServers();
        $data = $servers->list($request->input());
        return $this->getRes($data);
    }

    //todo 配额
    public function assign(Request $request){
        $servers = new VipServers();
        $data = $servers->assign($request->input(),$this->user['id']??0);
        return $this->getRes($data);
    }

    //todo 状态修改

}
