<?php


namespace App\Models\Message;


use App\Models\Base;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MessageView extends Base
{
    const DB_TABLE = 'nlsg_message_view';

    protected $table = 'nlsg_message_view';

    protected $fillable = [
        'title', 'message', 'type', 'created_at', 'updated_at', 'status','create_admin_id',
    ];

    public function typeInfo(): HasOne
    {
        return $this->hasOne(MessageType::class,'id','type');
    }
}
