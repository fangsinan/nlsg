<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class LivePoster extends Model
{
    protected $table = 'nlsg_live_poster';

    // 允许批量赋值
    protected  $fillable = ['live_id','image'];

    public $timestamps = false;
}
