<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class MallSkuValue extends Base {

    protected $table = 'nlsg_mall_sku_value';

    //根据sku_number获取属性值列表
    public static function getListBySkuNum($sku_number) {

        $cache_key_name = 'sku_val_' . $sku_number;

        $expire_num = CacheTools::getExpire('sku_value');
        $res = Cache::get($cache_key_name);
        if (empty($res)) {
            $res = DB::table('nlsg_mall_sku as s')
                    ->join('nlsg_mall_sku_value as sv', 's.id', '=', 'sv.sku_id')
                    ->where('s.sku_number', '=', $sku_number)
                    ->where('s.status', '=', 1)
                    ->where('sv.status', '=', 1)
                    ->select(['sv.id', 'sv.key_name', 'sv.value_name'])
                    ->get();
            Cache::add($cache_key_name, $res, $expire_num);
        }
        return $res;
    }

}
