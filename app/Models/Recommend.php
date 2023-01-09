<?php

namespace App\Models;

use App\Models\Lists;
use App\Models\LiveConsole;
use App\Models\Works;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\Wiki;
use App\Models\Column;
use Illuminate\Support\Facades\Redis;

class Recommend extends Base
{
    protected $table = 'nlsg_recommend';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'relation_id', 'position', 'type', 'sort'
    ];

    public function getIndexRecommend($type = 1, $position = '1', $limit = 5, $row = 1)
    {
        if ( ! $type) {
            return false;
        }
        //添加缓存\
        $cache_key_name = 'index_recommend_'.$type.'_'.$position;
        $result = Cache::get($cache_key_name);
        if ($result) {
            return $result;
        }


        $ids = Recommend::where('position', $position)
            ->where('type', $type)
            ->where('status', 1)
            ->where('app_project_type','=',APP_PROJECT_TYPE)
            ->orderBy('sort')
            ->orderBy('created_at', 'desc')
            ->pluck('relation_id')
            ->toArray();
        if(empty($ids)){
            return [];
        }
        $result = $this->getResult($type,$ids,$limit);


        $expire_num = CacheTools::getExpire('index_recommend');
        Cache::put($cache_key_name, $result, $expire_num);

        return $result;
    }



    public function getResult($type,$ids,$limit=3){
        $result =[];


        switch ($type) {
            case 1:
            case 3:
            case 13:
                $model = new Column();
                $result = $model->getIndexColumn($ids);
                break;
            case 2:
                $model = new Works();
                $result = $model->getIndexWorks($ids);
                break;
//            case 3:
//                $model = new Column();
//                $result = $model->getIndexColumn($ids);
//                break;
            case 4:
                $model = new Lists();
                $result = $model->getIndexListWorks($ids, [3]);
                break;
            case 5:
                $model = new Wiki();
                $result = $model->getIndexWiki($ids);
                break;
            case 7:
                $model = new Live();
                $result = $model->getIndexLive($ids);
                break;
            case 8:
                $model = new MallGoods();
                $result = $model->getIndexGoods($ids);
                break;
            case 9:
                //听书
                $model = new Works();
                $result = $model->getIndexWorks($ids, 1);
                break;
            case 10:
                $model = new Lists();
                $result = $model->getIndexListCourse($ids, 1);
                break;
            case 11:
                $model = new Lists();
                $result = $model->getNewIndexListCourse($ids,$limit);
                break;
            case 14:
                $model = new User();
                $result = $model->getIndexUser($ids);
                break;
            case 15:
                $model = new Lists();
                $result = $model->getIndexListWorks($ids, [7]);

        }
        return $result;
    }

    public function  getCourseLists()
    {
        $ids = Recommend::where('position', 1)
               ->where('type', 10)
               ->where('status', 1)
               ->orderBy('sort')
               ->orderBy('created_at', 'desc')
               ->pluck('relation_id')
               ->toArray();
        if (!$ids){
            return  [];
        }
        $model = new Lists();
        $result = $model->getIndexListCourse($ids, 1);
        return $result;
    }

    /**
     * 首页直播
     * @param $uid
     * @param  int  $type
     * @param  int  $position
     * @return bool
     */
    public function getLiveRecommend($uid, $type = 7, $position = 1)
    {
        if ( ! $type) {
            return false;
        }
//        //添加缓存
//        $cache_key_name = 'index_recommend_'.$type.'_'.$position;
//        $list = Cache::get($cache_key_name);
//        if ($list) {
//            $list = $this->getLiveRelation($uid, $list);
//            return $list;
//        }

        $ids = Recommend::where('position', $position)
            ->where('type', $type)
            ->where('status', 1)
            ->orderBy('sort')
            ->orderBy('created_at', 'desc')
            ->pluck('relation_id')
            ->toArray();
        if ( ! $ids) {
            return false;
        }
        $list = Live::select('id', 'title', 'describe', 'cover_img', 'begin_at', 'end_at', 'price', 'order_num',
            'is_free', 'helper','hide_sub_count')
            ->whereIn('id', $ids)
            ->where('is_del', 0)
            ->orderBy('created_at', 'desc')
            ->first();
        $list->live_length = strtotime($list->end_at)-strtotime($list->begin_at);
        if (!$list){
            return  [];
        }

//        $expire_num = CacheTools::getExpire('index_recommend_live');
//        Cache::put($cache_key_name, $list, $expire_num);

        $list = $this->getLiveRelation($uid, $list);
        return $list;

    }

    public function getLiveRelation($uid, $list)
    {
        $channel = LiveInfo::where('live_pid', $list->id)
            ->where('status', 1)
            ->orderBy('id', 'desc')
            ->first();
        if ($channel){
            if ($channel->is_begin == 0 && $channel->is_finish == 0) {
                $list['live_status'] = 1;
            } elseif ($channel->is_begin == 1 && $channel->is_finish == 0) {
                $list['live_status'] = 3;
            } elseif ($channel->is_begin == 0 && $channel->is_finish == 1) {
                $list['live_status'] = 2;
            }
            $list['info_id'] = $channel->id;
        }
        if($list->is_free==1){
            $isSub = LiveCountDown::where(['user_id' => $uid, 'live_id' => $channel->id])->first();
        } else {
            $isSub = Subscribe::isSubscribe($uid, $list->id, 3);
        }
        $isAdmin = LiveConsole::isAdmininLive($uid, $list->id);

        $list['is_sub'] = $isSub ? 1 : 0;
        $list['is_admin'] = $isAdmin ? 1 : 0;
        return $list;
    }


    public function getEditorWorks($uid = false)
    {
        $lists = Recommend::select('id', 'relation_id', 'relation_type', 'reason')
            ->where('position', 1)
            ->where('type', 12)
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
        if ($lists) {
            foreach ($lists as $k => $v) {
                if ($v['relation_type'] == 1 || $v['relation_type'] == 2) {
                    if ($uid) {
                        $lists[$k]['is_sub'] = Subscribe::isSubscribe($uid, $v['relation_id'], 2);
                    }
                    $lists[$k]['works'] = Works::with([
                        'user' => function ($query) {
                            $query->select('id', 'nickname', 'headimg');
                        }
                    ])
                        ->select([
                            'id', 'user_id', 'is_free', 'title', 'subtitle', 'cover_img', 'price', 'chapter_num',
                            'subscribe_num'
                        ])
                        ->where('id', $v['relation_id'])
                        ->where('status', 4)
                        ->first();

                } elseif ($v['relation_type'] == 3 || $v['relation_type'] == 4) {
                    if ($uid) {
                        $lists[$k]['is_sub'] = Subscribe::isSubscribe($uid, $v['relation_id'], 1);
                    }
                    $lists[$k]['works'] = Column::with([
                        'user' => function ($query) {
                            $query->select('id', 'nickname', 'headimg');
                        }
                    ])
                        ->select(['id', 'user_id', 'is_free', 'name', 'title', 'subtitle', 'cover_pic', 'price'])
                        ->where('id', $v['relation_id'])
                        ->where('status', 1)
                        ->first();
                }
                if (empty($lists[$k]['works'])) {
                    unset($lists[$k]);
                }

            }
            $lists = array_values($lists);
        }

        return $lists;

    }

    public function works()
    {
        return $this->belongsTo('App\Models\Works', 'relation_id', 'id');
    }

    public function goods()
    {
        return $this->belongsTo('App\Models\MallGoods', 'relation_id', 'id');
    }

    public function wiki()
    {
        return $this->belongsTo('App\Models\Wiki', 'relation_id', 'id');
    }

    public function live()
    {
        return $this->belongsTo('App\Models\Live', 'relation_id', 'id');
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'relation_id', 'id');
    }

    public function lists()
    {
        return $this->belongsTo(Lists::class, 'relation_id', 'id');
    }

}
