<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Lists extends Model
{
    protected $table = 'nlsg_lists';
    protected $fillable = [
        'title', 'subtitle', 'status', 'type','cover','details_pic','sort'
    ];


    // 获取热门榜单
    public function getList()
    {

        $cache_key_name = 'index_rank_data';
        $data = Cache::get($cache_key_name);

        if (empty($data)) {

            $data = [
                [
                    "title" => "销售榜单 Top10",
                    "type" => "1",
                    'data' => $this->getRankWorks(),
                ],

                
            ];

            $expire_num = CacheTools::getExpire($cache_key_name);
            Cache::put($cache_key_name, $data, $expire_num);
        }
        return $data;

    }





    /**
     * 首页听书推荐
     * @param $ids 相关作品id
     * @return bool
     */
    public function getIndexListWorks($ids, $type = [1])
    {
        if ( ! $ids) {
            return false;
        }

        $lists = Lists::with(['listWorks:id,lists_id,type,works_id'])
            ->select('id', 'title', 'subtitle', 'cover', 'num','details_pic','describe')
            ->whereIn('id', $ids)
            ->whereIn('type', $type)
            ->where('status', 1)
            ->limit(3)
            ->get()
            ->toArray();

        if ($lists) {
            foreach ($lists as $k => &$v) {
                foreach ($v['list_works'] as $kk => &$vv) {

                    if ($vv['type']==1){
                        $works = Works::select(['id','user_id','type', 'title', 'describe', 'cover_img','original_price','price', 'message','is_free','view_num',"chapter_num as info_num",])
                            ->with(['user'=>function($query){
                                $query->select('id','nickname', 'headimg','teacher_title');
                            }])
                            ->where('id', $vv['works_id'])
                            ->first();
                        //->get()->toArray();
                        $v['list_works'][$kk]['works'] = $works;
                    }else if ($vv['type'] == 2) {
                        $listen = Works::select([
                            'id', 'user_id', 'type', 'title', 'subtitle', 'cover_img', 'original_price', 'price',
                            'message', 'is_free','view_num',"chapter_num as info_num",
                        ])
                            ->with([
                                'user' => function ($query) {
                                    $query->select('id', 'nickname', 'headimg','teacher_title');
                                }
                            ])
                            ->where('id', $vv['works_id'])
                            ->where('is_audio_book', 1)
                            ->where('status', 4)
                            ->first();
                        $v['list_works'][$kk]['works'] = $listen;
                    } elseif ($vv['type'] == 4) {
                        $column = Column::select([
                            'id', 'user_id', 'title', 'subtitle', 'cover_pic', 'original_price', 'price', 'message',
                            'is_free','view_num',"info_num",
                        ])
                            ->with([
                                'user' => function ($query) {
                                    $query->select('id', 'nickname', 'headimg','teacher_title');
                                }
                            ])
                            ->where('id', $vv['works_id'])
                            ->where('type', 2)
                            ->where('status', 1)
                            ->first();
                        $v['list_works'][$kk]['works'] = $column;
                    } else {
                        unset($lists[$k]['list_works'][$kk]);
                    }
                }
                $lists[$k]['list_works'] = array_values($lists[$k]['list_works']);
                $lists[$k]['num'] = count($lists[$k]['list_works']);

            }
        }
        return $lists;
    }

    public function getIndexListCourse($ids, $type = 1)
    {
        if ( ! $ids) {
            return false;
        }
        $lists = Lists::select('id', 'title', 'subtitle', 'cover', 'num')
            ->with([
                'works'         => function ($query) {
                    $query->select('works_id', 'user_id', 'title', 'cover_img')
                        ->where('status', 4)
                        ->limit(3)
                        ->inRandomOrder();
                }, 'works.user' => function ($query) {
                    $query->select('id', 'nickname', 'headimg');
                }
            ])->whereIn('id', $ids)
            ->where('type', $type)
            ->limit(3)
            ->first();
        if ($lists) {
            $lists = $lists->toArray();
        }
        return $lists;
    }


    public function getIndexGoods($ids)
    {

        $lists = Lists::query()
            ->select('id', 'title', 'subtitle', 'cover')
            ->whereIn('id', $ids)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->toArray();
        return $lists;
    }

    public function getRankWorks()
    {
        $lists = Lists::select('id', 'title', 'num', 'cover')
            ->where('type', 4)
            ->get()
            ->toArray();

        if ($lists) {
            foreach ($lists as &$v) {
                $work_ids = ListsWork::where('lists_id', $v['id'])
                    ->where('state', 1)
                    ->orderBy('sort')
                    ->orderBy('created_at', 'desc')
                    ->pluck('works_id')
                    ->toArray();
                $works = Works::select('id as works_id', 'title')
                    ->whereIn('id', $work_ids)
                    ->orderByRaw('FIELD(id,'.implode(',', $work_ids).')')
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->toArray();
                $v['works'] = $works;
            }

        }
        return $lists;
    }


    public function getRankWiki()
    {
        $lists = Lists::select('id', 'title', 'num', 'cover')
            ->where('type', 5)
            ->get()
            ->toArray();

        if ($lists) {
            foreach ($lists as &$v) {
                $work_ids = ListsWork::where('lists_id', $v['id'])
                    ->where('state', 1)
                    ->orderBy('sort')
                    ->orderBy('created_at', 'desc')
                    ->pluck('works_id')
                    ->toArray();
                $wikis = Wiki::select('id as works_id', 'name')
                    ->whereIn('id', $work_ids)
                    ->orderByRaw('FIELD(id,'.implode(',', $work_ids).')')
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->toArray();
                $v['list_works'] = $wikis;
            }

        }
        return $lists;
    }

    public function getRankGoods()
    {
        $lists = Lists::select('id', 'title', 'num', 'cover')
            ->where('type', 6)
            ->get()
            ->toArray();
        if ($lists) {
            foreach ($lists as &$v) {
                $work_ids = ListsWork::where('lists_id', $v['id'])
                    ->where('state', 1)
                    ->orderBy('sort')
                    ->orderBy('created_at', 'desc')
                    ->pluck('works_id')
                    ->toArray();
                $wikis = MallGoods::select('id as works_id', 'name')
                    ->whereIn('id', $work_ids)
                    ->orderByRaw('FIELD(id,'.implode(',', $work_ids).')')
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->toArray();
                $v['list_goods'] = $wikis;
            }

        }
        return $lists;
    }

    public function listWorks()
    {
        return $this->hasMany('App\Models\ListsWork', 'lists_id', 'id')->where('state',1);
    }

    public function works()
    {
        return $this->belongsToMany('App\Models\Works',
            'nlsg_lists_work', 'lists_id', 'works_id')->where('state',1);
    }

    public function wiki()
    {
        return $this->belongsTo('App\Models\Wiki',
            'nlsg_lists_work', 'lists_id', 'works_id');
    }

    public function listGoods()
    {
        return $this->belongsToMany('App\Models\MallGoods',
            'nlsg_lists_work', 'lists_id', 'works_id');
    }



    //5.0新加入
    public function getNewIndexListCourse($ids)
    {
        $lists = Lists::select('id', 'title', 'num', 'cover','type')
            ->whereIn('id',$ids) ->get()->toArray();


        if ($lists) {
            foreach ($lists as &$v) {
                $work_ids = ListsWork::where('lists_id', $v['id'])
                    ->where('state', 1)->orderBy('sort')
                    ->orderBy('created_at', 'desc')->pluck('works_id')->toArray();
                $v['data'] = [];




                //
                if($v['type'] == 9){ //用户学习排序榜单
//                    $his_data = History::select("user_id")->selectRaw('sum(time_number) as num')
//                        ->orderBy('num', 'desc')->GroupBy("user_id")->limit(3)->get()->toArray();
//
//                    $user_ids = array_column($his_data,'user_id');
//                    $user = User::select('id','nickname', 'phone','headimg')
//                        ->whereIn('id', $user_ids)
//                        ->orderByRaw('FIELD(id,'.implode(',', $user_ids).')')
//                        ->get()->toArray();
//
//
//                    foreach ($user as &$user_v){
//                        foreach ($his_data as $his_datum){
//                            if($user_v['id'] == $his_datum['user_id']){
//                                $user_v['his_num'] = $his_datum['num'];
//                            }
//                        }
//                    }
//                    $v['data'] = $user;
                    $v['data'] = User::getUserHisLen();
                }


                if(empty($work_ids)){
                    continue;
                }
                //课程榜单
                if($v['type'] == 8){

                    $works = Works::with([
                        'user:id,nickname,teacher_title'
                    ])->select('id as works_id', 'title',"user_id",'cover_img')
                        ->whereIn('id', $work_ids)
                        ->orderByRaw('FIELD(id,'.implode(',', $work_ids).')')
                        ->orderBy('created_at', 'desc')
                        ->get()
                        ->toArray();
                    $v['data'] = $works;
                }


            }

        }
        return $lists;
    }


}
