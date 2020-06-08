<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Works extends Model
{
    protected $table = 'nlsg_works';
    public $timestamps = false;

    // 允许批量赋值
    protected  $fillable = [''];

    //状态 1上架  2 下架
    const STATUS_ONE = 1;
    const STATUS_TWO = 2;

    public function getDateFormat()
    {
        return time();
    }

    /**
     * 首页课程推荐
     * @param $ids 相关作品id
     * @return bool
     */
    public function getIndexWorks($ids)
    {
        if (!$ids){
            return false;
        }

        $lists= Works::select('id','user_id','title','cover_img','subtitle','price')
            ->with('user')
            ->whereIn('id',$ids)
            ->orderBy('created_at','desc')
            ->get()
            ->toArray();
        return $lists;

    }

    public  function user()
    {
        return $this->belongsTo('App\Models\User','user_id','id')->select('id', 'username');
    }

}
