<?php


namespace App\Models;


class TalkTemplateCategory extends Base
{
    protected $table = 'nlsg_talk_template_category';

    protected $fillable = [
        'title',
        'admin_id',
        'is_public',
        'status',
    ];

}
