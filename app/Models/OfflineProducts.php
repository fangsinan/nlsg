<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class OfflineProducts extends Base
{
    protected $table = 'nlsg_offline_products';


    static function search($keywords)
    {
        $res = OfflineProducts::select('id', 'title', 'subtitle', 'total_price', 'price', 'cover_img')
            ->where('is_del', 0)
            ->where(function ($query) use ($keywords) {
                $query->orWhere('title', 'LIKE', "%$keywords%");
                $query->orWhere('subtitle', 'LIKE', "%$keywords%");
            })->get();

        return ['res' => $res, 'count' => $res->count()];
    }

    /**
     * 直播首页线下课程
     * @return mixed
     */
    public function getIndexLists()
    {
        $cache_live_name = 'live_off_product';
        $offline = Cache::get($cache_live_name);
        if (empty($offline)){
            $offline = OfflineProducts::where('is_del', 0)
                      ->select('id', 'title', 'subtitle', 'total_price', 'price', 'cover_img')
                      ->orderBy('created_at', 'desc')
                      ->limit(3)
                      ->get()
                      ->toArray();
            $expire_num = CacheTools::getExpire('live_off_product');
            Cache::put($cache_live_name, $offline, $expire_num);
        }

        return $offline;

    }

    /**
     * getOfflineProducts get offline from ids
     * 
     * @return array $offline
     * */
    public function getOfflineProducts($ids=[]){
        if(empty($ids)){
            return [];
        }
        $fields = ['id','title','subtitle','describe','total_price','price','cover_img','image','video_url', 'off_line_pay_type','is_show','subscribe_num','user_id','cover_img as cover_images'];
        $offline = OfflineProducts::select($fields)
                      ->whereIn('id', $ids)
                      ->where([ 'type'=>3, 'is_del' => 0])
                      ->orderBy('created_at', 'desc')->get()->toArray();
        return $offline;
    }
}
