<?php


namespace App\Http\Controllers\Api\V4;


use App\Http\Controllers\Controller;
use App\Models\ImGroup;
use App\Models\ImGroupUser;
use App\Models\Live;
use App\Models\LiveComment;
use App\Models\LiveInfo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Libraries\ImClient;

class ImGroupController extends Controller
{
    /**
     * @api {post} /api/v4/im_group/edit_join_group 添加/删除成员入群
     * @apiName edit_join_group
     * @apiVersion 1.0.0
     * @apiGroup im_group
     *e
     * @apiParam {int} group_id   腾讯云的groupId
     * @apiParam {array} user_id  user_id  数组类型
     * @apiParam {array} type  type==del删除  add添加
     * @apiParam {array} silence  type==del删除时Silence是否静默删人。0表示非静默删人，1表示静默删人
     * @apiParam {array} reason  type==del删除时踢出用户原因
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
        $params    = $request->input();

        if(empty($params['type']) || empty($params['group_id']) || empty($params['user_id'])  ){
            return $this->error('0','request error');
        }

        $imGroup = ImGroup::select('type')->where(['group_id'=>$params['group_id']])->first();
        if(empty($imGroup)){
            return $this->error('0','该群不存在');
        }
        if( !empty($imGroup['type']) && $imGroup['type'] == "AVChatRoom" ){
            return $this->error('0','AVChatRoom 不支持该操作');
        }



        if($params['type'] == 'add'){
            $url = ImClient::get_im_url("https://console.tim.qq.com/v4/group_open_http_svc/add_group_member");
            $post_data['GroupId'] = $params['group_id'];
            foreach ($params['user_id'] as $v){
                $post_data['MemberList'][] = [
                    'Member_Account'=>$v,
                ];
            }
        }elseif($params['type'] == 'del'){
            $url = ImClient::get_im_url("https://console.tim.qq.com/v4/group_open_http_svc/delete_group_member");
            $post_data = [
                'GroupId' => $params['group_id'],
                'Silence' => $params['silence'] ?? '',
                'Reason' => $params['reason'] ?? '',
                'MemberToDel_Account' => $params['user_id'],
            ];
        }else{
            return $this->error(0,'type error');
        }

        $res = ImClient::curlPost($url,json_encode($post_data));
        $res = json_decode($res,true);

        if ($res['ActionStatus'] == 'OK'){
            return $this->success();
        }else{
            return $this->error(0,$res['ActionStatus'],$res['ErrorInfo']);
        }

    }



    /**
     * @api {post} /api/v4/im_group/forbid_send_msg 群成员禁言/解禁
     * @apiName forbid_send_msg
     * @apiVersion 1.0.0
     * @apiGroup im_group
     *e
     * @apiParam {int} group_id   腾讯云的groupId
     * @apiParam {array} user_id  user_id
     * @apiParam {int} shut_up_time  禁言时长  0解禁 其他表示禁言
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
    public function forbidSendMsg(Request $request){
        $params    = $request->input();

        if( empty($params['group_id']) || empty($params['user_id'])  ){
            return $this->error('0','request error');
        }
        $shut_up_time = empty($params['shut_up_time']) ?0 : $params['shut_up_time'];
        $url = ImClient::get_im_url("https://console.tim.qq.com/v4/group_open_http_svc/forbid_send_msg");
        $post_data = [
            'GroupId' => $params['group_id'],
            'Members_Account' => $params['user_id'],  //数组类型
            'ShutUpTime' => $shut_up_time,
        ];
        $res = ImClient::curlPost($url,json_encode($post_data));
        $res = json_decode($res,true);

        if ($res['ActionStatus'] == 'OK'){
            return $this->success();
        }else{
            return $this->error(0,$res['ActionStatus'],$res['ErrorInfo']);
        }

    }



    /**
     * @api {post} /api/v4/im_group/forbid_msg_list 群成员禁言list
     * @apiName forbid_msg_list
     * @apiVersion 1.0.0
     * @apiGroup im_group
     *e
     * @apiParam {int} group_id   腾讯云的groupId
     * @apiParam {array} user_id  user_id
     * @apiParam {int} shut_up_time  禁言时长  0解禁 其他表示禁言
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
    public function forbidMsgList(Request $request){
        $params    = $request->input();

        if( empty($params['group_id']) ){
            return $this->error('0','request error');
        }
        $url = ImClient::get_im_url("https://console.tim.qq.com/v4/group_open_http_svc/get_group_shutted_uin");
        $post_data = [
            'GroupId' => $params['group_id'],
        ];
        $res = ImClient::curlPost($url,json_encode($post_data));
        $res = json_decode($res,true);

        if ($res['ActionStatus'] == 'OK'){
            return $this->success($res['ShuttedUinList']);
        }else{
            return $this->error(0,$res['ActionStatus'],$res['ErrorInfo']);
        }

    }






    /********************************  回调接口 start ********************************/
    //创建群回调
    public static function addGroup($params){

        if (empty($params)){
            return false;
        }
        DB::beginTransaction();
        $group_add = [
            'group_id'          => $params['GroupId'],
            'operator_account'  => $params['Operator_Account'],
            'owner_account'     => $params['Owner_Account'],
            'type'              => $params['Type'],
            'name'              => $params['Name'],
            'created_at'        => date('Y-m-d H:i:s'),
            'updated_at'        => date('Y-m-d H:i:s'),
        ];
        $group_add_id = ImGroup::insert($group_add);

        $adds = [];
        foreach ($params['MemberList'] as $key=>$val){

            $add = [
                'group_id'      => $params['GroupId'],
                'group_account' => $val['Member_Account'],
                'operator_account'  => $params['Operator_Account'],
                'join_type'         => 'Invited',  //Invited 邀请入群
                'group_role'    => 0,
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ];


            if($val['Member_Account'] == $params['Owner_Account']){
                $add['group_role'] = 1;
            }

            $adds[] = $add;

        }
        $gu_res = true;
        if(!empty($adds)){
            $gu_res = ImGroupUser::insert($adds);
        }

        if($group_add_id && $gu_res){
            DB::commit();
            return true;
        }else{
            DB::rollBack();
        }

        return false;

    }

    //修改/解散  群
    public static function editGroup($params){

        if (empty($params)){
            return false;
        }
        if($params['CallbackCommand'] == 'Group.CallbackAfterGroupDestroyed'){
            $ed_data = ['status'=>2];
        }elseif($params['CallbackCommand'] == 'Group.CallbackAfterGroupInfoChanged'){

            $ed_data = [
                'Type'              => $params['Type'],
                'operator_account'  => $params['Operator_Account'],
            ];

            if(!empty($params['Name'])){
                $ed_data['name'] =$params['Name'];
            }
            if(!empty($params['Introduction'])){
                $ed_data['introduction'] =$params['Introduction'];
            }
            if(!empty($params['Notification'])){
                $ed_data['notification'] =$params['Notification'];
            }
            if(!empty($params['FaceUrl'])){
                $ed_data['face_url'] =$params['FaceUrl'];
            }

        }else{
            return false;
        }

        $group_res = ImGroup::where([
            'group_id' =>$params['GroupId'],
        ])->update($ed_data);

        return true;
    }


    //群聊消息回调
    public static function groupSend($params){

        if (empty($params)){
            return false;
        }
        dd($params);
        //回调 类型如果是直播群  需要发送至redis
        if($params['Type'] == "AVChatRoom"){
            //直播群
            $user_id = $params['From_Account'];
            $content = $params['MsgBody']['MsgContent']->Text;
            $liveData = Live::select("id")->where('im_group_id',$params['GroupId'])->first();

            $UserInfo = User::find($user_id);
            //查询live_id
            $data = json_encode(['type' => 2, 'content_text'=>$content, 'userinfo' => ['user_id'=>$user_id,
                'level' => $UserInfo['level'],'nickname' => $UserInfo['nickname']]]);

            Redis::rPush('live_comment_'.$liveData['id'],$data);

            $liveInfoData = LiveInfo::select("id")->where('live_pid',$liveData['id'])->first();

            //消息入库
            LiveComment::insert([
                'live_id'=>$liveData['id'],
                'live_info_id'=>$liveInfoData['id'],
                'user_id'=>$user_id,
                'content'=>$content,
            ]);
        }


        return true;

    }


    //新成员入群之后回调
    public static function joinGroup($params){

        if (empty($params)){
            return false;
        }

        $group = ImGroup::where(['group_id'=>$params['GroupId']])->first();
        if(empty($group)){
            return false;
        }
        $adds = [];
        foreach ($params['NewMemberList'] as $key=>$item) {
            $add = [
                'group_id'          => $params['GroupId'],
                'group_account'     => $item['Member_Account'],
                'operator_account'  => $params['Operator_Account'],
                'join_type'         => $params['JoinType'],
                'group_role'        => 0,
//                'created_at'    => date('Y-m-d H:i:s'),
//                'updated_at'    => date('Y-m-d H:i:s'),
            ];


            $adds[] = $add;
        }
        if(!empty($adds)){
            $gu_res = ImGroupUser::firstOrCreate($adds);
        }


        return true;

    }

    //群成员离开之后回调
    public static function exitGroup($params){

        if (empty($params)){
            return false;
        }
        //  未解散群
        $group = ImGroup::where(['group_id'=>$params['GroupId'], 'status'=>1,])->first();
        if(empty($group)){
            return false;
        }
        $exit_type = ['Kicked'=>1,'Quit'=>2];   // 成员离开方式：Kicked-被踢；Quit-主动退群
        foreach ($params['ExitMemberList'] as $key=>$item) {
            $ed_data = [
                'operator_account'  => $params['Operator_Account'],
                'exit_type'         => empty($exit_type[$params['ExitType']])?0:$exit_type[$params['ExitType']],
            ];

            ImGroupUser::where([
                'group_id'      =>$params['GroupId'],
                'group_account' => $item['Member_Account'],
            ])->update($ed_data);
        }

        return true;

    }



}