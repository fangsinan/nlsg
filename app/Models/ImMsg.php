<?php

namespace App\Models;



class ImMsg extends Base
{

    protected $table = 'nlsg_im_msg';

    protected $fillable = ['from_account', 'to_account', 'msg_seq','msg_random',
        'msg_time','msg_time','send_msg_result','unread_msg_num','group_id','type','online_only_flag'];





    public function  content(){
        return  $this->hasMany(ImMsgContent::class, 'msg_id');
    }

    public static function getMsgList($ids=[],$msg_seqs=[]){
        if(empty($ids) && empty($msg_seqs)){
            return [];
        }


        $query = ImMsg::with([
            'content:id,msg_id,msg_type as MsgType,text as Text,url as Url,video_url as VideoUrl,thumb_url as ThumbUrl',
            ])->select('id','msg_seq','msg_time');
        if(!empty($ids) ){
            return $query->whereIn('id',$ids)->get()->toArray();
        }
        if(!empty($msg_seqs) ){
            return $query->whereIn('msg_seq',$msg_seqs)->get()->toArray();
        }
    }



    //返回body消息格式
    public static function MsgBody($msg_content){

        if(empty($msg_content)){
            return [];
        }

        $res = [];

        foreach ($msg_content as $key=>$val) {
            $msg_type = $val['MsgType'];
            $params = $val;

            $msg_content_add = [];
            switch ($msg_type){
                case 'TIMTextElem' :  //文本消息元素
                    $msg_content_add['Text']            = $val['Text'];
                    break;
                case 'TIMFaceElem' : //表情消息元素
                    $msg_content_add['Index']           = $val['Index'];
                    $msg_content_add['Data']            = $val['Data'];
                    break;
                case 'TIMSoundElem' ://语音消息元素
                    $msg_content_add['Url']             = $val['Url'];
                    $msg_content_add['Size']            = $val['Size'];
                    $msg_content_add['Second']          = $val['Second'];
                    $msg_content_add['Download_Flag']   = 2;
                    break;
                case 'TIMImageElem' ://图片元素
//                    $msg_content_add['Type']        = $val['Type'];
//                    $msg_content_add['Size']        = $val['Size'];
//                    $msg_content_add['Width']       = $val['Width'];
//                    $msg_content_add['Height']      = $val['Height'];
                    $msg_content_add['URL']         = $val['Url'];
//                    $msg_content_add['UUID']        = $val['UUID'];
//                    $msg_content_add['ImageFormat'] = $val['ImageFormat'];

                    break;
                case 'TIMFileElem' ://文件类型元素
                    $msg_content_add['Url']             = $val['Url'];
                    $msg_content_add['FileSize']        = $val['FileSize'];
                    $msg_content_add['FileName']        = $val['FileName'];
                    $msg_content_add['Download_Flag']   = $val['Download_Flag'];
                    break;

                case 'TIMVideoFileElem' : //视频类型元素
                    $msg_content_add['VideoUrl']            = $val['VideoUrl'];
//                    $msg_content_add['VideoSize']           = $val['VideoSize'];
//                    $msg_content_add['VideoSecond']         = $val['VideoSecond'];
//                    $msg_content_add['VideoFormat']         = $val['VideoFormat'];
//                    $msg_content_add['VideoDownloadFlag']   = $val['VideoDownloadFlag'];
                    $msg_content_add['ThumbUrl']            = $params['ThumbUrl'];
    //                $msg_content_add['ThumbSize']           = $params['ThumbSize'];
    //                $msg_content_add['ThumbWidth']          = $params['ThumbWidth'];
    //                $msg_content_add['ThumbHeight']         = $params['ThumbHeight'];
    //                $msg_content_add['ThumbFormat']         = $params['ThumbFormat'];
                    break;

                case 'TIMCustomElem' : //自定义消息
                    $msg_content_add['Desc']    = $val['Desc'];
                    $msg_content_add['Data']    = $val['Data'];
                    break;

                default :
                    $msg_content_add = [];
                    break;
            }
            if($msg_content_add){
                $res[] = [ 'MsgType' => $msg_type,  'MsgContent' => $msg_content_add, ];
            }

        }





        return $res;
    }


}
