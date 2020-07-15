<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    protected $table = 'nlsg_like';

    // 允许批量赋值
    protected  $fillable = ['relation_id','user_id','type'];

    public function  user(){
        return  $this->belongsTo(User::class, 'user_id', 'id');
    }
}
