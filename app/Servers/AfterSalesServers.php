<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\servers;

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

    public function getList($params)
    {

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
            $query = M2R::where('id', '=', $params['id']);

            $field_sup = [
                'return_address_id', 'picture', 'pass_at', 'check_at',
                'receive_at', 'succeed_at', 'price', 'reason_id', 'description',
                'is_check_reject', 'check_reject_at', 'check_remark',
                'is_authenticate_reject', 'authenticate_reject_at',
                'authenticate_remark', 'express_info_id',
            ];

            $field = array_merge($field, $field_sup);
        } else {
            $query = new M2R();
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
                $query->where(function ($query) {
                    $query->where('user_cancel', '=', 1)
                        ->orWhere('status', '=', 70);
                });
                break;
        }

        $list = $query->limit($size)->paginate($size);

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

        if ($flag == 'check') {
            if ($check->status == 10) {
                if ($value == 1) {
                    //通过
                    if ($check->type == 2) {
                        //退货
                        $check->status = 20;
                    } else {
                        //只退款
                        $check->status = 40;
                    }
                    $check->is_check_reject = 2;
                } else {
                    //驳回
                    $check->status = 70;
                    $check->is_check_reject = 1;
                }

                $check->pass_at = $now_date;
                $check->check_reject_at = $now_date;
                $check->check_remark = $params['remark'] ?? '';

                $res = $check->save();
            } else {
                return ['code' => false, 'msg' => '状态错误'];
            }
        } elseif ($flag == 'identify') {
            if ($check->status == 30) {
                $check->status = 40;
                $check->is_authenticate_reject = 2;
                $check->authenticate_reject_at = $now_date;
                $check->check_remark = $params['remark'] ?? '';
                $res = $check->save();
            } else {
                return ['code' => false, 'msg' => '状态错误'];
            }
        } else {
            return ['code' => false, 'msg' => '参数错误 flag'];
        }

        if ($res) {
            return ['code' => true, 'msg' => '成功'];
        } else {
            return ['code' => false, 'msg' => '失败'];
        }
    }

    //todo 退款动作
}
