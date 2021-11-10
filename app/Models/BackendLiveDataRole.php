<?php

namespace App\Models;
use Illuminate\Support\Facades\DB;

class BackendLiveDataRole extends Base
{
    protected $table = 'nlsg_backend_live_data_role';

    protected $fillable = [
        'user_id', 'live_id', 'created_at', 'updated_at'
    ];
}
