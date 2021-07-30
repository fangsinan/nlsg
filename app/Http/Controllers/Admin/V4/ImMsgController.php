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



    /**
     * @api {post} api/admin_v4/im/msg_collection  后台管理-消息收藏操作
     * @apiName msg_collection
     * @apiVersion 1.0.0
     * @apiGroup im
     *
     * @apiParam {array} os_msg_id  消息序列号 array
     * @apiParam {int} type  收藏类型   1消息收藏
     * @apiParam {array} collection_id  收藏列表id (取消收藏只传该字段)
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
    {
    "code": 200,
    "msg": "成功",
    "data": {}
    }
     */
    public function MsgCollection(Request $request){

        $imObj = new ImMsgServers();
        $data = $imObj->MsgCollection($request->input(),$this->user['id']);
        return $this->getRes($data);
    }


    /**
     * @api {post} api/admin_v4/im/msg_collection_list  后台管理-消息收藏列表
     * @apiName msg_collection_list
     * @apiVersion 1.0.0
     * @apiGroup im
     *
     * @apiParam {string} keywords  收藏消息关键字
     * @apiParam {string} page
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
    {
    "code": 200,
    "msg": "成功",
    "data": {}
    }
     */
    public function MsgCollectionList(Request $request){

        $imObj = new ImMsgServers();
        $this->user['id'] = 211172;
        $data = $imObj->MsgCollectionList($request->input(),$this->user['id']);
        return $this->getRes($data);
    }




}
