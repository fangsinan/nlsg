<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Relations\HasMany;

class TalkTemplateCategory extends Base
{
    protected $table = 'nlsg_talk_template_category';

    protected $fillable = [
        'title',
        'admin_id',
        'is_public',
        'status',
    ];

    public function ListInfo(): HasMany
    {
        return $this->hasMany(TalkTemplate::class,'category_id','id');
    }

}
