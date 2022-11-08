<?php


namespace App\Models;


class TalkTemplate extends Base
{
    protected $table = 'nlsg_talk_template';

    protected $fillable = [
        'category_id',
        'content',
        'admin_id',
        'is_public',
        'status',
    ];

}
