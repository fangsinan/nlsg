<?php

namespace App\Models;


use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class Banner extends Base
{

    protected $table = 'nlsg_banner';

    /**
     * 首页Banner
     * @return mixed
     */
    public function getIndexBanner()
    {
        return $this->select('id', 'pic', 'title', 'url')
            ->where('status', 1)
            ->orderBy('rank', 'desc')
            ->take(5)
            ->get()
            ->toArray();
    }

    //商城banner数据
    public function mallBannerList()
    {
        $cache_key_name = 'mall_banner_list';
        $expire_num = CacheTools::getExpire('mall_banner_list');
        $res = Cache::get($cache_key_name);
        if (empty($res)) {
            $res = self::mallBannerListFromDb();
            Cache::add($cache_key_name, $res, $expire_num);
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
            ->select(['id', 'title', 'pic', 'url'])
            ->limit($banner_limit)
            ->get();
        $res['recommend'] = Banner::where('type', '=', 52)
            ->where('status', '=', 1)
            ->orderBy('rank', 'asc')
            ->orderBy('id', 'desc')
            ->select(['id', 'title', 'pic', 'url'])
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
            ->select(['id', 'title', 'pic', 'url'])
            ->limit($recommend_limit)
            ->get();
        $res['postage_line'] = ConfigModel::getData(1);

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

}
