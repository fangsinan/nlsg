<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ColumnOutline extends Model
{
    protected $table = 'nlsg_column_outline';
    public $timestamps = false;

    // 允许批量赋值
    protected  $fillable = [''];


    public function getDateFormat()
    {
        return time();
    }

}