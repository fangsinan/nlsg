<?php

namespace App\Models;



class ImMsgContent extends Base
{

    protected $table = 'nlsg_im_msg_content';

    protected $fillable = ['msg_id', 'msg_type', 'text','index',
'data','url','size','second','download_flag','uuid','image_format','file_size','file_name',
'video_url','video_format','thumb_url','thumb_size','thumb_width','thumb_height','thumb_format',
'desc','ext','sound'
        ];


    public function  imginfo(){
        return  $this->hasMany(ImMsgContentImg::class, 'uuid','uuid');
    }

}


