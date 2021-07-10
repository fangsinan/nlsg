<?php


namespace App\Http\Controllers\Api\V4;


use App\Http\Controllers\Controller;
use App\Models\ImGroup;
use App\Models\ImGroupUser;
use App\Models\Live;
use App\Models\LiveComment;
use App\Models\LiveInfo;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class ImGroupController extends Controller
{

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
        $id = ImGroup::insertGetId($group_add);

        $adds = [];
        foreach ($params['MemberList'] as $key=>$val){

            $add = [
                'group_id'      => $id,
                'group_account' => $val['Member_Account'],
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

}