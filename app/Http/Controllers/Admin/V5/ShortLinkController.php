<?php

namespace App\Http\Controllers\Admin\V5;

use App\Http\Controllers\ControllerBackend;
use App\Servers\V5\ShortLinkServers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShortLinkController extends ControllerBackend
{

    //获取管理员列表
    public function linkAdminList(Request $request): JsonResponse {
        return $this->getRes((new ShortLinkServers())->linkAdminList($request->input()));
    }

    //获取短链接
    public function linkGet(Request $request): JsonResponse {
        return $this->getRes((new ShortLinkServers())->linkGet($request->input()));
    }

    //短链接列表
    public function linkList(Request $request): JsonResponse {
        return $this->getRes((new ShortLinkServers())->getList($request->input()));
    }

    //添加短链接
    public function linkAddEdit(Request $request): JsonResponse {
        return $this->getRes((new ShortLinkServers())->LinkAddEdit($request->input(),$this->user));
    }

    //查看短链接
    public function linkShow(Request $request): JsonResponse {
        return $this->getRes((new ShortLinkServers())->linkShow($request->input()));
    }



}
