<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Libraries\ImClient;

class ImController extends Controller
{
    /**
    * @api {get} api/v4/im/get_user_sig  用户签名
    * @apiVersion 4.0.0
    * @apiName  get_user_sig
    * @apiGroup im
    * @apiParam {int} user_id  user_id  数组类型

    * @apiSuccess {string} data 内容
    *
    * @apiSuccessExample  Success-Response:
    * HTTP/1.1 200 OK
    * {
    *      "code": 200,
    *      "msg" : '成功',
    *      "data": {
    *          
    *       }
    * }
    *
    */
    public  function  getUserSig(Request $request)
    {
        $user_id = $request->get('user_id');
        $sig = ImClient::getUserSig($user_id);
        return success($sig);
    }
}
