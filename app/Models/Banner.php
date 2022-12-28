<?php

namespace App\Models;


use Illuminate\Support\Facades\Cache;

class Banner extends Base
{

    protected $table = 'nlsg_banner';

    protected $fillable = [
        'title', 'pic', 'url', 'rank', 'type', 'obj_id', 'status', 'jump_type', 'start_time', 'end_time'
    ];

    /**
     * 首页Banner
     * @return mixed
     */
    public function getIndexBanner($show_type=0)
    {
//        $cache_key_name = 'index_banner_list';
//        $expire_num = CacheTools::getExpire('mall_banner_list');
//        $res = Cache::get($cache_key_name);
//        if (empty($res)) {
        $today = date('Y-m-d H:i:s', time());
        $res = $this->select('id', 'pic', 'title', 'url', 'jump_type', 'obj_id', 'info_id')
            ->where('status', 1)
            ->where('type', 1)
            ->where('start_time', '<=', $today)
            ->where('end_time', '>=', $today)
            ->whereIn('show_type', [0,$show_type])
            ->orderBy('rank')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->toArray();
//            Cache::put($cache_key_name, $res, $expire_num);
//        }
        return $res;

    }

    //商城banner数据
    public function mallBannerList()
    {
        $cache_key_name = 'mall_banner_list';
        $expire_num = CacheTools::getExpire('mall_banner_list');
        $res = Cache::get($cache_key_name);
        if (empty($res)) {
            $res = self::mallBannerListFromDb();
            Cache::put($cache_key_name, $res, $expire_num);
        }
        return $res;
    }

    public static function mallBannerListFromDb()
    {
        $banner_limit = ConfigModel::getData(4);
        $recommend_limit = ConfigModel::getData(5);

        $res['banner'] = Banner::where('type', '=', 51)
            ->where('status', '=', 1)
            ->orderBy('rank', 'asc')
            ->orderBy('id', 'desc')
            ->select(['id', 'title', 'pic', 'url', 'jump_type', 'obj_id','info_id'])
            ->limit($banner_limit)
            ->get();
        $res['recommend'] = Banner::where('type', '=', 52)
            ->where('status', '=', 1)
            ->orderBy('rank', 'asc')
            ->orderBy('id', 'desc')
            ->select(['id', 'title', 'pic', 'url', 'jump_type', 'obj_id','info_id'])
            ->limit($recommend_limit)
            ->get();
        $res['goods_list'] = MallGoodsList::where('show_index', '=', 1)
            ->where('status', '=', 1)
            ->orderBy('rank', 'asc')
            ->select(['id', 'name', 'icon'])
            ->limit(3)
            ->with(['goods_list'])
            ->get();
        $res['hot_sale'] = Banner::where('type', '=', 53)
            ->where('status', '=', 1)
            ->orderBy('rank', 'asc')
            ->orderBy('id', 'desc')
            ->select(['id', 'title', 'pic', 'url', 'jump_type', 'obj_id','info_id'])
            ->limit($recommend_limit)
            ->get();
        $res['postage_line'] = ConfigModel::getData(1);

        $keywords = ConfigModel::getData(21);
        $res['keywords'] = explode(',', $keywords);

        foreach ($res['goods_list'] as $k => $v) {
            $temp_goods_list = [];
            foreach ($v->goods_list as $vv) {
                $temp_goods_list[] = $vv->goods_id;
            }
            $v->ids_str = implode(',', $temp_goods_list);
            unset($res['goods_list'][$k]->goods_list);
        }

        return $res;
    }

    public function appPopup($type=60)
    {
        $now_date = date('Y-m-d H:i:s');
        $data = Banner::where('type', '=', $type)
            ->where('status', '=', 1)
            ->where('start_time', '<=', $now_date)
            ->where('end_time', '>', $now_date)
            ->first();
        //1:h5(走url,其他都object_id)  2:商品  3:优惠券领取页面4精品课 5.讲座 6.听书 7 360  13活动开屏图
        $res = [];
        if (!empty($data)) {
            $res['id'] = $data->id;
            $res['obj_id'] = $data->obj_id;
            $res['info_id'] = $data->info_id;
            $res['type'] = $data->jump_type;
            $res['jump_type'] = $data->jump_type;
            $res['url'] = $data->url;
            $res['img'] = 'https://image.nlsgapp.com/' . $data->pic;
            $res['version'] = $data->version;
        }

        return $res;
    }

    public function cytxBanner()
    {
        $res['index'] = Banner::where('type', '=', 71)
            ->where('status', '=', 1)
            ->orderBy('rank', 'asc')
            ->orderBy('id', 'desc')
            ->select(['id', 'title', 'pic', 'url', 'jump_type', 'obj_id'])
            ->get();

        $res['home'] = Banner::where('type', '=', 72)
            ->where('status', '=', 1)
            ->orderBy('rank', 'asc')
            ->orderBy('id', 'desc')
            ->select(['id', 'title', 'pic', 'url', 'jump_type', 'obj_id'])
            ->get();


        foreach ( $res['index'] as &$v) {
            if ( $v['jump_type'] == 10) {
                $v['live'] = Live::teamInfo($v['obj_id'], 1);
            } else {
                $v['live'] = [];
            }
        }

        foreach ($res['home'] as &$v) {
            if ( $v['jump_type'] == 10) {
                $v['live'] = Live::teamInfo($v['obj_id'], 1);
            } else {
                $v['live'] = [];
            }
        }

//        if (empty($res['index'])) {
//            $res['index'] = [];
//        } else {
//            $res['index'] = $res['index']->toArray();
//        }
//
//        if (empty($res['home'])) {
//            $res['home'] = [];
//        } else {
//            $res['home'] = $res['home']->toArray();
//        }
//
//        foreach ($res as &$v) {
//            if ( $v['jump_type'] == 10) {
//                $v['live'] = Live::teamInfo($v['obj_id'], 1);
//            } else {
//                $v['live'] = [];
//            }
//        }

        return $res;

    }


    //获取banner
    public static function getBannerImg($type){



        $now_date = date('Y-m-d H:i:s');
        $data = Banner::where('type', '=', $type)
            ->where('status', '=', 1)
//            ->where('start_time', '<=', $now_date)
//            ->where('end_time', '>', $now_date)
            ->get()->toArray();
        return $data;
    }



    public function CheckBannerVersion($data,$version)
    {
        if( !empty($data['version']) ){
            if( $version < $data['version']){
                return [];
            }
        }
        unset($data['version']);
        return $data;
    }
}
