<?php

namespace App\Servers\V5;

use App\Models\ConfigModel;
use App\Models\MallAddress;
use App\Models\Order;
use App\Models\OrderErpList;
use App\Models\OrderRefundLog;
use App\Servers\ErpServers;
use Illuminate\Support\Facades\DB;

class erpOrderServers
{
    public function list($params, $is_excel = 0) {
        $search_relation_id = [7, 8, 10];
        $size               = $params['size'] ?? 10;
        $ordernum           = $params['ordernum'] ?? '';
        $goods_name         = $params['goods_name'] ?? '';
        $phone  = $params['phone'] ?? '';
        $send_status        = (int)($params['send_status'] ?? 0);  //1没发货 2发货了
        $shill_status       = (int)($params['shill_status'] ?? 0); //1没退款  2退款中 3退款完毕
        $order_info_flag    = $params['order_info_flag'] ?? '';

        $erp_push_order_flag = (int)ConfigModel::getData(56, 1);//0全部 1正常 2只测试

        $query = Order::query()
            ->where('type', '=', 14)
            ->whereIn('relation_id', $search_relation_id)
            ->where('status', '=', 1)
            ->where('textbook_id', '>', 0)
            ->select([
                'id', 'ordernum', 'type', 'live_num', 'live_id', 'user_id', 'status',
                'pay_time', 'pay_price', 'is_shill', 'shill_refund_sum', 'is_refund', 'refund_no',
                'created_at', 'express_info_id', 'textbook_id', 'address_id'
            ]);

        if ($erp_push_order_flag === 1){
            $query->where('id','>',1869289);
        }

        $query->with([
            'user:id,phone,nickname',
            'textbookInfo:id,erp_sku,cover_img,title',
            'addressInfo:id,name,phone,details,user_id,province,city,area',
            'addressInfo.area_province:id,name',
            'addressInfo.area_city:id,name',
            'addressInfo.area_area:id,name',
            'pushErpInfo:id,order_id,flag',
            'expressInfo:id,express_id,express_num,history',
            'payRefundInfo:id,order_id,created_at',
            'orderRefundLogInfo:ordernum,check_price',
        ]);

        switch ($order_info_flag) {
            case 'no_address':
                //没有地址
                $query->where('address_id', '=', 0);
                break;
            case 'not_push_erp':
                //未推送erp
                $query->whereHas('pushErpInfo', function ($q) {
                    $q->where('flag', '=', 1);
                });
                break;
            case 'push_erp':
                //已推送erp
                $query->whereHas('pushErpInfo', function ($q) {
                    $q->where('flag', '=', 2);
                });
                break;
        }


        switch ($erp_push_order_flag) {
            case 1:
                $query->whereHas('user', function ($q) {
                    $q->where('is_test_pay', '=', 0);
                });
                break;
            case 2:
                $query->whereHas('user', function ($q) {
                    $q->where('is_test_pay', '=', 1);
                });
                break;
        }


        //订单编号
        $query->when($ordernum, function ($q, $ordernum) {
            $q->where('ordernum', 'like', "%$ordernum%");
        });

        //账号
        $query->when($phone, function ($q, $phone) {
            $q->wherehas('user',function($q)use($phone){
                $q->where('phone','like',"%$phone%");
            });
//            $q->where('ordernum', 'like', "%$ordernum%");
        });

        //商品名称
        $query->when($goods_name, function ($q, $goods_name) {
            $q->wherehas('textbookInfo', function ($q) use ($goods_name) {
                $q->where('title', 'like', "%$goods_name%");
            });
        });

        //订单状态 (待发货,已发货,已退款,退款中)
        switch ($send_status) {
            //1没发货 2发货了
            case 1:
//                $query->whereHas('pushErpInfo', function ($q) {
//                    $q->where('flag', '=', 1);
//                });
                $query->where('express_info_id', '=', 0);
                break;
            case 2:
//                $query->whereHas('pushErpInfo', function ($q) {
//                    $q->where('flag', '=', 2);
//                });
                $query->where('express_info_id', '>', 0);
                break;
        }

        switch ($shill_status) {
            //1没退款  2退款中 3退款完毕
            case 1:
                $query->where('is_shill', '=', 0);
                break;
            case 2:
                $query->where('is_shill', '=', 1)->where('is_refund', '<>', 3);
                break;
            case 3:
                $query->where('is_shill', '=', 1)->where('is_refund', '=', 3);
                break;
        }

        //支付时间范围
        if (!empty($params['created_at'] ?? '')) {
            $created_at    = explode(',', $params['created_at']);
            $created_at[0] = date('Y-m-d H:i:s', strtotime($created_at[0]));
            if (empty($created_at[1] ?? '')) {
                $created_at[1] = date('Y-m-d 23:59:59');
            } else {
                $created_at[1] = date('Y-m-d H:i:s', strtotime($created_at[1]));
            }
            $query->whereBetween('created_at', [$created_at[0], $created_at[1]]);
        }

        $query->orderBy('id', 'desc');

        if ($is_excel === 1) {
            $page = $params['page'] ?? 1;
            $res  = $query->limit($size)->offset(($page - 1) * $size)->get();
        } else {
            $res = $query->paginate($size);
        }

        $now = time();

        foreach ($res as $v) {
            $v->can_refund_button = 0;
            if ($v->express_info_id) {
                $v->send_status = 2;
            } else {
                $v->send_status = 1;
            }

            if (isset($v->payRefundInfo->created_at)) {
                $v->refund_time = date('Y-m-d H:i:s', strtotime($v->payRefundInfo->created_at));
            } else {
                $v->refund_time = '';
            }

            if ($v->is_shill === 0) {
                //计算pay_time和现在相聚的天数
                $temp_pay_days = floor(($now - strtotime($v->pay_time)) / 86400);
                if ($temp_pay_days <= 90) {
                    $v->can_refund_button = 1;
                }

                $v->shill_status = 1;
            } else {
                $v->can_refund_button = 1;
                if ($v->is_refund === 3) {
                    $v->shill_status = 3;
                } else {
                    $v->shill_status = 2;
                }
            }

            $v->address_detail = '';
            $v->address_name   = '';
            $v->address_phone  = '';

            if (!empty($v->addressInfo)) {
                $v->address_name   = $v->addressInfo->name;
                $v->address_phone  = $v->addressInfo->phone;
                $v->address_detail = trim(($v->addressInfo->area_province->name ?? '') . ' ' .
                    ($v->addressInfo->area_city->name ?? '') . ' ' .
                    ($v->addressInfo->area_area->name ?? '') . ' ' .
                    $v->addressInfo->details);
            }

            if ($v->shill_refund_sum == 0 && isset($v->orderRefundLogInfo->check_price)) {
                $v->shill_refund_sum = $v->orderRefundLogInfo->check_price;
            }

            if (!empty($v->expressInfo)) {
//                try {

                $v->expressInfo->history = json_decode($v->expressInfo->history ?? '');

//                }catch (\Exception $e){
//                    dd($v->toArray());
//                }

            }

            unset($v->addressInfo);

        }

        return $res;
    }


    public function addRefundOrder($params,$admin_id){
        $order_id = $params['order_id'] ?? 0;
        $ordernum = $params['ordernum'] ?? '';
        $refund_money = (float)($params['refund_money'] ?? 0);

        if (empty($order_id) || empty($refund_money) || empty($ordernum)) {
            return ['code'=>false,'msg'=>'参数错误'];
        }

        $check_order = Order::query()
            ->where('id','=',$order_id)
            ->where('ordernum','=',$ordernum)
            ->where('status','=',1)
            ->first();

        if (empty($check_order)) {
            return ['code'=>false,'msg'=>'订单不存在'];
        }

        if ($check_order->is_shill === 1) {
            return ['code'=>false,'msg'=>'该订单已经退款'];
        }

        if ($check_order->pay_price < $refund_money) {
            return ['code'=>false,'msg'=>'退款金额不能大于支付金额'];
        }

        if ($refund_money > ($check_order->pay_price - $check_order->shill_refund_sum)){
            return ['code'=>false,'msg'=>'退款金额不能大于可退金额'];
        }

        $now = time();
        $temp_pay_days = floor(($now - strtotime($check_order->pay_time)) / 86400);
        if ($temp_pay_days > 90) {
            return ['code'=>false,'msg'=>'订单已过期，不能退款'];
        }


        DB::beginTransaction();

        $log_data['ordernum'] = $check_order->ordernum;
        $log_data['check_price'] = $refund_money;
        $log_data['status'] = 1;
        $log_data['excel_id'] = 0;
        $log_data['admin_id'] = $admin_id;
        $log_data['remark'] = '直播订单列表申请退款:'.$refund_money.'元;'.$admin_id;

        $log_res = OrderRefundLog::query()->insert($log_data);
        if ($log_res === false) {
            DB::rollBack();
            return ['code'=>false,'msg'=>'添加退款记录失败'];
        }

        $check_order->is_shill = 1;
        $check_order->is_refund = 0;
        $check_order->shill_job_price = $refund_money;
        $order_res = $check_order->save();
        if ($order_res === false) {
            DB::rollBack();
            return ['code'=>false,'msg'=>'更新订单状态失败'];
        }

        $list_res = OrderErpList::query()
            ->insert([
                'order_id' => $order_id,
                'flag' => 1,
            ]);
        if ($list_res === false) {
            DB::rollBack();
            return ['code'=>false,'msg'=>'添加订单列表失败'];
        }
        DB::commit();

        $es = new ErpServers();
        $es->pushRunForOrder([$order_id]);

        return ['code'=>true,'msg'=>'操作成功'];
    }


    public function bindAddress($params): array {
        $order_id   = $params['order_id'] ?? 0;
        $address_id = $params['address_id'] ?? 0;

        if (empty($order_id) || empty($address_id)) {
            return ['code' => false, 'msg' => '参数错误'];
        }

        $check_order = Order::query()
            ->where('id', '=', $order_id)
            ->where('type', '=', 14)
            ->where('textbook_id', '>', 0)
            ->select(['id', 'address_id', 'user_id'])
            ->first();

        if (empty($check_order)) {
            return ['code' => false, 'msg' => '订单错误'];
        }

        if (!empty($check_order->address_id)) {
            return ['code' => false, 'msg' => '已经有地址'];
        }

        $check_address = MallAddress::query()
            ->where('id', '=', $address_id)
            ->where('user_id', '=', $check_order->user_id)
            ->first();

        if (empty($check_address)) {
            return ['code' => false, 'msg' => '地址不存在'];
        }

        $res = Order::query()
            ->where('id', '=', $order_id)
            ->update([
                'address_id' => $address_id
            ]);

        if (!$res) {
            return ['code' => false, 'msg' => '失败,请重试'];
        }

        return ['code' => true, 'msg' => '成功'];

    }
}
