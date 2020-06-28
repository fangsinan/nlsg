<?php


namespace App\Models;



use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class Works extends Base
{
    protected $table = 'nlsg_works';
    public $timestamps = false;


    public function CategoryRelation()
    {
        //一对多
        return $this->hasMany('App\Models\WorksCategoryRelation','work_id', 'id');
    }


    public function UserName()
    {
        //一对多
        return $this->belongsTo('App\Models\User','user_id');
    }


    public function WorkInfo()
    {
        //一对多
        return $this->hasMany('App\Models\WorksInfo','pid');
    }

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
    public function getIndexWorks($ids,$is_audio_book=0)
    {
        if (!$ids){
            return false;
        }

        $lists= Works::select('id','user_id','title','cover_img','subtitle','price')
            ->with(['user'=>function($query){
                $query->select('id','phone','nickname');
            }])
            ->whereIn('id',$ids)
            ->where('is_audio_book',$is_audio_book)
            ->orderBy('created_at','desc')
            ->get()
            ->toArray();
        return $lists;

    }

    public  function user()
    {
        return $this->belongsTo('App\Models\User');
    }


    public function lists()
    {
        return $this->belongsToMany('App\Models\Lists',
            'nlsg_lists_work','works_id', 'lists_id');
    }


    static function search($keywords,$is_audio_book){
        $worksObj = new Works();
        $infoObj = new WorksInfo();
        $res = DB::table($worksObj->getTable(), 'works')
            ->leftJoin($infoObj->getTable() .' as info', 'works.id', '=', 'info.pid')
            ->select('works.id','works.type','works.title','works.user_id')
            ->where('works.status',4)
            ->where('works.is_audio_book',$is_audio_book)
            ->where('info.status',4)
            ->where(function ($query)use ($keywords) {
                $query->orwhere('works.title', 'like', "%{$keywords}%");
                $query->orwhere('info.title', 'like', "%{$keywords}%");
            })->groupBy('works.id')->get();

        return ['res' => $res, 'count'=> $res->count() ];

    }

}
