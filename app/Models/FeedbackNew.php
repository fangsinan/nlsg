<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Relations\HasOne;

class FeedbackNew extends Base
{
    const DB_TABLE = 'nlsg_feedback_new';
    protected $table = 'nlsg_feedback_new';

    public function UserInfo(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function FeedbackType(): HasOne
    {
        return $this->hasOne(FeedbackType::class, 'id', 'type');
    }

    protected $fillable = [
        'type',
        'user_id',
        'os_type',
        'content',
        'picture',
        'created_at',
        'updated_at',
        'status',
        'reply_admin_id',
        'reply_template_id',
        'reply_content',
        'reply_at',
        'del_at',
        'del_by',
        'target',
    ];


}
