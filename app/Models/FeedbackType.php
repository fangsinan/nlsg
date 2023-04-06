<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Relations\HasMany;

class FeedbackType extends Base
{
    const DB_TABLE = 'nlsg_feedback_type';
    protected $table = 'nlsg_feedback_type';

    public function feedbackList(): HasMany
    {
        return $this->hasMany(FeedbackNew::class,'type','id');
    }

    //获取意见反馈的类型
    public static function getFeedbackType($type){
        return  FeedbackType::where(['type'=>$type])->get();
    }
}
