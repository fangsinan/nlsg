<?php


namespace App\Models\Message;


use App\Models\Base;

class Message extends Base
{

    const DB_TABLE = 'nlsg_message';

    protected $table = 'nlsg_message';

    protected $fillable = [
        'title', 'message', 'type', 'receive_type', 'relation_id', 'relation_type',
        'created_at', 'updated_at', 'plan_time', 'status',
    ];

}
