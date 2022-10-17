<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Relations\HasOne;

class FeedbackNew extends Base
{
    const DB_TABLE = 'nlsg_feedback_new';
    protected $table = 'nlsg_feedback_new';

    public function UserInfo(): HasOne
    {
        return $this->hasOne(User::class,'id','user_id');
    }

    public function FeedbackType(): HasOne
    {
        return $this->hasOne(FeedbackType::class,'id','type');
    }


}
