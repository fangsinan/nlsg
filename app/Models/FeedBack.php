<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeedBack extends Model
{
    protected $table    = 'nlsg_feedback';
    protected $fillable = ['type','user_id','content','pic_url'];
}
