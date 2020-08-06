<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAttribution extends Model
{
    protected $table = 'nlsg_user_attribution';


    protected  $fillable = ['user_id','level','referrer_user_id','referrer_user_level'];

}
