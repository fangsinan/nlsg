<?php

namespace App\servers;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Illuminate\Support\Facades\DB;
use App\Models\MallOrder;

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
            case 'flash_sale':
                $res = $this->listOfFlashSale($params);
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

        $query = MallOrder::from('nlsg_mall_order as nmo');

        //todo 编号,昵称,账号,时间,支付时间,商品名称,支付渠道,客户端类型

        if (!empty($params['ordernum'])) {
            $query->where('nmo.ordernum', '=', $params['ordernum']);
        }

        $query->where('order_type', '=', 1)->where('is_del', '=', 0);

        switch (intval($params['status'] ?? 0)) {
            case 1:
                $query->where('nmo.status', '=', 1);
                break;
            case 10:
                $query->where('nmo.status', '=', 10);
                break;
            case 20:
                $query->where('nmo.status', '=', 20);
                break;
            case 30:
                $query->where('nmo.status', '=', 30);
                break;
            case 99:
                $query->where('nmo.is_stop', '=', 1);
                break;
        }

        $field = [
            'id', 'ordernum', 'price', 'dead_time',
            DB::raw('(case when is_stop = 1 then 99 ELSE `status` END) `status`')
        ];
        $with = ['orderDetails', 'orderDetails.goodsInfo'];

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
        
    }

    protected function listOfFlashSale($params) {
        
    }

    public function details($params) {
        
    }

    public function send($params) {
        
    }

}
