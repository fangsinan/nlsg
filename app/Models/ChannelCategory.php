<?php


namespace App\Models;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ChannelCategory extends Base
{

    protected $table = 'nlsg_channel_category';

    public function getCategoryList($channel_type = 1)
    {

        $expire_num = CacheTools::getExpire('channel_category_expire');
        $cache_key_name = 'channel_category_' . $channel_type;

        $res = Cache::get($cache_key_name);
        if (empty($res)) {
            $res = DB::table('nlsg_channel_works_list as wl')
                ->join('nlsg_channel_category_bind as cb', 'wl.id', '=', 'cb.works_list_id')
                ->join('nlsg_channel_category as cc', 'cb.category_id', '=', 'cc.id')
                ->where('wl.channel_type', '=', $channel_type)
                ->groupBy('cb.category_id')
                ->select(['cb.category_id', 'cc.name as category_name'])
                ->get();
            Cache::put($cache_key_name, $res, $expire_num);
        }
        return $res;
    }

}
