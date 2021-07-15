<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use App\Models\ImCollection;
use App\Models\ImMsgContentImg;
use App\Models\ImMsgContent;
use App\Models\ImMsg;
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


        $is_staff = User::where('id',$params['user_id'])->value('is_staff');
        $res_data['is_community_admin'] = $is_staff == 2 ? 1:0;  // im管理员


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