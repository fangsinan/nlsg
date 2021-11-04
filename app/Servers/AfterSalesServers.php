<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Servers;

use App\Models\MallOrderDetails;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Models\MallRefundRecord as M2R;
use App\Models\MallOrder;
use App\Models\MallOrderChild;
use App\Models\ExpressInfo;
use Illuminate\Database\Eloquent\Builder;

/**
 * Description of AfterSalesServers
 *
 * @author wangxh
 */
class AfterSalesServers
{

    //售后列表
    public function getList($params)
    {
        $size = $params['size'] ?? 10;
        $field = ['id', 'service_num', 'order_id', 'order_detail_id',
            'type', 'num', 'cost_price', 'refe_price', 'price', 'status', 'user_id',
            'user_cancel', 'user_cancel_time', 'created_at'];

        $with = [
            'infoOrder',
            'infoOrder.infoOrderDetail',
            'userInfo',
            'infoOrder.infoOrderDetail.goodsInfo',
            'infoDetail',
            'infoDetail.goodsInfo',
            'expressInfo'
        ];

        if ($params['id'] ?? 0) {
            $query = M2R::whereId($params['id']);
            $field_sup = [
                'return_address_id', 'picture', 'pass_at', 'check_at',
                'receive_at', 'succeed_at', 'price', 'reason_id', 'description',
                'is_check_reject', 'check_reject_at', 'check_remark',
                'is_authenticate_reject', 'authenticate_reject_at',
                'authenticate_remark', 'express_info_id',
            ];
            $field = array_merge($field, $field_sup);
        } else {
            $query = M2R::query();
        }

        if ($params['service_num'] ?? 0) {
            $query->where('service_num', 'like', '%' . $params['service_num'] . '%');
        }

        if ($params['ordernum'] ?? 0) {
//            $query->where('service_num', 'like', '%' . $params['service_num'] . '%');
            $ordernum = $params['ordernum'];
            $query->whereHas('infoOrder',function ($q)use($ordernum){
                $q->where('ordernum','like',"%$ordernum%");
            });
        }



        if (in_array($params['type'] ?? 0, [1, 2])) {
            $query->where('type', '=', $params['type']);
        }

        $query->where('status', '<>', 80);

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
                $query->where('status', '=', 50)->where('user_cancel', '=', 0);
                break;
            case 60:
                $query->where('status', '=', 60)->where('user_cancel', '=', 0);
                break;
            case 70:
                $query->where('status', '=', 70)->where('user_cancel', '=', 0);
//                $query->where(function ($query) {
//                    $query->where('user_cancel', '=', 1)
//                        ->orWhere('status', '=', 70);
//                });
                break;
            case 99:
                $query->where('user_cancel', '=', 1);
                break;
        }

        $list = $query->select($field)
            ->with($with)
            ->limit($size)
            ->orderBy('id', 'desc')
            ->paginate($size);

        //如果type=1  读取infoOrder   =2读取infoDetail
        foreach ($list as $k => &$v) {
            if ($v->user_cancel == 1 || $v->status == 70) {
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
                $v->expressInfo->history_array = json_decode($v->expressInfo->history);
            }
//            unset($list[$k]->infoOrder, $list[$k]->infoDetail);
        }
        return $list;
    }

    //审核,鉴定
    public function statusChange($params, $uid)
    {
        $flag = $params['flag'] ?? 0;
        $id = $params['id'] ?? 0;
        $value = $params['value'] ?? 0;
        if (empty($flag) || empty($id) || empty($value)) {
            return ['code' => false, 'msg' => '参数错误'];
        }
        $check = M2R::find($id);
        if (!$check) {
            return ['code' => false, 'msg' => 'id错误'];
        }
        $now_date = date('Y-m-d H:i:s');
        DB::beginTransaction();
        if ($flag === 'check') {
            if ($check->status === 10) {
                if ($value == 1) {
                    //通过
                    if ($check->type === 2) {
                        //退货
                        $check->status = 20;
                    } else {
                        //只退款
                        $check->status = 40;

                        //确定退款金额和任务
                        if (empty($params['price'])) {
                            DB::rollBack();
                            return ['code' => false, 'msg' => 'price参数错误'];
                        }
                        $check->price = $params['price'];
                        $check->run_refund = 1;

                    }
                    $check->is_check_reject = 2;
                } else {
                    //如果是驳回 需要把after_sale_used_num减掉
                    $detail_info = MallOrderDetails::find($check->order_detail_id);
                    if (empty($detail_info)) {
                        DB::rollBack();
                        return ['code' => false, 'msg' => '订单发生错误,请重试'];
                    }
                    $new_used_num = $detail_info->after_sale_used_num - $check->num;
                    $detail_info->after_sale_used_num = $new_used_num < 0 ? 0 : $new_used_num;
                    $info_res = $detail_info->save();
                    if ($info_res === false) {
                        DB::rollBack();
                        return ['code' => false, 'msg' => '订单修改错误,请重试'];
                    }

                    //驳回
                    $check->status = 70;
                    $check->is_check_reject = 1;
                }
                $check->return_address_id = $params['return_address_id'] ?? 0;
                $check->pass_at = $now_date;
                $check->check_reject_at = $now_date;
                $check->check_remark = $params['remark'] ?? '';
                $res = $check->save();
            } else {
                DB::rollBack();
                return ['code' => false, 'msg' => '状态错误'];
            }
        } elseif ($flag === 'identify') {
            if ($check->status === 30) {
                $check->status = 40;
                $check->is_authenticate_reject = 2;
                $check->authenticate_reject_at = $now_date;
                $check->check_remark = $params['remark'] ?? '';

                //确定退款金额和任务
                if (empty($params['price'])) {
                    return ['code' => false, 'msg' => 'price参数错误'];
                }
                $check->price = $params['price'];
                $check->run_refund = 1;

                $res = $check->save();
            } else {
                DB::rollBack();
                return ['code' => false, 'msg' => '状态错误'];
            }
        } else {
            DB::rollBack();
            return ['code' => false, 'msg' => '参数错误 flag'];
        }

        if ($res) {
            DB::commit();
            return ['code' => true, 'msg' => '成功'];
        }

        DB::rollBack();
        return ['code' => false, 'msg' => '失败'];

    }
}
