<?php

namespace App\Models;

class LiveStreaming extends Base
{
    protected $table = 'nlsg_live_streaming';

    //允许批量赋值
    protected $fillable = ['title','video_id','video_url'];

}
