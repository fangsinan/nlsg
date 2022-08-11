<?php

namespace App\Models;


class ShortLink extends Base
{
    protected $table = 'a_short_link';

    public function backendUser()
    {
        return $this->belongsTo(BackendUser::class, 'admin_id', 'id');
    }

}
