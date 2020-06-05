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

    //状态 1上架  2 下架
    const STATUS_ONE = 1;
    const STATUS_TWO = 2;

    public function getDateFormat()
    {
        return time();
    }


    public function user()
    {
        return $this->hasOne('App\Models\User', 'user_id','id');
        //->select(['field']);
    }

    /**
     * 首页专栏推荐
     * @param $ids
     * @return bool
     */
    public function getIndexColumn($ids)
    {
        if (!$ids){
            return false;
        }
        $lists= $this->select('id','name', 'title','subtitle', 'message','price', 'cover_pic')
            ->whereIn('id', $ids)
            ->where('status',self::STATUS_ONE)
            ->orderBy('created_at', 'desc')
            ->take(2)
            ->get()
            ->toArray();
        return $lists;
    }
}
