<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserInvite extends Model
{
    protected $table = 'nlsg_user_invite';
    protected $fillable = [
       'from_uid', 'to_uid','type'
   ];
}
