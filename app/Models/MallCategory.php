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
class MallCategory extends Base
{

    protected $table = 'nlsg_mall_category';

    public static function getUsedListFromDb()
    {
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

    public function getUsedList()
    {
        $cache_key_name = 'goods_category_list';
        $expire_num = CacheTools::getExpire('goods_category_list');
        $res = Cache::get($cache_key_name);
        if (empty($res)) {
            $res = self::getUsedListFromDb();
            Cache::add($cache_key_name, $res, $expire_num);
        }
        return $res;
    }

    public function getAllList()
    {
        $list = MallCategory::whereIn('status', [1, 2])
            ->select(['id', 'name', 'level', 'pid', 'status'])
            ->orderBY('rank', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $res = [];
        foreach ($list as $k => $v) {
            if (!isset($v->list)) {
                $list[$k]->list = [];
            }
            if ($v->pid == 0) {
                $res[] = $v;
                unset($list[$k]);
            }
        }

        foreach ($res as $rv) {
            foreach ($list as $k => $v) {
                if ($v->level == 2 && $v->pid == $rv->id) {
                    $temp = $rv->list;
                    $temp[] = $v;
                    $rv->list = $temp;
                    unset($list[$k]);
                }
            }

            foreach ($rv->list as $rvv) {
                foreach ($list as $k => $v) {
                    if ($v->level == 3 && $v->pid = $rvv->id) {
                        $temp = $rvv->list;
                        $temp[] = $v;
                        $rvv->list = $temp;
                        unset($list[$k]);
                    }
                }
            }
        }

        return [$res, $list];
    }

}
