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
class Order extends Base {

    protected $table = 'nlsg_order';

    protected $fillable = ['ordernum' , 'type' , 'user_id' , 'relation_id' , 'cost_price'  , 'price' , 'twitter_id'  , 'coupon_id' , 'ip'  , 'os_type'  , 'live_id' ,];


    public function user()
    {
        return  $this->belongsTo(User::class, 'user_id', 'id');
    }
}
