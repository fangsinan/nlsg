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
    public static function checkGroupKeyCanUse($key, $user_id) {
        $now_date = date('Y-m-d H:i:s', time());
        $check = self::where('group_key', '=', $key)
                        ->where('begin_at', '<=', $now_date)
                        ->where('end_at', '>=', $now_date)
                        ->get()->toArray();
        if (empty($check)) {
            return ['code' => false, 'msg' => '活动不存在'];
        } else {
            $user_id_list = array_column($check, 'user_id');
            if (in_array($user_id, $user_id_list)) {
                return ['code' => false, 'msg' => '已经参加了该次拼团'];
            } else {
                return true;
            }
        }
    }

    public function teamOrderCount() {
        return $this->hasOne('App\Models\MallGroupBuyList', 'group_key', 'group_key')
                        ->select([DB::raw('count(1) counts'), 'group_key'])->groupBy('group_key');
    }

    public function userInfo() {
        return $this->hasOne('App\Models\User', 'id', 'user_id')
            ->select(['id','phone','headimg']);
    }

    public function spInfo(){
        return $this->hasOne(SpecialPriceModel::class,'id','group_buy_id')
            ->select(['id','group_num']);
    }
}
