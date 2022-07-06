<?php

namespace App\Http\Controllers\Admin\V5;

use App\Http\Controllers\ControllerBackend;
use App\Servers\V5\OfflineOrderServers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OfflineOrderController extends ControllerBackend
{
    //订单列表
    public function orderList(Request $request): JsonResponse {
        return $this->getRes((new OfflineOrderServers())->list($request->input()));
    }

    //当前订单跟进记录列表
    public function orderLogList(Request $request): JsonResponse {
        return $this->getRes((new OfflineOrderServers())->orderLogList($request->input()));
    }

    //添加订单跟进记录
    public function orderLogAdd(Request $request): JsonResponse {
        return $this->getRes((new OfflineOrderServers())->orderLogAdd($request->input(),$this->user));
    }

}
