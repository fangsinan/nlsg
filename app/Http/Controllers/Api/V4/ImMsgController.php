<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use App\Models\ImMsgContentImgModel;
use App\Models\ImMsgContentModel;
use App\Models\ImMsgModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


/**
 * Description of ExpressController
 *
 * @author wangxh
 */
class ImMsgController extends Controller
{

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
        $msg_add_id = ImMsgModel::insert($msg_add);

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
                        $img_res = ImMsgContentImgModel::insert($img_adds);
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
            $content_res = ImMsgContentModel::insert($msg_content_adds);
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