<?php

namespace App\Models;


class ShortLink extends Base
{
    protected $table = 'a_short_link';

    protected $fillable = [
        'name',
        'url',
        'status',
        'admin_id',
        'code',
        'created_at',
        'updated_at',
    ];

    public function backendUser()
    {
        return $this->belongsTo(BackendUser::class, 'admin_id', 'id');
    }

}
