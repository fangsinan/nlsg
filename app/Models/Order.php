<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;

/**
 * Description of MallCategory
 *
 * @author wangxh
 */
class Order extends Base
{

    protected $table = 'nlsg_order';

    protected $fillable = [
        'ordernum', 'status', 'type', 'user_id', 'relation_id', 'cost_price', 'price', 'twitter_id', 'coupon_id', 'ip',
        'os_type', 'live_id', 'reward_type',
    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    //下单check
    public function addOrderCheck($user_id, $tweeter_code, $target_id, $type)
    {

        //校验用户等级
        $rst = User::getLevel($user_id);
        if ($rst > 2) {
            return ['code' => 0, 'msg' => '您已是vip用户,可免费观看'];
        }

        //校验下单用户是否关注
        $is_sub = Subscribe::isSubscribe($user_id, $target_id, $type);
        if ($is_sub) {
            return ['code' => 0, 'msg' => '您已订阅过'];
        }

        //校验推客信息有效
        $tweeter_level = User::getLevel($tweeter_code);
        if ($tweeter_level > 0) {
            //推客是否订阅
            $is_sub = Subscribe::isSubscribe($tweeter_code, $target_id, $type);
            if ($is_sub == 0) {
                $tweeter_code = 0;
            }
        } else {
            $tweeter_code = 0;
        }
        return ['code' => 1, 'tweeter_code' => $tweeter_code];

    }

}
