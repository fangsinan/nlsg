<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use App\Models\ImCollection;
use App\Models\ImMsgContentImg;
use App\Models\ImMsgContent;
use App\Models\ImMsg;
use App\Models\ImUserFriend;
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

            $del_id = ImUserFriend::select('*')->where([
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

}