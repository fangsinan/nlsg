<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Description of Coupon
 *
 * @author wangxh
 */
class Coupon extends Base
{

    protected $table = 'nlsg_coupon';

    protected $fillable = [
        'name', 'number', 'type', 'price', 'money', 'fullcut_price', 'explain', 'begin_time', 'end_time', 'get_way', 'user_id', 'cr_id'
    ];

    static function getCouponMoney($coupon_id, $user_id, $price, $type = 1)
    {
        $data = Coupon::select()->where([
            'id' => $coupon_id,
            'user_id' => $user_id,
            'type' => $type,
            'status' => 1,
        ])->where('end_time', '>=', date("Y-m-d H:i:s", time()))
            ->where('full_cut', '<=', $price)->first();
        return $data->price ?? 0;
    }

    public function getCoupon($flag, $uid, $must_all_true = false, $get_info = 0)
    {

        if (!is_array($flag)) {
            $flag = explode(',', $flag);
            $flag = array_unique($flag);
        }

        $check_uid = User::find($uid);
        if (!$check_uid) {
            return ['code' => false, 'msg' => '用户不存在'];
        }

        if (count($flag) === 1) {
            $must_all_true = true;
        }

        return self::getCouponRun($flag, $uid, $must_all_true, $get_info);
    }

    protected static function getCouponRun($flag, $uid, $must_all_true, $get_info = 0)
    {

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
                if ($v->used_stock - $v->stock >= 0) {
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
            $data['created_at'] = $data['updated_at'] = $now;
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
        return ['code' => true, 'msg' => '领取成功'];
    }

    public static function createCouponNum($buffet, $id)
    {
        $res = date('YmdHis') . $buffet . str_pad($id, 6, 0) . random_int(100000, 999999);
        $check = Coupon::where('number', '=', $res)->count();
        if ($check) {
            self::createCouponNum($buffet, $id);
        } else {
            return $res;
        }
    }

    public function sub_list()
    {
        return $this->hasMany('App\Models\CouponRuleSub', 'rule_id', 'cr_id')
            ->select(['id', 'rule_id', 'use_type', 'goods_id']);
    }

    public static function getCouponListForOrder($uid, $money = 0, $goods_id_list = [])
    {
        $now_date = date('Y-m-d H:i:s');

        $coupon_goods = [];
        $coupon_freight = [];

        $temp_res = self::where('user_id', '=', $uid)
            ->where('status', '=', 1)
            ->whereIn('type', [3, 4])
            ->where('order_id', '=', 0)
            ->where('begin_time', '<=', $now_date)
            ->where('end_time', '>', $now_date)
            ->select(['id', 'name', 'type', 'price', 'full_cut',
                'explain as remarks', 'cr_id', 'begin_time', 'end_time',
                DB::raw('unix_timestamp(begin_time) as begin_timestamp'),
                DB::raw('unix_timestamp(end_time) as end_timestamp'),
            ])
            ->with(['sub_list'])
            ->orderBy('end_time', 'asc')
            ->orderBy('id', 'asc')
            ->get();
        if ($temp_res->isEmpty()) {
            $temp_res = [];
        } else {
            $temp_res = $temp_res->toArray();
        }

        if (empty($temp_res)) {
            return [
                'coupon_goods' => $coupon_goods,
                'coupon_freight' => $coupon_freight
            ];
        }

        foreach ($temp_res as $k => $v) {
            if ($v['type'] == 3 && !empty($v['sub_list'])) {
                $del_flag = 1;
                foreach ($v['sub_list'] as $vv) {
                    if ($vv['use_type'] === 1) {
                        if (in_array($vv['goods_id'], $goods_id_list)) {
                            $del_flag = 0;
                        }
                    }
                }
                if ($del_flag) {
                    unset($temp_res[$k]);
                }
            }
        }

        foreach ($temp_res as $v) {
            if ($v['type'] == 3) {
                //商品优惠券
                if ($money === 0) {
                    $coupon_goods[] = $v;
                } else {
                    if ($money >= $v['full_cut']) {
                        $coupon_goods[] = $v;
                    }
                }
            } else {
                //免邮券
                $coupon_freight[] = $v;
            }
        }

        return [
            'coupon_goods' => $coupon_goods,
            'coupon_freight' => $coupon_freight
        ];
    }

    public function listInHome($user_id, $params)
    {

        $status = intval($params['status'] ?? 1);
        $page = intval($params['page'] ?? 1);
        $size = intval($params['size'] ?? 10);

        $now_date = date('Y-m-d H:i:s');

        $count['status_1'] = self::where('user_id', '=', $user_id)
            ->where('end_time', '>', $now_date)
            ->whereIn('type', [1, 2, 3, 4, 5, 6])
            ->where('status', '=', 1)
            ->count();

        $count['status_2'] = self::where('user_id', '=', $user_id)
            ->where('status', '=', 2)
            ->whereIn('type', [1, 2, 3, 4, 5, 6])
            ->count();

        $count['status_3'] = self::where('user_id', '=', $user_id)
            ->Where(function ($query) {
                $query->where('status', '=', 3)
                    ->orWhere('end_time', '<=', date('Y-m-d H:i:s'));
            })
            ->whereIn('type', [1, 2, 3, 4, 5, 6])
            ->count();

        $query = self::where('user_id', '=', $user_id)
            ->whereIn('type', [1, 2, 3, 4, 5, 6]);

        switch ($status) {
            case 2:
                $query->where('status', '=', 2);
                break;
            case 3:
                $query->Where(function ($query) {
                    $query->where('status', '=', 3)
                        ->orWhere('end_time', '<=', date('Y-m-d H:i:s'));
                });
                break;
            default :
                $query->where('end_time', '>', $now_date)->where('status', '=', 1);
        }

        $query->select([
            'id', 'number', 'name', 'type', 'price', 'full_cut',
            'explain', 'begin_time', 'end_time'
        ]);

        $list = $query->limit($size)->offset(($page - 1) * $size)->get();

        return ['count' => $count, 'list' => $list];
    }

    public function giveCoupon($user_id, $cid)
    {
        if (!$user_id || !$cid) {
            return error(1000, '参数无效');
        }

        $rule = CouponRule::where('id', $cid)->first();
        if (!$rule) {
            return error(1000, '规则不存在');
        }

        $created_at = User::where('id', $user_id)->value('created_at');
        $res = Coupon::create([
            'name' => $rule->name,
            'number' => self::createCouponNum($rule->buffet, $cid),
            'type' => $rule->use_type,
            'price' => $rule->price,
            'full_cut' => $rule->full_cut,
            'explain' => $rule->remarks,
            'begin_time' => date('Y-m-d H:i:s', strtotime($created_at)),
            'end_time' => date('Y-m-d H:i:s', strtotime('+1 month', strtotime($created_at))),
            'get_way' => 1,
            'user_id' => $user_id,
            'cr_id' => $cid
        ]);

        return success($res);
    }

    //清理过期优惠券(定时)
    public static function clear()
    {
        Coupon::where('status', '=', 1)
            ->whereRaw('end_time < now()')
            ->update([
                'status' => 3
            ]);
    }

}
