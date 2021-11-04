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
class MallRefundRecord extends Base
{

    protected $table = 'nlsg_mall_refund_record';

    public function goodsList($params, $user)
    {
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

        $query->where(DB::raw('(nmod.num - nmod.after_sale_used_num)'), '>', 0);

        $query->select([
            'nmo.ordernum', 'nmo.id as order_id', 'nmod.id as order_detail_id',
            'nmod.goods_id', 'nmod.sku_number', 'nmod.sku_history', 'nmg.picture',
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

            if (empty($v->receipt_at) || $v->num == 0) {
                $v->is_pass = 1;
            } else {
                if (strtotime($v->receipt_at) < strtotime($begin_date)) {
                    $v->is_pass = 1;
                } else {
                    $v->is_pass = 0;
                }
            }
        }

        if (($params['get_first'] ?? 0) == 1) {
            return $list[0];
        } else {
            $temp_list = [];
            foreach ($list as $v) {
                if (isset($temp_list[$v->order_id])) {
                    $temp_list[$v->order_id][] = $v;
                } else {
                    $temp_list[$v->order_id] = [];
                    $temp_list[$v->order_id][] = $v;
                }
            }
            $temp_list = array_values($temp_list);

            foreach ($temp_list as &$v) {
                $temp_v = new class {
                };
                $temp_v->data = $v;
                $v = $temp_v;
            }

            return $temp_list;
        }
    }

    public function getAfterSalesbeginDate($flag = 1)
    {
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

    public function createOrder($params, $user)
    {
        $type = (int)($params['type'] ?? 0);
        $order_id = (int)($params['order_id'] ?? 0);
        $order_detail_id = (int)($params['order_detail_id'] ?? 0);
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
        $data['reason_id'] = $params['reason_id'] ?? 0;
        $data['description'] = $params['description'] ?? '';
        $data['picture'] = $params['picture'] ?? '';
        if (is_array($data['picture'])) {
            $data['picture'] = implode(',', $data['picture']);
        }

        $data['num'] = $params['num'] ?? 0;

        if ($type === 2) {
            //退货
            $data['order_detail_id'] = $order_detail_id;
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
            $data['order_detail_id'] = $order_detail_id;
            if ($data['num'] == 0 || $data['num'] > $get_data->num) {
                return ['code' => false, 'msg' => '数量错误', 'ps' => 'num'];
            }
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

            //退款
            $data['refe_price'] = $get_data->pay_price;
        }

        $rr_res = DB::table('nlsg_mall_refund_record')
            ->insertGetId($data);

        if (!$rr_res) {
            DB::rollBack();
            return ['code' => false, 'msg' => '失败', 'ps' => 'refund_record error'];
        }

        DB::commit();
        return ['code' => true, 'msg' => '成功', 'id' => $rr_res];
    }

    protected $casts = [
        'created_at' => 'date:Y-m-d H:i',
        'pass_at' => 'date:Y-m-d H:i',
        'check_at' => 'date:Y-m-d H:i',
        'receive_at' => 'date:Y-m-d H:i',
        'succeed_at' => 'date:Y-m-d H:i',
        'check_reject_at' => 'date:Y-m-d H:i',
        'authenticate_reject_at' => 'date:Y-m-d H:i',

    ];

    public function list($params, $user)
    {

        $page = $params['page'] ?? 1;
        $size = $params['size'] ?? 10;

        $field = ['id', 'service_num', 'order_id', 'order_detail_id', 'express_info_id',
            'type', 'num', 'cost_price', 'refe_price', 'price', 'status', 'description',
            'user_cancel', 'user_cancel_time', 'created_at', 'return_address_id'];

        $with = ['infoOrder',
            'infoOrder.infoOrderDetail',
            'infoOrder.infoOrderDetail.goodsInfo',
            'infoDetail', 'infoDetail.goodsInfo',
            'expressList'
        ];

        if ($params['id'] ?? 0) {
            $query = MallRefundRecord::where('id', '=', $params['id'])
                ->where('user_id', '=', $user['id']);

            $field_sup = [
                'return_address_id', 'picture', 'pass_at', 'check_at',
                'receive_at', 'succeed_at', 'price', 'reason_id', 'description',
                'is_check_reject', 'check_reject_at', 'check_remark',
                'is_authenticate_reject', 'authenticate_reject_at',
                'authenticate_remark'
            ];
            $with[] = 'expressInfo';

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
                $query->where('status', '=', 10)->where('user_cancel', '=', 0);
                break;
            case 20:
                $query->where('status', '=', 20)->where('user_cancel', '=', 0);
                break;
            case 30:
                $query->where('status', '=', 30)->where('user_cancel', '=', 0);
                break;
            case 40:
                $query->where('status', '=', 40)->where('user_cancel', '=', 0);
                break;
            case 50:
            case 60:
                $query->whereIn('status', [50, 60])->where('user_cancel', '=', 0);
                break;
            case 99:
                $query->where('user_cancel', '=', 1);
                break;
            case 70:
                $query->where('status', '=', 70)->where('user_cancel', '=', 0);
//                $query->where(function($query) {
//                    $query->where('user_cancel', '=', 1)
//                            ->orWhere('status', '=', 70);
//                });
                break;
        }

        $list = $query->limit($size)->offset(($page - 1) * $size)->get();

        //如果type=1  读取infoOrder   =2读取infoDetail
        $ftModel = new FreightTemplate();

        foreach ($list as $k => $v) {

            $freight_template_data = $ftModel->listOfShop(3);

            $v->refund_address = new class {
            };
            foreach ($freight_template_data as $ftv) {
                if ($ftv->id == $v->return_address_id) {
                    $v->refund_address = $ftv;
                }
            }

            if (empty($v->picture)) {
                $v->picture = [];
            } else {
                $v->picture = explode(',', $v->picture);
            }
            if ($v->user_cancel == 1) {
                $v->status = 99;
            } else {
                if ($v->status == 50 || $v->status == 60) {
                    $v->status = 60;
                }
            }
            $temp_data = [];
            if (false && $v->type == 1) {
                foreach ($v->infoOrder->infoOrderDetail as $vv) {
                    $temp = [];
                    $temp['goods_id'] = $vv->goods_id;
                    $temp['name'] = $vv->goodsInfo->name;
                    $temp['subtitle'] = $vv->goodsInfo->subtitle;
                    $temp['picture'] = $vv->goodsInfo->picture;
                    $temp_sku = json_decode($vv->sku_history);
                    //$temp['num'] = $temp_sku->actual_num;
                    $temp['num'] = $v->num;
                    $temp['price'] = $temp_sku->actual_price;
                    $temp['sku_value'] = $temp_sku->sku_value;
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
                    $temp['sku_value'] = $temp_sku->sku_value;
                    $temp_data[] = $temp;
                }
            }
            $v->goods_list = $temp_data;
            if (!empty($v->expressInfo)) {
                $v->expressInfo->history = json_decode($v->expressInfo->history);
            }

            $v->ordernum = $v->infoOrder->ordernum;

            unset($list[$k]->infoOrder, $list[$k]->infoDetail);
        }

        return $list;
    }

    public function expressInfo()
    {
        return $this->hasOne('App\Models\ExpressInfo', 'id', 'express_info_id')
            ->select(['id', 'history']);
    }

    public function expressList()
    {
        return $this->hasMany('App\Models\ExpressInfo', 'id', 'express_info_id')
            ->select(['id', 'express_id', 'express_num']);
    }

    public function infoOrder()
    {
        return $this->hasOne('App\Models\MallOrder', 'id', 'order_id')
            ->select(['id', 'ordernum','cost_price','freight','normal_cut','vip_cut','coupon_money',
                'coupon_freight_id','special_price_cut','price','pay_price','os_type','status']);
    }

    public function infoDetail()
    {
        return $this->hasMany('App\Models\MallOrderDetails', 'id', 'order_detail_id')
            ->select(['id', 'order_id', 'goods_id', 'sku_history']);
    }

    public function userInfo(){
        return $this->hasOne(User::class, 'id', 'user_id')
            ->select(['id','phone','nickname','headimg','level','expire_time']);
    }

    public function orderInfo($params, $user)
    {
        if (empty($params['id'] ?? 0)) {
            return ['code' => false, 'msg' => '参数错误', 'ps' => '未查询到售后单'];
        }
        $data = $this->list($params, $user);
        if ($data->isEmpty()) {
            return ['code' => false, 'msg' => '参数错误', 'ps' => '未查询到售后单'];
        }

        $data = $data[0];
        $data->express_name = ExpressCompany::onlyGetName($data->express_id);

        $ftModel = new FreightTemplate();
        $freight_template_data = $ftModel->listOfShop(3);

        $data->refund_address = new class {
        };
        foreach ($freight_template_data as $v) {
            if ($v->id == $data->return_address_id) {
                $data->refund_address = $v;
            }
        }

        $refund_reason = ConfigModel::getData(15);
        $refund_reason = json_decode($refund_reason, true);
        $data->reason_name = '';
        foreach ($refund_reason as $rrv) {
            if ($data->reason_id == $rrv['id']) {
                $data->reason_name = $rrv['value'];
            }
        }

        $data['progress_bar'] = $this->createProgressBar(
            $data->id,
            $data->expressInfo->history->list ?? []
        );

        //拼接
        $header = [];
        if (in_array($data->status, [40, 50, 60])) {
            $header[] = ['key' => '鉴定结果', 'value' => '同意退款'];
            if ($data->authenticate_reject_at) {
                $header[] = ['key' => '鉴定时间', 'value' => date('Y-m-d H:i:s',strtotime($data->authenticate_reject_at))];
            }
            if ($data->status == 50 && $data->refund_sub_at) {
                $header[] = ['key' => '完成时间', 'value' => date('Y-m-d H:i:s',strtotime($data->refund_sub_at))];
            }
            if ($data->status == 60 && $data->succeed_at) {
                $header[] = ['key' => '完成时间', 'value' => date('Y-m-d H:i:s',strtotime($data->succeed_at))];
            }
            if ($data->status == 40) {
                $header[] = ['key' => '退款金额', 'value' => $data->refe_price];
            } else {
                $header[] = ['key' => '退款金额', 'value' => $data->price];
            }
        }

        $footer = [
            ['key' => '服务单号', 'value' => $data->service_num],
            ['key' => '提交时间', 'value' => date('Y-m-d H:i:s',strtotime($data->created_at))],
            ['key' => '商品单号', 'value' => $data->ordernum],
        ];

        $data->header = $header;
        $data->footer = $footer;
        //进度条
        if ($params['only_bar'] ?? 0 == 1) {
            $data = $data['progress_bar'];
        }
        return $data;
    }

    public function dateDelSec($date)
    {
        return date('Y-m-d H:i', strtotime($date));
    }

    public function createProgressBar($id, $progress_bar)
    {
        $info = MallRefundRecord::find($id)->toArray();

        if ($info['status'] == 70) {
            $info['status'] = 15;
        }
        $before_arr = []; //10:待审核  15:驳回  20:待寄回
        $after_arr = []; //30:待鉴定  40待退款  50:退款中 60:已退款
        //顺序  10 15 20 () 30 40 50 60

        if ($info['status'] == 15) {
            $before_arr[] = ['time' => $this->dateDelSec($info['check_reject_at']), 'status' => '驳回'];
            $before_arr[] = ['time' => $this->dateDelSec($info['created_at']), 'status' => '提交申请'];
        } else {
            switch ($i = intval($info['status'])) {
                case $i > 50:
                case $i > 40:
                    $after_arr[] = ['time' => $this->dateDelSec($info['succeed_at']), 'status' => '退款完毕'];
                case $i > 30:
                    $after_arr[] = ['time' => $this->dateDelSec($info['authenticate_reject_at']), 'status' => '待退款'];
                case $i > 20:
                    $after_arr[] = ['time' => $this->dateDelSec($info['receive_at']), 'status' => '待鉴定'];
                case $i > 15:
                    $before_arr[] = ['time' => $this->dateDelSec($info['pass_at']), 'status' => '待寄回'];
                case $i > 0:
                    $before_arr[] = ['time' => $this->dateDelSec($info['created_at']), 'status' => '提交申请'];
            }
        }
        $progress_bar = array_merge($progress_bar, $before_arr);
        $progress_bar = array_merge($after_arr, $progress_bar);
        return $progress_bar;

    }

    //删除,取消,寄回
    public function statusChange($id, $flag, $user_id)
    {

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

    public function refundPost($params, $user)
    {
        $id = $params['id'] ?? 0;
        $express_id = $params['express_id'] ?? 0;
        $express_num = $params['express_num'] ?? 0;
        if (empty($id) || empty($express_id) || empty($express_num)) {
            return ['code' => false, 'msg' => '参数错误'];
        }

        DB::beginTransaction();

        $check = self::where('user_id', '=', $user['id'])
            ->where('status', '<>', 80)
            ->find($id);

        if ($check->status == 20 && $check->user_cancel == 0) {

            if (0) {
                //顺丰暂时不需要正确手机号
                $return_address_id = $check->return_address_id;
                if (!$return_address_id) {
                    DB::rollBack();
                    return ['code' => false, 'msg' => '状态错误'];
                }
                $address_info = FreightTemplate::where('type', '=', 3)->find($return_address_id);
                if (!$address_info) {
                    DB::rollBack();
                    return ['code' => false, 'msg' => '状态错误'];
                }
            }

            $check_express = ExpressCompany::find($express_id);
            if ($check_express) {

                $check_ex = ExpressInfo::where('express_id', '=', $express_id)
                    ->where('express_num', '=', $express_num)
                    ->first();
                if ($check_ex) {
                    $express_info_id = $check_ex->id;
                } else {
                    $ex_data['express_id'] = $express_id;
                    $ex_data['express_num'] = $express_num;
                    $ex_data['created_at'] = $ex_data['updated_at'] = date('Y-m-d H:i:s');
                    $express_info_id = DB::table('nlsg_express_info')->insertGetId($ex_data);
                    if (!$express_info_id) {
                        DB::rollBack();
                        return ['code' => false, 'msg' => '错误', 'ps' => 'ex error'];
                    }
                }

                $check->status = 30;
                $check->express_info_id = $express_info_id;
                $check->refund_at = date('Y-m-d H:i:s');

                $res = $check->save();
                if ($res) {
                    DB::commit();
                    return ['code' => true, 'msg' => '成功'];
                } else {
                    DB::rollBack();
                    return ['code' => false, 'msg' => '失败'];
                }
            } else {
                DB::rollBack();
                return ['code' => false, 'msg' => '状态错误'];
            }
        } else {
            DB::rollBack();
            return ['code' => false, 'msg' => '状态错误'];
        }
    }

}
