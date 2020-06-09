<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

/**
 * Description of MallCategory
 *
 * @author wangxh
 */
class MallCategory extends Model {

    protected $table = 'nlsg_mall_category';

    public static function getUsedListFromDb() {
        $res = DB::table('nlsg_mall_category as nmc')
                ->join('nlsg_mall_goods as nmg', 'nmc.id', '=', 'nmg.category_id')
                ->where('nmc.status', '=', 1)
                ->groupBy('nmc.id')
                ->select(['nmc.id', 'nmc.name'])
                ->orderBy('rank', 'asc')
                ->orderBy('id', 'asc')
                ->get();
        return $res;
    }

    public function getUsedList() {
        $cache_key_name = 'goods_category_list';
        $expire_num = CacheTools::getExpire('goods_category_list');
        $res = Cache::get($cache_key_name);
        if (empty($res)) {
            $res = self::getUsedListFromDb();
            Cache::add($cache_key_name, $res, $expire_num);
        }
        return $res;
    }

}
