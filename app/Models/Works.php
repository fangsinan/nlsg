<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Works extends Base
{
    protected $table = 'nlsg_works';

    protected $fillable = [
        'column_id', 'title', 'subtitle', 'des', 'type', 'cover_img', 'detail_img', 'user_id', 'original_price',
        'price', 'is_end', 'status', 'timing_online', 'content', 'message', 'is_pay', 'is_free', 'chapter_num',
        'comment_num', 'collection_num', 'duration', 'is_audio_book', 'online_time', 'timing_time'
    ];

    //状态 1上架  2 下架
    const STATUS_ONE = 1;
    const STATUS_TWO = 2;


    /**
     * 首页课程推荐
     * @param $ids 相关作品id
     * @return bool
     */
    public function getIndexWorks($ids, $is_audio_book = 2, $user_id = 0, $is_free = false)
    {
        if ( ! $ids) {
            return false;
        }
        $WorksObj = Works::select('id', 'column_id', 'type', 'user_id', 'title', 'cover_img', 'detail_img', 'subtitle',
            'price', 'is_free', 'is_pay', 'works_update_time', 'chapter_num', 'subscribe_num as sub_num',
            'is_audio_book', 'cover_img as cover_pic', 'detail_img as detail_pic')
            ->with([
                'user' => function ($query) {
                    $query->select('id', 'nickname', 'headimg');
                }
            ])
            ->whereIn('id', $ids)
            ->whereIn('type', [2, 3]) //课程只有音频
            ->where('status', 4);
        //2时   不考虑是否听书
        if ($is_audio_book !== 2) {
            $WorksObj->where('is_audio_book', $is_audio_book);
        }
        if ($is_free !== false) {
            $WorksObj->where('is_free', $is_free);
        }

        $lists = $WorksObj
            ->orderByRaw('FIELD(id,'.implode(',', $ids).')')
            ->take(4)
            ->get()
            ->toArray();

        $time = Config('web.is_new_time');
        foreach ($lists as &$v) {
            $v['is_new'] = 0;
            if ($v['works_update_time'] > $time) {
                $v['is_new'] = 1;
            }
            $v['is_sub'] = Subscribe::isSubscribe($user_id, $v['id'], 2);
        }
        return $lists;

    }

    static function search($keywords, $is_audio_book)
    {
        $worksObj = new Works();
        $infoObj = new WorksInfo();
        $userObj = new User();
        $res = DB::table($worksObj->getTable(), 'works')
            ->leftJoin($infoObj->getTable().' as info', 'works.id', '=', 'info.pid')
            ->leftJoin($userObj->getTable().' as user', 'works.user_id', '=', 'user.id')
            ->select('works.id', 'works.type', 'works.title', 'works.user_id', 'works.cover_img', 'works.price',
                'works.original_price', 'works.subtitle', 'user.nickname')
            ->where('works.status', 4)
            ->where('works.type', 2)
            ->where('works.is_audio_book', $is_audio_book)
            ->where('info.status', 4)
            ->where(function ($query) use ($keywords) {
                $query->orwhere('works.title', 'like', "%{$keywords}%");
                $query->orwhere('user.nickname', 'like', "%{$keywords}%");
//                $query->orwhere('info.title', 'like', "%{$keywords}%");
            })->groupBy('works.id')->paginate(100)->toArray();
        //->get();
        return ['res' => $res['data'], 'count' => $res['total']];
        return ['res' => $res, 'count' => $res->count()];

    }

    /**
     * 首页推荐的课程
     * @param $id
     * @return bool
     */
    public function getRecommendWorks($id, $user_id = 0)
    {
        if ( ! $id) {
            return false;
        }
        $limit=1;
//        if($id==566){
//            $limit=1;
//        }
        $list = Works::with([
            'workInfo' => function ($query) use ($limit) {
                $query->select('id', 'pid', 'rank', 'title', 'duration', 'view_num', 'online_time')
                    ->orderBy('rank', 'desc')
                    ->orderBy('id', 'desc')
                    ->where('status', 4)
                    ->limit($limit);
            },
            'user:id,nickname,headimg'
        ])
            ->select('id', 'user_id', 'title', 'subscribe_num')
            ->where('id', $id)
            ->where(['type' => 2, 'status' => 4])
            ->first();
        $is_sub = Subscribe::isSubscribe($user_id, $id, 2);
        $list['is_sub'] = $is_sub ? 1 : 0;
        if ($list['workInfo']) {
            $now = date('Y-m-d', time());
            foreach ($list['workInfo'] as &$v) {
                if ($v['online_time'] > $now) {
                    $v['is_new'] = 1;
                } else {
                    $v['is_new'] = 0;
                }
            }
        }
        return [];
        return $list ?: [];

    }

    /**
     * 免费专区
     * @return array
     */
    public function getFreeWorks($uid = 0)
    {
        $works = Works::with([
            'user' => function ($query) {
                $query->select('id', 'nickname');
            }
        ])
            ->select('id', 'user_id', 'is_free', 'title', 'subtitle', 'cover_img', 'chapter_num',
                'chapter_num as info_num')
            ->where('type', 2)
            ->where('is_free', 1)
            ->where('is_audio_book', 0)
            ->where('status', 4)
            ->limit(5)
            ->get();
        if ($works) {
            foreach ($works as &$v) {
                $v['is_sub'] = 0;
                if ($uid) {
                    $v['is_sub'] = Subscribe::isSubscribe($uid, $v['id'], 2);
                }
                $v['is_new'] = 1;
            }
        }

        $book = Works::with([
            'user' => function ($query) {
                $query->select('id', 'nickname');
            }
        ])
            ->select('id', 'user_id', 'is_free', 'title', 'subtitle', 'cover_img', 'chapter_num',
                'chapter_num as info_num')
            ->where('is_free', 1)
            ->where('is_audio_book', 1)
            ->where('status', 4)
            ->limit(5)
            ->get();
        if ($book) {
            foreach ($book as &$v) {
                $v['is_sub'] = 0;
                if ($uid) {
                    $v['is_sub'] = Subscribe::isSubscribe($uid, $v['id'], 2);;
                }
                $v['is_new'] = 1;
            }
        }

        $lecture = Column::with([
            'user' => function ($query) {
                $query->select('id', 'nickname');
            }
        ])
            ->select('id', 'user_id', 'is_free', 'name', 'title', 'subtitle', 'cover_pic', 'details_pic', 'info_num')
            ->where('is_free', 1)
            ->where('type', 2)
            ->where('status', 1)
            ->limit(5)
            ->get();
        if ($lecture) {
            foreach ($lecture as &$v) {
                $v['is_sub'] = 0;
                if ($uid) {
                    $v['is_sub'] = Subscribe::isSubscribe($uid, $v['id'], 6);;
                }
                $v['is_new'] = 1;
            }
        }

        return ['works' => $works, 'book' => $book, 'lecture' => $lecture];

    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }


    public function lists()
    {
        return $this->belongsToMany('App\Models\Lists',
            'nlsg_lists_work', 'works_id', 'lists_id');
    }

    public function categoryRelation()
    {
        //一对多
        return $this->hasMany('App\Models\WorksCategoryRelation', 'work_id', 'id')
            ->select(['id', 'work_id', 'category_id']);
    }


    public function userName()
    {
        //一对多
        return $this->belongsTo('App\Models\User', 'user_id');
    }


    public function workInfo()
    {
        //一对多
        return $this->hasMany('App\Models\WorksInfo', 'pid');
    }

    public function getAllVipWorks($params)
    {
        $works_id_list = ConfigModel::getData(27);
        $works_order_str = 'FIELD(id,'.$works_id_list.') asc';
        $works_id_list_arr = explode(',', $works_id_list);

        $query = Works::whereIn('id', $works_id_list_arr)
            ->with([
                'categoryRelation', 'categoryRelation.categoryName' => function ($query) {
                    $query->select(['id', 'name']);
                }, 'columnInfo', 'user'                             => function ($query) {
                    $query->select('id', 'nickname', 'intro');
                }
            ])
            ->select([
                'id', 'type as works_type', 'title', 'subtitle', 'cover_img',
                'detail_img', 'price', 'column_id', 'user_id'
            ]);

        if ( ! empty($params['category_id'] ?? 0)) {
            $query->whereHas('categoryRelation', function (\Illuminate\Database\Eloquent\Builder $query) use ($params) {
                $query->where('category_id', '=', $params['category_id']);
            });
        }

        $list = $query->orderByRaw($works_order_str)->get();


        $category_list = WorksCategoryRelation::whereIn('work_id', $works_id_list_arr)
            ->select(['category_id'])->get()->toArray();
        $category_list = array_column($category_list, 'category_id');
        $category_list = WorksCategory::whereIn('id', $category_list)->select(['id', 'name'])->get()->toArray();


        return ['category' => $category_list, 'list' => $list];
    }

    public function columnInfo()
    {
        return $this->hasOne(Column::class, 'id', 'column_id')
            ->select(['id', 'type', 'column_type', 'subtitle', 'title']);
    }

    public function cytxClick()
    {
        return $this->hasOne(Click::class, 'cpid', 'works_id')
            ->where('flag', '=', 'cytx')
            ->groupBy('cpid')
            ->select([DB::raw('count(1) as counts'), 'cpid']);
    }

    public function listForCytx($params)
    {

        if (1) {
            $m = new ChannelWorksList();
            $list = $m->getList(0, 0, 0, 1, $this->user['id'] ?? 0);
            $list['banner'] = ConfigModel::getData(47);
            return $list;
        } else {
            $banner = ConfigModel::getData(47);
            $list = Works::where('for_cytx', '=', 1)
                ->where('status', '=', 4)
                ->where('type', '=', 2)
                ->with([
                    'columnInfo', 'user' => function ($query) {
                        $query->select('id', 'nickname', 'intro');
                    }, 'cytxClick'
                ])
                ->orderBy('cytx_sort', 'asc')
                ->orderBy('id', 'asc')
                ->select([
                    'id as works_id', 'type as works_type', 'title', 'subtitle', 'cover_img',
                    'column_id', DB::raw('2 as type'),
                    'detail_img', 'cytx_price as price', 'column_id', 'user_id', 'view_num'
                ])
                ->get();

            foreach ($list as &$v) {
                $v->id = $v->works_id;
                if ($v['view_num'] >= 10000) {
                    $leftNumber = floor($v['view_num'] / 10000);
                    $rightNumber = round(($v['view_num'] % 10000) / 10000, 2);
                    $v['view_num'] = floatval($leftNumber + $rightNumber).'万';
                }
                $user_info = [];
                $user_info['name'] = $v['user']['nickname'];
                $user_info['title'] = $v['columnInfo']['title'];
                $user_info['subtitle'] = $v['columnInfo']['subtitle'];
                $v->userInfo = $user_info;
            }

            return [
                'banner' => $banner,
                'list'   => $list
            ];
        }

    }

    /**
     * 作品、书单数据统计
     * @return \Illuminate\Http\JsonResponse
     */
    public static function statistic()
    {
        $works = Works::orderBy('created_at', 'desc')
            ->get()
            ->toArray();
        if ($works) {
            foreach ($works as $v) {
                $num = WorksInfo::where('status', 4)
                    ->where('pid', $v['id'])
                    ->count();
                Works::where('id', $v['id'])->update([
                    'chapter_num' => $num
                ]);
            }
        }

        $lists = Lists::orderBy('created_at', 'desc')
            ->get()
            ->toArray();
        if ($lists) {
            foreach ($lists as $v) {
                $num = ListsWork::where('lists_id', $v['id'])
                    ->where('state', 1)
                    ->count();
                Lists::where('id', $v['id'])->update([
                    'num' => $num
                ]);
            }
        }

        return success('成功');
    }

    public static function deal()
    {
        Works::where('status', 5)
            ->where('timing_online', 1)
            ->where('timing_time', '<=', Carbon::now()->toDateTimeString())
            ->update(['status' => 4, 'online_time' => date('Y-m-d H:i:s')]);

        WorksInfo::where('status', 5)
            ->where('timing_online', 1)
            ->where('timing_time', '<=', Carbon::now()->toDateTimeString())
            ->update(['status' => 4, 'online_time' => date('Y-m-d H:i:s')]);

        Column::where('status', 2)
            ->where('type', 2)
            ->where('timing_online', 1)
            ->where('timing_time', '<=', Carbon::now()->toDateTimeString())
            ->update(['status' => 1, 'online_time' => date('Y-m-d H:i:s')]);

    }


    /**
     * 首页推荐的课程
     * @param $option   1 阅读数   2订阅数
     * @param $type   1课程   2专栏|讲座   3 章节 (针对阅读量)
     *
     * @return bool
     */
    public static function edit_view_num($relation_id,$type,$option)
    {

        if(empty($option)){
            return ;
        }
        //  3000以下1：50   以上1：5
        //1课程  2专栏|讲座   3章节
        $edit_num = 1;
        switch ($type){
            case 1:
                $model = new Works();
                break;
            case 2:
                $model = new Column();
                break;
            case 3:
                $model = new WorksInfo();
                break;
        }
        if( !empty($model) ){
            $data = $model::find($relation_id);

            //  阅读数
            if($option == 1){
                $num = $data['view_num'];
            }else if($option = 2){//订阅数
                $num = $data['subscribe_num'];
            }


            if($num < 3000){
                $edit_num *= 50;
            }else{
                $edit_num *= 5;
            }

            if($option == 1){
                $model::where(['id'=>$relation_id])->increment('view_num',$edit_num);
            }else if($option == 2){//订阅数
                $model::where(['id'=>$relation_id])->increment('subscribe_num',$edit_num);;
            }

        }
        return ;
    }



}
