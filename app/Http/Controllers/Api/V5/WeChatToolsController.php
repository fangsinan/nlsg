<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use App\Servers\V5\WeChatToolsServers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WeChatToolsController extends Controller
{
    public function getUrlLink(Request $request): JsonResponse
    {
        return $this->getRes((new WeChatToolsServers())->getUrlLink($request->input()));
    }
}
