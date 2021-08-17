<?php


namespace App\Http\Controllers\Admin\V4;


use App\Http\Controllers\ControllerBackend;
use App\Servers\ImFriendServers;
use Illuminate\Http\Request;

class ImFriendController extends ControllerBackend
{
    /**
     * @api {post} api/admin_v4/im_friend/friend_check  管理后台-校验用户关系
     * @apiName admin  friend_check
     * @apiVersion 1.0.0
     * @apiGroup im_friend
     *
     * @apiParam {int} From_Account  需要校验该 UserID 的好友
     * @apiParam {array} To_Account  请求校验的好友的 UserID 列表
     *
     * @apiSuccess {string} Relation  CheckResult_Type_BothWay	From_Account 的好友表中有 To_Account，To_Account 的好友表中也有 From_Account
    CheckResult_Type_AWithB	From_Account 的好友表中有 To_Account，但 To_Account 的好友表中没有 From_Account
    CheckResult_Type_BWithA	From_Account 的好友表中没有 To_Account，但 To_Account 的好友表中有 From_Account
    CheckResult_Type_NoRelation	From_Account 的好友表中没有 To_Account，To_Account 的好友表中也没有 From_Account
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     *  {
    "code": 200,
    "msg": "成功",
    "data": [
    ]
    }
     */
    public function friendCheck(Request $request){

        $imObj = new ImFriendServers();
        $data = $imObj->friendCheck($request->input());
        return $this->getRes($data);
    }


    /**
     * @api {get} api/admin_v4/im_friend/portrait_get  管理后台-拉取im 用户资料
     * @apiName admin portrait_get
     * @apiVersion 1.0.0
     * @apiGroup im_friend
     *
     * @apiParam {int} user_id   user_id
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     *  {
    "code": 200,
    "msg": "成功",
    "data": [
    ]
    }
     */
    public function getPortrait(Request $request){

        $imObj = new ImFriendServers();
        $data = $imObj->getPortrait($request->input());
        return $this->getRes($data);
    }


    /**
     * @api {post} api/admin_v4/im_friend/add_friend  管理后台-Im添加好友
     * @apiName admin add_friend
     * @apiVersion 1.0.0
     * @apiGroup im_friend
     *
     * @apiParam {int} From_Account 为该 用户 添加好友
     * @apiParam {int} To_Account   需要添加好友的id
     * @apiParam {int} AddWording   添加的备注
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     *  {
    "code": 200,
    "msg": "成功",
    "data": [
    ]
    }
     */
    public function addFriend(Request $request){

        $imObj = new ImFriendServers();
        $data = $imObj->addFriend($request->input());
        return $this->getRes($data);
    }

    /**
     * @api {post} api/admin_v4/im_friend/del_friend  管理后台-Im删除好友
     * @apiName admin del_friend
     * @apiVersion 1.0.0
     * @apiGroup im_friend
     *
     * @apiParam {int} From_Account 需要删除该 用户 的好友
     * @apiParam {array} To_Account   需要删除好友的id
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     *  {
    "code": 200,
    "msg": "成功",
    "data": [
    ]
    }
     */
    public function delFriend(Request $request){

        $imObj = new ImFriendServers();
        $data = $imObj->delFriend($request->input());
        return $this->getRes($data);
    }

    //从im获取好友列表  （废弃中)
    public function imFriendList(Request $request){

        $imObj = new ImFriendServers();
        $data = $imObj->imFriendList($request->input(),$this->user['user_id']);
        return $this->getRes($data);
    }

}
