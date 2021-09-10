<?php

namespace App\Servers;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use App\Models\ExpressCompany;
use App\Models\ExpressInfo;
use App\Models\MallGoods;
use App\Models\MallGroupBuyList;
use App\Models\MallOrder;
use App\Models\MallOrderChild;
use App\Models\MallOrderDetails;
use App\Models\SpecialPriceModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Description of MallOrder
 *
 * @author wangxh
 */
class MallOrderServers
{

    public function listNew($params, $user_id = 0)
    {
        $query = MallOrder::query();
        $size = $params['size'] ?? 10;

        $query->where('status', '>', 1);

        //订单类型
        $order_type = $params['order_type'] ?? '';

        switch ((string)$order_type) {
            case '2':
            case 'flash_sale':
                $query->where('order_type', '=', 2);
                break;
            case '3':
            case 'group_buy':
                $query->where('order_type', '=', 3);
                break;
            case '1':
            case 'normal':
                $query->where('order_type', '=', 1);
                break;
        }

        if (!empty($params['user_id'] ?? 0)) {
            $query->where('user_id', '=', $params['user_id']);
        }

        if (!empty($params['id'])) {
            $query->where('id', '=', intval($params['id']));
        }

        if (!empty($params['ordernum'])) {
            $query->where('ordernum', 'like', '%' . $params['ordernum'] . '%');
        }

        $field = [
            'id', 'ordernum', 'dead_time', 'created_at',
            'pay_price', 'price', 'post_type', 'pay_type',
            'normal_cut', 'user_id', 'order_type', 'gp_status',
            DB::raw('(case order_type when 1 then "普通" when  2 then "秒杀"
            when  3 then "拼团" else "数据错误" end) as order_type_name'),
            DB::raw('(IF(is_stop=1,99,(IF(STATUS=1,1,(IF(gp_status=1,95,STATUS)))))) AS search_status'),
            DB::raw('(IF(is_stop=1,"已取消",(IF(STATUS=1,"待付款",(IF(gp_status=1,"拼团中",
            (IF(STATUS=10,"待发货",(IF(STATUS=20,"待收货","已完成")))))))))) AS search_status_name'),
        ];
        //订单状态 1待付款  10待发货 20待收货 30已完成 95拼团中 99已取消

        // status : 订单状态 1待付款  10待发货 20待收货 30已完成
        // gp_status : 补充状态,用于简化拼团订单状态筛选  1拼团中  2拼团成功 3拼团失败

        switch (intval($params['status'] ?? 0)) {
            case 1:
                $query->where('status', '=', 1)
                    ->where('is_stop', '=', 0);
                break;
            case 10:
                $query->where('status', '=', 10)
                    ->where('is_stop', '=', 0)
                    ->where(function ($q) {
                        $q->whereRaw('order_type = 3 and gp_status = 2')
                            ->orWhereRaw('order_type <> 3');
                    });
                break;
            case 20:
                $query->where('status', '=', 20)
                    ->where('is_stop', '=', 0);
                break;
            case 30:
                $query->where('status', '=', 30)
                    ->where('is_stop', '=', 0);
                break;
            case 95:
                $query->where('order_type', '=', 3)
                    ->where('gp_status', '=', 1)
                    ->where('status', '=', 10)
                    ->where('is_stop', '=', 0);
                break;
            case 99:
                $query->where('is_stop', '=', 1);
                break;
        }

        $query->where('is_del', '=', 0)
            ->with([
                'orderDetails', 'orderDetails.goodsInfo',
                'userInfo',
                'orderChild', 'orderChild.expressInfo',
                'refundRecord' => function ($q) {
                    $q->select([
                        'id', 'service_num', 'order_id', 'order_detail_id', 'type'
                    ]);
                }
            ])
            ->has('userInfo')
            ->has('orderDetails');


//        DB::connection()->enableQueryLog();
        $query->select($field);


//        $list = $query->orderBy('id', 'desc')->limit(10)->get();
        $list = $query->orderBy('id', 'desc')->paginate($size);
//        return DB::getQueryLog();

        foreach ($list as &$v) {
            if (count($v->refundRecord) == 0) {
                $v->refund_type_name = '无售后';
            } else {
                $v->refund_type_name = '有售后';
            }
            foreach ($v->orderDetails as &$vv) {
                $vv['sku_history'] = json_decode($vv['sku_history'], true);
            }
        }

        return $list;
    }

    public function getList($params)
    {
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

    public function makeGroupSuccess($params)
    {
        $order_id = $params['id'] ?? 0;
        if (empty($order_id)) {
            return ['code' => false, 'msg' => 'id错误'];
        }

        $check_group = MallGroupBuyList::where('order_id', '=', $order_id)->first();
        if (empty($check_group)) {
            return ['code' => false, 'msg' => 'id错误'];
        }
        if ($check_group->is_success == 1 || $check_group->is_fail == 1) {
            return ['code' => false, 'msg' => '状态错误,无法操作.'];
        }

        $now_date = date('Y-m-d H:i:s');
        $group_key = $check_group->group_key;
        $group_name = $check_group->group_name;

        //拼团活动的详情
        $price_info = SpecialPriceModel::where('group_name', '=', $group_name)
            ->where('type', '=', 4)
            ->first();

        //计算已有订单数量
        $count = DB::table('nlsg_mall_group_buy_list as gb')
            ->join('nlsg_mall_order as o', 'gb.order_id', '=', 'o.id')
            ->where('gb.group_key', '=', $group_key)
            ->where('o.status', '>', 1)
            ->where('o.is_stop', '=', 0)
            ->count();

        //需要虚拟生成的数量
        $add_count = $price_info->group_num - $count;
        if ($add_count < 1) {
            return ['code' => false, 'msg' => '数据错误'];
        }

        $robot = User::where('is_robot', '=', 1)
            ->select(['id'])
            ->orderByRaw('rand()')
            ->limit($add_count)
            ->get()->toArray();

        DB::beginTransaction();

        $tr_flag = true;
        foreach ($robot as $v) {
            $oModel = new MallOrder();
            $oModel->ordernum = MallOrder::createOrderNumber($v['id'], 1);
            $oModel->user_id = $v['id'];
            $oModel->order_type = 3;
            $oModel->status = 30;
            $oModel->gp_status = 2;
            $oModel->cost_price = $price_info->group_price;
            $oModel->price = $price_info->group_price;
            $oModel->pay_price = $price_info->group_price;
            $oModel->pay_time = $now_date;
            $o_res = $oModel->save();
            if ($o_res === false) {
                $tr_flag = false;
                continue;
            }
            $gModel = new MallGroupBuyList();
            $gModel->group_buy_id = $check_group->group_buy_id;
            $gModel->group_key = $check_group->group_key;
            $gModel->group_name = $check_group->group_name;
            $gModel->order_id = $oModel->id;
            $gModel->created_at = $now_date;
            $gModel->updated_at = $now_date;
            $gModel->user_id = $v['id'];
            $gModel->begin_at = $check_group->begin_at;
            $gModel->end_at = $check_group->end_at;
            $g_res = $gModel->save();
            if ($g_res === false) {
                $tr_flag = false;
                continue;
            }
        }


        $update_res = MallGroupBuyList::where('group_key', '=', $group_key)
            ->where('is_fail', '=', 0)
            ->update([
                'is_success' => 1,
                'success_at' => $now_date
            ]);

        $order_id_list = MallGroupBuyList::where('group_key', '=', $group_key)
            ->where('is_success', '=', 1)
            ->pluck('order_id')
            ->toArray();

        MallOrder::whereIn('id', $order_id_list)
            ->update([
                'gp_status' => 2
            ]);

        if ($update_res === false) {
            $tr_flag = false;
        }

        MallGoods::where('id', '=', $price_info->goods_id)->increment('sales_num_virtual', $add_count);

        if ($tr_flag === false) {
            DB::rollBack();
            return ['code' => false, 'msg' => '失败'];
        } else {
            DB::commit();
            return ['code' => true, 'msg' => '成功'];
        }
    }

    protected function listOfNormal($params)
    {
        $now = time();
        $now_date = date('Y-m-d H:i:s', $now);

        $size = $params['size'] ?? 10;
        //库数据:订单状态 1待付款  10待发货 20待收货 30已完成
        //列表tab栏:全部0,待付款1,待发货10,待签收20,已完成30,已取消99
        //展示数据:订单编号,状态,商品列表,价格,数量,取消时间,金额

        $query = MallOrder::from('nlsg_mall_order');

        if (!empty($params['id'])) {
            $query->where('id', '=', intval($params['id']));
        }

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
            $created_at = explode(',', $params['created_at']);
            $created_at[0] = date('Y-m-d 00:00:00', strtotime($created_at[0]));
            if (empty($created_at[1] ?? '')) {
                $created_at[1] = $now_date;
            } else {
                $created_at[1] = date('Y-m-d 23:59:59', strtotime($created_at[1]));
            }
            $query->whereBetween('created_at', [$created_at[0], $created_at[1] ?? $now_date]);
        }
        if (!empty($params['pay_time'])) {
            $pay_time = explode(',', $params['pay_time']);
            $pay_time[0] = date('Y-m-d 00:00:00', strtotime($pay_time[0]));
            if (empty($pay_time[1] ?? '')) {
                $pay_time[1] = $now_date;
            } else {
                $pay_time[1] = date('Y-m-d 23:59:59', strtotime($pay_time[1]));
            }
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
            $query->whereHas('userInfo', function (Builder $query) use ($params) {
                $query->where('phone', 'like', '%' . $params['phone'] . '%');
            });
        }
        if (!empty($params['nickname'])) {
            $query->whereHas('userInfo', function (Builder $query) use ($params) {
                $query->where('nickname', 'like', '%' . $params['nickname'] . '%');
            });
        }

        //商品名称
        if (!empty($params['goods_name'])) {
            $query->whereHas('orderDetails.goodsInfo', function (Builder $query) use ($params) {
                $query->where('name', 'like', '%' . $params['goods_name'] . '%');
            });
        }

        $query->where('is_del', '=', 0);

        switch (intval($params['status'] ?? 0)) {
            case 1:
                $query->where('status', '=', 1)->where('is_stop', '=', 0);
                break;
            case 10:
                $query->where('status', '=', 10)->where('is_stop', '=', 0);
                break;
            case 20:
                $query->where('status', '=', 20)->where('is_stop', '=', 0);
                break;
            case 30:
                $query->where('status', '=', 30);
                break;
            case 99:
                $query->where('is_stop', '=', 1);
                break;
        }

        $field = [
            'id', 'ordernum', 'price', 'dead_time', 'user_id', 'order_type', 'pay_price', 'messages', 'created_at',
            DB::raw('(case when is_stop = 1 then 99 ELSE `status` END) `status`'), 'address_history'
        ];
        $with = ['orderDetails', 'orderDetails.goodsInfo', 'userInfo', 'refundRecord'];

//        if (($params['flag'] ?? 0) == 1) {
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
        $with[] = 'orderChild.expressInfo';
//        }

//        $query->whereRaw('(case when `status` = 1 AND dead_time < "' .
//            $now_date . '" then FALSE ELSE TRUE END) ');

        $query->orderBy('id', 'desc');

        $list = $query->with($with)->select($field)->paginate($size);

        foreach ($list as $v) {
            $v->goods_count = 0;
            foreach ($v->orderDetails as $vv) {
                $v->goods_count += $vv->num;
                $vv->sku_history = json_decode($vv->sku_history);
            }
            $v->address_history = json_decode($v->address_history);

            foreach ($v->orderChild as $cv) {

            }
        }

        return $list;
    }

    protected function listOfGroupBy($params)
    {
        $now = time();
        $now_date = date('Y-m-d H:i:s', $now);
        $size = $params['size'] ?? 10;
        //库数据:订单状态 1待付款  10待发货 20待收货 30已完成
        //全部0,拼团中95,待付款1,代发货10,待签收20,已完成30,已取消99
        //展示数据:订单编号,状态,商品列表,价格,数量,取消时间,金额

        $query = MallOrder::from('nlsg_mall_order as nlsg_mall_order')
            ->join('nlsg_mall_group_buy_list as gbl', 'nlsg_mall_order.id', '=', 'gbl.order_id');

        if (!empty($params['id'])) {
            $query->where('nlsg_mall_order.id', '=', intval($params['id']));
        }

        if (!empty($params['ordernum'])) {
            $query->where('nlsg_mall_order.ordernum', 'like', '%' . $params['ordernum'] . '%');
        }

        $query->whereHas('userInfo', function (Builder $query) use ($params) {
            $query->where('is_robot', '=', 0);
        });

        //时间,支付时间,支付渠道,客户端类型 created_at,pay_time,pay_type,os_type
        if (!empty($params['created_at'])) {
            $created_at = explode(',', $params['created_at']);
            $created_at[0] = date('Y-m-d 00:00:00', strtotime($created_at[0]));
            if (empty($created_at[1] ?? '')) {
                $created_at[1] = $now_date;
            } else {
                $created_at[1] = date('Y-m-d 23:59:59', strtotime($created_at[1]));
            }
            $query->whereBetween('nlsg_mall_order.created_at', [$created_at[0], $created_at[1] ?? $now_date]);
        }
        if (!empty($params['pay_time'])) {
            $pay_time = explode(',', $params['pay_time']);
            $pay_time[0] = date('Y-m-d 00:00:00', strtotime($pay_time[0]));
            if (empty($pay_time[1] ?? '')) {
                $pay_time[1] = $now_date;
            } else {
                $pay_time[1] = date('Y-m-d 23:59:59', strtotime($pay_time[1]));
            }
            $query->whereBetween('nlsg_mall_order.pay_time', [$pay_time[0], $pay_time[1] ?? $now_date]);

        }
        if (!empty($params['pay_type'])) {
            $query->where('pay_type', '=', $params['pay_type']);
        }
        if (!empty($params['os_type'])) {
            $query->where('os_type', '=', $params['os_type']);
        }

        //昵称,账号,
        if (!empty($params['phone'])) {
            $query->whereHas('userInfo', function (Builder $query) use ($params) {
                $query->where('phone', 'like', '%' . $params['phone'] . '%');
            });
        }

        if (!empty($params['nickname'])) {
            $query->whereHas('userInfo', function (Builder $query) use ($params) {
                $query->where('nickname', 'like', '%' . $params['nickname'] . '%');
            });
        }

        //商品名称
        if (!empty($params['goods_name'])) {
            $query->whereHas('orderDetails.goodsInfo', function (Builder $query) use ($params) {
                $query->where('name', 'like', '%' . $params['goods_name'] . '%');
            });
        }

        $query->where('nlsg_mall_order.order_type', '=', 3)->where('nlsg_mall_order.is_del', '=', 0);

        switch (intval($params['status'] ?? 0)) {
            case 1:
                $query->where('nlsg_mall_order.status', '=', 1)
                    ->where('nlsg_mall_order.is_stop', '=', 0)
                    ->where('gbl.is_fail', '=', 0);
                break;
            case 10:
                $query->where('nlsg_mall_order.status', '=', 10)
                    ->where('nlsg_mall_order.is_stop', '=', 0)
                    ->where('gbl.is_success', '=', 1)
                    ->where('gbl.is_fail', '=', 0);
                break;
            case 20:
                $query->where('nlsg_mall_order.status', '=', 20)
                    ->where('nlsg_mall_order.is_stop', '=', 0)
                    ->where('gbl.is_success', '=', 1)
                    ->where('gbl.is_fail', '=', 0);
                break;
            case 30:
                $query->where('nlsg_mall_order.status', '=', 30)
                    ->where('nlsg_mall_order.is_stop', '=', 0)
                    ->where('gbl.is_success', '=', 1)
                    ->where('gbl.is_fail', '=', 0);
                break;
            case 95:
                $query->where('nlsg_mall_order.status', '=', 10)
                    ->where('nlsg_mall_order.is_stop', '=', 0)
                    ->where('gbl.is_success', '=', 0)
                    ->where('gbl.is_fail', '=', 0);
                break;
            case 99:
                //$query->where('nlsg_mall_order.is_stop', '=', 1);
                $query->where(function ($query) {
                    $query->orWhere('nlsg_mall_order.is_stop', '=', 1)
                        ->orWhere('gbl.is_fail', '=', 1);
                });
                break;
        }

        $field = [
            'nlsg_mall_order.id', 'nlsg_mall_order.ordernum', 'nlsg_mall_order.price', 'nlsg_mall_order.pay_price',
            'nlsg_mall_order.messages', 'nlsg_mall_order.created_at',
            'nlsg_mall_order.dead_time', 'nlsg_mall_order.user_id', DB::raw('3 as order_type'),
            DB::raw('(CASE
				WHEN (nlsg_mall_order.STATUS = 1 AND is_success = 0 AND is_stop = 0 ) THEN 1
				WHEN (is_success = 0 AND is_fail = 0 AND nlsg_mall_order.STATUS > 1 AND is_stop = 0 ) THEN 95
				WHEN (is_success = 1 AND is_fail = 0 AND nlsg_mall_order.STATUS > 1 AND is_stop = 0 ) THEN nlsg_mall_order.`status`
				WHEN is_fail = 1 THEN 99
				WHEN is_stop = 1 THEN 99
                ELSE nlsg_mall_order.status
				END ) status')
        ];

        $with = ['orderDetails', 'orderDetails.goodsInfo', 'userInfo', 'refundRecord'];

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

        $query->orderBy('nlsg_mall_order.id', 'desc');

//        DB::connection()->enableQueryLog();
//        $query->select($field)->get();
//        dd(DB::getQueryLog());

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

    public function send($params)
    {
        if (empty($params)) {
            return ['code' => false, 'msg' => '参数错误'];
        }

        $order_id_list = array_unique(array_column($params, 'order_id'));

        $check_count = MallOrder::where('is_del', '=', 0)
            ->where('is_stop', '=', 0)
            ->whereIn('status', [10, 20])
            ->whereIn('id', $order_id_list)->count();

        if ($check_count !== count($order_id_list)) {
            return ['code' => false, 'msg' => '包含状态错误订单'];
        }

        $now = time();
        $now_date = date('Y-m-d H:i:s', $now);

        DB::beginTransaction();

        foreach ($params as $v) {
            //检查order_id和order_detail_id是否匹配
            $check_od_id = MallOrderDetails::whereId($v['order_detail_id'])
                ->where('order_id', '=', $v['order_id'])
                ->first();
            if (empty($check_od_id)) {
                DB::rollBack();
                return ['code' => false, 'msg' => '订单id不匹配', 'ps' => ''];
            }

            //只要有发货的  order状态就是已发货
            $order_obj = MallOrder::find($v['order_id']);
            $order_obj->status = 20;
            $order_res = $order_obj->save();
            if (!$order_res) {
                DB::rollBack();
                return ['code' => false, 'msg' => '错误', 'ps' => $v['order_id'] . ' order error'];
            }
            //校验发货订单表,快递公司和快递单号不重复
            $check_ex = ExpressInfo::where('express_id', '=', $v['express_id'])
                ->where('express_num', '=', $v['num'])
                ->first();
            if ($check_ex) {
                $express_info_id = $check_ex->id;
            } else {
                //$c_res = DB::table('nlsg_mall_comment')->insertGetId($c_data);
                $ex_data['express_id'] = $v['express_id'];
                $ex_data['express_num'] = $v['num'];

                $express_company_info = ExpressCompany::find($v['express_id']);
                $history = [];
                $history['number'] = $v['num'];
                $history['type'] = $express_company_info->code;
                $history['typename'] = $express_company_info->name;
                $history['express_phone'] = $express_company_info->phone;
                $history['logo'] = $express_company_info->logo;
                $history['list'] = [
                    [
                        'time' => $now_date,
                        'status' => '商家发货'
                    ]
                ];

                $ex_data['history'] = json_encode($history);

                $ex_data['created_at'] = $ex_data['updated_at'] = $now_date;
                $express_info_id = DB::table('nlsg_express_info')->insertGetId($ex_data);
                if (!$express_info_id) {
                    DB::rollBack();
                    return ['code' => false, 'msg' => '错误', 'ps' => $v['order_id'] . ' ex error'];
                }
            }
            //order_detail发货
            $check_oc = MallOrderChild::where('order_id', '=', $v['order_id'])
                ->where('order_detail_id', '=', $v['order_detail_id'])
                ->first();
            if ($check_oc) {
                $check_oc->express_info_id = $express_info_id;
            } else {
                $check_oc = new MallOrderChild();
                $check_oc->order_id = $v['order_id'];
                $check_oc->order_detail_id = $v['order_detail_id'];
                $check_oc->express_info_id = $express_info_id;
            }
            $child_res = $check_oc->save();

            $notify_data = [
                'from_uid' => 0,
                'to_uid' => $order_obj->user_id,
                'type' => 5,
                'relation_type' => 6,
                'content' => '',
                'source_id' => $order_obj->id,
                'created_at' => $now_date,
                'updated_at' => $now_date
            ];
            DB::table('nlsg_notify')->insert($notify_data);
            //todo 极光推送
//            Task::send(
//                8,$order_obj->user_id,0,0,0,
//                $order_obj->ordernum,
//                $express_company_info->name ?? '圆通',
//                0,''
//            );
            /**
             * JPush::pushNow('别名', '通知', '附加信息');
             * JPush::pushNow(['别名数组'], '通知', '附加信息');
             * JPush::pushNow('all', '通知', '附加信息');
             */

            if (!$child_res) {
                DB::rollBack();
                return ['code' => false, 'msg' => '错误', 'ps' => 'child error'];
            }
        }
        DB::commit();
        return ['code' => true, 'msg' => '成功'];
    }


    //群组
    public function allMallOrder($params, $user_id)
    {
        $size = $params['size'] ?? 10;
        $query = MallOrder::query();
        $query->with([
            'userInfo',
            'groupBuy',
            'orderDetails',
            'orderDetails.goodsInfo',
        ])->has('userInfo')
            ->has('orderDetails');

        $field = ['id', 'ordernum', 'user_id', 'order_type', 'status', 'gp_status'];

        $query->select($field);

        return $query->paginate($size);
    }

}
