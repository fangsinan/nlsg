<?php


namespace App\Http\Controllers\Admin\V4;


use App\Http\Controllers\ControllerBackend;
use App\Servers\ImMsgServers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ImMsgController extends ControllerBackend
{
    public function getMsgList(Request $request): JsonResponse
    {
        $servers = new ImMsgServers();
        $data = $servers->getMsgList($request->input(), $this->user['user_id']);
        return $this->getRes($data);
    }
}
