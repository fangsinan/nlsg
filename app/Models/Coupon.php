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
 * Description of Coupon
 *
 * @author wangxh
 */
class Coupon extends Model {

    protected $table = 'nlsg_coupon';

    static function getCouponMoney($coupon_id,$user_id,$price,$type=1){
        $data = Coupon::select()->where([
            'id'        => $coupon_id,
            'user_id'   => $user_id,
            'type'      => $type,
            'status'    => 1,
        ])->where('deadline','>=',time())
        ->where('fullcut_price','>=',$price)->first();
        return $data->money ?? 0;
    }
}
