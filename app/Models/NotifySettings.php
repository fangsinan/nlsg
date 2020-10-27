<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotifySettings extends Model
{
    protected $table = 'nlsg_notify_settings';

    protected $fillable = [
        'user_id','is_comment','is_reply','is_like','is_fans','is_income','is_update'
    ];
}
