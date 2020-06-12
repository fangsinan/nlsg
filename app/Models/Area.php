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
 * Description of Area
 *
 * @author wangxh
 */
class Area extends Model {

    protected $table = 'nlsg_area';

    public static function getData() {
        if (0) {
            $cache_key_name = 'area_list';
            $expire_num = CacheTools::getExpire('area_list');
            $res = Cache::get($cache_key_name);
            if (empty($res)) {
                $res = self::getDataFromDb();
                Cache::add($cache_key_name, $res, $expire_num);
            }
        }
        $res = self::getDataFromDb();
        return $res;
    }

    public static function getDataFromDb() {
        $list = self::where('pid', '=', 0)
                ->with(['area_list', 'area_list.area_list'])
                ->select(['id', 'name', 'pid'])
                ->get();
        return $list;
    }

    public function area_list() {
        return $this->hasMany('App\Models\Area', 'pid', 'id')
                        ->select(['id', 'name', 'pid']);
    }

    

}
