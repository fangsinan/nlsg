<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;

use Illuminate\Support\Facades\DB;

/**
 * Description of MallGroupBuyList
 *
 * @author wangxh
 */
class MallGroupBuyList extends Base {

    protected $table = 'nlsg_mall_group_buy_list';

    //检查拼团key是否过期    
    public static function checkGroupKeyCanUse($key) {
        $now_date = date('Y-m-d H:i:s', time());
        $check = self::where('group_key', '=', $key)
                ->where('begin_at', '<=', $now_date)
                ->where('end_at', '>=', $now_date)
                ->where('is_captain', '=', 1)
                ->first();

        if ($check->isEmpty()) {
            return ['code' => false, 'msg' => '活动不存在'];
        } else {
            return true;
        }
    }

}
