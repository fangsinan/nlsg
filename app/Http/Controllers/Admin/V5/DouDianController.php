<?php

namespace App\Http\Controllers\Admin\V5;

use App\Http\Controllers\ControllerBackend;
use App\Servers\V5\DouDianDataServers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DouDianController extends ControllerBackend
{
    public function orderList(Request $request): JsonResponse {
        return $this->getRes((new DouDianDataServers())->list($request->input()));
    }
}
