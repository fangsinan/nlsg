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

}
