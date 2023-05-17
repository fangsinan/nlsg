<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use App\Servers\UserWechatServers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class WeComController
 * @package App\Http\Controllers\Api\V5
 * 企业微信
 */
class WeComController extends Controller
{
    /**
     * @api {post} /api/v5/wecom/get_we_com 获取企业微信信息
     * @apiName get_we_com
     * @apiVersion 1.0.0
     * @apiGroup WeCom
     *
     * @apiParam {int} WeComType      类型id
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     * {
     *  "code": 200,
     *  "msg": "成功",
     *  "data": { }
     * }
     */
    public function GetWeCom(Request $request): JsonResponse
    {

        $WeComType = intval($request->input('WeComType', 0));
        if(empty($WeComType)){
            return $this->error(1000, '类型不能为空');
        }
        $UserWechatObj=new UserWechatServers();
        $data=$UserWechatObj->getEnterpriseLabel($WeComType);
        if(empty($data)){
            $data=((object)[]);
        }

        return $this->success($data);

    }
}
