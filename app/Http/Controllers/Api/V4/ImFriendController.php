<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use App\Models\ImCollection;
use App\Models\ImMsgContentImg;
use App\Models\ImMsgContent;
use App\Models\ImMsg;
use App\Models\ImUser;
use App\Models\ImUserBlacklist;
use App\Models\ImUserFriend;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Libraries\ImClient;


/**
 * Description of ExpressController
 *
 * @author wangxh
 */
class ImFriendController extends Controller
{


    /**
     * @api {post} /api/v4/im_friend/friend_check  校验用户关系
     * @apiName friend_check
     * @apiVersion 1.0.0
     * @apiGroup im_friend
     *e
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
        $params    = $request->input();

        if( empty($params['From_Account']) || empty($params['To_Account']) || !is_array($params['To_Account']) ){
            return $this->error('0','request From_Account or To_Account error');
        }

        $url = ImClient::get_im_url("https://console.tim.qq.com/v4/sns/friend_check");
        $post_data = [
            'From_Account'  => $params['From_Account'],
            'To_Account'    => $params['To_Account'],
            'CheckType'     => 'CheckResult_Type_Both',
        ];

        $res = ImClient::curlPost($url,json_encode($post_data));
        $res = json_decode($res,true);

        return $this->success($res['InfoItem'] ?? []);

    }


    /**
     * @api {get} /api/v4/im_friend/portrait_get  拉取im 用户资料
     * @apiName portrait_get
     * @apiVersion 1.0.0
     * @apiGroup im_friend
     *e
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
        $params    = $request->input();

        if( empty($params['user_id']) ){
            return $this->error('0','request user_id error');
        }
        $user = ImMsgController::getImUser([$params['user_id']]);

        if(!empty($user)){
            foreach ($user as $key=>$value) {
                $is_user = ImUser::where(['tag_im_to_account'=>$key ])->first();

                $data = [
                    'tag_im_to_account'     => $params['user_id'],
                    'tag_im_nick'           => $value['Tag_Profile_IM_Nick']??'',
                    'tag_im_gender'         => $value['Tag_Profile_IM_Gender']??'',
                    'tag_im_birth_day'      => $value['Tag_Profile_IM_BirthDay']??'',
                    'tag_im_location'       => $value['Tag_Profile_IM_Location']??'',
                    'tag_im_self_signature' => $value['Tag_Profile_IM_SelfSignature']??'',
                    'tag_im_allow_type'     => $value['Tag_Profile_IM_AllowType']??'',
                    'tag_im_language'       => $value['Tag_Profile_IM_Language']??'',
                    'tag_im_image'          => $value['Tag_Profile_IM_Image']??'',
                    'tag_im_msg_settings'   => $value['Tag_Profile_IM_MsgSettings']??'',
                    'tag_im_admin_forbid_type'  => $value['Tag_Profile_IM_AdminForbidType']??'',
                    'tag_im_level'          => $value['Tag_Profile_IM_Level']??0,
                    'tag_im_role'           => $value['Tag_Profile_IM_Role']??0,
                    'created_at'            => date("y-m-d H:i:s"),
                    'updated_at'            => date("y-m-d H:i:s"),
                ];

                if(empty($is_user)){
                    //add
                    ImUser::insert($data);
                }else{
                    //edit
                    ImUser::where(['tag_im_to_account'=>$params['user_id']])->update($data);
                }
            }
        }

        return $this->success();

    }








    /**
     * @api {get} /api/v4/im_friend/get_im_user  拉取im权限
     * @apiName get_im_user
     * @apiVersion 1.0.0
     * @apiGroup im_friend
     *e
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
    public function getImUser(Request $request){
        $params    = $request->input();

        if( empty($params['user_id']) ){
            return $this->error('0','request user_id error');
        }
        $res_data = [
            'is_community_admin' =>0,
            'AccountStatus' =>0,
        ];


        $is_community_admin = User::where('id',$params['user_id'])->value('is_community_admin');
        $res_data['is_community_admin'] = $is_community_admin;  // im管理员


        $url = ImClient::get_im_url("https://console.tim.qq.com/v4/im_open_login_svc/account_check");
        $post_data = [
            'CheckItem'=>[
                ['UserID' => $params['user_id'],]
            ]
        ];

        $res = ImClient::curlPost($url,json_encode($post_data));
        $res = json_decode($res,true);

        if ($res['ActionStatus'] == 'OK'){
            foreach ($res['ResultItem'] as $Item) {
                if($Item['UserID'] == $params['user_id']){
                    if($Item['AccountStatus'] == 'NotImported'){
                        $res_data['AccountStatus'] = 0;
                    }else{
                        $res_data['AccountStatus'] = 1;

                    }
                }

            }
        }

        return $this->success($res_data);

    }








    /**
     * @api {get} /api/v4/im_friend/add_friend  Im添加好友
     * @apiName add_friend
     * @apiVersion 1.0.0
     * @apiGroup im_friend
     *e
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
        $params    = $request->input();

        if( empty($params['From_Account']) || empty($params['To_Account']) ){
            return $this->error('0','request From_Account or To_Account error');
        }
        $user = ImUserFriend::where(['from_account'=>$params['From_Account'], 'to_account'=>$params['To_Account'],'status'=>1])->first();
        if(!empty($user)){
            return $this->success([],0,'已经是好友');
        }
        $os_type = empty($params['os_type']) ?3:$params['os_type'];

        switch ($os_type){
            case 1:
                $AddSource = 'Android'; break;
            case 2:
                $AddSource = 'Ios';     break;
            case 3:
                $AddSource = 'Web';     break;
            default:
                $AddSource = 'web';     break;
        }


        $url = ImClient::get_im_url("https://console.tim.qq.com/v4/sns/friend_add");
        $post_data = [
            'From_Account'  =>  $params['From_Account'],
            'AddFriendItem'     =>  [
                [   'To_Account' => $params['To_Account'],
                    'AddSource'=>'AddSource_Type_'.$AddSource,
                    'AddWording'=>$params['AddWording'] ??'',
                ]
            ]
        ];
        $res = ImClient::curlPost($url,json_encode($post_data));
        $res = json_decode($res,true);

        if ($res['ActionStatus'] == 'OK'){
            return $this->success();
        }else{
            return $this->error(0,$res['ErrorCode'],$res['ErrorInfo']);
        }
    }

    /**
     * @api {get} /api/v4/im_friend/del_friend  Im删除好友
     * @apiName del_friend
     * @apiVersion 1.0.0
     * @apiGroup im_friend
     *e
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
        $params    = $request->input();

        if( empty($params['From_Account']) || empty($params['To_Account']) || !is_array($params['To_Account']) ){
            return $this->error('0','request From_Account or To_Account error');
        }
        $url = ImClient::get_im_url("https://console.tim.qq.com/v4/sns/friend_delete");
        $post_data = [
            'From_Account'  =>  $params['From_Account'],
            'To_Account'    =>  $params['To_Account'],
            'DeleteType'    => "Delete_Type_Both",
        ];
        $res = ImClient::curlPost($url,json_encode($post_data));
        $res = json_decode($res,true);

        if ($res['ActionStatus'] == 'OK'){
            return $this->success();
        }else{
            return $this->error(0,$res['ErrorCode'],$res['ErrorInfo']);
        }
    }








    /********************************  回调接口 start ********************************/

    //添加好友
    public static function friendAdd($params){
        if (empty($params)){
            return false;
        }

        $adds = [];
        foreach ($params['PairList'] as $key=>$val){
            $add['from_account'] = $val['From_Account'];
            $add['to_account'] = $val['To_Account'];
            $add['initiator_account'] = $val['Initiator_Account'];
            $add['status'] = 1;
            $add['force_flag'] = $params['ForceFlag'];
            $add['created_at'] = date("Y-m-d h:i:s");
            $add['updated_at'] = date("Y-m-d h:i:s");
            $adds[] = $add;
        }
        if($adds){
            ImUserFriend::insert($adds);
        }

        return true;
    }


    //删除好友
    public static function friendDel($params){
        if (empty($params)){
            return false;
        }

        $del_ids = [];
        foreach ($params['PairList'] as $key=>$val){

            $del_id = ImUserFriend::where([
                'from_account'  =>$val['From_Account'],
                'to_account'    =>$val['To_Account'],
                'status'        =>1,
            ])->value('id');

            $del_ids[] = $del_id;
        }
        if($del_ids){
            ImUserFriend::whereIn('id',$del_ids)->update(['status'=>2]);
        }

        return true;
    }



    //添加黑名单
    public static function blackListAdd($params){
        if (empty($params)){
            return false;
        }
        $adds = [];
        foreach ($params['PairList'] as $key=>$val){
            $add['from_account']= $val['From_Account'];
            $add['to_account']  = $val['To_Account'];
            $add['created_at']  = date("Y-m-d h:i:s");
            $add['updated_at']  = date("Y-m-d h:i:s");
            $adds[] = $add;
        }
        if($adds){
            ImUserBlacklist::insert($adds);
        }

        return true;
    }


    //删除黑名单
    public static function blackListDel($params){
        if (empty($params)){
            return false;
        }

        $del_ids = [];
        foreach ($params['PairList'] as $key=>$val){

            $del_id = ImUserBlacklist::where([
                'from_account'  =>$val['From_Account'],
                'to_account'    =>$val['To_Account'],
                'status'        =>1,
            ])->value('id');

            $del_ids[] = $del_id;
        }
        if($del_ids){
            ImUserBlacklist::whereIn('id',$del_ids)->update(['status'=>2]);
        }

        return true;
    }


}