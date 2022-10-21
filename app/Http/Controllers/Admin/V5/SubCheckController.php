<?php


namespace App\Http\Controllers\Admin\V5;


use App\Http\Controllers\ControllerBackend;
use App\Servers\V5\SubCheckServers;
use App\Servers\V5\TalkServers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubCheckController extends ControllerBackend
{
    public function relationTypeList(): JsonResponse
    {
        return $this->getRes((new SubCheckServers())->relationTypeList());
    }

    public function relationInfoList(Request $request): JsonResponse
    {
        return $this->getRes((new SubCheckServers())->relationInfoList($request->input('type',1)));
    }

    public function toCheck(Request $request): JsonResponse
    {
        return $this->getRes((new SubCheckServers())->toCheck($request->input()));
    }

}
