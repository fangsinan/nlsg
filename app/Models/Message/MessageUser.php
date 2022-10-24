<?php


namespace App\Models\Message;


use App\Models\Base;
use App\Models\User;

class MessageUser extends Base
{
    const DB_TABLE = 'nlsg_message_user';

    protected $table = 'nlsg_message_user';

    protected $fillable = [
        'send_user', 'receive_user', 'message_id', 'status', 'is_del', 'type','is_send',
        'created_at', 'updated_at', 'read_at', 'del_at', 'group_id','plan_time'
    ];

    public function message()
    {
        return $this->belongsTo(Message::class, 'message_id', 'id');
    }

    public function send_user()
    {
        return $this->belongsTo(User::class, 'send_user', 'id');
    }

    public function receive_user()
    {
        return $this->belongsTo(User::class, 'receive_user', 'id');
    }
}
