<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasOne;

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

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'target_id');
    }

    public function liveComment(): HasOne
    {
        return $this->hasOne(LiveComment::class, 'id', 'comment_id');
    }

    public function live(): HasOne
    {
        return $this->hasOne(Live::class, 'id', 'live_id');
    }

}
