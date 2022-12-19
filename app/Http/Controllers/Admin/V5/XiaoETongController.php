<?php


namespace App\Http\Controllers\Admin\V5;


use App\Http\Controllers\ControllerBackend;
use App\Servers\V5\XiaoETongServers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * 合伙人：
 * 1.合伙人列表：合伙人手机号   昵称   成为合伙人时间   到期时间   上级推荐人   钻石合伙人
 * 2.添加、导入合伙人
 * 3.添加、导入合伙人客户(就是保护)
 * 4.合伙人详情：其他合伙人关联信息，比如身份证号  银行卡信息等。
 *
 * 订单管理：
 * 1.合伙人订单
 *
 * 收益管理
 */
class XiaoETongController extends ControllerBackend
{
    //推广员列表
    public function vipList(Request $request): JsonResponse
    {
        return $this->getRes((new XiaoETongServers())->vipList($request->input(), $this->user));
    }

    public function vipAdd(Request $request): JsonResponse
    {
        return $this->getRes((new XiaoETongServers())->vipAdd($request->input(), $this->user));
    }

    public function vipBindUser(Request $request): JsonResponse
    {
        return $this->getRes((new XiaoETongServers())->vipBindUser($request->input(), $this->user));
    }

    public function XeUserInfo(Request $request): JsonResponse
    {
        return $this->getRes((new XiaoETongServers())->vipInfo($request->input(), $this->user));
    }

    public function userList(Request $request): JsonResponse
    {
        return $this->getRes((new XiaoETongServers())->userList($request->input(), $this->user));
    }

    public function orderList(Request $request): JsonResponse
    {
        return $this->getRes((new XiaoETongServers())->orderList($request->input(), $this->user));
    }

}
