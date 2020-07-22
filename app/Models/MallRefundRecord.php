<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

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
            'nmod.goods_id', 'nmod.sku_number', 'nmod.sku_history',
            'nmo.pay_type', 'nmg.name as goods_name', 'nmg.subtitle',
            'nmo.receipt_at', 'nmo.pay_price', 'nmo.coupon_money',
            DB::raw('(nmo.cost_price - nmo.vip_cut - nmo.special_price_cut) as temp_money'),
            DB::raw('(nmod.num - nmod.after_sale_used_num) as num'),
            DB::raw('(SELECT sum(num) FROM nlsg_mall_order_detail '
                    . 'where order_id = nmo.id) as all_num'),
            DB::raw('(SELECT sum(after_sale_used_num) FROM nlsg_mall_order_detail '
                    . 'where order_id = nmo.id) as all_after_sale_used_num'),
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
            //退货
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

            //分优惠券金额
            if ($get_data->coupon_money > 0) {
                if ($get_data->all_after_sale_used_num == 0 &&
                        $data['num'] == $get_data->all_num) {
                    //如果是全退,则是支付金额
                    $data['refe_price'] = $get_data->pay_price;
                } else {
                    //不是全退,计算每个商品优惠券的金额占比
                    $temp_cm = GetPriceTools::PriceCalc('/',
                                    $get_data->coupon_money, $get_data->temp_money);

                    $temp_cm = GetPriceTools::PriceCalc('*',
                                    $data['cost_price'], $temp_cm);

                    $temp_cm = GetPriceTools::PriceCalc('-',
                                    $data['cost_price'], $temp_cm);

                    $data['refe_price'] = GetPriceTools::PriceCalc('*',
                                    $temp_cm, $data['num']);
                }
            } else {
                $data['refe_price'] = GetPriceTools::PriceCalc('*',
                                $data['cost_price'], $data['num']);
            }

            $od->after_sale_used_num = $new_num;
            $od_res = $od->save();

            if (!$od_res) {
                DB::rollBack();
                return ['code' => false, 'msg' => '失败', 'ps' => 'order_detail error'];
            }
        } else {
            //退款
            $data['refe_price'] = $get_data->pay_price;
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

        $page = $params['page'] ?? 1;
        $size = $params['size'] ?? 10;

        $field = ['id', 'service_num', 'order_id', 'order_detail_id',
            'type', 'num', 'cost_price', 'refe_price', 'price', 'status',
            'user_cancel', 'user_cancel_time', 'created_at'];

        $with = ['infoOrder',
            'infoOrder.infoOrderDetail',
            'infoOrder.infoOrderDetail.goodsInfo',
            'infoDetail',
            'infoDetail.goodsInfo',
            'expressInfo'
        ];

        if ($params['id'] ?? 0) {
            $query = MallRefundRecord::where('id', '=', $params['id'])
                    ->where('user_id', '=', $user['id']);

            $field_sup = [
                'return_address_id', 'picture', 'pass_at', 'check_at',
                'receive_at', 'succeed_at', 'price', 'reason_id', 'description',
                'is_check_reject', 'check_reject_at', 'check_remark',
                'is_authenticate_reject', 'authenticate_reject_at',
                'authenticate_remark', 'express_info_id'
//                'express_id', 'express_num'
            ];

            $field = array_merge($field, $field_sup);
        } else {
            $query = MallRefundRecord::where('user_id', '=', $user['id']);
        }

        $query->where('status', '<>', 80)->whereIn('type', [1, 2]);

        $query->select($field)->with($with);

        //全部,待审核,待寄回,带鉴定,带退款,已完成,已取消
        //10:待审核  20:待寄回   30:待鉴定  40待退款  50:退款中 60:已退款  70:驳回
        //全部0  待审核10 待寄回20 待鉴定30 待退款40 已完成:50,60 已取消99(包含70)
        switch (intval($params['status'] ?? 0)) {
            case 10:
                $query->where('status', '=', 10);
                break;
            case 20:
                $query->where('status', '=', 20);
                break;
            case 30:
                $query->where('status', '=', 30);
                break;
            case 40:
                $query->where('status', '=', 40);
                break;
            case 50:
            case 60:
                $query->whereIn('status', [50, 60]);
                break;
            case 99:
            case 70:
                $query->where(function($query) {
                    $query->where('user_cancel', '=', 1)
                            ->orWhere('status', '=', 70);
                });
                break;
        }

        $list = $query->limit($size)->offset(($page - 1) * $size)->get();

        //如果type=1  读取infoOrder   =2读取infoDetail
        foreach ($list as $k => $v) {
            if ($v->user_cancel == 1 || $v->status = 70) {
                $v->status = 99;
            }
            $temp_data = [];
            if ($v->type == 1) {
                foreach ($v->infoOrder->infoOrderDetail as $vv) {
                    $temp = [];
                    $temp['goods_id'] = $vv->goods_id;
                    $temp['name'] = $vv->goodsInfo->name;
                    $temp['subtitle'] = $vv->goodsInfo->subtitle;
                    $temp['picture'] = $vv->goodsInfo->picture;
                    $temp_sku = json_decode($vv->sku_history);
                    $temp['num'] = $temp_sku->actual_num;
                    $temp['price'] = $temp_sku->actual_price;
                    $temp_data[] = $temp;
                }
            } else {
                foreach ($v->infoDetail as $vv) {
                    $temp = [];
                    $temp['goods_id'] = $vv->goods_id;
                    $temp['name'] = $vv->goodsInfo->name;
                    $temp['subtitle'] = $vv->goodsInfo->subtitle;
                    $temp['picture'] = $vv->goodsInfo->picture;
                    $temp_sku = json_decode($vv->sku_history);
                    $temp['num'] = $v->num;
                    $temp['price'] = $temp_sku->actual_price;
                    $temp_data[] = $temp;
                }
            }
            $v->goods_list = $temp_data;
            if (!empty($v->expressInfo)) {
                $v->expressInfo->history = json_decode($v->expressInfo->history);
            }
            unset($list[$k]->infoOrder, $list[$k]->infoDetail);
        }

        return $list;
    }

    public function expressInfo() {
        return $this->hasOne('App\Models\ExpressInfo', 'id', 'express_info_id')
                        ->select(['id', 'history']);
    }

    public function infoOrder() {
        return $this->hasOne('App\Models\MallOrder', 'id', 'order_id')
                        ->select(['id', 'ordernum']);
    }

    public function infoDetail() {
        return $this->hasMany('App\Models\MallOrderDetails', 'id', 'order_detail_id')
                        ->select(['id', 'order_id', 'goods_id', 'sku_history']);
    }

    public function orderInfo($params, $user) {
        $data = $this->list($params, $user);
        if ($data->isEmpty()) {
            return ['code' => false, 'msg' => '参数错误', 'ps' => '未查询到售后单'];
        }

        $data = $data[0];
        $data->express_name = ExpressCompany::onlyGetName($data->express_id);

        $ftModel = new FreightTemplate();
        $freight_template_data = $ftModel->listOfShop(3);

        foreach ($freight_template_data as $v) {
            if ($v->id == $data->return_address_id) {
                $data->refund_address = $v;
            }
        }
        
        //todo 进度条
        $data['progress_bar'] = $this->createProgressBar($data, $data->expressInfo->history->list??[]);
        return $data;
    }
    
    public function createProgressBar($data,$progress_bar){
        //全部0  待审核10 待寄回20 已取消99(包含70)
        //待鉴定30 待退款40 已完成:50,60 
        
        
        dd($data->toArray());
        array_push($progress_bar, ['time'=>11]);
        array_unshift($progress_bar,['time'=>1121]);
        
        
        return $progress_bar;
    }

    //删除,取消,寄回
    public function statusChange($id, $flag, $user_id) {

        $check = self::where('user_id', '=', $user_id)
                ->where('status', '<>', 80)
                ->find($id);

        if (!$check) {
            return ['code' => false, 'msg' => '订单错误'];
        }

        $now_date = date('Y-m-d H:i:s');

        DB::beginTransaction();

        // 10:待审核 20:待寄回  30:待鉴定 40待退款  
        // 50:退款中 60:已退款  70:驳回   80删除

        switch ($flag) {
            case 'stop':
                //取消:待审核,待寄回
                if ($check->status == 10 || $check->status == 20) {
                    $check->user_cancel = 1;
                    $check->user_cancel_time = $now_date;

                    //如果是退货,需要把order_details的after_sale_num改回
                    if ($check->type == 2) {
                        $d_res = DB::table('nlsg_mall_order_detail')
                                ->where('id', '=', $check->order_detail_id)
                                ->decrement('after_sale_used_num', $check->num);

                        if (!$d_res) {
                            DB::rollBack();
                            return ['code' => false, 'msg' => '参数错误',
                                'ps' => 'detail error'];
                        }
                    }
                } else {
                    return ['code' => false, 'msg' => '订单状态错误'];
                }
                break;
            case 'del':
                //删除:已拒绝,已取消
                if ($check->user_cancel == 1 || $check->status == 70) {
                    $check->status = 80;
                } else {
                    return ['code' => false, 'msg' => '订单状态错误'];
                }
                break;
            default:
                return ['code' => false, 'msg' => '参数错误'];
        }

        $res = $check->save();

        if ($res) {
            DB::commit();
            return ['code' => true, 'msg' => '成功'];
        } else {
            DB::rollBack();
            return ['code' => false, 'msg' => '失败'];
        }
    }

    public function refundPost($params, $user) {
        $id = $params['id'] ?? 0;
        $express_id = $params['express_id'] ?? 0;
        $express_num = $params['express_num'] ?? 0;
        if (empty($id) || empty($express_id) || empty($express_num)) {
            return ['code' => false, 'msg' => '参数错误'];
        }


        $check = self::where('user_id', '=', $user['id'])
                ->where('status', '<>', 80)
                ->find($id);

        if ($check->status == 20 && $check->user_cancel == 0) {
            $check_express = ExpressCompany::find($express_id);
            if ($check_express) {
                $check->status = 30;
                $check->express_id = $express_id;
                $check->express_num = $express_num;
                $check->refund_at = date('Y-m-d H:i:s');

                $res = $check->save();
                if ($res) {
                    return ['code' => true, 'msg' => '成功'];
                } else {
                    return ['code' => false, 'msg' => '失败'];
                }
            } else {
                return ['code' => false, 'msg' => '状态错误'];
            }
        } else {
            return ['code' => false, 'msg' => '状态错误'];
        }
    }

}
