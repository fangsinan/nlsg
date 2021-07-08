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
     * @api {get} /api/v4/im/msg_send_all  消息群发
     * @apiName msg_send_all
     * @apiVersion 1.0.0
     * @apiGroup im
     *
     * @apiParam {int} From_Account  发送方帐号
     * @apiParam {array} To_Account  接收方用户 数组类型
     * @apiParam {array} To_Group   接收方群组 数组类型
     * @apiParam {array} Msg_Content 消息体 数组类型  根据MsgType  对应im的字段类型
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
        $to_accounts   = $params['To_Account']??'';  //消息接收方用户
        $to_group   = $params['To_Group']??'';  //消息接收方用户
        $msg_content   = $params['Msg_Content'];  //消息体


        if(empty($from_account) || empty($msg_content)){
            return $this->error('0','request error');
        }
        if (empty($to_accounts) && empty($to_group)){
            return $this->error('0','request error');
        }
//        $to_accounts = explode(',',$to_accounts);


        $msgBody = ImMsg::MsgBody($msg_content);

        if(empty($msgBody)){
            return $this->error('0','Msg Body Error');
        }

        $url = "https://console.tim.qq.com/v4/openim/batchsendmsg?";
        $url.=http_build_query([
            'sdkappid' => config('env.OPENIM_APPID'),
            'identifier' => config('web.Im_config.admin'),
            'usersig' => ImClient::getUserSig(config('web.Im_config.admin')),
            'random' => rand(0,4294967295),
            'contenttype'=>'json',
        ]);



        $post_data['From_Account'] = $from_account;
        $post_data['To_Account'] = $to_accounts;
        $post_data['MsgRandom'] = rand(10000000,99999999);
        $post_data['MsgBody'] = $msgBody;

        $res = ImClient::curlPost($url,json_encode($post_data));

        return $this->success($res);
    }



    /**
     * @api {get} /api/v4/im/msg_collection  消息收藏操作
     * @apiName msg_collection
     * @apiVersion 1.0.0
     * @apiGroup im
     *
     * @apiParam {array} msg_seq  消息序列号 array
     * @apiParam {int} type  收藏类型   1消息收藏
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
        $msg_seq = $request->input('msg_seq', 0);  //消息序列号
        $type = $request->input('type') ?? 1;  //类型

        if(!is_array($msg_seq)){
            return $this->error('0','msg_seq error');
        }
        $msg = ImMsg::whereIn('msg_seq',$msg_seq)->get()->toArray();
        if(empty($msg)){
            return $this->error('0','msg_seq error');
        }
        $uid = $this->user['id']; //uid

        foreach ($msg_seq as $k=>$v){
            $data = [
                'user_id' => $uid,
                'msg_seq' => $v['msg_seq'],
                'type' => $type,
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
        $request->input('user_id', 0);  //消息序列号
        $uid = $this->user['id'];

        $collectionList = ImCollection::select("id","user_id","msg_seq")->where([
            'type'=>1,'user_id'=>$uid
        ])->orderBy('created_at',"desc")->paginate($this->page_per_page)->toArray();

        $msg_seqs = array_column($collectionList['data'],'msg_seq');
        $msg_list = ImMsg::getMsgList([],$msg_seqs);

        foreach ($collectionList['data'] as $key=>$val) {
            $collectionList['data'][$key]['msg_list'] = [];
            foreach ($msg_list as $item){
                if($val['msg_seq'] == $item['msg_seq']){
                    $collectionList['data'][$key]['msg_list'] = $item;
                    break;
                }
            }
        }

        return $this->success($collectionList);
    }

    public static function callbackMsg($params=[]){

        if (empty($params)){
            return 0;
        }

        DB::beginTransaction();
        //入库操作
        //消息主库
        $msg_add = [
            'from_account'      => $params['From_Account'],
            'to_account'        => $params['To_Account'],
            'msg_seq'           => $params['MsgSeq'],
            'msg_random'        => $params['MsgRandom'],
            'msg_time'          => $params['MsgTime'],
            'msg_key'           => $params['MsgKey'],
            'send_msg_result'   => $params['SendMsgResult'],
            'unread_msg_num'    => $params['UnreadMsgNum'],
        ];
        $msg_add_id = ImMsg::insert($msg_add);

        $img_res= true;
        //消息体
        foreach ($params['MsgBody'] as $k=>$v){
            $msg_content_add = [
                'msg_id'        => $msg_add_id,
                'msg_type'      => $v['MsgType'],
                ];

            switch ($v['MsgType']){
                case 'TIMTextElem' :  //文本消息元素
                    $msg_content_add['text']            = $v['MsgContent']->Text;
                    break;
                case 'TIMFaceElem' : //表情消息元素
                    $msg_content_add['index']           = $v['MsgContent']->Index;
                    $msg_content_add['data']            = $v['MsgContent']->Data;
                    break;
                case 'TIMSoundElem' ://语音消息元素
                    $msg_content_add['url']             = $v['MsgContent']->Url;
                    $msg_content_add['size']            = $v['MsgContent']->Size;
                    $msg_content_add['second']          = $v['MsgContent']->Second;
                    $msg_content_add['download_flag']   = $v['MsgContent']->Download_Flag;
                    break;
                case 'TIMImageElem' ://图片元素
                    $msg_content_add['uuid']            = $v['MsgContent']->UUID;
                    $msg_content_add['image_format']    = $v['MsgContent']->ImageFormat;
                    //保留缩略图
                    foreach ($v['MsgContent']->ImageFormat as $img_k=>$img_v){
                        if($img_v['Type'] == 3){
                            $msg_content_add['url'] = $img_v['URL'];
                        }
                        //入库图片表
                        $img_add = [
                            'type'      => $img_v['Type'],
                            'size'      => $img_v['Size'],
                            'width'     => $img_v['Width'],
                            'height'    => $img_v['Height'],
                            'url'       => $img_v['URL'],
                        ];
                        $img_adds[] = $img_add;

                    }
                    if(!empty($img_adds)){
                        $img_res = ImMsgContentImg::insert($img_adds);
                    }else{
                        $img_res = false;
                    }

                    break;
                case 'TIMFileElem' ://文件类型元素
                    $msg_content_add['url']            = $v['MsgContent']->Url;
                    $msg_content_add['file_size']      = $v['MsgContent']->FileSize;
                    $msg_content_add['file_name']      = $v['MsgContent']->FileName;
                    $msg_content_add['download_flag']  = $v['MsgContent']->Download_Flag;
                    break;



                case 'TIMVideoFileElem' : //视频类型元素
                    $msg_content_add['video_url']           = $v['MsgContent']->VideoUrl;
                    $msg_content_add['size']                = $v['MsgContent']->VideoSize;
                    $msg_content_add['second']              = $v['MsgContent']->VideoSecond;
                    $msg_content_add['video_format']        = $v['MsgContent']->VideoFormat;
                    $msg_content_add['video_download_flag'] = $v['MsgContent']->VideoDownloadFlag;
                    $msg_content_add['video_thumb_url']     = $v['MsgContent']->ThumbUrl;
                    $msg_content_add['thumb_size']          = $v['MsgContent']->ThumbUrl;
                    $msg_content_add['thumb_width']         = $v['MsgContent']->ThumbWidth;
                    $msg_content_add['thumb_height']        = $v['MsgContent']->ThumbHeight;
                    $msg_content_add['thumb_format']        = $v['MsgContent']->ThumbFormat;
                    break;
            }

            $msg_content_adds[] = $msg_content_add;
        }
        if(!empty($msg_content_adds)){
            $content_res = ImMsgContent::insert($msg_content_adds);
        }else{
            $content_res=false;
        }

        if($msg_add_id && $img_res && $content_res){
            DB::commit();
        }else{
            DB::rollBack();
        }





        return 1;

    }

}