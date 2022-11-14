<?php


namespace App\Http\Controllers\Admin\V5;


use App\Http\Controllers\ControllerBackend;
use App\Servers\V5\OfflineOrderServers;
use App\Servers\V5\ZeroOrderListServers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends ControllerBackend
{
    public function zeroOrderList(Request $request): JsonResponse {
        return $this->getRes((new ZeroOrderListServers())->list($request->input(),$this->user));
    }
}
