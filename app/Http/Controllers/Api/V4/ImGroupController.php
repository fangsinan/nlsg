<?php


namespace App\Http\Controllers\Api\V4;


use App\Http\Controllers\Controller;
use App\Models\ImGroup;
use App\Models\ImGroupUser;
use App\Models\Live;
use App\Models\LiveComment;
use App\Models\LiveInfo;
use App\Models\User;
use Illuminate\Support\Facades\Redis;

class ImGroupController extends Controller
{

    //创建群回调
    public function addGroup($params){

        if (empty($params)){
            return false;
        }

        $group_add = [
            'group_id'      => $params['GroupId'],
            'operator_account'        => $params['Operator_Account'],
            'owner_account'           => $params['Owner_Account'],
            'type'           => $params['Type'],
            'name'           => $params['Name'],

        ];
        $group_add_id = ImGroup::insert($group_add);

        $adds = [];
        foreach ($params['MemberList'] as $key=>$val){

            $add = [
                'group_id' => $group_add_id,
                'group_account' => $val['Member_Account'],
                'group_role' => 0,
            ];

            $adds[] = $add;

        }
        if(!empty($adds)){
            ImGroupUser::insert($adds);
        }


        return true;

    }


    //群聊消息回调
    public function groupSend($params){

        if (empty($params)){
            return false;
        }
        
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

}