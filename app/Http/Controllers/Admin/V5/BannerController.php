<?php


namespace App\Http\Controllers\Admin\V5;


use App\Http\Controllers\ControllerBackend;
use App\Servers\V5\BannerServers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BannerController extends ControllerBackend
{
    public function list(Request $request): JsonResponse
    {
        return $this->getRes((new BannerServers())->list($request->input()));
    }

    public function add(Request $request): JsonResponse
    {
        return $this->getRes((new BannerServers())->add($request->input()));
    }

    public function info(Request $request): JsonResponse
    {
        return $this->getRes((new BannerServers())->info($request->input()));
    }

    public function selectData(Request $request): JsonResponse
    {
        return $this->getRes((new BannerServers())->selectData($request->input()));
    }

}
