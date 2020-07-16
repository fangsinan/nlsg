<?php

namespace App\servers;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Illuminate\Support\Facades\DB;
use App\Models\MallOrder;
use App\Models\MallOrderDetails as MD;
use App\Models\MallOrderChild as MC;
use App\Models\ExpressInfo as EI;
use App\Models\ExpressCompany as EC;
use Illuminate\Database\Eloquent\Builder;

/**
 * Description of MallOrder
 *
 * @author wangxh
 */
class MallOrderServers {

    public function getList($params) {
        $order_type = $params['order_type'] ?? 0;

        switch (strtolower($order_type)) {
            case 'group_buy':
                $res = $this->listOfGroupBy($params);
                break;
            default :
                $res = $this->listOfNormal($params);
        }

        return $res;
    }

    protected function listOfNormal($params) {
        $now = time();
        $now_date = date('Y-m-d H:i:s', $now);

        $size = $params['size'] ?? 10;
        //库数据:订单状态 1待付款  10待发货 20待收货 30已完成
        //列表tab栏:全部0,待付款1,待发货10,待签收20,已完成30,已取消99
        //展示数据:订单编号,状态,商品列表,价格,数量,取消时间,金额

        $query = MallOrder::from('nlsg_mall_order');

        if (!empty($params['ordernum'])) {
            $query->where('ordernum', 'like', '%' . $params['ordernum'] . '%');
        }

        //普通or秒杀
        if (($params['order_type'] ?? '') == 'flash_sale') {
            $query->where('order_type', '=', 2);
        } else {
            $query->where('order_type', '=', 1);
        }

        //时间,支付时间,支付渠道,客户端类型 created_at,pay_time,pay_type,os_type
        if (!empty($params['created_at'])) {
            $created_at = implode(',', $params['created_at']);
            $query->whereBetween('created_at', [$created_at[0], $created_at[1] ?? $now_date]);
        }
        if (!empty($params['pay_time'])) {
            $pay_time = implode(',', $params['pay_time']);
            $query->whereBetween('pay_time', [$pay_time[0], $pay_time[1] ?? $now_date]);
        }
        if (!empty($params['pay_type'])) {
            $query->where('pay_type', '=', $params['pay_type']);
        }
        if (!empty($params['os_type'])) {
            $query->where('os_type', '=', $params['os_type']);
        }

        //昵称,账号,
        if (!empty($params['phone'])) {
            $query->whereHas('userInfo', function(Builder $query) use ($params) {
                $query->where('phone', 'like', '%' . $params['phone'] . '%');
            });
        }
        if (!empty($params['nickname'])) {
            $query->whereHas('userInfo', function(Builder $query) use ($params) {
                $query->where('nickname', 'like', '%' . $params['nickname'] . '%');
            });
        }

        //商品名称
        if (!empty($params['goods_name'])) {
            $query->whereHas('orderDetails.goodsInfo', function(Builder $query) use ($params) {
                $query->where('name', 'like', '%' . $params['goods_name'] . '%');
            });
        }

        $query->where('order_type', '=', 1)->where('is_del', '=', 0);

        switch (intval($params['status'] ?? 0)) {
            case 1:
                $query->where('status', '=', 1);
                break;
            case 10:
                $query->where('status', '=', 10);
                break;
            case 20:
                $query->where('status', '=', 20);
                break;
            case 30:
                $query->where('status', '=', 30);
                break;
            case 99:
                $query->where('is_stop', '=', 1);
                break;
        }

        $field = [
            'id', 'ordernum', 'price', 'dead_time', 'user_id',
            DB::raw('(case when is_stop = 1 then 99 ELSE `status` END) `status`')
        ];
        $with = ['orderDetails', 'orderDetails.goodsInfo', 'userInfo'];

        if (($params['flag'] ?? 0) == 1) {
            $field[] = 'address_history';
            $field[] = 'cost_price';
            $field[] = 'freight';
            $field[] = 'vip_cut';
            $field[] = 'coupon_money';
            $field[] = 'special_price_cut';
            $field[] = 'price';
            $field[] = 'pay_time';
            $field[] = 'pay_type';
            $field[] = 'messages';
            $field[] = 'post_type';
            $field[] = 'bill_type';
            $field[] = 'bill_title';
            $field[] = 'bill_number';
            $field[] = 'bill_format';
            $with[] = 'orderChild';
            $with[] = 'expressInfo';
        }

        $query->whereRaw('(case when `status` = 1 AND dead_time < "' .
                $now_date . '" then FALSE ELSE TRUE END) ');

        $list = $query->with($with)->select($field)->paginate($size);

        foreach ($list as $v) {
            $v->goods_count = 0;
            foreach ($v->orderDetails as $vv) {
                $v->goods_count += $vv->num;
                $vv->sku_history = json_decode($vv->sku_history);
            }
            $v->address_history = json_decode($v->address_history);
        }

        return $list;
    }

    protected function listOfGroupBy($params) {
        $now = time();
        $now_date = date('Y-m-d H:i:s', $now);
        $size = $params['size'] ?? 10;
        //库数据:订单状态 1待付款  10待发货 20待收货 30已完成
        //全部0,拼团中95,待付款1,代发货10,待签收20,已完成30,已取消99
        //展示数据:订单编号,状态,商品列表,价格,数量,取消时间,金额

        $query = MallOrder::from('nlsg_mall_order as nmo')
                ->join('nlsg_mall_group_buy_list as gbl', 'nmo.id', '=', 'gbl.order_id');

        if (!empty($params['ordernum'])) {
            $query->where('nmo.ordernum', 'like', '%' . $params['ordernum'] . '%');
        }

        //时间,支付时间,支付渠道,客户端类型 created_at,pay_time,pay_type,os_type
        if (!empty($params['created_at'])) {
            $created_at = implode(',', $params['created_at']);
            $query->whereBetween('created_at', [$created_at[0], $created_at[1] ?? $now_date]);
        }
        if (!empty($params['pay_time'])) {
            $pay_time = implode(',', $params['pay_time']);
            $query->whereBetween('pay_time', [$pay_time[0], $pay_time[1] ?? $now_date]);
        }
        if (!empty($params['pay_type'])) {
            $query->where('pay_type', '=', $params['pay_type']);
        }
        if (!empty($params['os_type'])) {
            $query->where('os_type', '=', $params['os_type']);
        }

        //昵称,账号,
        if (!empty($params['phone'])) {
            $query->whereHas('userInfo', function(Builder $query) use ($params) {
                $query->where('phone', 'like', '%' . $params['phone'] . '%');
            });
        }
        if (!empty($params['nickname'])) {
            $query->whereHas('userInfo', function(Builder $query) use ($params) {
                $query->where('nickname', 'like', '%' . $params['nickname'] . '%');
            });
        }

        //商品名称
        if (!empty($params['goods_name'])) {
            $query->whereHas('orderDetails.goodsInfo', function(Builder $query) use ($params) {
                $query->where('name', 'like', '%' . $params['goods_name'] . '%');
            });
        }

        $query->where('nmo.order_type', '=', 3)->where('nmo.is_del', '=', 0);

        switch (intval($params['status'] ?? 0)) {
            case 1:
                $query->where('nmo.status', '=', 1);
                break;
            case 10:
                $query->where('nmo.status', '=', 10)
                        ->where('gbl.is_success', '=', 1);
                break;
            case 20:
                $query->where('nmo.status', '=', 20)
                        ->where('gbl.is_success', '=', 1);
                break;
            case 30:
                $query->where('nmo.status', '=', 30)
                        ->where('gbl.is_success', '=', 1);
                break;
            case 95:
                $query->where('nmo.status', '=', 10)
                        ->where('gbl.is_success', '=', 0);
                break;
            case 99:
                $query->where('nmo.is_stop', '=', 1);
                break;
        }

        $field = [
            'nmo.id', 'nmo.ordernum', 'nmo.price', 'nmo.dead_time', 'nmo.user_id',
            DB::raw('(case when nmo.`status` = 1 then 1
                when is_success = 0 then 95 when nmo.is_stop = 1
                then 99 ELSE nmo.`status` END) `status`')
        ];

        $with = ['orderDetails', 'orderDetails.goodsInfo', 'userInfo'];

        if (($params['flag'] ?? 0) == 1) {
            $field[] = 'address_history';
            $field[] = 'cost_price';
            $field[] = 'freight';
            $field[] = 'vip_cut';
            $field[] = 'coupon_money';
            $field[] = 'special_price_cut';
            $field[] = 'price';
            $field[] = 'pay_time';
            $field[] = 'pay_type';
            $field[] = 'messages';
            $field[] = 'post_type';
            $field[] = 'bill_type';
            $field[] = 'bill_title';
            $field[] = 'bill_number';
            $field[] = 'bill_format';
            $with[] = 'orderChild';
        }

        $query->whereRaw('(case when `status` = 1 AND dead_time < "' .
                $now_date . '" then FALSE ELSE TRUE END) ');

        $list = $query->with($with)->select($field)->paginate($size);

        foreach ($list as $v) {
            $v->goods_count = 0;
            foreach ($v->orderDetails as $vv) {
                $v->goods_count += $vv->num;
                $vv->sku_history = json_decode($vv->sku_history);
            }
            $v->address_history = json_decode($v->address_history);
        }

        return $list;
    }

    public function send($params) {

        $params = [
            ['order_id' => 9526, [
                    ['express_id' => 2, 'num' => 'express_num', 'order_detail_id' => [1, 2]],
                    ['express_id' => 2, 'num' => 'express_num', 'order_detail_id' => [3, 4]],
                ]
            ],
            ['order_id' => 9529, [
                    ['express_id' => 2, 'num' => 'express_num', 'order_detail_id' => [1, 2]],
                    ['express_id' => 2, 'num' => 'express_num', 'order_detail_id' => [3, 4]],
                ]
            ],
        ];

        if (empty($params)) {
            return ['code' => false, 'msg' => '参数错误'];
        }

        $order_id_list = array_column($params, 'order_id');

        $check_count = MallOrder::where('is_del', '=', 0)
                ->where('is_stop', '=', 0)
                ->where('status', '=', 10)
                ->whereIn('id', $order_id_list)
                ->count();

        if ($check_count !== count($order_id_list)) {
            return ['code' => false, 'msg' => '包含状态错误订单'];
        }
    }

}
