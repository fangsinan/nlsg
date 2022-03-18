<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Poster extends Model
{
    protected $table = 'nlsg_poster';

    // 允许批量赋值
    protected  $fillable = ['relation_id', 'image', 'status', 'type'];

}
