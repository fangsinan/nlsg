<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;


use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Description of Coupon
 *
 * @author wangxh
 */
class Coupon extends Base {

    protected $table = 'nlsg_coupon';

    static function getCouponMoney($coupon_id, $user_id, $price, $type = 1) {
        $data = Coupon::select()->where([
                            'id' => $coupon_id,
                            'user_id' => $user_id,
                            'type' => $type,
                            'status' => 1,
                        ])->where('deadline', '>=', time())
                        ->where('fullcut_price', '>=', $price)->first();
        return $data->money ?? 0;
    }

    public function getCoupon($flag, $uid, $must_all_true = false) {

        if (!is_array($flag)) {
            $flag = explode(',', $flag);
            $flag = array_unique($flag);
        }

        $check_uid = User::find($uid)->toArray();
        if (empty($check_uid)) {
            return ['code' => false, 'msg' => '用户不存在'];
        }

        $res = self::getCouponRun($flag, $uid, $must_all_true);
        return $res;
    }

    protected static function getCouponRun($flag, $uid, $must_all_true) {

        $today_time = date('Y-m-d 00:00:00');
        $now = date('Y-m-d H:i:s');
        $coupon_rule_list = CouponRule::whereIn('id', $flag)
                ->where('status', '=', 1)
                ->whereIN('use_type', [3, 4])
                ->where('buffet', '=', 1)
                ->where('get_begin_time', '<=', $now)
                ->where('get_end_time', '>=', $now)
                ->get();

        if (count($flag) !== count($coupon_rule_list)) {
            return ['code' => false, 'msg' => '优惠券参数错误'];
        }

        foreach ($coupon_rule_list as $k => $v) {
            switch (intval($v->restrict)) {
                case 1:
                    //每人一张     
                    $had_count = Coupon::where('user_id', '=', $uid)
                            ->where('cr_id', '=', $v->id)
                            ->count();
                    if ($had_count >= 1) {
                        if ($must_all_true) {
                            return ['code' => false, 'msg' => '您已经领取过了'];
                        } else {
                            unset($coupon_rule_list[$k]);
                        }
                    }
                    break;
                case 2:
                    //活动时间段内每日领取一次
                    $had_count = Coupon::where('user_id', '=', $uid)
                            ->where('cr_id', '=', $v->id)
                            ->where('created_at', '>', $today_time)
                            ->count();
                    if ($had_count >= 1) {
                        if ($must_all_true) {
                            return ['code' => false, 'msg' => '您今天已经领取过了'];
                        } else {
                            unset($coupon_rule_list[$k]);
                        }
                    }
                    break;
                case 3:
                    //有个人数量上限(hold_max_num)
                    $had_count = Coupon::where('user_id', '=', $uid)
                            ->where('cr_id', '=', $v->id)
                            ->count();
                    if ($had_count >= $v->hold_max_num) {
                        if ($must_all_true) {
                            return ['code' => false, 'msg' => '您已经领取过了.'];
                        } else {
                            unset($coupon_rule_list[$k]);
                        }
                    }
                    break;
                default :
                    return ['code' => false, 'msg' => '优惠券参数错误'];
            }

            if ($v->infinite === 0) {
                if ($v->used_stock >= $v->stock) {
                    if ($must_all_true) {
                        return ['code' => false, 'msg' => '该优惠券没有库存'];
                    } else {
                        unset($coupon_rule_list[$k]);
                    }
                }
            }
        }

        DB::beginTransaction();
        $used_stock = true;
        $add_data = [];
        foreach ($coupon_rule_list as $v) {

            $temp_used_stock = DB::table('nlsg_coupon_rule')
                    ->where('id', '=', $v->id)
                    ->increment('used_stock');
            if (!$temp_used_stock) {
                $used_stock = false;
            }

            $data = [];
            $data['name'] = $v->name;
            $data['number'] = self::createCouponNum($v->buffet, $v->id);
            $data['type'] = $v->use_type;
            $data['price'] = $v->price;
            $data['full_cut'] = $v->full_cut;
            $data['explain'] = $v->remarks;
            if ($v->use_time_begin) {
                $data['begin_time'] = $v->use_time_begin;
            } else {
                $data['begin_time'] = $today_time;
            }
            if ($v->use_time_end) {
                $data['end_time'] = $v->use_time_end;
            } else {
                $data['end_time'] = date('Y-m-d H:i:s', strtotime($today_time) + ($v->past + 1) * 86400 - 1);
            }
            $data['get_way'] = 1;
            $data['user_id'] = $uid;
            $data['cr_id'] = $v->id;
            $add_data[] = $data;
        }
        if ($used_stock === false) {
            DB::rollBack();
            return ['code' => false, 'msg' => '领取失败'];
        }

        $add_res = DB::table('nlsg_coupon')->insert($add_data);

        if (!$add_res) {
            DB::rollBack();
            return ['code' => false, 'msg' => '领取失败'];
        }

        DB::commit();
        return ['msg' => '领取成功'];
    }

    protected static function createCouponNum($buffet, $id) {
        $res = date('YmdHis') . $buffet . str_pad($id, 6, 0) . random_int(100000, 999999);
        $check = Coupon::where('number', '=', $res)->count();
        if ($check) {
            self::createCouponNum($buffet, $id);
        } else {
            return $res;
        }
    }

}
