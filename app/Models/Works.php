<?php


namespace App\Models;



use EasyWeChat\Work\ExternalContact\Client;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class Works extends Base
{
    protected $table = 'nlsg_works';
    public $timestamps = false;

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
                $query->select('id','nickname');
            }])
            ->whereIn('id',$ids)
            ->where('is_audio_book',$is_audio_book)
            ->orderBy('created_at','desc')
            ->get()
            ->toArray();
        foreach ($lists as &$v) {
            $v['is_new'] = 1;
            $v['is_free']= 1;
        }
        return $lists;

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

    /**
     * 首页推荐的课程
     * @param $id
     * @return bool
     */
    public function  getRecommendWorks($id)
    {
        if (!$id){
            return false;
        }

        $list = Works::with(['workInfo'=> function ($query){
                    $query->select('id', 'pid','rank', 'title','duration', 'view_num','online_time')
                          ->orderBy('rank', 'desc')
                          ->orderBy('id','desc')
                          ->limit(2);
                    },
                    'user:id,nickname,headimg'
                ])
                ->select('id','user_id','title', 'subscribe_num')
                ->where('id', $id)
                ->where(['type'=>2, 'status'=>4])
                ->first()
                ->toArray();
        if ($list['work_info']){
            $now = date('Y-m-d', time());
            foreach ($list['work_info'] as &$v) {
                if ($v['online_time'] > $now ){
                    $v['is_new']  = 1;
                } else {
                    $v['is_new']  = 0;
                }
            }
        }
        return $list ?: [];

    }

    /**
     * 免费专区
     * @return array
     */
    public  function getFreeWorks()
    {
        $works =  Works::with(['user'=>function($query){
                        $query->select('id','nickname');
                    }])
                    ->select('id','user_id', 'title', 'subtitle','cover_img','chapter_num')
                    ->where('is_free', 1)
                    ->where('is_audio_book', 0)
                    ->limit(5)
                    ->get();
        if ($works){
            foreach ($works as &$v) {
                $v['is_new'] = 1;
            }
        }

        $book =  Works::with(['user'=>function($query){
                        $query->select('id','nickname');
                    }])
                    ->select('id','user_id', 'title', 'subtitle','cover_img','chapter_num')
                    ->where('is_free', 1)
                    ->where('is_audio_book', 1)
                    ->limit(5)
                    ->get();
        if ($book){
            foreach ($book as &$v) {
                $v['is_new'] = 1;
            }
        }

        return [ 'works'=>$works, 'book'=>$book];

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

       public function categoryRelation()
    {
        //一对多
        return $this->hasMany('App\Models\WorksCategoryRelation','work_id', 'id');
    }


    public function userName()
    {
        //一对多
        return $this->belongsTo('App\Models\User','user_id');
    }


    public function workInfo()
    {
        //一对多
        return $this->hasMany('App\Models\WorksInfo','pid');
    }

}
