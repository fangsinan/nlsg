<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPhoneRegion extends Model
{
    protected $table = 'nlsg_user_phone_region';
    protected $fillable = [
        'user_id', 'phone', 'prov', 'city', 'area_code', 'post_code', 'type'
    ];
}
