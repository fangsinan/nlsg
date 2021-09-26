<?php

namespace App\Http\Controllers\Admin\V4;

use App\Http\Controllers\ControllerBackend;
use App\Servers\OrderRefundServers;
use Illuminate\Http\Request;

class OrderRefundLogController extends ControllerBackend
{
    public function add(Request $request)
    {
        $servers = new OrderRefundServers();
        $data = $servers->add($request->input(), $this->user['id'] ?? 0);
        return $this->getRes($data);
    }

    public function list(Request $request)
    {
        $servers = new OrderRefundServers();
        $data = $servers->list($request->input(), $this->user['id'] ?? 0);
        return $this->getRes($data);
    }
}
