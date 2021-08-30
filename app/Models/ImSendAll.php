<?php

namespace App\Models;



class ImSendAll extends Base
{

    protected $table = 'nlsg_im_send_all';

    protected $fillable = ['from_account', 'to_account', 'group_id','type','msg_id'];


    //处理群发list
    public function getList($list,$name_len=0){

        $uids = [];
        $group_id = [];
        foreach ($list as $key=>$value){
            if(!empty($value['to_account'])){
                $uids = array_merge($uids, explode(',',$value['to_account']));
            }
            if(!empty($value['to_group'])){
                $group_id = array_merge($group_id, explode(',',$value['to_group']));
            }
        }

        if($name_len > 0){
            $uids = array_slice($uids, 0,$name_len);
            $group_id = array_slice($group_id, 0,$name_len);
        }
        //由于=腾讯的限制(100)   多个获取时 慢
        //$userProfileItem = ImMsgController::getImUser($uids);
        $userProfileItem=ImUser::select("tag_im_to_account as Tag_Profile_IM_UID","tag_im_nick as Tag_Profile_IM_Nick")
            ->whereIn('tag_im_to_account',$uids)->get()->toArray();

        $groups = ImGroup::select('name','group_id')->whereIn('group_id',$group_id)->get()->toArray();

        foreach ($list as $key=>$value){
            $list[$key]['to_account_name'] = [];
            $list[$key]['to_group_name'] = [];
            //群发好友
            if( !empty($value['to_account']) ){
                $to_account = (string)','.$value['to_account'].',';  //防止出现有相同数字的不同位数的id
                foreach ($userProfileItem as $user_v) {
                    if( strpos($to_account,(string)','.$user_v['Tag_Profile_IM_UID'].',') !== false ){
                        $list[$key]['to_account_name'][] = $user_v['Tag_Profile_IM_Nick'];
                    }
                }
            }
            //群发群组
            if( !empty($value['to_group']) ){
                foreach ($groups as $group_v) {
                    if(strpos($value['to_group'],$group_v['group_id']) !== false ){
                        $list[$key]['to_group_name'][] = $group_v['name'];
                    }
                }
            }
        }

        return $list;
    }

}
