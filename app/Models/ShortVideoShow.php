<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;

class ShortVideoShow extends Base
{

    protected $table = 'nlsg_short_video_show';

    protected $fillable = [
        'relation_id', 'user_id', 'is_finish','os_type',
    ];


}
