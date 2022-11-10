<?php


namespace App\Servers;

use App\Http\Controllers\Api\V4\ImMsgController;
use App\Models\ImUser;
use App\Models\ImUserFriend;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Libraries\ImClient;

class ImFriendServers
{


    //校验用户关系
    public function friendCheck($params){

        if( empty($params['From_Account']) || empty($params['To_Account']) || !is_array($params['To_Account']) ){
            return ['code'=>false,   'msg'=>'request From_Account or To_Account error'];
        }

        $url = ImClient::get_im_url("https://console.tim.qq.com/v4/sns/friend_check");
        $post_data = [
            'From_Account'  => (string)$params['From_Account'],
            'To_Account'    => $params['To_Account'],
            'CheckType'     => 'CheckResult_Type_Both',
        ];

        $res = ImClient::curlPost($url,json_encode($post_data));
        $res = json_decode($res,true);

        return $res['InfoItem'] ?? [];

    }


    //拉取用户资料
    public function getPortrait($params){

        //直播开始后 此接口可不执行
        if(  time() >= strtotime(date("Y-m-d 18:30:0")) && time() <= strtotime(date("Y-m-d 21:30:0")) ){
            return [];
        }

        if( empty($params['user_id']) ){
            return ['code'=>false,   'msg'=>'request user_id error'];
        }



        $user = ImMsgController::getImUser([$params['user_id']]);

        if(!empty($user)){
            foreach ($user as $key=>$value) {
                $is_user = ImUser::where(['tag_im_to_account'=>$key ])->first();

                $data = [
                    'tag_im_to_account'     => $key,
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
                    //dd(ImUser::insert($data));
                }else{
                    //edit
                    ImUser::where(['tag_im_to_account'=>$params['user_id']])->update($data);
                }
            }
        }

        return [];

    }

    //添加好友
    public function addFriend($params){


        if( empty($params['From_Account']) || empty($params['To_Account']) ){
            return ['code'=>false,   'msg'=>'request From_Account or To_Account error'];
        }
        $user = ImUserFriend::where(['from_account'=>$params['From_Account'], 'to_account'=>$params['To_Account'],'status'=>1])->first();
        if(!empty($user)){
            return [];
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
            'From_Account'  =>  (string)$params['From_Account'],
            'AddFriendItem'     =>  [
                [   'To_Account' => (string)$params['To_Account'],
                    'AddSource'=>'AddSource_Type_'.$AddSource,
                    'AddWording'=>$params['AddWording'] ??'',
                ]
            ]
        ];
        $res = ImClient::curlPost($url,json_encode($post_data));
        $res = json_decode($res,true);

        if ($res['ActionStatus'] == 'OK'){
            return [];
        }else{
            return ['code'=>false,   'msg'=>$res['ErrorInfo']];
        }
    }

    //删除 好友
    public function delFriend($params){

        if( empty($params['From_Account']) || empty($params['To_Account']) || !is_array($params['To_Account']) ){
            return ['code'=>false,   'msg'=>'request From_Account or To_Account error'];
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
            return [];
        }else{
            return ['code'=>false,   'msg'=>$res['ErrorInfo']];
        }
    }

    //获取好友列表 从im  （废弃中)
    public function imFriendList($params){

        $url = ImClient::get_im_url("https://console.tim.qq.com/v4/sns/friend_get");
        $post_data = [
            'From_Account'          =>  (string)$params['From_Account'],
            'StartIndex'            =>  $params['StartIndex'] ?? 0,
            'StandardSequence'      =>  $params['StandardSequence'] ?? 0,
            'CustomSequence'        =>  $params['CustomSequence'] ?? 0,
        ];
        $res = ImClient::curlPost($url,json_encode($post_data));
        $res = json_decode($res,true);

        return $res;
    }

    public function getImUserId($params){
        if(empty($params['phone'])){
            return [];
        }

//        $user = User::select("id","nickname","headimg","phone")
//            ->where('phone', 'like',  $params['phone'] . '%')->limit(30)->get()->toArray();



        $user = DB::table('nlsg_user as u')
            ->select("u.id","u.nickname","u.headimg","u.phone")
            ->join('nlsg_im_user as iu',
                'u.id', '=', 'iu.tag_im_to_account')
            ->where('phone', 'like', $params['phone'] . '%')->limit(30)->get()->toArray();

        return $user;
    }
}
