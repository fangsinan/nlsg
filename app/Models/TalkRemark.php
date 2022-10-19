<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Relations\HasOne;

class TalkRemark extends Base
{
    protected $table = 'nlsg_talk_remark';

    protected $fillable = [
        'talk_id', 'content', 'admin_id'
    ];

    public function adminInfo(): HasOne
    {
        return $this->hasOne(BackendUser::class, 'id', 'admin_id');
    }
}
