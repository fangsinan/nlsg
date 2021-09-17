<?php

namespace App\Models;
class ShieldKey extends Base
{
    protected $table = 'nlsg_shield_key';

    protected $fillable = ['name', 'status'];
}
