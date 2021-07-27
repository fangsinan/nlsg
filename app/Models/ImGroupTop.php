<?php


namespace App\Models;


class ImGroupTop extends Base
{
    protected $table = 'nlsg_im_group_top';
    protected $fillable = [
        'id','user_id','group_id','created_at','updated_at'
    ];
}
