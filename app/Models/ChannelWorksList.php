<?php


namespace App\Models;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ChannelWorksList extends Base
{
    protected $table = 'nlsg_channel_works_list';

    public static function getPrice($type, $id)
    {
        $price = self::where('works_id', '=', $id)->where('type', '=', $type)->first();
        if (empty($price)) {
            return 0;
        } else {
            return $price->price;
        }
    }

    public function checkSub()
    {
        return $this->hasOne(Subscribe::class, 'channel_works_list_id', 'id')
            ->where('end_time', '>', date('Y-m-d H:i:s'))
            ->where('status', '=', 1)
            ->where('is_del', '=', 0)
            ->select(['id', 'type', 'user_id', 'relation_id', 'start_time', 'end_time', 'channel_works_list_id',]);
    }

    private function listForCytxFromDB($page, $size, $ob, $category_id, $works_type, $is_buy, $user_id, $is_coll)
    {
        //查询出用户已经订阅的数据
        $sub_list = Subscribe::where('user_id', '=', $user_id)
            ->where('channel_works_list_id', '>', 0)
            ->where('end_time', '>', date('Y-m-d H:i:s'))
            ->where('status', '=', 1)
            ->where('is_del', '=', 0)
            ->select(['id', 'channel_works_list_id'])
            ->get();
        if ($sub_list->isEmpty()) {
            $sub_list = [];
        } else {
            $sub_list = $sub_list->toArray();
            $sub_list = array_column($sub_list, 'channel_works_list_id');
        }

        //查询出用户收藏的数据
        $coll_list = Collection::where('user_id', '=', $user_id)
            ->whereIn('type', [2, 7])
            ->select([DB::raw("CONCAT(if(type = 7,1,2),'-',relation_id) as coll_id")])
            ->get();

        if ($coll_list->isEmpty()) {
            $coll_list = '';
        } else {
            $coll_list = array_column($coll_list->toArray(), 'coll_id');
            $temp_coll_list = '';
            foreach ($coll_list as $cllv) {
                $temp_coll_list .= ",'" . $cllv . "'";
            }
            $coll_list = trim($temp_coll_list, ',');
        }

        $query = self::where('status', '=', 1)
            ->where('channel_type', '=', 1)
            ->with([
                'column',
                'works',
                'categoryBind',
                'categoryBind.categoryName',
            ])
            ->withCount(['checkSub' => function ($q) use ($user_id) {
                $q->where('user_id', '=', $user_id);
            }]);

        //已购过滤  0全部  1已购  2未够
        if ($is_buy === 1) {
            $query->whereIn('id', $sub_list);
        } elseif ($is_buy === 2) {
            $query->whereNotIn('id', $sub_list);
        }

        //过滤收藏
        if ($is_coll === 1) {
            if (!empty($coll_list)) {
                $query->whereRaw(DB::raw("CONCAT(type,'-',works_id) in ($coll_list)"));
            } else {
                $query->whereRaw(DB::raw("CONCAT(type,'-',works_id) in ('0-0')"));
            }
        }

        //视频,音频过滤
        if (!empty($works_type)) {
            $query->where('type', '=', $works_type);
        }

        //筛选 分类,视频/音频
        if (!empty($category_id)) {
            $query->whereHas('categoryBind', function ($q) use ($category_id) {
                $q->where('category_id', '=', $category_id);
            });
        }

        //最多学习,最新上架,价格
        switch ($ob) {
            case 'view_num_asc':
                $query->orderBy('subscribe_num', 'asc');
                break;
            case 'view_num_desc':
                $query->orderBy('subscribe_num', 'desc');
                break;
            case 'created_asc':
                $query->orderBy('created_at', 'asc');
                break;
            case 'created_desc':
                $query->orderBy('created_at', 'desc');
                break;
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
        }

        $list = $query->orderBy('rank', 'asc')
            ->orderBy('id', 'asc')
            ->limit($size)
            ->offset(($page - 1) * $size)
            ->get();

        $res = [];

        foreach ($list as $v) {
            $temp_res = [];
            $temp_res['id'] = $v['works_id'];
            $temp_res['works_id'] = $v['works_id'];
            $temp_res['works_type'] = $v['type'];
            $temp_res['price'] = $v['price'];
            $temp_res['view_num'] = $v['view_num'];
            $temp_res['created_at'] = $v['created_at'];
            $temp_res['is_buy'] = ($v['check_sub_count'] > 0) ? 1 : 0;

            $temp_res['category_info'] = [];
            foreach ($v['categoryBind'] as $cbv) {
                if (!empty($cbv['categoryName'] ?? '')) {
                    $temp_res['category_info'][] = $cbv['categoryName'];
                }
            }

            if ($v['type'] == 1) {
                if (empty($v['column'])) {
                    continue;
                }
                $temp_res['title'] = $v['column']['title'];
                $temp_res['subtitle'] = $v['column']['subtitle'];
                //$temp_res['cover_img'] = $v['cover_img'];
                $temp_res['cover_img'] = $v['column']['cover_img'];
                $temp_res['detail_img'] = $v['column']['cover_img'];
                $temp_res['type'] = 1;
                $temp_res['column_type'] = $v['column']['column_type'];
                $temp_res['user_id'] = $v['column']['user_id'];
                $temp_res['subscribe_num'] = $v['column']['subscribe_num'];
                $temp_res['info_num'] = $v['column']['info_num'];
                $temp_res['history'] = History::getHistoryCount($v['works_id'], 2, $user_id);
            } else if ($v['type'] == 2) {
                if (empty($v['works'])) {
                    continue;
                }
                $temp_res['title'] = $v['works']['title'];
                $temp_res['subtitle'] = $v['works']['subtitle'];
                //$temp_res['cover_img'] = $v['cover_img'];
                $temp_res['cover_img'] = $v['works']['cover_img'];
                $temp_res['detail_img'] = $v['works']['cover_img'];
                $temp_res['type'] = $v['works']['type'];
                $temp_res['column_type'] = 1;
                $temp_res['user_id'] = $v['works']['user_id'];
                $temp_res['subscribe_num'] = $v['works']['subscribe_num'];
                $temp_res['info_num'] = $v['works']['info_num'];
                $temp_res['history'] = History::getHistoryCount($v['works_id'], 4, $user_id);
            } else {
                continue;
            }

            if ($temp_res['view_num'] >= 10000) {
                $leftNumber = floor($temp_res['view_num'] / 10000);
                $rightNumber = round(($temp_res['view_num'] % 10000) / 10000, 2);
                $temp_res['view_num'] = floatval($leftNumber + $rightNumber) . 'W';
            }

            if ($temp_res['subscribe_num'] >= 10000) {
                $leftNumber = floor($temp_res['subscribe_num'] / 10000);
                $rightNumber = round(($temp_res['subscribe_num'] % 10000) / 10000, 2);
                $temp_res['subscribe_num'] = floatval($leftNumber + $rightNumber) . 'W';
            }

            $temp_res['user_info'] = User::getTeacherInfo($temp_res['user_id']);
            $res[] = $temp_res;
        }
        return $res;

    }

    public function listForCytx($params, $user)
    {
        $page = $params['page'] ?? 1;
        $size = $params['size'] ?? 10;
        $ob = $params['ob'] ?? '';
        $category_id = $params['category_id'] ?? 0;
        $works_type = $params['works_type'] ?? 0;
        $is_buy = intval($params['is_buy'] ?? 0);
        $is_coll = intval($params['is_coll'] ?? 0);
        $user_id = $user['id'] ?? 0;

        $works_list = $this->listForCytxFromDB($page, $size, $ob, $category_id, $works_type, $is_buy, $user_id, $is_coll);

        //分类信息和banner列表
        $channelCategoryModel = new ChannelCategory();
        $category_res = $channelCategoryModel->getCategoryList(1);

        return [
            'banner' => [],
            'category' => $category_res,
            'list' => $works_list,
        ];

    }

    public function cytxBanner($params, $user)
    {

        $user_id = $params['user_id'] ?? 0;

        $banner_index = ConfigModel::getData(47);
        $banner_home = ConfigModel::getData(51);

        $bannerModel = new Banner();
        $banner = $bannerModel->cytxBanner();


//        $live_team = Live::teamInfo(1,1);
//
//        if(isset($live_team[0]['order_num'])){
//            $live_team[0]['order_num']=$live_team[0]['order_num']+5000;
//        }
//        if(isset($live_team[0]['id'])) { //直播状态
//            $live_info = LiveInfo::query()->where('live_pid', $live_team[0]['id'])->first();
//            $live_team[0]['is_begin'] = $live_info->is_begin;
//
//            $is_sub=0;
//            if($live_team[0]['is_free']==1){ //免费
//                $Order=Subscribe::query()->select('id')->where('user_id',$user_id)->where('type' ,3)
//                    ->where('relation_id',$live_team[0]['liveInfo']['id']) //live_info表id
//                    ->where('status',1)->where('is_flag','cytx')->first();
//            }else {
//                //是否订阅
//                $Order = Order::query()->select('id', 'live_id')
//                    ->where('user_id', $user_id)->where('live_id', $live_team[0]['id'])->where('type', 10)->where('status', 1)
//                    ->where('activity_tag', 'cytx')->where('pay_price', '>', '1')
//                    ->first();
//            }
//            if(!empty($Order)){
//                $is_sub=1;
//            }
//            $live_team[0]['is_sub']=$is_sub;
//        }
        return [
            'index' => array_filter(explode(',', $banner_index)),
            'home' => array_filter(explode(',', $banner_home)),
            'banner'=>$banner,
//            'live'=>$live_team,
            'live'=>[],
        ];

    }

    public function cytxOrder($params, $user)
    {
        $page = $params['page'] ?? 1;
        $size = $params['size'] ?? 10;

        $list = Order::query()
            ->where('user_id', '=', $user['id'])
            ->where('activity_tag', '=', 'cytx')
            ->whereIn('type', [9, 15])
            ->where('status', '=', 1)
            ->where('is_shill', '=', 0)
            ->with([
                'works' => function ($q) {
                    $q->select(['id', 'title', 'type', 'subtitle',
                        'cover_img', 'detail_img']);
                },
                'column' => function ($q) {
                    $q->select(['id', 'name as title', 'type', 'subtitle',
                        'cover_pic as cover_img', 'details_pic as detail_img']);
                },
                'payRecord' => function ($q) {
                    $q->select(['ordernum', 'price', 'type', 'created_at']);
                }])
            ->select(['id', 'type', 'relation_id', 'pay_time', 'price', 'pay_price', 'pay_type', 'ordernum'])
            ->limit($size)
            ->offset(($page - 1) * $size)
            ->get();

        foreach ($list as &$v) {
            if ($v->type == 9) {
                $v->title = $v->works->title;
                $v->subtitle = $v->works->subtitle;
                $v->cover_img = $v->works->cover_img;
                $v->detail_img = $v->works->detail_img;
            } else {
                $v->title = $v->column->title;
                $v->subtitle = $v->column->subtitle;
                $v->cover_img = $v->column->cover_img;
                $v->detail_img = $v->column->detail_img;
            }
            unset($v->works, $v->column);
        }

//        $list = DB::table('nlsg_order as o')
//            ->join('nlsg_pay_record as pr', 'o.ordernum', '=', 'pr.ordernum')
//            ->where('o.user_id','=',$user['id'])
//            ->whereIn('o.type', [9, 15])
//            ->where('o.activity_tag', '=', 'cytx')
//            ->where('o.status', '=', 1)
//            ->where('o.is_shill', '=', 0)
//            ->select(['o.id', 'o.type', 'o.relation_id', 'o.pay_time', 'o.price', 'pr.price as pay_price', 'pr.type as pay_type'])
//            ->limit($size)
//            ->offset(($page - 1) * $size)
//            ->orderBy('pr.created_at', 'desc')
//            ->get();


        return $list;
    }

    public function getList($page = 1, $size = 10, $category_id = 0, $channel = 0, $user_id)
    {
        if (empty($channel)) {
            return [];
        }

        $cache_key_name = 'channel_works_list_' . $channel;
        $expire_num = CacheTools::getExpire('channel_works_list');
        $data = Cache::get($cache_key_name);
        if (empty($data)) {
            $data = $this->getListFromDB($channel);
            Cache::put($cache_key_name, $data, $expire_num);
        }

        $list = [];
        if (!empty($category_id)) {
            foreach ($data['list'] as $v) {
                if (in_array($category_id, $v['category_id'])) {
                    $list[] = $v;
                }
            }
        } else {
            $list = $data['list'];
        }

        return ['category' => $data['category'], 'list' => $list];
    }

    public function getListFromDB($channel = 0)
    {
        $list = self::where('status', '=', 1)
            ->where('channel_type', '=', $channel)
            ->with([
                'column',
                'works',
                'works.categoryRelation', 'works.categoryRelation.categoryName'
            ])
            ->orderBy('rank', 'asc')
            ->orderBy('id', 'asc')
            ->get()
            ->toArray();

        $res = [];

        $category_res = [];

        foreach ($list as $v) {
            $temp_res = [];
            $temp_res['id'] = $v['works_id'];
            $temp_res['works_id'] = $v['works_id'];
            $temp_res['works_type'] = $v['type'];
            $temp_res['price'] = $v['price'];
            if ($v['type'] == 1) {
                if (empty($v['column'])) {
                    continue;
                }
                $temp_res['title'] = $v['column']['title'];
                $temp_res['subtitle'] = $v['column']['subtitle'];
                //$temp_res['cover_img'] = $v['cover_img'];
                $temp_res['cover_img'] = $v['column']['cover_img'];
                $temp_res['detail_img'] = $v['column']['cover_img'];
                $temp_res['type'] = 1;
                $temp_res['column_type'] = $v['column']['column_type'];
                $temp_res['category_id'] = [0];
                $temp_res['user_id'] = $v['column']['user_id'];
                $temp_res['view_num'] = $v['column']['view_num'];

            } else if ($v['type'] == 2) {
                if (empty($v['works'])) {
                    continue;
                }
                $temp_res_category_id = [];
                foreach ($v['works']['category_relation'] as $cv) {
                    $temp_category_res = [];
                    $temp_category_res['id'] = $cv['category_name']['id'];
                    $temp_category_res['name'] = $cv['category_name']['name'];

                    if (!empty($temp_category_res['id']) && !empty($temp_category_res['name'])) {
                        $temp_res_category_id[] = $temp_category_res['id'];
                        if (!in_array($cv['category_name']['id'], array_column($category_res, 'id'))) {
                            $category_res[] = $temp_category_res;
                        }
                    }
                }

                $temp_res['title'] = $v['works']['title'];
                $temp_res['subtitle'] = $v['works']['subtitle'];
                //$temp_res['cover_img'] = $v['cover_img'];
                $temp_res['cover_img'] = $v['works']['cover_img'];
                $temp_res['detail_img'] = $v['works']['cover_img'];
                $temp_res['type'] = $v['works']['type'];
                $temp_res['column_type'] = 1;
                $temp_res['category_id'] = $temp_res_category_id;
                $temp_res['user_id'] = $v['works']['user_id'];
                $temp_res['view_num'] = $v['works']['view_num'];
            } else {
                continue;
            }

            if ($temp_res['view_num'] >= 10000) {
                $leftNumber = floor($temp_res['view_num'] / 10000);
                $rightNumber = round(($temp_res['view_num'] % 10000) / 10000, 2);
                $temp_res['view_num'] = floatval($leftNumber + $rightNumber) . '万';
            }

            $temp_res['user_info'] = User::getTeacherInfo($temp_res['user_id']);
            $res[] = $temp_res;
        }

        return ['list' => $res, 'category' => $category_res];
    }

    public function categoryBind()
    {
        return $this->hasMany(ChannelCategoryBind::class, 'works_list_id', 'id')
            ->select(['id', 'works_list_id', 'category_id']);
    }

    public function column()
    {
        return $this->hasOne(Column::class, 'id', 'works_id')
            ->select(['id', 'name as title', 'subtitle', 'cover_pic as cover_img',
                'details_pic as detail_img', 'column_type', 'price', 'user_id', 'view_num',
                'info_num', 'subscribe_num']);
    }

    public function works()
    {
        return $this->hasOne(Works::class, 'id', 'works_id')
            ->select(['id', 'title', 'subtitle', 'cover_img', 'detail_img', 'type', 'price', 'user_id', 'view_num',
                'chapter_num as info_num', 'subscribe_num']);
    }
}
