<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Libraries\ImClient;

class ImController extends Controller
{
    public  function  getUserSig(Request $request)
    {
        $user_id = $request->get('user_id');
        $sig = ImClient::getUserSig($user_id);
        return success($sig);
    }
}
