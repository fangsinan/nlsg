<?php

namespace App\Servers\V5;

use App\Models\DouDian\DouDianOrder;
use App\Models\DouDian\DouDianOrderDecryptQuota;
use App\Models\DouDian\DouDianOrderStatus;
use Illuminate\Support\Facades\DB;

class DouDianDataServers
{
    public function list($params, $is_excel = 0) {
        $size                   = $params['size'] ?? 10;
        $order_status           = $params['order_status'] ?? 0;
        $main_status            = $params['main_status'] ?? 0;
        $product_name           = $params['product_name'] ?? '';
        $post_tel               = $params['post_tel'] ?? '';
        $order_id               = $params['order_id'] ?? '';
        $create_time_date_begin = $params['create_time_date_begin'] ?? '';
        $create_time_date_end   = $params['create_time_date_end'] ?? '';

        $query = DouDianOrder::query()
            ->select([
                'user_id', 'order_id', 'order_status', 'order_status_desc', 'main_status', 'main_status_desc',
                'pay_time', DB::raw('FROM_UNIXTIME(pay_time,"%Y-%m-%d %H:%i") as pay_time_date'),
                'finish_time', DB::raw('FROM_UNIXTIME(finish_time,"%Y-%m-%d %H:%i") as finish_time_date'),
                'create_time', DB::raw('FROM_UNIXTIME(create_time,"%Y-%m-%d %H:%i") as create_time_date'),
                'update_time', DB::raw('FROM_UNIXTIME(update_time,"%Y-%m-%d %H:%i") as update_time_date'),
                'order_amount', 'pay_amount', 'post_amount',
                DB::raw('order_amount/100 as order_amount_yuan'),
                DB::raw('pay_amount/100 as pay_amount_yuan'),
                DB::raw('post_amount/100 as post_amount_yuan'),
                'post_addr_province_name', 'post_addr_city_name', 'post_addr_town_name', 'post_addr_street_name',
                'post_addr_detail', 'post_tel', 'post_receiver',
                'decrypt_step', 'decrypt_err_no', 'decrypt_err_msg',
                'cancel_reason', 'buyer_words',
            ]);

        $query->with([
            'orderList'             => function ($q) {
                $q->select([
                    'id', 'order_id', 'parent_order_id',
                    'create_time', DB::raw('FROM_UNIXTIME(create_time,"%Y-%m-%d %H:%i") as create_time_date'),
                    'update_time', DB::raw('FROM_UNIXTIME(update_time,"%Y-%m-%d %H:%i") as update_time_date'),
                    'sku_id', 'product_id', 'goods_type', 'item_num'
                ]);
            },
            'orderList.productInfo' => function ($q) {
                $q->select([
                    'id', 'product_id', 'img', 'name'
                ]);
            },
//            'orderList.skuInfo'     => function ($q) {
//                $q->select([
//                    'id', 'product_id',
//                    'spec_detail_name1', 'spec_detail_name2', 'spec_detail_name3',
//                    'price', 'settlement_price', 'spec_id',
//                ]);
//            },
        ]);

        //订单状态
        $query->when($order_status, function ($q, $order_status) {
            $q->where('order_status', '=', $order_status);
        });

        //主要状态
        $query->when($main_status, function ($q, $main_status) {
            $q->where('main_status', '=', $main_status);
        });

        //商品名称
        $query->when($product_name, function ($q, $product_name) {
            $q->wherehas('orderList.productInfo', function ($q) use ($product_name) {
                $q->where('name', 'like', '%' . $product_name . '%');
            });
        });

        //下单时间
        $query->when($create_time_date_begin, function ($q, $create_time_date_begin) {
            $create_time_date_begin = strtotime($create_time_date_begin);
            $q->where('create_time', '>=', $create_time_date_begin);
        });
        $query->when($create_time_date_end, function ($q, $create_time_date_end) {
            $create_time_date_end = strtotime($create_time_date_end) + 59;
            $q->where('create_time', '<=', $create_time_date_end);
        });

        //用户手机号
        $query->when($post_tel, function ($q, $post_tel) {
            $q->where('post_tel', '=', $post_tel);
        });

        //抖音订单号
        $query->when($order_id, function ($q, $order_id) {
            $q->where('order_id', '=', $order_id);
        });

        $query->orderBy('created_at', 'desc');
        $query->orderBy('order_id', 'desc');

        if ($is_excel === 1) {
            $page = $params['page'] ?? 1;
            return $query->limit($size)->offset(($page - 1) * $size)->get();
        }

        return $query->paginate($size);
    }

    public function selectOrderStatus($params) {

        $type = (int)($params['type'] ?? 1);

        return DouDianOrderStatus::query()
            ->select(['key', 'value'])
            ->where('type', '=', $type === 1 ? 1 : 2)
            ->orderBy('key')
            ->get();

    }

    public function orderDecryptQuota(): array {
        $decrypt_quota = DouDianOrderDecryptQuota::query()
            ->orderBy('id', 'desc')
            ->first();

        $now_date = date('Y-m-d H:i:s');

        if (!empty($decrypt_quota) && $decrypt_quota->flag === 1 && $decrypt_quota->expire > $now_date) {
            return [
                'status' => 1,
                'msg'    => '解密配额已满,请重新申请.下次尝试解密时间:' . $decrypt_quota->expire . '.请即使申请配额.'
            ];
        }

        return [
            'status' => 0,
            'msg'    => '',
        ];

    }

    public function orderDecryptQuotaReset(): array {
        $decrypt_quota = DouDianOrderDecryptQuota::query()
            ->orderBy('id', 'desc')
            ->first();

        $decrypt_quota->flag = 2;
        $decrypt_quota->save();

        return ['code' => true, 'msg' => '成功'];
    }

}
