<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Column extends Model
{
    protected $table = 'nlsg_column';
    public $timestamps = false;

    // 允许批量赋值
    protected  $fillable = ['name','user_id'];


    public function getDateFormat()
    {
        return time();
    }


    public function user()
    {
        return $this->hasOne('App\Models\User', 'user_id','id');
        //->select(['field']);
    }

    public function get($field){
        $email = DB::table('nlsg_column')
            ->where('status', 1)
            ->orderBy('sort', 'desc')
            ->get($field)
            ->map(function ($value) {
                return (array)$value;
            })->toArray();
        return $email;
    }
}