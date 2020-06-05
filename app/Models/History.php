<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class History extends Model
{
    protected $table = 'nlsg_history';

    // 允许批量赋值
    protected  $fillable = ['column_id','works_id','worksinfo_id','user_id','is_del'];

}