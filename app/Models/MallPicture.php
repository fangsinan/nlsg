<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Description of MallPicture
 *
 * @author wangxh
 */
class MallPicture extends Base {

    protected $table = 'nlsg_mall_picture';

    public static function getList($goods_id) {

        $list = DB::table('nlsg_mall_picture')
                ->where('goods_id', '=', $goods_id)
                ->where('status', '=', 1)
                ->select(['id', 'url', 'is_main', 'is_video','duration'])
                ->orderBy('is_video', 'desc')
                ->orderBy('is_main', 'desc')
                ->orderBy('rank', 'asc')
                ->orderBy('id', 'asc')
                ->get();

        return $list->toArray();
    }

}
