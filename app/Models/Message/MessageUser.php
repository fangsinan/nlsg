<?php


namespace App\Models\Message;


use App\Models\Base;

class MessageUser extends Base
{
    const DB_TABLE = 'nlsg_message_user';

    protected $table = 'nlsg_message_user';

    protected $fillable = [
        'send_user', 'receive_user', 'message_id', 'status', 'is_del',
        'created_at', 'updated_at', 'read_at', 'del_at',
    ];

}
