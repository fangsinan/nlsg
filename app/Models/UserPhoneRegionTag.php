<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPhoneRegionTag extends Model
{
    protected $table    = 'nlsg_user_phone_region_tag';
    protected $fillable = [
        'area_code',
        'prov',
        'city',
        'tag_id',
    ];
}
