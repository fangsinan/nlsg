<?php

namespace App\Http\Controllers\Admin\V5;

use App\Http\Controllers\ControllerBackend;
use App\Servers\V5\erpOrderServers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ErpOrderController extends ControllerBackend
{

    //虚拟订单需要发货的列表
    public function erpOrderList(Request $request): JsonResponse {
        return $this->getRes((new erpOrderServers())->list($request->input()));
    }


    public function bindAddress(Request $request): JsonResponse {
        return $this->getRes((new erpOrderServers())->bindAddress($request->input()));
    }

}
