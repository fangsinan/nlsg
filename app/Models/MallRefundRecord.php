<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

/**
 * Description of MallRefundRecord
 *
 * @author wangxh
 */
class MallRefundRecord extends Base {

    protected $table = 'nlsg_mall_refund_record';

    public function goodsList($params, $user) {
        $begin_date = $this->getAfterSalesbeginDate(1);
        $page = $params['page'] ?? 1;
        $size = $params['size'] ?? 10;

        $query = DB::table('nlsg_mall_order_detail as nmod')
                ->join('nlsg_mall_order as nmo', 'nmod.order_id', '=', 'nmo.id')
                ->join('nlsg_mall_goods as nmg', 'nmod.goods_id', '=', 'nmg.id')
                ->where('nmod.user_id', '=', $user['id'])
                ->where('nmo.user_id', '=', $user['id'])
                ->where('nmo.status', '=', 30)
                ->where('nmod.num', '>', 'after_sale_used_num');

        //不过滤已失效的
        //$query->where('nmo.receipt_at', '>', $begin_date);

        if (!empty($params['order_id'] ?? 0)) {
            $query->where('nmo.id', '=', $params['order_id']);
        }

        if (!empty($params['order_detail_id'] ?? 0)) {
            $query->where('nmod.id', '=', $params['order_detail_id']);
        }

        //$query->where('nmo.receipt_at','>',$begin_date);

        $query->select([
            'nmo.ordernum', 'nmo.id as order_id', 'nmod.id as order_detail_id',
            'nmod.goods_id', 'nmod.sku_number', 'nmod.sku_history', 'nmo.pay_type',
            'nmg.name as goods_name', 'nmg.subtitle', 'nmo.receipt_at',
            DB::raw('(nmod.num - nmod.after_sale_used_num) as num')
        ]);

        $query->limit($size)->offset(($page - 1) * $size)
                ->orderBy('nmo.receipt_at', 'desc')
                ->orderBy('nmo.id', 'desc');

        $list = $query->get();

        if ($list->isEmpty()) {
            return [];
        }

        foreach ($list as $k => $v) {
            $temp = json_decode($v->sku_history);
            $v->sku_history = $temp;
            $v->sku_value = $temp->sku_value;

            if (empty($v->receipt_at)) {
                $v->is_pass = 1;
            } else {
                if (strtotime($v->receipt_at) < strtotime($begin_date)) {
                    $v->is_pass = 1;
                } else {
                    $v->is_pass = 0;
                }
            }

            if (($params['get_first'] ?? 0) == 0) {
                unset($list[$k]->sku_history);
            }
        }

        if (($params['get_first'] ?? 0) == 1) {
            return $list[0];
        } else {
            return $list;
        }
    }

    public function getAfterSalesbeginDate($flag = 1) {
        $now = time();
        $can_after_sales_day = ConfigModel::getData(14);

        if (empty($can_after_sales_day)) {
            $can_after_sales_day = 7;
        }

        $temp = strtotime(date('Y-m-d', $now)) - intval($can_after_sales_day) * 86400;

        if ($flag == 1) {
            $temp = date('Y-m-d H:i:s', $temp);
        }

        return $temp;
    }

    public function createOrder($params, $user) {
        $type = intval($params['type'] ?? 0);
        $order_id = intval($params['order_id'] ?? 0);
        $order_detail_id = intval($params['order_detail_id'] ?? 0);
        if (!$order_id || !$order_detail_id) {
            return ['code' => false, 'msg' => '参数错误',
                'ps' => 'order_id,order_detail_id'];
        }
        //校验订单是否能够申请售后
        $get_data = $this->goodsList(['order_id' => $order_id,
            'order_detail_id' => $order_detail_id,
            'get_first' => 1], $user
        );
        if ($get_data->is_pass !== 0) {
            return ['code' => false, 'msg' => '订单状态错误', 'ps' => '售后已超时'];
        }

        $data = [];
        $now_date = date('Y-m-d H:i:s');

        if (!in_array($type, [1, 2])) {
            return ['code' => false, 'msg' => '参数错误', 'ps' => 'type'];
        }

        DB::beginTransaction();

        $mallOrderModel = new MallOrder();
        $data['service_num'] = $mallOrderModel->createOrderNumber($user['id'], 2);
        $data['order_id'] = $order_id;
        $data['type'] = $type;
        $data['status'] = 10;
        $data['user_id'] = $user['id'];
        $data['created_at'] = $now_date;
        $data['updated_at'] = $now_date;
        $data['pay_type'] = $get_data->pay_type;

        if ($type == 2) {
            $data['order_detail_id'] = $order_detail_id;

            $data['num'] = $params['num'] ?? 0;
            if ($data['num'] == 0 || $data['num'] > $get_data->num) {
                return ['code' => false, 'msg' => '数量错误', 'ps' => 'num'];
            }

            $data['cost_price'] = $get_data->sku_history->actual_price;

            $od = MallOrderDetails::find($get_data->order_detail_id);
            $new_num = $od->after_sale_used_num + $data['num'];
            if ($new_num > $od->num) {
                DB::rollBack();
                return ['code' => false, 'msg' => '失败,数量超出限制'];
            }

            $od->after_sale_used_num = $new_num;
            $od_res = $od->save();

            if (!$od_res) {
                DB::rollBack();
                return ['code' => false, 'msg' => '失败', 'ps' => 'order_detail error'];
            }
        }

        $rr_res = DB::table('nlsg_mall_refund_record')
                ->insert($data);

        if (!$rr_res) {
            DB::rollBack();
            return ['code' => false, 'msg' => '失败', 'ps' => 'refund_record error'];
        }

        DB::commit();
        return ['code' => true, 'msg' => '成功'];
    }

    public function list($params, $user) {
        
    }

    public function orderInfo($params, $user) {
        
    }

    public function statusChange($params, $user) {
        
    }

}
