<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;

use Illuminate\Support\Facades\DB;

/**
 * Description of MallTosBind
 *
 * @author wangxh
 */
class MallTosBind extends Base {

    protected $table = 'nlsg_mall_goods_tos_bind';

    public static function getList($goods_id) {

        $list = DB::table('nlsg_mall_goods_tos_bind as tb')
                ->join('nlsg_mall_tos as t', 't.id', '=', 'tb.tos_id')
                ->where('tb.goods_id', '=', $goods_id)
                ->where('t.status', '=', 1)
                ->select(['title', 'content', 'icon'])
                ->get();

        return $list->toArray();
    }
    
    
        
    public function tos() {
        return $this->hasOne('App\Models\MallTos', 'id', 'tos_id')
                        ->where('status', '=', 1)
                        ->select(['title', 'content', 'icon','id']);
    }

}
