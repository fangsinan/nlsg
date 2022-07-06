<?php

namespace App\Servers\V5;

use App\Models\OfflineProducts;
use App\Models\OfflineProductsOrderLog;
use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OfflineOrderServers
{
    public function list($params): LengthAwarePaginator
    {
        $size                   = $params['size'] ?? 10;
        $order_by               = $params['order_by'] ?? 'id_desc';
        $offline_title          = $params['offline_title'] ?? '';
        $ordernum               = $params['ordernum'] ?? '';
        $phone                  = $params['phone'] ?? '';
        $pay_type               = $params['pay_type'] ?? 0;
        $os_type                = $params['os_type'] ?? 0;
        $full_payment           = $params['full_payment'] ?? 0;
        $offline_status         = $params['offline_status'] ?? 0;
        $create_time_date_begin = $params['create_time_date_begin'] ?? '';
        $create_time_date_end   = $params['create_time_date_end'] ?? '';

        //线下课名称
        $offline_id = OfflineProducts::query()->where('type', '=', 3);
        if (!empty($offline_title)) {
            $offline_id->where('title', 'like', '%' . $offline_title . '%');
        }
        $offline_id = $offline_id->pluck('id')->toArray();
        if (empty($offline_id)) {
            $offline_id = [-1];
        }

        $query = Order::query()
            ->where('type', '=', 14)
            ->where('status', '=', 1)
            ->whereIn('relation_id', $offline_id)
            ->select([
                'id', 'ordernum', 'type', 'live_num', 'relation_id', 'user_id', 'status', 'pay_time', 'price',
                'twitter_id', 'pay_type', 'os_type', 'is_shill', 'shill_job_price', 'shill_refund_sum',
                'is_refund', 'refund_no', 'created_at', 'full_payment', 'offline_status',
            ]);

        $query->with([
            'user:id,phone,nickname,is_robot,is_test_pay',
            'offline:id,title,cover_img,is_del,is_show,price,total_price',
            'offlineLastLog',
            'offlineLastLog.adminInfo:id,username,user_remark',
        ]);

        //订单编号
        if (!empty($ordernum)) {
            $query->where('ordernum', 'like', '%' . $ordernum . '%');
        }

        //用户账号
        if (!empty($phone)) {
            $query->wherehas('user', function ($q) use ($phone) {
                $q->where('phone', 'like', '%' . $phone . '%');
            });
        }

        //订单来源
        if (!empty($os_type)) {
            $query->where('os_type', '=', $os_type);
        }
        //支付方式
        if (!empty($pay_type)) {
            $query->where('pay_type', '=', $pay_type);
        }

        //订单支付状态
        if (!empty($full_payment)) {
            $query->where('full_payment', '=', $full_payment);
        }

        //状态
        if (!empty($offline_status)) {
            $query->where('offline_status', '=', $offline_status);
        }

        //支付时间
        $query->when($create_time_date_begin, function ($q, $create_time_date_begin) {
            $create_time_date_begin = strtotime($create_time_date_begin);
            $q->where('pay_time', '>=', $create_time_date_begin);
        });
        $query->when($create_time_date_end, function ($q, $create_time_date_end) {
            $create_time_date_end = strtotime($create_time_date_end) + 86400;
            $q->where('pay_time', '<=', $create_time_date_end);
        });


        switch ($order_by) {
            case 'id_asc':
                $query->orderBy('id');
                break;
            default:
                $query->orderBy('id', 'desc');
                break;
        }


        return $query->paginate($size);

    }

    public function orderLogList($params): LengthAwarePaginator
    {
        $size     = $params['size'] ?? 10;
        $order_id = $params['order_id'] ?? 0;
        return OfflineProductsOrderLog::query()
            ->where('order_id', '=', $order_id)
            ->with([
                'adminInfo:id,username,user_remark',
            ])->orderBy('log_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($size);
    }

    public function orderLogAdd($params, $admin): array
    {
        $p                   = [];
        $p['admin_id']       = $admin['id'] ?? 0;
        $p['order_id']       = (int)($params['order_id'] ?? 0);
        $p['remark']         = $params['remark'] ?? '';
        $p['log_date']       = $params['log_date'] ?? '';
        $p['full_payment']   = $params['full_payment'] ?? 0;
        $p['offline_status'] = $params['offline_status'] ?? 0;

        $validator = Validator::make($p, [
                'admin_id'       => 'bail|required|integer|min:1',
                'order_id'       => 'bail|required|integer|min:1',
                'remark'         => 'bail|required|string|max:255',
                'log_date'       => 'bail|required|date|date_format:Y-m-d H:i:s',
                'full_payment'   => 'bail|required|integer|in:1,2',
                'offline_status' => 'bail|required|integer|in:1,2,3,4',
            ]
        );

        if ($validator->fails()) {
            return ['code' => false, 'msg' => $validator->messages()->first()];
        }

        DB::beginTransaction();

        $add_res = OfflineProductsOrderLog::query()->create($p);
        if (!$add_res) {
            DB::rollBack();
            return ['code' => false, 'msg' => '添加失败'];
        }

        $last_log = OfflineProductsOrderLog::query()
            ->where('order_id', '=', $p['order_id'])
            ->orderBy('log_date', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        $update_res = Order::query()
            ->where('id', '=', $p['order_id'])
            ->update([
                'full_payment'   => $last_log->full_payment,
                'offline_status' => $last_log->offline_status,
            ]);

        if (!$update_res) {
            DB::rollBack();
            return ['code' => false, 'msg' => '更新失败'];
        }

        DB::commit();
        return ['code' => true, 'msg' => '添加成功'];
    }


}
