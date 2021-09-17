<?php

namespace App\Http\Controllers\Admin\V4;

use App\Http\Controllers\ControllerBackend;
use App\Servers\ShieldKeyServers;
use Illuminate\Http\Request;

class ShieldKeyController extends ControllerBackend
{
    public function list(Request $request): \Illuminate\Http\JsonResponse
    {
        $s = new ShieldKeyServers();
        $data = $s->list($request->input(), $this->user['id'] ?? 0);
        return $this->getRes($data);
    }

    public function add(Request $request): \Illuminate\Http\JsonResponse
    {
        $s = new ShieldKeyServers();
        $data = $s->add($request->input(), $this->user['id'] ?? 0);
        return $this->getRes($data);
    }

    public function changeStatus(Request $request): \Illuminate\Http\JsonResponse
    {
        $s = new ShieldKeyServers();
        $data = $s->changeStatus($request->input(), $this->user['id'] ?? 0);
        return $this->getRes($data);
    }
}
