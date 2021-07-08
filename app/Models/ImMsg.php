<?php

namespace App\Models;



class ImMsg extends Base
{

    protected $table = 'nlsg_im_msg';

    public function  content(){
        return  $this->hasMany(ImMsgContent::class, 'msg_id');
    }

    public static function getMsgList($ids=[],$msg_seqs=[]){
        if(empty($ids) && empty($msg_seqs)){
            return [];
        }


        $query = ImMsg::with(['content:id,msg_id,msg_type,text,url,video_url,thumb_url',])->select('id','msg_seq','msg_time');
        if(!empty($ids) ){
            return $query->whereIn('id',$ids)->get();
        }
        if(!empty($msg_seqs) ){
            return $query->whereIn('msg_seq',$msg_seqs)->get();
        }
    }



    //返回body消息格式
    public static function MsgBody($msg_type, $params){

        $msg_content_add = [];
        switch ($msg_type){
            case 'TIMTextElem' :  //文本消息元素
                $msg_content_add['text']            = $params['Text'];
                break;
            case 'TIMFaceElem' : //表情消息元素
                $msg_content_add['index']           = $params['Index'];
                $msg_content_add['data']            = $params['Data'];
                break;
            case 'TIMSoundElem' ://语音消息元素
                $msg_content_add['Url']             = $params['Url'];
                $msg_content_add['Size']            = $params['Size'];
                $msg_content_add['Second']          = $params['Second'];
                $msg_content_add['Download_Flag']   = 2;
                break;
            case 'TIMImageElem' ://图片元素
                $msg_content_add['Type']        = $params['Type'];
                $msg_content_add['Size']        = $params['Size'];
                $msg_content_add['Width']       = $params['Width'];
                $msg_content_add['Height']      = $params['Height'];
                $msg_content_add['URL']         = $params['URL'];
                $msg_content_add['UUID']        = $params['UUID'];
                $msg_content_add['ImageFormat'] = $params['ImageFormat'];

                break;
            case 'TIMFileElem' ://文件类型元素
                $msg_content_add['Url']             = $params['Url'];
                $msg_content_add['FileSize']        = $params['FileSize'];
                $msg_content_add['FileName']        = $params['FileName'];
                $msg_content_add['Download_Flag']   = $params['Download_Flag'];
                break;

            case 'TIMVideoFileElem' : //视频类型元素
                $msg_content_add['VideoUrl']            = $params['VideoUrl'];
                $msg_content_add['VideoSize']           = $params['VideoSize'];
                $msg_content_add['VideoSecond']         = $params['VideoSecond'];
                $msg_content_add['VideoFormat']         = $params['VideoFormat'];
                $msg_content_add['VideoDownloadFlag']   = $params['VideoDownloadFlag'];
//                $msg_content_add['ThumbUrl']            = $params['ThumbUrl'];
//                $msg_content_add['ThumbSize']           = $params['ThumbSize'];
//                $msg_content_add['ThumbWidth']          = $params['ThumbWidth'];
//                $msg_content_add['ThumbHeight']         = $params['ThumbHeight'];
//                $msg_content_add['ThumbFormat']         = $params['ThumbFormat'];
                break;

            default :
                return [];
                break;
        }


        return [ 'MsgType' => $msg_type,  'MsgContent' => $msg_content_add, ];
    }


}
