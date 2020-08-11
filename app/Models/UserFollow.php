<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserFollow extends Model
{
    protected $table = 'nlsg_user_follow';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'from_uid', 'to_uid'
    ];

    public  function toUser()
    {
        return $this->belongsTo('App\Models\User','from_uid','id');
    }

    public  function fromUser()
    {
        return $this->belongsTo('App\Models\User','to_uid','id');
    }

}
