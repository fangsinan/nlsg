<?php


namespace App\Http\Controllers\Admin\V4;


use App\Http\Controllers\ControllerBackend;
use App\Servers\ImGroupServers;
use Illuminate\Http\Request;
use Libraries\ImClient;

class ImGroupController extends ControllerBackend
{
    /**
     * @api {post} api/admin_v4/im_group/statistics 群列表统计信息
     * @apiVersion 4.0.0
     * @apiName  api/admin_v4/im_group/statistics
     * @apiGroup 后台-社群
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/im_group/statistics
     * @apiDescription 群列表统计信息
     */
    public function statistics(Request $request)
    {
        $servers = new ImGroupServers();
        $data = $servers->statistics($request->input(), $this->user['user_id']);
        return $this->getRes($data);
    }

    /**
     * @api {post} api/admin_v4/im_group/list 群列表
     * @apiVersion 4.0.0
     * @apiName  api/admin_v4/im_group/list
     * @apiGroup 后台-社群
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/im_group/list
     * @apiDescription 群列表
     * @apiParam {string=time_asc,time_desc} [ob] 排序
     * @apiParam {string} [name] 群名
     * @apiParam {number=1,2} [owner_type] 加入类型(1我创建的  2我加入的)
     * @apiParam {number=1,2,3} [group_role] 级别(1群主 2管理员 9全都是)
     * @apiParam {string=0,1,2} [status] 群状态
     *
     * @apiSuccess {string} id 群id
     * @apiSuccess {string} group_id 腾讯群id
     * @apiSuccess {string} owner_account 群组用户id
     * @apiSuccess {string} type 类型(群组类型 陌生人社交群（Public）,好友工作群（Work）,临时会议群（Meeting）,直播群（AVChatRoom）)
     * @apiSuccess {string} name 群名
     * @apiSuccess {string} status 状态(1正常 2解散)
     * @apiSuccess {string} created_at 创建时间
     * @apiSuccess {string} owner_phone 群组账号
     * @apiSuccess {string} owner_id 群组id
     * @apiSuccess {string} owner_nickname 群组昵称
     * @apiSuccess {string} member_num 群人数
     * @apiSuccess {string} is_top 是否置顶(1是 0否)
     * @apiSuccess {string} max_num 最高人数
     * @apiSuccess {string[]} admin 管理员列表
     * @apiSuccess {string} admin.phone 管理员账号
     * @apiSuccess {string} admin.group_account 管理员id
     * @apiSuccess {string} admin.group_role  级别(1群组2管理员)
     * @apiSuccessExample {json} Request-Example:
     * {
     * "id": 56,
     * "group_id": "@TGS#2ICPIJJHB",
     * "operator_account": 211172,
     * "owner_account": 211172,
     * "type": "Public",
     * "name": "房思楠、鬼见愁、邢成",
     * "status": 1,
     * "created_at": "2021-07-22 15:31:45",
     * "owner_phone": "15650701817",
     * "owner_id": 211172,
     * "owner_nickname": "房思楠",
     * "member_num": 5,
     * "is_top": 1,
     * "max_num": 2000,
     * "admin": [
     * {
     * "group_account": "211172",
     * "phone": "15650701817",
     * "nickname": "房思楠",
     * "group_role": 1
     * }
     * ]
     * }
     */
    public function list(Request $request)
    {
        $servers = new ImGroupServers();
        $data = $servers->groupList($request->input(), $this->user['user_id']);
        return $this->getRes($data);
    }

    /**
     * @api {put} api/admin_v4/im_group/change_top 设置或取消置顶
     * @apiVersion 4.0.0
     * @apiName  api/admin_v4/im_group/change_top
     * @apiGroup 后台-社群
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/im_group/change_top
     * @apiDescription 群列表
     * @apiParam {string} group_id 群组id
     * @apiParam {string=top,cancel_top} flag 操作
     */
    public function changeTop(Request $request)
    {
        $servers = new ImGroupServers();
        $data = $servers->changeTop($request->input(), $this->user['user_id']);
        return $this->getRes($data);
    }


     /**
      * @api {post} api/admin_v4/im_group/bind_works 群绑定课程
      * @apiVersion 4.0.0
      * @apiName  api/admin_v4/im_group/bind_works
      * @apiGroup 后台-社群
      * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/im_group/bind_works
      * @apiDescription 群绑定课程
      * @apiParam {number} group_id 群id
      * @apiParam {number} works_id 课程id
      */
    public function bindWorks(Request $request)
    {
        $servers = new ImGroupServers();
        $data = $servers->bindWorks($request->input(), $this->user['user_id']);
        return $this->getRes($data);
    }






    /**
     * @api {post} api/admin_v4/im_group/edit_join_group 管理后台-添加/删除成员入群
     * @apiName admin edit_join_group
     * @apiVersion 1.0.0
     * @apiGroup im_group
     *
     * @apiParam {int} group_id   腾讯云的groupId
     * @apiParam {array} user_id  user_id  数组类型
     * @apiParam {array} type  type==del删除  add添加
     * @apiParam {int} silence  type==del删除时Silence是否静默删人。0表示非静默删人，1表示静默删人
     * @apiParam {string} reason  type==del删除时踢出用户原因
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
    public function editJoinGroup(Request $request){

        $servers = new ImGroupServers();
        $data = $servers->editJoinGroup($request->input());
        return $this->getRes($data);
    }


    /**
     * @api {post} api/admin_v4/im_group/create_group 管理后台-创建群
     * @apiName admin create_group
     * @apiVersion 1.0.0
     * @apiGroup im_group
     *
     * @apiParam {array} user_id  user_id  数组类型 群初始人员
     * @apiParam {string} Name 群名称

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
    public function createGroup(Request $request){

        $servers = new ImGroupServers();
        $data = $servers->createGroup($request->input(), $this->user['user_id']);
        return $this->getRes($data);
    }



    /**
     * @api {post} api/admin_v4/im_group/destroy_group 管理后台-解散群
     * @apiName admin destroy_group
     * @apiVersion 1.0.0
     * @apiGroup im_group
     *
     * @apiParam {string} GroupId  GroupId

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
    public function destroyGroup(Request $request){

        $servers = new ImGroupServers();
        $data = $servers->destroyGroup($request->input(), $this->user['user_id']);
        return $this->getRes($data);
    }




    /**
     * @api {post} api/admin_v4/im_group/change_group_owner 管理后台-转让群
     * @apiName admin change_group_owner
     * @apiVersion 1.0.0
     * @apiGroup im_group
     *
     * @apiParam {string} GroupId  GroupId
     * @apiParam {string} NewOwner_Account  新群主id

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
    public function changeGroupOwner(Request $request){
        $servers = new ImGroupServers();
        $data = $servers->changeGroupOwner($request->input(), $this->user['user_id']);
        return $this->getRes($data);
    }





    /**
     * @api {post} api/admin_v4/im_group/get_group_member_info 管理后台-获取群成员
     * @apiName admin get_group_member_info
     * @apiVersion 1.0.0
     * @apiGroup im_group
     *
     * @apiParam {string} GroupId  GroupId
     * @apiParam {string} Limit  最多获取多少个成员的资料  //默认100
     * @apiParam {string} Offset  从第多少个成员开始获取资料

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
    public function getGroupMemberInfo(Request $request){
        $servers = new ImGroupServers();
        //$this->user['user_id'] = 211172;
        $data = $servers->getGroupMemberInfo($request->input(), $this->user['user_id']);
        return $this->getRes($data);
    }
}
