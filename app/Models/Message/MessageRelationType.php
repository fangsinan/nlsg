<?php


namespace App\Models\Message;


use App\Models\Base;

class MessageRelationType extends Base
{
    const DB_TABLE = 'nlsg_message_relation_type';

    protected $table = 'nlsg_message_relation_type';

    protected $fillable = [
        'title',
    ];
}
