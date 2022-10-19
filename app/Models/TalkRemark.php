<?php


namespace App\Models;


class TalkRemark extends Base
{
    protected $table = 'nlsg_talk_remark';

    protected $fillable = [
        'talk_id','content','admin_id'
    ];
}
