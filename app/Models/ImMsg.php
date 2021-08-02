<?php

namespace App\Models;



class ImMsg extends Base
{

    protected $table = 'nlsg_im_msg';

    protected $fillable = ['from_account', 'to_account', 'msg_seq','msg_random','msg_key',
        'msg_time','msg_time','send_msg_result','unread_msg_num','group_id','type','online_only_flag','os_msg_id','send_all_id','platform'];





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



    public static function setMsgContent($params_msg){
        if(empty($params_msg)){
            return [];
        }


        $res = [];
        foreach ($params_msg as $key=>$val) {
            $res[$key]['MsgType'] = $val['MsgType'];

            $media = [];
            if(!empty($val['videoId'])){

                // 由于视频音频转码 延迟  需设置一个延时超时操作
                for ($i=0;$i<4;$i++){
//                    $media_data = ImMedia::where(['media_id'=>$val['videoId'],'is_finish'=>1])->first();
                    $media_data = ImMedia::where(['media_id'=>$val['videoId'],])->first();
                    if(!empty($media_data)){
                        continue;
                    }
                    sleep(2);
                }

                if(empty($media_data)) {
                    return [];
                }
                //查询媒体表详情
                $media = [
                    'size'          => $media_data['size']??0, //大小
                    'second'        => $media_data['second'], //时长
                    'uuid'          => md5($val['Url']), //uuid
                    'format'        => 255, //格式
                    'width'         => $media_data['width'],
                    'height'        => $media_data['height'],

                    //封面图
                    'thumb_uuid'    => md5($media_data['thumb_url']),
                    'thumb_url'     => $media_data['thumb_url'],
                    'thumb_size'    => $media_data['thumb_size'],
                    'thumb_width'   => $media_data['thumb_width'],
                    'thumb_height'  => $media_data['thumb_height'],
                    'thumb_format'  => 255,
                ];
            }



            switch ($val['MsgType']){
                case 'TIMTextElem' :  //文本消息元素
                case 'TIMFaceElem' : //表情消息元素
                    $res[$key] = $val;
                    break;
                case 'TIMSoundElem' ://语音消息元素
                    $res[$key]['Size']  = $media['size'];
                    $res[$key]['Second']  = $media['second'];
                    $res[$key]['Url']  = $val['Url'];
                    $res[$key]['UUID']  = md5($val['Url']);
                    break;
                case 'TIMImageElem' ://图片元素

                    $res[$key]['UUID']          = md5($val['Url']);
                    $res[$key]['ImageFormat']   = $media['format'];
                    $res[$key]['imginfo'] = [
                        [
                            'type' => 1,
                            'size' => $media['size'],
                            'width' => $media['width'],
                            'height' => $media['height'],
                            'url' => $val['Url'],
                        ],[
                            'type' => 2,
                            'size' => $media['size'],
                            'width' => $media['width'],
                            'height' => $media['height'],
                            'url' => $val['Url'],
                        ],[
                            'type' => 3,
                            'size' => $media['size'],
                            'width' => $media['width'],
                            'height' => $media['height'],
                            'url' => $val['Url'],
                        ],
                    ];



                    break;
                case 'TIMFileElem' ://文件类型元素
                    $res[$key]['FileSize']  = $media['size'];
                    $res[$key]['FileName']  = $val['FileName'];
                    $res[$key]['Url']       = $val['Url'];
                    $res[$key]['UUID']       = md5($val['Url']);
                    break;

                case 'TIMVideoFileElem' : //视频类型元素
                    $res[$key]['VideoUrl']      = $val['Url'];
                    $res[$key]['VideoSize']     = $media['size'];
                    $res[$key]['VideoSecond']   = $media['second'];
                    $res[$key]['VideoFormat']   = $media['format'];
                    $res[$key]['video_uuid']    = $media['uuid']; //视频uuid

                    $res[$key]['UUID']          = $media['thumb_uuid'];//封面图uuid
                    $res[$key]['ThumbUrl']      = $media['thumb_url'];
                    $res[$key]['ThumbSize']     = $media['thumb_size'];
                    $res[$key]['ThumbWidth']    = $media['thumb_width'];
                    $res[$key]['ThumbHeight']   = $media['thumb_height'];
                    $res[$key]['ThumbFormat']   = $media['thumb_format'];

                    break;

                case 'TIMCustomElem' : //自定义消息
                    $res[$key]['Desc']    = $val['Desc']??'msg';
                    $res[$key]['Data']    = $val['Data'];
                    break;

                default :
                    break;
            }

        }




        return $res;

    }



    //返回body消息格式
    public static function MsgBody($msg_content){

        if(empty($msg_content)){
            return [];
        }
        $res = [];
        foreach ($msg_content as $key=>$val) {
            $msg_type = $val['MsgType'];

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
                    $msg_content_add['UUID']            = $val['UUID']??'';
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
                    $msg_content_add['UUID']         = ($val['UUID']);
                    $msg_content_add['ImageFormat']         = $val['ImageFormat'];

                    break;
                case 'TIMFileElem' ://文件类型元素
                    $msg_content_add['Url']             = $val['Url'];
                    $msg_content_add['FileSize']        = $val['FileSize']??0;
                    $msg_content_add['FileName']        = $val['FileName'];
                    $msg_content_add['Download_Flag']   = 2;
                    $msg_content_add['UUID']            = ($val['UUID']);
                    break;

                case 'TIMVideoFileElem' : //视频类型元素
                    $msg_content_add['VideoUrl']            = $val['VideoUrl'];
                    $msg_content_add['VideoSize']           = $val['VideoSize']??0;
                    $msg_content_add['VideoSecond']         = $val['VideoSecond']??0;
                    $msg_content_add['VideoFormat']         = $val['VideoFormat']??0;
                    $msg_content_add['VideoDownloadFlag']   = 2;
                    $msg_content_add['VideoUUID']           = $val['video_uuid']??'';
                    $msg_content_add['ThumbUUID']           = $val['UUID']??'';
                    $msg_content_add['ThumbUrl']            = $val['ThumbUrl']??'';
                    $msg_content_add['ThumbSize']           = $val['ThumbSize']??0;
                    $msg_content_add['ThumbWidth']          = $val['ThumbWidth']??0;
                    $msg_content_add['ThumbHeight']         = $val['ThumbHeight']??0;
                    $msg_content_add['ThumbFormat']         = $val['ThumbFormat'] ?? 'mp4';
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
