<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Relations\HasOne;

class TalkTemplate extends Base
{
    protected $table = 'nlsg_talk_template';

    protected $fillable = [
        'category_id',
        'content',
        'admin_id',
        'status',
    ];

    public function categoryInfo(): HasOne
    {
        return $this->hasOne(TalkTemplateCategory::class,'id','category_id');
    }

}
