<?php


namespace App\Models\Message;


use App\Models\Base;

class MessageType extends Base
{
    const DB_TABLE = 'nlsg_message_type';

    protected $table = 'nlsg_message_type';

    protected $fillable = [
        'title', 'created_at', 'updated_at',
    ];

}
