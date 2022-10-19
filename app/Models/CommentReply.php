<?php

namespace App\Models;


class CommentReply extends Base
{
    const DB_TABLE = 'nlsg_comment_reply';
    protected $table = 'nlsg_comment_reply';
    protected $fillable = ['comment_id','from_uid','to_uid','content','status'];

    public function from_user()
    {
        return  $this->belongsTo(User::class, 'from_uid', 'id');
    }

    public function to_user()
    {
        return  $this->belongsTo(User::class, 'to_uid', 'id');
    }
}
