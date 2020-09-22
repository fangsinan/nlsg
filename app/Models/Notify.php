<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notify extends Base
{
    protected $table = 'nlsg_notify';

    public function toUser()
    {
        return $this->belongsTo(User::class, 'to_uid', 'id');
    }

    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_uid', 'id');
    }

    public  function  comment()
    {
        return $this->belongsTo(Comment::class, 'source_id', 'id');
    }
}
