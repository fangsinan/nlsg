<?php

namespace App\Servers;

use App\Http\Controllers\Api\V4\ImMsgController;
use App\Models\ImCollection;
use App\Models\ImGroup;
use App\Models\ImMsg;
use App\Models\ImSendAll;
use App\Models\ImUser;
use Illuminate\Http\Request;

class ImMsgServers
{
    public function getMsgList($params, $user_id)
    {
        $type = $params['type'] ?? -1;
        if (!in_array($type, [0, 1])) {
            return ['code' => false, 'msg' => '参数错误', 'ps' => 'type'];
        }

        $query = ImMsg::query()->where('type', '=', $type);

        $from_account = $params['from_account'] ?? 0;
        $to_account = $params['to_account'] ?? 0;
        $group_id = $params['group_id'] ?? 0;
        $size = $params['size'] ?? 10;
        $begin_date = $params['begin_date'] ?? 0;
        $end_date = $params['end_date'] ?? 0;


        switch (intval($type)) {
            case 0://个人
                if (empty($from_account ?? '') || empty($to_account)) {
                    return ['code' => false, 'msg' => '参数错误', 'ps' => 'from_account,to_account'];
                }
                break;
            case 1://群聊
                if (empty($group_id)) {
                    return ['code' => false, 'msg' => '参数错误', 'ps' => 'group_id'];
                }
                break;
        }
        $query->with(['content']);

        //TIMTextElem(文本消息)，TIMLocationElem(位置消息)，TIMFaceElem(表情消息)，
        //TIMCustomElem(自定义消息)，TIMSoundElem(语音消息)，TIMImageElem(图像消息)
        //TIMFileElem(文件消息)，TIMVideoFileElem(视频消息)',
        if (!empty($params['msg_type'] ?? '')) {
            $msg_type = $params['msg_type'];
            $query->whereHas('content.msg_type', function ($q) use ($msg_type) {
                $q->where('msg_type', '=', $msg_type);
            });
        }

        if (!empty($begin_date)) {
            $query->where('msg_time', '>=', strtotime($begin_date));
        }
        if (!empty($end_date)) {
            $query->where('msg_time', '<=', strtotime($end_date));
        }
        if (!empty($from_account)) {
            $query->where('from_account', '=', $params['from_account']);
        }
        if (!empty($to_account)) {
            $query->where('to_account', '=', $params['to_account']);
        }
        if (!empty($group_id)) {
            $query->where('group_id', '=', $params['group_id']);
        }

        return $query->orderBy('msg_time', 'desc')
            ->orderBy('msg_seq', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($size);

    }



    //群发list
    public function sendAllList($params,$uid){
        if(empty($uid)){
            return [];
        }
        //群发列表
        //$list = ImSendAll::where(['from_account' => $uid,'status'=>0])->get()->toArray();
        $lists = ImSendAll::where(['from_account' => $uid,'status'=>0])->orderBy('created_at',"desc")->paginate(20)->toArray();
        if(empty($lists['data'])){
            return [];
        }
        $list = $lists['data'];
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

        //由于=腾讯的限制(100)   多个获取时 慢
        //$userProfileItem = ImMsgController::getImUser($uids);
        $userProfileItem=ImUser::select("tag_im_to_account as Tag_Profile_IM_UID","tag_im_nick as Tag_Profile_IM_Nick")
            //->whereIn('tag_im_to_account',array_slice($uids, 0,20))->get()->toArray();
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


    //清空群发记录 delSendAllList
    public function delSendAllList($params,$uid){
        if(empty($uid)){
            return [];
        }
//dd($params);
        //清空群发列表
        $query = ImSendAll::where(['from_account' => $uid]);

        if(!empty($params['id']) && is_array($params['id'])){
            $query->whereIn('id',$params['id']);
        }

        $query->update(['status'=>1]);

        return [];
    }

    //收藏列表
    public function MsgCollectionList($params,$uid){
        $keywords = $params['keywords'] ?? '';  //消息关键字

        $collectionList = ImCollection::select("id","user_id","msg_id","created_at")->where([
            'type'=>1,'user_id'=>$uid,'state'=>1
        ])->orderBy('created_at',"desc")->paginate(20)->toArray();

        //获取消息
        $msg_ids = array_column($collectionList['data'],'msg_id');
        //$msg_list = ImMsg::getMsgList($msg_ids);
        $query = ImMsg::with([
            'content:id,msg_id,msg_type as MsgType,text as Text,url as Url,video_url as VideoUrl,thumb_url as ThumbUrl,data as Data,file_name as FileName,file_size as FileSize',
        ]);

        if(!empty($keywords)){
            $query->whereHas('content', function ($query) use ($keywords){
                $query->where(function ($query)use($keywords){
                    $query->orWhere('nlsg_im_msg_content.text','LIKE',"%$keywords%");
                    $query->orWhere('nlsg_im_msg_content.data','LIKE',"%$keywords%");
                    $query->orWhere('nlsg_im_msg_content.file_name','LIKE',"%$keywords%");
                });
            });
        }

        $msg_list = $query->select('id', 'type', 'msg_seq','msg_time','from_account', 'to_account', 'group_id')
            ->whereIn('id',$msg_ids)->get()->toArray();

        //获取用户信息
        $uids = array_column($msg_list,'from_account');
        $uids = array_merge($uids, array_column($msg_list,'to_account'));
        $userProfileItem = ImMsgController::getImUser($uids);

        // 群名称
        $group_ids = array_column($msg_list,'group_id');
        $new_group = [];
        if(!empty($group_ids)){
            $group = ImGroup::whereIn('group_id',$group_ids)->get()->toArray();
            foreach ($group as $val){
                $new_group[$val['group_id']] = $val['name'];
            }
        }
        foreach ($collectionList['data'] as $key=>$val) {
            $collectionList['data'][$key]['msg_list'] = [];
            foreach ($msg_list as $item){
                $item['collection_time'] = $val['created_at'];
                //消息昵称
                $item['nick_name'] = $userProfileItem[$item['from_account']]['Tag_Profile_IM_Nick']??'';
                //收藏来源
                $item['to_coll_name'] = '';
                if(!empty($userProfileItem[$item['to_account']]['Tag_Profile_IM_Nick'])){
                    $item['to_coll_name'] = $userProfileItem[$item['to_account']]['Tag_Profile_IM_Nick'];
                }else if(!empty($new_group[$item['group_id']])){
                    $item['to_coll_name'] = $new_group[$item['group_id']];
                }

                if($val['msg_id'] == $item['id']){
                    $collectionList['data'][$key]['msg_list'] = $item;
                    break;
                }
            }

            //如果搜索keyword后 没有匹配到 则删除该key值
            if(empty($collectionList['data'][$key]['msg_list'])){
                unset($collectionList['data'][$key]);
            }

        }
        $collectionList['data'] = array_values($collectionList['data']);

        return $collectionList;
    }


    //收藏操作
    public function MsgCollection($params,$uid){

        $os_msg_id = $params['os_msg_id']??[];  //消息
        $type = $params['type'] ?? 1;  //类型
        $collection_id = $params['collection_id']??[];  //id

        if(!empty($collection_id)){
            ImCollection::whereIn('id',$collection_id)->update(['state' => 2,]);
            return [];
        }
        if(!is_array($os_msg_id)){
            return ['code'=>false,'msg' => 'msg_key error'];//$this->error('0','msg_key error');
        }
        $msg = ImMsg::whereIn('os_msg_id',$os_msg_id)->get()->toArray();
        if(empty($msg)){
            return ['code'=>false,'msg' => 'os_msg_id error'];
        }

        foreach ($msg as $k=>$v){
            $data = [
                'user_id' => $uid,
                'msg_id' => $v['id'],
                'type'    => $type,
                'state'    => 1,
            ];
            ImCollection::firstOrCreate($data);
        }


        return [];
    }


}
