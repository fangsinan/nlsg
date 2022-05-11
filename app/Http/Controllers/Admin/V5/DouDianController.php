<?php

namespace App\Http\Controllers\Admin\V5;

use App\Http\Controllers\ControllerBackend;
use App\Servers\V5\DouDianDataServers;
use App\Servers\V5\DouDianServers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DouDianController extends ControllerBackend
{
    public function orderList(Request $request): JsonResponse {
        return $this->getRes((new DouDianDataServers())->list($request->input()));
    }

    //下拉商品数据
//    public function selectGoodsList(): JsonResponse {
//        return $this->getRes((new DouDianDataServers())->selectGoodsList());
//    }

    //下拉订单状态
    public function selectOrderStatus(Request $request): JsonResponse {
        return $this->getRes((new DouDianDataServers())->selectOrderStatus($request->input()));
    }

}
