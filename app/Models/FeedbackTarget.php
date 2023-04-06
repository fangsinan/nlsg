<?php


namespace App\Models;

class FeedbackTarget extends Base
{
    const DB_TABLE = 'nlsg_feedback_target';
    protected $table = 'nlsg_feedback_target';

    protected $fillable = [
        'type',
        'live_id',
        'target_id',
        'comment_id',
    ];


}
