<?php


namespace App\Http\Controllers\Admin\V5;


use App\Http\Controllers\ControllerBackend;
use App\Servers\LiveBgpServers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LiveBgpController extends ControllerBackend
{
    public function list(Request $request): JsonResponse
    {
        return $this->getRes((new LiveBgpServers())->list($request->input()));
    }
}
