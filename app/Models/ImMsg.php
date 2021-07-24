<?php

namespace App\Models;



class ImMsg extends Base
{

    protected $table = 'nlsg_im_msg';

    protected $fillable = ['from_account', 'to_account', 'msg_seq','msg_random','msg_key',
        'msg_time','msg_time','send_msg_result','unread_msg_num','group_id','type','online_only_flag','os_msg_id','send_all_id'];





    public function  content(){
        return  $this->hasMany(ImMsgContent::class, 'msg_id');
    }

    public static function getMsgList($ids=[],$msg_keys=[]){
        if(empty($ids) && empty($msg_keys)){
            return [];
        }

        $query = ImMsg::with([
            'content:id,msg_id,msg_type as MsgType,text as Text,url as Url,video_url as VideoUrl,thumb_url as ThumbUrl,data as Data,file_name as FileName,file_size as FileSize
            ,uuid,image_format as ImageFormat,file_name as FileName,size as VideoSize,video_format as VideoFormat,thumb_url as ThumbUrl,thumb_size as ThumbSize,thumb_width as ThumbWidth,thumb_height as ThumbHeight,thumb_format as ThumbFormat,second as VideoSecond,video_uuid',
            'content.imginfo:content_id,type,size,width,height,url',
            ])->select('id','msg_seq','msg_time','from_account');
        if(!empty($ids) ){
            return $query->whereIn('id',$ids)->get()->toArray();
        }
        if(!empty($msg_keys) ){
            return $query->whereIn('msg_key',$msg_keys)->get()->toArray();
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
                    $msg_content_add['Index']           = $val['Index']??'';
                    $msg_content_add['Data']            = $val['Data'];
                    break;
                case 'TIMSoundElem' ://语音消息元素
                    $msg_content_add['Url']             = $val['Url'];
                    $msg_content_add['Size']            = $val['Size']??0;
                    $msg_content_add['Second']          = $val['Second']??0;
                    $msg_content_add['Download_Flag']   = 2;
                    break;
                case 'TIMImageElem' ://图片元素

                    foreach ($val['imginfo'] as $item) {
                        if( in_array($item['type'],[1,2,3])){
                            $msg_content_add['ImageInfoArray'][] = [
                                "Type" => $item['type'],
                                "Size" => (int)$item['size'],
                                "Width" => (int)$item['width'],
                                "Height" => (int)$item['height'],
                                "URL" => $item['url'],
                            ];
                        }
                    }
                    $msg_content_add['UUID']         = ($val['uuid']);
                    $msg_content_add['ImageFormat']         = $val['ImageFormat'];

                    break;
                case 'TIMFileElem' ://文件类型元素
                    $msg_content_add['Url']             = $val['Url'];
                    $msg_content_add['FileSize']        = $val['FileSize']??0;
                    $msg_content_add['FileName']        = $val['FileName'];
                    $msg_content_add['Download_Flag']   = 2;
                    break;

                case 'TIMVideoFileElem' : //视频类型元素
                    $msg_content_add['VideoUrl']            = $val['VideoUrl'];
                    $msg_content_add['VideoSize']           = $val['VideoSize']??0;
                    $msg_content_add['VideoSecond']         = $val['VideoSecond']??0;
                    $msg_content_add['VideoFormat']         = $val['VideoFormat']??0;
                    $msg_content_add['VideoDownloadFlag']   = 2;
                    $msg_content_add['VideoUUID']           = $params['video_uuid']??'';
                    $msg_content_add['ThumbUUID']           = $params['uuid']??'';
                    $msg_content_add['ThumbUrl']            = $params['ThumbUrl']??'';
                    $msg_content_add['ThumbSize']           = $params['ThumbSize']??0;
                    $msg_content_add['ThumbWidth']          = $params['ThumbWidth']??0;
                    $msg_content_add['ThumbHeight']         = $params['ThumbHeight']??0;
                    $msg_content_add['ThumbFormat']         = $params['ThumbFormat'] ?? 'mp4';
                    $msg_content_add['ThumbDownloadFlag']         = 2;
                    break;

                case 'TIMCustomElem' : //自定义消息
                    $msg_content_add['Desc']    = $val['Desc']??'';
                    $msg_content_add['Data']    = $val['Data'];
                    break;

                default :
                    $msg_content_add = [];
                    break;
            }
            //dd($msg_content_add);
            if($msg_content_add){
                $res[] = [ 'MsgType' => $msg_type,  'MsgContent' => $msg_content_add, ];
            }

        }





        return $res;
    }


}
