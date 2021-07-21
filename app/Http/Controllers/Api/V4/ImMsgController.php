<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use App\Models\ImCollection;
use App\Models\ImMsgContentImg;
use App\Models\ImMsgContent;
use App\Models\ImMsg;
use App\Models\ImSendAll;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
     * @apiParam {array} Msg_Content 消息体:[{"MsgType":"TIMTextElem","Text":"文本消息"},{"MsgType":"TIMSoundElem","Url":"语音url"}] 数组类型  根据MsgType  对应im的字段类型 参考：https://cloud.tencent.com/document/product/269/2720
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

        //发送收藏的消息
        if( !empty($collection_id) ){
            $msg_ids = ImCollection::whereIn('id',$collection_id)->pluck('msg_id')->toArray();

            $contents = ImMsg::getMsgList($msg_ids);

            $msg_content = [];  //初始化消息体
            foreach ($contents as $key=>$value) {
                $msg_content = array_merge($msg_content,$value['content']);
            }
        }

        $msgBody = ImMsg::MsgBody($msg_content);

        if(empty($msgBody)){
            return $this->error('0','Msg Body Error');
        }
        //群发列表
        //查询当前消息的
        //因为群发给多个群无法确定唯一key  所以保留消息体'
        $add_data = [
            'from_account'  => $from_account,
            'to_account'    => implode(",", $to_accounts) ??'',
            'to_group'      => implode(",", $to_group)??'',
            'collection_id' => implode(",", $collection_id)??'',
            'msg_body'      => json_encode($msgBody),
            'created_at'    => date("Y-m-d h:i:s"),
            'updated_at'    => date("Y-m-d h:i:s"),
        ];

        $id = ImSendAll::insertGetId($add_data);

        $post_data['From_Account'] = $from_account;
        $post_data['MsgBody'] = $msgBody;
        $post_data['CloudCustomData'] = json_encode(['ImSendAllId'=>$id]);

        //用户体 群发
        if(!empty($to_accounts)){
            //本接口不会触发回调  所以需要储存消息体
            $url = ImClient::get_im_url("https://console.tim.qq.com/v4/openim/batchsendmsg");
            $post_data['To_Account'] = $to_accounts;
            $post_data['MsgSeq']    = rand(10000000,99999999);
            $post_data['MsgRandom'] = rand(10000000,99999999);
            $res = ImClient::curlPost($url,json_encode($post_data));
            $res = json_decode($res,true);
            if($res['ActionStatus'] == "OK"){
                $post_data['CallbackCommand']   = 'C2C.CallbackAfterSendMsg';
                $post_data['SendMsgResult']     = 0;
                $post_data['UnreadMsgNum']      = 0;
                //$post_data['type']              = 3; //群发
                $post_data['MsgTime']           = time();
                $post_data['MsgKey']            = $res['MsgKey'];
                $to_accounts = array_unique($to_accounts);
                foreach ($to_accounts as $key=>$val){
                    $post_data['To_Account'] = $val;
                    self::sendMsg($post_data);
                }

            }
        }

        //群组体 群发
        if(!empty($to_group)) {
            $url = ImClient::get_im_url("https://console.tim.qq.com/v4/group_open_http_svc/send_group_msg");
            foreach ($to_group as $item) {
                $post_data['GroupId'] = $item;
                $post_data['Random'] = rand(10000000,99999999);
                ImClient::curlPost($url,json_encode($post_data));
            }
        }
        return $this->success();
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
        $os_msg_id = $request->input('os_msg_id');  //消息
        $type = $request->input('type') ?? 1;  //类型
        $collection_id = $request->input('collection_id');  //id

        if(!empty($collection_id)){
            ImCollection::whereIn('id',$collection_id)->update(['state' => 2,]);
            return $this->success();
        }
        \Log::info('im_param_log'.json_encode($request->input()));
        if(!is_array($os_msg_id)){
            return $this->error('0','msg_key error');
        }
        $msg = ImMsg::whereIn('os_msg_id',$os_msg_id)->get()->toArray();
        if(empty($msg)){
            return $this->error('0','os_msg_id error');
        }
        $uid = $this->user['id']; //uid

        foreach ($msg as $k=>$v){
            $data = [
                'user_id' => $uid,
                'msg_id' => $v['id'],
                'type'    => $type,
                'state'    => 1,
            ];
            ImCollection::firstOrCreate($data);
        }


        return $this->success();
    }


    /**
     * @api {get} /api/v4/im/msg_collection_list  消息收藏列表
     * @apiName msg_collection_list
     * @apiVersion 1.0.0
     * @apiGroup im
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
        $uid = $this->user['id'];

        $collectionList = ImCollection::select("id","user_id","msg_id","created_at")->where([
            'type'=>1,'user_id'=>$uid,'state'=>1
        ])->orderBy('created_at',"desc")->paginate($this->page_per_page)->toArray();

        //获取消息
        $msg_ids = array_column($collectionList['data'],'msg_id');
        $msg_list = ImMsg::getMsgList($msg_ids);

        //获取用户信息
        $uids = array_column($msg_list,'from_account');
        $userProfileItem = self::getImUser($uids);

        foreach ($collectionList['data'] as $key=>$val) {
            $collectionList['data'][$key]['msg_list'] = [];
            foreach ($msg_list as $item){
                //消息昵称
                $item['nick_name'] = $userProfileItem[$item['from_account']]['Tag_Profile_IM_Nick']??'';
                if($val['msg_id'] == $item['id']){
                    $collectionList['data'][$key]['msg_list'] = $item;
                    break;
                }
            }

        }

        return $this->success($collectionList);
    }



    public static function getImUser($ids=[]){
        if(empty($ids)){
            return [];
        }
        $url = ImClient::get_im_url("https://console.tim.qq.com/v4/profile/portrait_get");
        $post_data['To_Account'] = $ids;
        $post_data['TagList'] = ['Tag_Profile_IM_Nick','Tag_Profile_IM_Gender'];
        $res = ImClient::curlPost($url,json_encode($post_data));
        $res = json_decode($res,true);

        $return_data = [];
        foreach ($res['UserProfileItem'] as $userProfile_key=>$userProfileItem_item) {
            $return_data[$userProfileItem_item['To_Account']] = [];
            foreach ($userProfileItem_item['ProfileItem'] as $key=>$value) {
                $return_data[$userProfileItem_item['To_Account']][$value['Tag']] = $value['Value'];
            }
        }


        return $return_data;


    }

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
            $msg_add['os_msg_id']           = $params['From_Account'].'_'.$params['To_Account'].'_'.$params['MsgRandom'].'_'.$params['MsgSeq'];
        }
        //群聊消息
        if($params['CallbackCommand'] == 'Group.CallbackAfterSendMsg'){
            $msg_add['group_id']            = $params['GroupId'];
            $msg_add['online_only_flag']    = $params['OnlineOnlyFlag'];
            $msg_add['type']                = 1;
            $msg_add['msg_random']          = $params['Random'];
            $msg_add['os_msg_id']           = $params['GroupId'].'_'.$params['MsgSeq'];
        }

        $msg_add_res = ImMsg::create($msg_add);
        $img_res= true;
        $content_res = true;
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

                    $msg_content_add['uuid']            = $v['MsgContent']['UUID'];
                    $msg_content_add['image_format']    = $v['MsgContent']['ImageFormat'];
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
                                'created_at'=> date("Y-m-d h:i:s"),
                                'updated_at'=> date("Y-m-d h:i:s"),
                            ];
                            $img_adds[] = $img_add;

                        }

                        if(!empty($img_adds)){
                            $img_res = ImMsgContentImg::insert($img_adds);
                        }else{
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
            $content_res = ImMsgContent::insert($msg_content_add);
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
        if($msg_add_res && $img_res && $content_res){
            DB::commit();
            return true;
        }else{
            DB::rollBack();
        }

        return false;

    }

}