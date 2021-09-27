<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use App\Models\ImCollection;
use App\Models\ImGroup;
use App\Models\ImMsgContentImg;
use App\Models\ImMsgContent;
use App\Models\ImMsg;
use App\Models\ImSendAll;
use App\Servers\ImMsgServers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Libraries\ImClient;


/**
 * Description of ExpressController
 *
 * @author wangxh
 */
class ImMsgController extends Controller
{

    /**
     * @api {post} /api/v4/im/msg_send_all  消息群发
     * @apiName msg_send_all
     * @apiVersion 1.0.0
     * @apiGroup im
     *
     * @apiParam {int} From_Account  发送方帐号
     * @apiParam {array} To_Account  接收方用户 数组类型
     * @apiParam {array} To_Group   接收方群组 数组类型
     * @apiParam {json} Msg_Content 消息体:[{"MsgType":"TIMTextElem","Text":"文本消息"},{"MsgType":"TIMSoundElem","Url":"语音url"},{"MsgType":"TIMImageElem","Url":"http://xxx/3200490432214177468_144115198371610486_D61040894AC3DE44CDFFFB3EC7EB720F/0"},{"MsgType":"TIMFileElem","Url":"http://xxx/3200490432214177468_144115198371610486_D61040894AC3DE44CDFFFB3EC7EB720F/0","FileName":"file"},{"MsgType":"TIMVideoFileElem","Url":"http://xxx/3200490432214177468_144115198371610486_D61040894AC3DE44CDFFFB3EC7EB720F/0"},{"MsgType":"TIMCustomElem","Data":"eqweqeqe"}]  消息类型  根据MsgType  对应im的字段类型 参考：https://cloud.tencent.com/document/product/269/2720
     * @apiParam {array}  collection_id 收藏id
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
    {
    "code": 200,
    "msg": "成功",
    "data": {}
    }
     */
    public function MsgSendAll(Request $request){
        $params    = $request->input();
        \Log::info('im_log:MsgSendAll-'.json_encode($params));
        $from_account   = $params['From_Account']??'';  //发送方帐号
        $to_accounts    = $params['To_Account']??[];  //消息接收方用户
        $to_group       = $params['To_Group']??[];  //消息接收方群
        $msg_content    = $params['Msg_Content'] ??[];  //消息体
        $collection_id  = $params['collection_id'] ??0;  //消息收藏id


        if( empty($from_account) ){
            return $this->error('0','request from_account error');
        }
        if(empty($collection_id) && empty($msg_content)){
            return $this->error('0','request msg error');
        }
        //接收方用户或者群
        if (empty($to_accounts) && empty($to_group)){
            return $this->error('0','request to users error');
        }
//        //接收方用户或者群
//        if ( count($to_accounts) > 500 ||  count($to_group) > 100){
//            return $this->error('0','群发最多支持500人');
//        }
        $to_accounts = array_values(array_unique($to_accounts));
        //发送收藏的消息
        if( !empty($collection_id) ){
            $msg_ids = ImCollection::whereIn('id',$collection_id)->pluck('msg_id')->toArray();

            $contents = ImMsg::getMsgList($msg_ids);

            $msg_body = [];  //初始化消息体
            foreach ($contents as $key=>$value) {
                $msg_body = array_merge($msg_body,$value['content']);
            }
            $collection_id = implode(",", $collection_id)??'';
        }else{
            //群发的聊天消息
            $msg_content = json_decode($msg_content,true);
            $msg_body = ImMsg::setMsgContent($msg_content);
        }
        //dd($msg_body);
        $msgBody = ImMsg::MsgBody($msg_body);

        if(empty($msgBody)){
            return $this->error('0','Msg Body Error');
        }
        $add_data = [
            'from_account'  => $from_account,
            'to_account'    => implode(",", $to_accounts) ??'',
            'to_group'      => implode(",", $to_group)??'',
            'collection_id' => $collection_id,
            'msg_body'      => json_encode($msgBody),
            'created_at'    => date("Y-m-d H:i:s"),
            'updated_at'    => date("Y-m-d H:i:s"),
        ];


        if(empty($collection_id)){
            //收藏的转发 不进入群发list
            $id = ImSendAll::insertGetId($add_data);
        }else{
            $id = 0;
        }



        //群发请求较多 list处理
        Redis::rpush("send_all_msg_callback", json_encode([
            "from_account"  =>$from_account,
            "msgBody"       =>$msgBody,
            "to_accounts"   =>$to_accounts,
            "to_group"      =>$to_group,
            "add_id"        =>$id,
        ]));

        return $this->success();
    }
    //redis list 群发回调入库调用
    public function RedisSendAllMsgCallback(){
        $redis_key = 'send_all_msg_callback';
        $redis_data=Redis::lrange($redis_key,0,-1);// 获取所有数据
        if(empty($redis_data)){
            return ;
        }
        Redis::ltrim($redis_key,count($redis_data),-1);//删除已取出数据
        foreach ($redis_data as $data) {
            $data = json_decode($data, true);
            $from_account = $data['from_account'];
            $msgBody = $data['msgBody'];
            $to_group = $data['to_group'];
            $to_accounts = $data['to_accounts'];
            $id = $data['add_id'];


            //群发列表
            //查询当前消息的
            //因为群发给多个群无法确定唯一key  所以保留消息体'
//            $add_data = [
//                'from_account'  => $from_account,
//                'to_account'    => implode(",", $to_accounts) ??'',
//                'to_group'      => implode(",", $to_group)??'',
//                'collection_id' => $collection_id,
//                'msg_body'      => json_encode($msgBody),
//                'created_at'    => $time,
//                'updated_at'    => $time,
//            ];
//            if(empty($collection_id)){
//                //收藏的转发 不进入群发list
//                $id = ImSendAll::insertGetId($add_data);
//            }else{
//                $id = 0;
//            }


            $post_data['From_Account'] = $from_account;
            $post_data['MsgBody'] = $msgBody;
            $post_data['CloudCustomData'] = json_encode(['ImSendAllId'=>$id]);

            //用户体 群发
            if(!empty($to_accounts)){
                //本接口不会触发回调  所以需要储存消息体
//                $url = ImClient::get_im_url("https://console.tim.qq.com/v4/openim/batchsendmsg");
//                //去重后 拆分二维数组 每个500数据
//                $to_accounts_arr = array_chunk(array_unique($to_accounts), 500);
//                //该接口最大支持500人
//                foreach ($to_accounts_arr as $to_accounts) {
//                    $post_data['To_Account'] = $to_accounts;
//                    $post_data['MsgSeq']    = rand(10000000,99999999);
//                    $post_data['MsgRandom'] = rand(10000000,99999999);
//                    $res = ImClient::curlPost($url,json_encode($post_data));
//                    $res = json_decode($res,true);
//                    if($res['ActionStatus'] == "OK"){
//                        $post_data['CallbackCommand']   = 'C2C.CallbackAfterSendMsg';
//                        $post_data['SendMsgResult']     = 0;
//                        $post_data['UnreadMsgNum']      = 0;
//                        //$post_data['type']              = 3; //群发
//                        $post_data['MsgTime']           = time();
//                        $post_data['MsgKey']            = $res['MsgKey'];
//                        $post_data['OptPlatform']       = 'RESTAPI' ;
//                        $to_accounts = array_unique($to_accounts);
//                        foreach ($to_accounts as $key=>$val){
//                            $post_data['To_Account'] = $val;
//                            self::sendMsg($post_data);
//                        }
//
//                    }
//                }
                //https://cloud.tencent.com/document/product/269/2282
                $url = ImClient::get_im_url("https://console.tim.qq.com/v4/openim/sendmsg");
                // 接口支持200次/秒
                $to_accounts_arr = array_values(array_unique($to_accounts));
                //该接口最大支持500人
                foreach ($to_accounts_arr as $key=>$to_accounts) {
                    if( $key % 200 == 0 ){
                        sleep(1);
                    }
                    $post_data['To_Account'] = $to_accounts;
                    $post_data['MsgSeq']    = rand(10000000,99999999);
                    $post_data['MsgRandom'] = rand(10000000,99999999);
                    $res = ImClient::curlPost($url,json_encode($post_data));
                }
            }

            //群组体 群发 https://cloud.tencent.com/document/product/269/1629
            if(!empty($to_group)) {
                $url = ImClient::get_im_url("https://console.tim.qq.com/v4/group_open_http_svc/send_group_msg");
                $ImGroup = new ImGroup();
                foreach ($to_group as $item) {
                    //校验改用户在该群中是否禁言
                    $res = $ImGroup->getForbidList($item);
                    if($res['ActionStatus'] =='OK'){
                        $user_ids = array_column($res['ShuttedUinList'],'Member_Account');
                        if(in_array($from_account,$user_ids)){
                            continue;
                        }
                    }

                    $post_data['GroupId'] = $item;
                    $post_data['Random'] = rand(10000000,99999999);
                    ImClient::curlPost($url,json_encode($post_data));
                }
            }
        }


    }


    /**
     * @api {post} /api/v4/im/send_all_list  群发列表
     * @apiName send_all_list
     * @apiVersion 1.0.0
     * @apiGroup im
     *
     * @apiParam {int} page page
     * @apiParam {int} list_id list_id  如果有该参数  获取全部名字  没有只获取10个名称
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
    {
    "code": 200,
    "msg": "成功",
    "data": {}
    }
     */
    function sendAllList(Request $request){
        $imObj = new ImMsgServers();
        //$this->user['id'] = 211172;
        $data = $imObj->sendAllList($request->input(),$this->user['id']);
        return $this->getRes($data);
    }

    /**
     * @api {post} /api/v4/im/del_send_all_list  清空群发列表
     * @apiName del_send_all_list
     * @apiVersion 1.0.0
     * @apiGroup im
     *
     * @apiParam {array} id  需要清空的列表id  不传则全部清空
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
    {
    "code": 200,
    "msg": "成功",
    "data": {}
    }
     */
    function delSendAllList(Request $request){
        $imObj = new ImMsgServers();
        //$this->user['id'] = 211172;
        $data = $imObj->delSendAllList($request->input(),$this->user['id']);
        return $this->getRes($data);


    }


    /**
     * @api {post} /api/v4/im/msg_collection  消息收藏操作
     * @apiName msg_collection
     * @apiVersion 1.0.0
     * @apiGroup im
     *
     * @apiParam {array} os_msg_id  消息序列号 array
     * @apiParam {int} type  收藏类型   1消息收藏
     * @apiParam {array} collection_id  收藏列表id (取消收藏只传该字段)
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
    {
    "code": 200,
    "msg": "成功",
    "data": {}
    }
     */
    public function MsgCollection(Request $request){

        $imObj = new ImMsgServers();
        $data = $imObj->MsgCollection($request->input(),$this->user['id']);
        return $this->getRes($data);
    }


    /**
     * @api {get} /api/v4/im/msg_collection_list  消息收藏列表
     * @apiName msg_collection_list
     * @apiVersion 1.0.0
     * @apiGroup im
     *
     * @apiParam {string} keywords  收藏消息关键字
     * @apiParam {string} page
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
    {
    "code": 200,
    "msg": "成功",
    "data": {}
    }
     */
    public function MsgCollectionList(Request $request){

        $imObj = new ImMsgServers();
        $data = $imObj->MsgCollectionList($request->input(),$this->user['id']);
        return $this->getRes($data);
    }



    public static function getImUser($ids=[]){
        if(empty($ids)){
            return [];
        }
        $url = ImClient::get_im_url("https://console.tim.qq.com/v4/profile/portrait_get");
        $post_data['To_Account'] = $ids;
        $post_data['TagList'] = ['Tag_Profile_IM_Nick','Tag_Profile_IM_Gender',
            'Tag_Profile_IM_BirthDay','Tag_Profile_IM_Location','Tag_Profile_IM_SelfSignature',
            'Tag_Profile_IM_AllowType','Tag_Profile_IM_Language','Tag_Profile_IM_Image',
            'Tag_Profile_IM_MsgSettings','Tag_Profile_IM_AdminForbidType','Tag_Profile_IM_Level','Tag_Profile_IM_Role'
        ];
        $res = ImClient::curlPost($url,json_encode($post_data));
        $res = json_decode($res,true);
        $return_data = [];
        if($res['ActionStatus'] == "OK"){
            foreach ($res['UserProfileItem'] as $userProfile_key=>$userProfileItem_item) {
                if($userProfileItem_item['ResultCode'] == 0){
                    $return_data[$userProfileItem_item['To_Account']] = [];
                    $return_data[$userProfileItem_item['To_Account']]["Tag_Profile_IM_UID"] = $userProfileItem_item['To_Account'];
                    foreach ($userProfileItem_item['ProfileItem'] as $key=>$value) {
                        $return_data[$userProfileItem_item['To_Account']][$value['Tag']] = $value['Value'];
                    }
                }

            }
        }



        return $return_data;


    }


    //回调入库
    public static function sendMsg($params=[]){

        if (empty($params)){
            return false;
        }

        DB::beginTransaction();
        //入库操作
        //消息主库
        $msg_add = [
            'from_account'      => $params['From_Account'],
            'msg_seq'           => $params['MsgSeq'],       //群消息的唯一标识
            'msg_time'          => $params['MsgTime'],
            'platform'          => $params['OptPlatform'],
        ];
        if( !empty($params['CloudCustomData']) ){
            $cloudCustomData = json_decode($params['CloudCustomData'],true);
            $msg_add['send_all_id'] = $cloudCustomData['ImSendAllId']??0;
        }

        //单聊消息
        if($params['CallbackCommand'] == 'C2C.CallbackAfterSendMsg'){
            $msg_add['to_account']          = $params['To_Account'];
            $msg_add['msg_random']          = $params['MsgRandom'];
            $msg_add['msg_key']             = $params['MsgKey'];
            $msg_add['send_msg_result']     = $params['SendMsgResult'];
            $msg_add['unread_msg_num']      = $params['UnreadMsgNum'];
            //单聊    From_Account_To_Account_MsgRandom_MsgSeq
            $msg_add['os_msg_id']           = $params['From_Account'].'_'.$params['To_Account'].'_'.$params['MsgRandom'].'_'.$params['MsgSeq'];
        }
        //群聊消息
        if($params['CallbackCommand'] == 'Group.CallbackAfterSendMsg'){
            $msg_add['group_id']            = $params['GroupId'];
            $msg_add['online_only_flag']    = $params['OnlineOnlyFlag'];
            $msg_add['type']                = 1;
            $msg_add['msg_random']          = $params['Random'];
            //群聊   GroupId_MsgSeq
            $msg_add['os_msg_id']           = $params['GroupId'].'_'.$params['MsgSeq'];
        }

        $msg_add_res = ImMsg::create($msg_add);
        $img_res= true;
        $content_res = true;
        $img_up_res = true;
        //消息体
        foreach ($params['MsgBody'] as $k=>$v){

            $msg_content_add = [
                    'msg_id'        => $msg_add_res->id,
                    'msg_type'      => $v['MsgType'],
                    'created_at'      => date('Y-m-d H:i:s'),
                    'updated_at'      => date('Y-m-d H:i:s'),
                ];

            switch ($v['MsgType']){
                case 'TIMTextElem' :  //文本消息元素
                    $msg_content_add['text']            = $v['MsgContent']['Text'];
                    break;
                case 'TIMFaceElem' : //表情消息元素
                    $msg_content_add['index']           = $v['MsgContent']['Index'];
                    $msg_content_add['data']            = $v['MsgContent']['Data'];
                    break;
                case 'TIMSoundElem' ://语音消息元素
                    $msg_content_add['url']             = $v['MsgContent']['Url'];
                    $msg_content_add['size']            = $v['MsgContent']['Size']??0;
                    $msg_content_add['second']          = $v['MsgContent']['Second']??0;
                    $msg_content_add['download_flag']   = $v['MsgContent']['Download_Flag']??2;
                    break;
                case 'TIMImageElem' ://图片元素

                    $msg_content_add['uuid']            = $v['MsgContent']['UUID']??'';
                    $msg_content_add['image_format']    = $v['MsgContent']['ImageFormat']??0;
                    //群发时  同步图片url
                    if(!empty($v['MsgContent']['URL'])){
                        $msg_content_add['url'] = $v['MsgContent']['URL'];
                    }

                    //保留缩略图
                    $img_res = true;
                    if(!empty($v['MsgContent']['ImageInfoArray'])){
                        foreach ($v['MsgContent']['ImageInfoArray'] as $img_k=>$img_v){
                            if($img_v['Type'] == 3){
                                $msg_content_add['url'] = $img_v['URL'];
                            }
                            //入库图片表
                            $img_add = [
                                'uuid'      => $v['MsgContent']['UUID'],
                                'type'      => $img_v['Type'],
                                'size'      => $img_v['Size'],
                                'width'     => $img_v['Width'],
                                'height'    => $img_v['Height'],
                                'url'       => $img_v['URL'],
                                'created_at'=> date("Y-m-d H:i:s"),
                                'updated_at'=> date("Y-m-d H:i:s"),
                            ];
                            $img_adds[] = $img_add;

                        }

                        if(!empty($img_adds)){
                            foreach($img_adds as $key=>$img_adds_data){
                                $ids[] = ImMsgContentImg::insertGetId($img_adds_data);
                            }

                        }
                        if(empty($ids)){
                            $img_res = false;
                        }

                    }


                    break;
                case 'TIMFileElem' ://文件类型元素
                    $msg_content_add['url']            = $v['MsgContent']['Url'];
                    $msg_content_add['file_size']      = $v['MsgContent']['FileSize']??0;
                    $msg_content_add['file_name']      = $v['MsgContent']['FileName'];
                    $msg_content_add['download_flag']  = $v['MsgContent']['Download_Flag']??2;
                    break;



                case 'TIMVideoFileElem' : //视频类型元素
                    $msg_content_add['video_url']           = $v['MsgContent']['VideoUrl'];
                    $msg_content_add['size']                = $v['MsgContent']['VideoSize']??0;
                    $msg_content_add['second']              = $v['MsgContent']['VideoSecond']??0;
                    $msg_content_add['video_format']        = $v['MsgContent']['VideoFormat']??'';
                    $msg_content_add['download_flag']       = $v['MsgContent']['VideoDownloadFlag']??2;
                    $msg_content_add['video_uuid']          = $v['MsgContent']['VideoUUID']??'';
                    $msg_content_add['uuid']                = $v['MsgContent']['ThumbUUID']??'';
                    $msg_content_add['thumb_url']           = $v['MsgContent']['ThumbUrl']??'';
                    $msg_content_add['thumb_size']          = $v['MsgContent']['ThumbSize']??0;
                    $msg_content_add['thumb_width']         = $v['MsgContent']['ThumbWidth']??0;
                    $msg_content_add['thumb_height']        = $v['MsgContent']['ThumbHeight']??0;
                    $msg_content_add['thumb_format']        = $v['MsgContent']['ThumbFormat']??'';
                    break;
                case 'TIMCustomElem' : //自定义类型
                    $msg_content_add['data']    = $v['MsgContent']['Data']??'';
                    $msg_content_add['desc']    = $v['MsgContent']['Desc']??'';
                    $msg_content_add['ext']     = $v['MsgContent']['Ext']??'';
                    $msg_content_add['sound']   = $v['MsgContent']['Sound']??'';

                    break;
            }
            $content_res = ImMsgContent::insertGetId($msg_content_add);

            if(!empty($ids)){
                $img_up_res = ImMsgContentImg::whereIn('id',$ids)->update(['content_id'=>$content_res]);
            }
            if($content_res){
                $content_res = true;
            }else{
                $content_res=false;
                continue;
            }
            //$msg_content_adds[] = $msg_content_add;
        }
//        if(!empty($msg_content_adds)){
//            $content_res = ImMsgContent::insert($msg_content_adds);
//        }else{
//            $content_res=false;
//        }
        if($msg_add_res && $img_res && $content_res && $img_up_res){
            DB::commit();
            return true;
        }else{
            DB::rollBack();
        }

        return false;

    }

}
