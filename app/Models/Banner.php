<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class Banner extends Model
{
    protected $table = 'nlsg_banner';

    /**
     * 首页Banner
     * @return mixed
     */
    public function getIndexBanner()
    {
        $lists = $this->select('id', 'pic','title','url')
            ->where('status', 1)
            ->orderBy('sort', 'desc')
            ->take(5)
            ->get()
            ->toArray();
        return $lists;
    }
    
    //商城banner数据
    public function mallBannerList(){
        $cache_key_name = 'mall_banner_list';
        $expire_num = CacheTools::getExpire('mall_banner_list');
        $res = Cache::get($cache_key_name);
        if (empty($res)) {
            $res = self::mallBannerListFromDb();
            Cache::add($cache_key_name, $res, $expire_num);
        }
        return $res;
    }
    
    public static function mallBannerListFromDb(){
        $banner_limit = ConfigModel::getData(4);
        $recommend_limit = ConfigModel::getData(5);
        //DB::connection()->enableQueryLog();
        $res['banner'] = Banner::where('type','=',51)
                ->where('status','=',1)
                ->orderBy('rank','asc')
                ->orderBy('id','desc')
                ->select(['id','title','pic','url'])
                ->limit($banner_limit)
                ->get();
        $res['recommend'] = Banner::where('type','=',52)
                ->where('status','=',1)
                ->orderBy('rank','asc')
                ->orderBy('id','desc')
                ->select(['id','title','pic','url'])
                ->limit($recommend_limit)
                ->get();
        $res['goods_list'] = MallGoodsList::where('show_index','=',1)
                ->where('status','=',1)
                ->orderBy('rank','asc')
                ->select(['id','name','icon'])
                ->limit(3)
                ->get();
        //dd(DB::getQueryLog());
        return $res;
    }
    
}
