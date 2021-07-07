<?php

namespace App\Models;



class ImCollection extends Base
{

    protected $table = 'nlsg_im_collection';

    protected $fillable = ['user_id', 'msg_seq', 'type',];
}
