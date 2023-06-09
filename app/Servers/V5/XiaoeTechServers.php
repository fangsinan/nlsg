<?php

namespace App\Servers\V5;

use App\Models\Bliss\VipUserBindModel;
use App\Models\Bliss\VipUserModel;
use App\Models\User;
use App\Models\XiaoeTech\XeDistributor;
use App\Models\XiaoeTech\XeDistributorCustomer;
use App\Models\XiaoeTech\XeOrder;
use App\Models\XiaoeTech\XeOrderDistribute;
use App\Models\XiaoeTech\XeOrderGoods;
use App\Models\XiaoeTech\XeUser;
use App\Models\XiaoeTech\XeUserJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;
use function Symfony\Component\String\b;

class XiaoeTechServers
{
    public $err_msg = '';
    public $access_token = '';

    public function __construct()
    {
        $this->get_token();
    }

    public function test()
    {
//        try {
//            $XeUser = new XeUser();
//            $XeUser->xe_user_id = 'u_api_639969f9c4d2c_iqP78lagKb';
//            $XeUser->avatar = '';
//            $XeUser->nickname = '';
//            $XeUser->save();
//        } catch (\Exception $e) {
//            if(!checkDuplicateEntry($e)){
//                return $e->getMessage();
//            }
//            var_dump(1);
//        }

    }

    public function get_token($is_refresh = 0)
    {

        $token_key = 'xiaoe-tech-token';
        if (!$is_refresh) {
            $access_token = Redis::get($token_key);
            if ($access_token) {
                $this->access_token = $access_token;
                return $access_token;
            }
        }

        $paratms =
            [
                "app_id" => "appPfbUuN2M8786",
                "client_id" => "xopNbM35i9O5609",
                "secret_key" => "QS7bKFK2N4SRXTDM0Slcm4D5U5qL1Uo8",
                "grant_type" => "client_credential"    //获取token时， grant_type = client_credential
            ];

        $res = self::curlGet('https://api.xiaoe-tech.com/token', $paratms);
//        DB::table('nlsg_log_info')->insert([
//            'url' => 'token',
//            'parameter' => json_encode($paratms),
//            'message' => json_encode($res),
//            'created_at' => date('Y-m-d H:i:s', time())
//        ]);
        if (empty($res['body']['data']['access_token'])) {
            $this->err_msg = $res['body']['msg'];
            return false;
        }

        Redis::setex($token_key, 3000, $res['body']['data']['access_token']);
        $this->access_token = $res['body']['data']['access_token'];
        return $res['body']['data']['access_token'];
    }


    /**
     * 获取小鹅通订单
     * 一小时运行一次 todo
     */
    public function sync_order_list($is_init = 0)
    {

        if (!$this->access_token) {
            return $this->err_msg;
        }


        for ($i = 1; $i <= 1000; $i++) {

            $redis_page_index_key = 'xe_sync_order_list_page_index';
            $page_index = Redis::lpop($redis_page_index_key);

            if ($is_init) {
                $page_index = 1;
            }

            if (empty($page_index)) {
                return false;
            }

            $page_size = 100;
            $paratms = [
                'access_token' => $this->get_token(),
                'page' => intval($page_index),
                'page_size' => intval($page_size),
                'order_asc' => 'desc',
            ];

            $res = self::curlPost('https://api.xiaoe-tech.com/xe.ecommerce.order.list/1.0.0', $paratms);
//            DB::table('nlsg_log_info')->insert([
//                'url' => 'xe.ecommerce.order.list',
//                'line' => $res['body']['code'],
//                'parameter' => json_encode($paratms),
//                'message' => $res['body']['data']['total'] ?? 0,
//                'created_at' => date('Y-m-d H:i:s', time())
//            ]);

            if ($res['body']['code'] != 0) {
                if ($res['body']['code'] == 2008) {
                    $this->get_token(1);
                    continue;
                }
                $this->err_msg = $res['body']['msg'];
                return false;
            }


            $return_list = $res['body']['data']['list'] ?? [];

            if (empty($return_list)) {
                return false;
            } else {
                if ($is_init) {
                    Redis::del($redis_page_index_key);
                    $count = $res['body']['data']['total'];
                    $total_page = ceil($count / $page_size) + 1;
                    for ($i = 2; $i <= $total_page; $i++) {
                        var_dump($i);
                        Redis::rpush($redis_page_index_key, $i);
                    }
                }
            }

            foreach ($return_list as $order) {

                $order_info = $order['order_info'] ?? [];
                $good_list = $order['good_list'] ?? [];
                $buyer_info = $order['buyer_info'] ?? [];
                $payment_info = $order['payment_info'] ?? [];
                $price_info = $order['price_info'] ?? [];
                $ship_info = $order['ship_info'] ?? [];

                var_dump($order_info['order_id']);
                try {
                    //保存小鹅通用户
                    $XeUser = XeUser::query()->where('xe_user_id', $order_info['user_id'])->first();
                    if (!$XeUser) {
                        $XeUser = new XeUser();
                        $XeUser->xe_user_id = $order_info['user_id'];
                        $XeUser->avatar = $buyer_info['avatar_url'];
                        $XeUser->nickname = $buyer_info['nickname'];
                        $XeUser->phone = $buyer_info['phone_number'];
                        $XeUser->is_sync = 1;
                        $XeUser->save();
                    }
                } catch (\Exception $e) {
                    if(!checkDuplicateEntry($e)){
                        return $e->getMessage();
                    }
                }

                //保存推广员用户
                if ($order_info['share_user_id']) {
                    try {
                        $XeShareUser = XeUser::query()->where('xe_user_id', $order_info['share_user_id'])->first();
                        if (!$XeShareUser) {
                            $XeShareUser = new XeUser();
                            $XeShareUser->xe_user_id = $order_info['share_user_id'];
                            $XeShareUser->is_sync = 1;
                            $XeShareUser->save();
                        }
                    } catch (\Exception $e) {
                        if(!checkDuplicateEntry($e)){
                            return $e->getMessage();
                        }
                    }
                }

                try {
                    //查询订单是否存在
                    $XeOrder = XeOrder::query()->where('order_id', $order_info['order_id'])->first();
                    if (!$XeOrder) {
                        $XeOrder = new XeOrder();
                    }

                    foreach ($order_info as $key => $val) {

                        if ($val === '0000-00-00 00:00:00') {
                            $val = null;
                        }

                        if (in_array($key, [
                            'actual_fee', 'aftersale_show_state', 'aftersale_show_state_time', 'app_id', 'channel_bus_id', 'channel_type', 'check_state',
                            'created_time', 'deduct_amount', 'discount_amount', 'freight_actual_price', 'freight_original_price', 'goods_buy_num', 'goods_name',
                            'goods_original_total_price', 'goods_spu_sub_type', 'goods_spu_type', 'modified_amount', 'order_close_type', 'order_id', 'order_state',
                            'order_state_time', 'order_type', 'pay_state', 'pay_state_time', 'pay_type', 'refund_fee', 'refund_time', 'relation_order_appid',
                            'relation_order_id', 'relation_order_type', 'settle_state', 'settle_state_time', 'share_type', 'share_user_id', 'ship_way_choose_type',
                            'sub_order_type', 'trade_id', 'update_time', 'use_collection', 'user_id', 'wx_app_type'])) {
                            switch ($key) {
                                case 'user_id':
                                    $XeOrder->xe_user_id = $val;
                                    break;
                                case 'created_time':
                                    $XeOrder->xe_created_time = $val;
                                    break;
                                case 'update_time':
                                    $XeOrder->xe_update_time = $val;
                                    break;
                                default:
                                    $XeOrder->$key = $val;
                            }
                        }
                    }

                    foreach ($buyer_info as $k => $v) {
                        if (in_array($k, ['nickname', 'avatar_url', 'phone_number'])) {
                            $XeOrder->$k = $v;
                        }
                    }

                    foreach ($payment_info as $k => $v) {
                        if (in_array($k, ['third_order_id', 'out_order_id'])) {
                            $XeOrder->$k = $v;
                        }
                    }
                    foreach ($price_info as $k => $v) {
                        if (in_array($k, ['actual_price', 'freight_modified_price', 'freight_price', 'origin_price', 'total_modified_amount', 'total_price'])) {
                            $XeOrder->$k = $v;
                        }
                    }
                    foreach ($ship_info as $k => $v) {
                        if (in_array($k, ["city", "company", "confirm_time", "county", "detail", "express_id", "invalid_time", "phone", "province", "receiver", "remark", "ship_time", "user_id"])) {
                            if (in_array($k, ['confirm_time', 'ship_time', 'invalid_time']) && empty($v)) {
                                $v = null;
                            }
                            $key = 'ship_info_' . $k;
                            $XeOrder->$key = $v;
                        }
                    }

                    //判断是否是推广员
                    if ($XeOrder->is_distributor == 0
                        && $XeOrder->goods_name == '幸福学社合伙人'
                        && $XeOrder->goods_original_total_price == 258000
                        && $XeOrder->pay_state == 1
                        && $XeOrder->order_state == 4
                    ) {

                        var_dump($XeOrder->goods_name);
                        $res = $this->distributor_member_add('', $XeOrder->xe_user_id);
                        var_dump($res);
                        if (checkRes($res)) {
                            if (empty($res['is_exist'])) {
                                $XeOrder->is_distributor = 1;
                            } else {
                                $XeOrder->is_distributor = 2;
                            }
                        }
                    }

                    $XeOrder->save();
                } catch (\Exception $e) {
                    if(!checkDuplicateEntry($e)){
                        return $e->getMessage();
                    }
                }


                foreach ($good_list as $good) {


                    $XeOrderGoods = XeOrderGoods::query()->where('order_id', $order_info['order_id'])->where('sku_id', $good['sku_id'])->first();
                    if (!$XeOrderGoods) {
                        $XeOrderGoods = new XeOrderGoods();
                    }

                    $discounts_info = $good['discounts_info'] ?? [];
                    unset($good['discounts_info']);

                    $XeOrderGoods->xe_user_id = $order_info['user_id'];
                    $XeOrderGoods->order_id = $order_info['order_id'];
                    $XeOrderGoods->discount_amount_total = $discounts_info['discount_amount_total'] ?? 0;
                    $XeOrderGoods->discount_count = $discounts_info['discount_count'] ?? 0;
                    $XeOrderGoods->discount_desc = $discounts_info['discount_detail']['discount_desc'] ?? '';
                    $XeOrderGoods->discount_id = $discounts_info['discount_detail']['discount_id'] ?? '';
                    $XeOrderGoods->discount_name = $discounts_info['discount_detail']['discount_name'] ?? '';
                    $XeOrderGoods->discount_type = $discounts_info['discount_detail']['discount_type'] ?? '';
                    $XeOrderGoods->discount_price = $discounts_info['discount_detail']['discount_price'] ?? 0;

                    foreach ($good as $k => $v) {
                        if (in_array($k, [
                            "buy_num", "check_state", "discounts_info", "expire_desc", "expire_end", "expire_start",
                            "goods_desc", "goods_image", "goods_name", "goods_sn", "goods_spec_desc", "period_type",
                            "refund_state", "refund_state_desc", "relation_goods_id", "relation_goods_type", "relation_goods_type_desc",
                            "resource_id", "resource_type", "ship_state", "ship_state_desc", "sku_id", "sku_spec_code", "spu_id", "spu_type", "total_price", "unit_price"])) {

                            if ($v === '0000-00-00 00:00:00') {
                                $v = null;
                            }
                            $XeOrderGoods->$k = $v;
                        }

                    }

                    $XeOrderGoods->save();
                }
            }

            if ($is_init) {
                return false;
            }

        }

    }

    /**
     * 注册新用户
     */
    public function user_register($phone)
    {

        if (!$this->access_token) {
            return $this->err_msg;
        }

        try {
            //保存客户信息
            $baseUser = User::query()->where('phone', $phone)->first();
            if (!$baseUser) {
                $baseUser = new User();
                $baseUser->phone = strval($phone);
                $baseUser->nickname = substr_replace($phone, '****', 3, 4);
                $res = $baseUser->save();
                if (!$res) {
                    return '用户保存失败';
                }
            }
        } catch (\Exception $e) {
            if(!checkDuplicateEntry($e)){
                return $e->getMessage();
            }
        }

        //查询小鹅通用户
        $XeUser = XeUser::query()->where('phone', $phone)->first();
        if ($XeUser) {
            return ['user_id' => $XeUser->xe_user_id, 'created_at' => $XeUser->user_created_at];
        }

        if (empty($baseUser->headimg)) {
            $avatar = config('env.IMAGES_URL') . '/image/202009/13f952e04c720a550193e5655534be86.jpg';
        } else {
            $avatar = config('env.IMAGES_URL') . $baseUser->headimg;
        }

        $paratms = [
            'access_token' => $this->get_token(),
            'data' => [
                'phone' => strval($phone),
                'avatar' => $avatar,
                'nickname' => $baseUser->nickname,
            ],
        ];

        $res = self::curlPost('https://api.xiaoe-tech.com/xe.user.register/1.0.0', $paratms);
        if ($res['body']['code'] != 0) {
            $this->err_msg = $res['body']['msg'];
            return $res['body']['msg'];
        }

        if (empty($res['body']['data']['user_id'])) {
            return 'user_id为空';
        }
        try {
            $xe_user_id = $res['body']['data']['user_id'];
            $XeUser = XeUser::query()->where('xe_user_id', $xe_user_id)->first();
            if (!$XeUser) {
                $XeUser = new XeUser();
                $XeUser->xe_user_id = $xe_user_id;
                $XeUser->avatar = $avatar;
                $XeUser->phone = $phone;
                $XeUser->nickname = $baseUser->nickname;
                $XeUser->save();
            }
        } catch (\Exception $e) {
            if(!checkDuplicateEntry($e)){
                return $e->getMessage();
            }
        }
        return $res['body']['data'];
    }

    /**
     * @return string
     * 更新用户列表数据
     */
    public function sync_user_batch_get()
    {

        if (!$this->access_token) {
            return $this->err_msg;
        }

        do {

            $redis_page_index_key = 'xe_sync_user_batch_get_page_index';
            $page_index = Redis::get($redis_page_index_key) ?? '';
            $page_size = 50;
            $paratms = [
                'access_token' => $this->get_token(),
                'page_size' => intval($page_size),
            ];

            if ($page_index) {
                $paratms['es_skip'] = json_decode($page_index, true);
            }

            $res = self::curlPost('https://api.xiaoe-tech.com/xe.user.batch.get/2.0.0', $paratms);
            var_dump($paratms);

            if ($res['body']['code'] != 0) {
                $this->err_msg = $res['body']['msg'];
                return $this->err_msg;
            }

            $return_list = $res['body']['data']['list'] ?? [];

            if (empty($return_list)) {
                Redis::set($redis_page_index_key, '');
                return false;
            }

            $last = $return_list[count($return_list) - 1];
            if (!empty($last['es_skip'])) {
                Redis::set($redis_page_index_key, json_encode($last['es_skip']));
            }

            foreach ($return_list as $user) {
                var_dump($user['user_id']);
                try {
                    $XeUser = XeUser::query()->where('xe_user_id', $user['user_id'])->first();
                    if (!$XeUser) {
                        $XeUser = new XeUser();
                    }
                    $XeUser->xe_user_id = $user['user_id'];
                    $XeUser->wx_union_id = $user['wx_union_id'];
                    $XeUser->wx_open_id = $user['wx_open_id'];
                    $XeUser->wx_app_open_id = $user['wx_app_open_id'];
                    $XeUser->nickname = $user['user_nickname'];
                    $XeUser->user_created_at = $user['user_created_at'];
                    $XeUser->avatar = $user['avatar'];
                    $XeUser->phone = $user['bind_phone'];
                    $XeUser->save();
                } catch (\Exception $e) {
                    if(!checkDuplicateEntry($e)){
                        return $e->getMessage();
                    }
                }
            }

        } while ($return_list);

    }

    /**
     * 获取客户详情列表
     * 五分钟一次 todo
     */
    public function sync_user_info($is_init = 0)
    {

        if (!$this->access_token) {
            return $this->err_msg;
        }
        $redis_page_index_key = 'sync_user_info_user_ids';
        if ($is_init) {
            $user_id_list = XeUser::query()->where('is_sync', 1)->pluck('xe_user_id')->toArray();
            if (empty($user_id_list)) {
                return false;
            }
            $user_id_list_arr = array_chunk($user_id_list, 50);
            Redis::del($redis_page_index_key);
            foreach ($user_id_list_arr as $user_ids) {
                Redis::rpush($redis_page_index_key, json_encode($user_ids));
            }
            return false;
        }


        for ($i = 1; $i <= 1000; $i++) {

            $user_ids = json_decode(Redis::lpop($redis_page_index_key), true);
            if (empty($user_ids)) {
                return false;
            }

            $page_index = 1;
            $page_size = 50;
            $paratms = [
                'user_id_list' => $user_ids,
                'access_token' => $this->get_token(),
                'page' => intval($page_index),
                'page_size' => intval($page_size),
            ];

            var_dump($paratms);

            $res = self::curlPost('https://api.xiaoe-tech.com/xe.user.batch_by_user_id.get/1.0.0', $paratms);

//            DB::table('nlsg_log_info')->insert([
//                'url' => 'xe.user.batch_by_user_id.get',
//                'line' => $res['body']['code'],
//                'parameter' => json_encode($paratms),
////                'message'       =>  json_encode($res),
//                'created_at' => date('Y-m-d H:i:s', time())
//            ]);

            if ($res['body']['code'] != 0) {
                if ($res['body']['code'] == 2008) {
                    $this->get_token(1);
                }

                $this->err_msg = $res['body']['msg'];
                return $res['body']['msg'];
            }

            $return_list = $res['body']['data']['list'] ?? [];

            foreach ($return_list as $user) {

                try {
                    //保存小鹅通用户
                    $XeUser = XeUser::query()->where('xe_user_id', $user['user_id'])->first();
                    if ($XeUser) {
                        $XeUser->avatar = $user['avatar'];
                        $XeUser->phone = $user['bind_phone'];
                        $XeUser->phone_collect = $user['collect_phone'];
                        $XeUser->user_created_at = $user['user_created_at'];
                        $XeUser->nickname = $user['user_nickname'];
                        $XeUser->wx_union_id = $user['wx_union_id'];
                        $XeUser->wx_open_id = $user['wx_open_id'];
                        $XeUser->wx_app_open_id = $user['wx_app_open_id'];
                        $XeUser->is_sync = 2;
                        $XeUser->sync_time = times();
                        $XeUser->save();

                    }
                } catch (\Exception $e) {
                    if(!checkDuplicateEntry($e)){
                        return $e->getMessage();
                    }
                }
            }
        }

    }

    /**
     * 获取推广员列表
     * 5分钟一次 todo
     */
    public function sync_distributor_list($is_init = 0)
    {

        if (!$this->access_token) {
            return $this->err_msg;
        }

        for ($i = 1; $i <= 1000; $i++) {

            $redis_page_index_key = 'xe_get_distributor_list_page_index';
            $page_index = Redis::lpop($redis_page_index_key);
            if ($is_init) {
                $page_index = 1;
            }

            if (empty($page_index)) {
                return false;
            }

            $page_size = 50;
            $paratms = [
                'access_token' => $this->get_token(),
                'page_index' => intval($page_index),
                'page_size' => intval($page_size),
            ];

            var_dump($paratms);
            $res = self::curlPost('https://api.xiaoe-tech.com/xe.distributor.list.get/1.0.0', $paratms);
//            DB::table('nlsg_log_info')->insert([
//                'url' => 'xe.distributor.list.get',
//                'line' => $res['body']['code'],
//                'parameter' => json_encode($paratms),
//                'message' => $res['body']['data']['count'] ?? 0,
//                'created_at' => date('Y-m-d H:i:s', time())
//            ]);

            if ($res['body']['code'] != 0) {
                if ($res['body']['code'] == 2008) {
                    $this->get_token(1);
                }
                $this->err_msg = $res['body']['msg'];
                return $res['body']['msg'];
            }

            $return_list = $res['body']['data']['return_list'] ?? [];

            if (empty($return_list)) {
                return false;
            } else {
                if ($is_init) {
                    //清除过期数据
                    XeDistributor::query()->where('refresh_time', '<=', date("Y-m-d H:i:s", strtotime("-2 hour")))->delete();
                    Redis::del($redis_page_index_key);
                    $count = $res['body']['data']['count'];
                    $total_page = ceil($count / $page_size) + 1;
                    for ($i = 2; $i <= $total_page; $i++) {
                        var_dump($i);
                        Redis::rpush($redis_page_index_key, $i);
                    }
                }
            }

            foreach ($return_list as $distributor) {

                var_dump($distributor['user_id']);
                try {
                    //保存小鹅通用户
                    $XeUser = XeUser::query()->where('xe_user_id', $distributor['user_id'])->first();
                    if (!$XeUser) {
                        $XeUser = new XeUser();
                    }
                    $XeUser->xe_user_id = $distributor['user_id'];
                    $XeUser->avatar = $distributor['avatar'];
                    $XeUser->nickname = $distributor['nickname'];
                    $XeUser->is_sync = 1;
                    $XeUser->save();

                } catch (\Exception $e) {
                    if(!checkDuplicateEntry($e)){
                        return $e->getMessage();
                    }
                }

                try {

                    //保存推广员
                    $XeDistributor = XeDistributor::query()->where('xe_user_id', $distributor['user_id'])->first();
                    if (!$XeDistributor) {
                        $XeDistributor = new XeDistributor();
                    }

                    $XeDistributor->xe_user_id = $distributor['user_id'];
                    $XeDistributor->nickname = $distributor['nickname'];
                    $XeDistributor->level = $distributor['level'];
                    $XeDistributor->group_name = $distributor['group_name'];
                    $XeDistributor->group_id = $distributor['group_id'];
                    $XeDistributor->avatar = $distributor['avatar'];
                    $XeDistributor->refresh_time = times();
                    $XeDistributor->status = 1;
                    $XeDistributor->is_sync_customer = 1;
                    $XeDistributor->save();

                } catch (\Exception $e) {

                    if(!checkDuplicateEntry($e)){
                        return $e->getMessage();
                    }
                }
            }

            if ($is_init) {
                return false;
            }

        }
    }
    /**
     * 获取客户详情列表
     * 五分钟一次 todo
     */
    public function sync_distributor_info($is_init = 0)
    {
        if (!$this->access_token) {
            return $this->err_msg;
        }

        $redis_page_index_key = 'sync_distributor_info_user_ids';

        if ($is_init) {
            $user_id_list = XeDistributor::query()->where('status', 1)->pluck('xe_user_id')->toArray();
            if (empty($user_id_list)) {
                return false;
            }
            Redis::del($redis_page_index_key);
            foreach ($user_id_list as $user_id) {
                Redis::rpush($redis_page_index_key, $user_id);
            }
            return false;
        }

        for ($i = 1; $i <= 10000; $i++) {

            var_dump($i);
            $user_id = Redis::lpop($redis_page_index_key);
            if (empty($user_id)) {
                return false;
            }

            $paratms = [
                'user_id' => $user_id,
                'access_token' => $this->get_token(),
            ];

            $res = self::curlPost('https://api.xiaoe-tech.com/xe.distributor.info.get/1.0.0', $paratms);
            var_dump($res);

            if ($res['body']['code'] != 0) {
                if ($res['body']['code'] == 2008) {
                    $this->get_token(1);
                }

                $this->err_msg = $res['body']['msg'];
                return $res['body']['msg'];
            }

            $data = $res['body']['data'] ?? [];

            if($data){

                $XeDistributor=XeDistributor::query()->where('xe_user_id',$user_id)->first();
                if($XeDistributor){
                    $XeDistributor->group_id=$data['group_id'];
                    $XeDistributor->group_name=$data['group_name'];
                    $XeDistributor->level=$data['level'];
                    $XeDistributor->xe_parent_user_id=$data['parent_user_id'];
                    $XeDistributor->total_amount=$data['total_amount'];
                    $XeDistributor->underling_number=$data['underling_number'];
                    $XeDistributor->save();
                }
            }

        }
    }


    /**
     * 推广员客户列表
     * 一小时一次 todo
     */
    public function sync_distributor_customer_list($is_init = 0,$task=0)
    {

//        DB::table('nlsg_log_info')->insert([
//            'url' => 'sync_distributor_customer_list',
//            'parameter' => $is_init.'-'.$task,
//            'created_at' => date('Y-m-d H:i:s', time())
//        ]);

        if (!$this->access_token) {
            return $this->err_msg;
        }

        //获取推广员列表
        if ($is_init) {
            $batch_number = date('YmdHis');
            $redis_page_index_key = 'xe_sync_distributor_customer_list_page_index';
            Redis::del($redis_page_index_key);
            $XeDistributorList = XeDistributor::query()->where('is_sync_customer', 1)->get();
            foreach ($XeDistributorList as $k => $XeDistributor) {
                var_dump($k);
                Redis::rpush($redis_page_index_key, json_encode(['batch_number' => $batch_number, 'xe_user_id' => $XeDistributor->xe_user_id, 'page_index' => 1]));
            }

        } else {
            $this->do_distributor_customer_list();
        }

    }

    public function sync_fast_distributor_customer_list()
    {
        $redis_page_index_key = 'xe_sync_distributor_customer_list_page_index';
        $XeDistributorList = XeDistributor::query()->where('is_sync_customer', 1)->get();
        $total = Redis::llen($redis_page_index_key);
        if ($total > 3000) {
            return false;
        }
        foreach ($XeDistributorList as $k => $XeDistributor) {
            var_dump($k);
            Redis::rpush($redis_page_index_key, json_encode(['is_fast' => 1, 'customer_number' => $XeDistributor->customer_number, 'xe_user_id' => $XeDistributor->xe_user_id, 'page_index' => 1]));
        }
    }


    public function do_distributor_customer_list()
    {

        $redis_page_index_key = 'xe_sync_distributor_customer_list_page_index';

        for ($i = 1; $i <= 100; $i++) {

            $page_index_json = Redis::lpop($redis_page_index_key);

            if (!$page_index_json) {
                return false;
            }

            $page_index_arr = json_decode($page_index_json, true);
            $xe_user_id = $page_index_arr['xe_user_id'] ?? 0;
            $page_index = $page_index_arr['page_index'] ?? 0;
            $is_fast = $page_index_arr['is_fast'] ?? 0;
            $customer_number = $page_index_arr['customer_number'] ?? 0;
            $batch_number = $page_index_arr['batch_number'] ?? 0;

            if (empty($xe_user_id)) {
                return false;
            }
            if (empty($page_index)) {
                return false;
            }

            $page_size = 100;
            $paratms = [
                'access_token' => $this->get_token(),
                'user_id' => $xe_user_id,
                'page_index' => intval($page_index),
                'page_size' => intval($page_size),
            ];

            var_dump($i);
            var_dump($paratms);

            try {

                $res = self::curlPost('https://api.xiaoe-tech.com/xe.distributor.member.sub_customer/1.0.0', $paratms);

                $page_count=0;
                if(isset($res['body']['data']['list'])){
                    $page_count=count($res['body']['data']['list']);
                }

                var_dump($page_count);

                if ($res['body']['code'] != 0) {

                    Redis::rpush($redis_page_index_key, $page_index_json);

                    if ($res['body']['code'] == 2008) {
                        $this->get_token(1);
                    }

                    if ($res['body']['code'] == 1002) {
                        sleep(rand(1,3));
                    }

                    $this->err_msg = $res['body']['msg'];
                    var_dump($this->err_msg);
                    continue;
                }

                $return_list = $res['body']['data']['list'] ?? [];

                if ($page_index == 1 && $return_list) {

                    $count = $res['body']['data']['count'];
                    XeDistributor::query()->where('xe_user_id', $xe_user_id)->update(['customer_number' => $count]);

                    if ($is_fast && $customer_number) {
                        $count = $count - $customer_number;
                        if ($count < 0) {
                            $count = 1;
                        }
                    }

                    $total_page = ceil($count / $page_size) + 1;

                    for ($i = 2; $i <= $total_page; $i++) {
                        var_dump($i);
                        Redis::rpush($redis_page_index_key, json_encode(['is_fast'=>$is_fast,'customer_number'=>$customer_number,'batch_number' => $batch_number, 'xe_user_id' => $xe_user_id, 'page_index' => $i]));
                    }
                }

            } catch (\Exception $e) {

                Redis::rpush($redis_page_index_key, $page_index_json);
                var_dump($e->getMessage());
                continue;
            }

            foreach ($return_list as $customer) {

//                if(!$is_fast){
//
//                    DB::table('nlsg_xe_log')->insert([
//                        'url' => $xe_user_id,
//                        'xe_user_id' => $customer['sub_user_id']??'',
//                        'file' => $page_count??0,
//                        'line'=>$res['body']['data']['count']??0,
//                        'parameter' => json_encode($customer),
//                        'batch_number' => $batch_number,
//                        'page_index' => $page_index,
//                        'created_at' => date('Y-m-d H:i:s', time())
//                    ]);
//                }

                try {

                    //保存小鹅通用户
                    $XeUser = XeUser::query()->where('xe_user_id', $customer['sub_user_id'])->first();
                    if (!$XeUser) {
                        $XeUser = new XeUser();
                        $XeUser->xe_user_id = $customer['sub_user_id'];
                        $XeUser->avatar = $customer['wx_avatar'];
                        $XeUser->nickname = $customer['wx_nickname'];
                        $XeUser->is_sync = 1;
                        $XeUser->save();
                    }

                } catch (\Exception $e) {

                    if(!checkDuplicateEntry($e)){
                        return $e->getMessage();
                    }
                }

                try {

                    //保存推广员客户
                    $XeDistributorCustomer = XeDistributorCustomer::query()->where('xe_user_id', $xe_user_id)->where('sub_user_id', $customer['sub_user_id'])->first();
                    if (!$XeDistributorCustomer) {
                        $XeDistributorCustomer = new XeDistributorCustomer();
                    }

                    $XeDistributorCustomer->xe_user_id = $xe_user_id;
                    $XeDistributorCustomer->sub_user_id = $customer['sub_user_id'];
                    $XeDistributorCustomer->wx_nickname = $customer['wx_nickname'];
                    $XeDistributorCustomer->wx_avatar = $customer['wx_avatar'];
                    $XeDistributorCustomer->order_num = $customer['order_num'];
                    $XeDistributorCustomer->sum_price = $customer['sum_price'];
                    $XeDistributorCustomer->bind_time = $customer['bind_time'];
                    $XeDistributorCustomer->status = $customer['status'];
                    $XeDistributorCustomer->status_text = $customer['status_text'];
                    $XeDistributorCustomer->remain_days = $customer['remain_days'];
                    $XeDistributorCustomer->expired_at = $customer['expired_at'];
                    $XeDistributorCustomer->is_editable = $customer['is_editable'];
                    $XeDistributorCustomer->is_anonymous = $customer['is_anonymous'] ? 1 : 0;
                    $XeDistributorCustomer->refresh_time = times();
                    $XeDistributorCustomer->batch_number = $batch_number;
                    $XeDistributorCustomer->page_index = $page_index;

                    if($XeDistributorCustomer->refresh_number){
                        $XeDistributorCustomer->refresh_number=$XeDistributorCustomer->refresh_number+1;
                    }else{
                        $XeDistributorCustomer->refresh_number=1;
                    }

                    $XeDistributorCustomer->save();

                } catch (\Exception $e) {
                    if(!checkDuplicateEntry($e)){
                        return $e->getMessage();
                    }
                }
            }

            var_dump('end');

        }

    }

    /**
     * @param $xe_user_id
     * @param $page_index
     * @return mixed
     * 获取推广员下的客户
     */
    public function get_page_distributor_customer_list($xe_user_id,$page_index=1){
        $page_size = 100;
        $paratms = [
            'access_token' => $this->get_token(),
            'user_id' => $xe_user_id,
            'page_index' => intval($page_index),
            'page_size' => intval($page_size),
        ];
        $res = self::curlPost('https://api.xiaoe-tech.com/xe.distributor.member.sub_customer/1.0.0', $paratms);
        return $res['body'];
    }


    /**
     * 同步订单详情
     */
    public function sync_order_detail($is_init = 0)
    {

        if (!$this->access_token) {
            return $this->err_msg;
        }

        $redis_page_index_key = 'sync_order_detail_order_ids';
        if ($is_init) {

            $list = XeOrder::query()->where('share_type', 5)->where('order_state', 4)->whereIn('settle_state', [0, 1])->get()->toArray();
            if (empty($list)) {
                return false;
            }
            Redis::del($redis_page_index_key);
            foreach ($list as $order) {
                var_dump($order['order_id']);
                Redis::rpush($redis_page_index_key, $order['order_id']);
            }
            return false;
        }

        for ($i = 1; $i <= 1000; $i++) {

            $order_id = Redis::lpop($redis_page_index_key);

            if (empty($order_id)) {
                return false;
            }

            $paratms = [
                'access_token' => $this->get_token(),
                'order_id' => $order_id,
            ];
            var_dump($paratms);
            $res = self::curlPost('https://api.xiaoe-tech.com/xe.order.detail/1.0.0', $paratms);
//            DB::table('nlsg_log_info')->insert([
//                'url' => 'xe.order.detail',
//                'line' => $res['body']['code'],
//                'parameter' => json_encode($paratms),
//                'created_at' => date('Y-m-d H:i:s', time())
//            ]);

            if ($res['body']['code'] != 0) {

                if ($res['body']['code'] == 2008) {
                    $this->get_token(1);
                }

                $this->err_msg = $res['body']['msg'];
                return $res['body']['msg'];
            }

            $distribute_info = $res['body']['data']['distribute_info'] ?? [];

            if ($distribute_info) {


                try {
                    //保存分销
                    $XeOrderDistribute = XeOrderDistribute::query()->where('order_id', $order_id)->first();
                    if (!$XeOrderDistribute) {
                        $XeOrderDistribute = new XeOrderDistribute();
                    }

                    $XeOrderDistribute->order_id = $order_id;
                    $XeOrderDistribute->share_user_id = $distribute_info['share_user_id'];
                    $XeOrderDistribute->share_user_nickname = $distribute_info['share_user_nickname'];
                    $XeOrderDistribute->distribute_price = $distribute_info['distribute_price'];
                    $XeOrderDistribute->superior_distribute_user_id = $distribute_info['superior_distribute_user_id'];
                    $XeOrderDistribute->superior_distribute_user_nickname = $distribute_info['superior_distribute_user_nickname'];
                    $XeOrderDistribute->superior_distribute_price = $distribute_info['superior_distribute_price'];
                    $XeOrderDistribute->save();

                } catch (\Exception $e) {

                    if(!checkDuplicateEntry($e)){
                        return $e->getMessage();
                    }
                }

            }

        }

    }

    public function sync_user_userid()
    {

        //同步user_id
        $list = XeUser::query()->from(XeUser::DB_TABLE . ' as XeUser')
            ->select('XeUser.*', 'User.unionid', 'User.id as base_user_id')
            ->leftJoin('nlsg_user as User', 'User.phone', '=', 'XeUser.phone')
            ->whereRaw(DB::raw("XeUser.phone <> '' and XeUser.user_id=0 and User.id is not null "))
            ->orderBy('XeUser.id', 'asc')
            ->get();

        foreach ($list as $k => $XeUser) {

            $XeUser->user_id = $XeUser->base_user_id;
            $XeUser->save();

            $user_id = $XeUser->base_user_id;
            $unionid = $XeUser->unionid;

            if ($unionid) {

                //如果没有客服
                if (!DB::table('crm_live_user_waiter')->where([
                    "user_id" => $user_id,
                ])->count()) {

                    $old_check = DB::table('nlsg_user_wechat as uw')
                        ->join('crm_live_waiter_wechat as ww',
                            'uw.follow_user_userid', '=', 'ww.follow_user_userid'
                        )
                        ->where('uw.unionid', '=', $unionid)
                        ->select(['ww.follow_user_userid', 'uw.follow_user_createtime as bind_admin_time', 'ww.admin_id'])
                        ->first();

                    if ($old_check) {
                        $savedata = [
                            'admin_id' => $old_check->admin_id,
                            'user_id' => $user_id,
                            'follow_user_userid' => $old_check->follow_user_userid,
                        ];
                        var_dump($savedata);
                        DB::table('crm_live_user_waiter')
                            ->insert($savedata);
                    }
                }

            }


        }
    }

    /**
     * 新增推广员
     */
    public function distributor_member_add($phone = '', $user_id = '', $params = [])
    {
        if (empty($phone) && empty($user_id)) {
            return '参数错误';
        }

        if ($phone) {
            $res = $this->user_register($phone);
            if (!checkRes($res)) {
                return $res;
            }
            $user_id = $res['user_id'] ?? '';
        }

        if (!$user_id) {
            return '客户不存在';
        }

        $XeDistributor = XeDistributor::query()->where('xe_user_id', $user_id)->first();
        if ($XeDistributor) {
            return ['user_id' => $user_id, 'is_exist' => 1, 'created_at' => $XeDistributor->created_at];
        }

        $paratms = [
            'access_token' => $this->get_token(),
            'user_id' => $user_id,
        ];

        $res = self::curlPost('https://api.xiaoe-tech.com/xe.distributor.member.add/1.0.0', $paratms);
        if ($res['body']['code'] != 0 && $res['body']['code'] != 20003) {
            $this->err_msg = $res['body']['msg'];
            return $res['body']['msg'];
        }

        $XeDistributor = new XeDistributor();
        $XeDistributor->xe_user_id = $user_id;
        $XeDistributor->level = 1;
        $XeDistributor->group_id = 0;
        $XeDistributor->group_name = '合伙人';
        $XeDistributor->source = $params['source'] ?? 0;
        $XeDistributor->admin_id = $params['admin_id'] ?? 0;
        $XeDistributor->is_sync_customer = 1;
        $XeDistributor->save();

        $is_exist = 0;
        if ($res['body']['code'] == 20003) {
            $is_exist = 1;
        }

        return ['user_id' => $user_id, 'is_exist' => $is_exist, 'created_at' => date('Y-m-d H:i:s')];

    }

    /**
     * 批量添加推广员
     */
    public function distributor_member_batch_add($phone_arr, $parent_phone = '')
    {

        if (count($phone_arr) > 100) {
            return '最多添加100个';
        }

        $user_id_arr = [];
        foreach ($phone_arr as $phone) {
            $res = $this->distributor_member_add($phone);
            if (!checkRes($res)) {
                return $res;
            }
            $user_id_arr[] = $res['user_id'];
        }

        if (empty($parent_phone)) {
            return $user_id_arr;
        }

        $res = $this->distributor_member_add($parent_phone);
        if (!checkRes($res)) {
            return $res;
        }
        $parent_user_id = $res['user_id'];
        foreach ($user_id_arr as $user_id) {
            $res = $this->distributor_superior_set($parent_user_id, $user_id);
            if (!checkRes($res)) {
                return $res;
            }
        }

        return true;
    }

    /**
     * 推广员上级
     */
    public function distributor_superior_set($parent_user_id, $user_id)
    {


        $XeDistributor = XeDistributor::query()->where('xe_user_id', $user_id)->first();
        if (!$XeDistributor) {
            return '推广员不已存在';
        }

        $XeParentDistributor = XeDistributor::query()->where('xe_user_id', $parent_user_id)->first();
        if (!$XeParentDistributor) {
            return '上级推广员不已存在';
        }

        $paratms = [
            'access_token' => $this->get_token(),
            'user_id' => $user_id,
            'parent_user_id' => $parent_user_id,
        ];

        $res = self::curlPost('https://api.xiaoe-tech.com/xe.distributor.superior.set/1.0.0', $paratms);
        if ($res['body']['code'] != 0) {
            $this->err_msg = $res['body']['msg'];
            return false;
        }

        $XeDistributor->xe_parent_user_id = $parent_user_id;
        $XeDistributor->save();

        return true;

    }

    /**
     * 推广员绑定客户
     */
    public function distributor_member_bind($parent_user_id, $user_id = '', $phone = '')
    {

        if ($phone) {
            $res = $this->user_register($phone);
            if (!checkRes($res)) {
                return $res;
            }
            $user_id = $res['user_id'] ?? '';
        }

        if (empty($user_id)) {
            return ['code' => false, 'msg' => '客户不存在'];
        }

        $XeDistributor = XeDistributor::query()->where('xe_user_id', $parent_user_id)->first();
        if (!$XeDistributor) {
            return ['code' => false, 'msg' => '推广员不存在'];
        }

        $XeDistributorCustomer = XeDistributorCustomer::query()
            ->where('xe_user_id', $parent_user_id)
            ->where('sub_user_id', $user_id)
            ->where('status', 1)
            ->first();

        if ($XeDistributorCustomer) {
            return ['code' => true, 'msg' => '成功', 'created_at' => $XeDistributorCustomer->bind_time];
        }

        $paratms = [
            'access_token' => $this->get_token(),
            'user_id' => $user_id,
            'parent_user_id' => $parent_user_id,
        ];

        $res = self::curlPost('https://api.xiaoe-tech.com/xe.distributor.member.bind/1.0.0', $paratms);
        if ($res['body']['code'] != 0) {
            $this->err_msg = $res['body']['msg'];
            return ['code' => false, 'msg' => $this->err_msg];
        }
        try {
            $XeDistributorCustomer = XeDistributorCustomer::query()
                ->where('xe_user_id', $parent_user_id)
                ->where('sub_user_id', $user_id)
                ->first();
            if(!$XeDistributorCustomer){
                $XeDistributorCustomer = new XeDistributorCustomer();
            }
            $XeDistributorCustomer->xe_user_id = $parent_user_id;
            $XeDistributorCustomer->sub_user_id = $user_id;
            $XeDistributorCustomer->status = 1;
            $XeDistributorCustomer->status_text = '绑定中';
            $XeDistributorCustomer->remain_days = 365;
            $XeDistributorCustomer->bind_time = times();
            $XeDistributorCustomer->expired_at = times(strtotime('+1 years'));
            $XeDistributorCustomer->refresh_time = times();
            $XeDistributorCustomer->save();
        } catch (\Exception $e) {
            if(!checkDuplicateEntry($e)){
                return ['code' => false, 'msg' => $e->getMessage()];
            }
        }
        return ['code' => true, 'msg' => '成功', 'created_at' => times()];

    }

    /**
     * 修改/解除绑定关系
     */
    public function distributor_member_change($user_id, $former_parent_user_id='', $parent_user_id = '')
    {

        if (empty($user_id)) {
            return '客户不存在';
        }


        if($former_parent_user_id){
            $XeOldDistributorCustomer = XeDistributorCustomer::query()
                ->where('xe_user_id', $former_parent_user_id)
                ->where('sub_user_id', $user_id)
                ->where('status', 1)
                ->first();
        }else{
            $XeOldDistributorCustomer = XeDistributorCustomer::query()
                ->where('sub_user_id', $user_id)
                ->where('status', 1)
                ->first();
        }


        if (!$XeOldDistributorCustomer) {
            return '原推广员未绑定客户';
        }

        $former_parent_user_id = $XeOldDistributorCustomer->xe_user_id;

//        $XeDistributor = XeDistributor::query()->where('xe_user_id', $former_parent_user_id)->first();
//        if (!$XeDistributor) {
//            return '推广员不存在';
//        }

        $paratms = [
            'access_token' => $this->get_token(),
            'user_id' => $user_id,
            'parent_user_id' => $parent_user_id,
            'former_parent_user_id' => $former_parent_user_id,
        ];

        $res = self::curlPost('https://api.xiaoe-tech.com/xe.distributor.member.change/1.0.0', $paratms);
        if($parent_user_id){
            if ($res['body']['code'] != 0 ) {
                $this->err_msg = $res['body']['msg'];
                return $this->err_msg;
            }
        }else{
            if ($res['body']['code'] != 0 && $res['body']['code'] !=  20200) {
                $this->err_msg = $res['body']['msg'];
                return $this->err_msg;
            }
        }

        if ($parent_user_id) {
            $XeDistributorCustomer = XeDistributorCustomer::query()
                ->where('xe_user_id', $parent_user_id)
                ->where('sub_user_id', $user_id)
                ->first();
            if(!$XeDistributorCustomer){
                $XeDistributorCustomer = new XeDistributorCustomer();
            }
            $XeDistributorCustomer->xe_user_id = $parent_user_id;
            $XeDistributorCustomer->sub_user_id = $user_id;
            $XeDistributorCustomer->status = 1;
            $XeDistributorCustomer->status_text = '绑定中';
            $XeDistributorCustomer->remain_days = 365;
            $XeDistributorCustomer->bind_time = times();
            $XeDistributorCustomer->expired_at = times(strtotime('+1 years'));
            $XeDistributorCustomer->refresh_time = times();
            $XeDistributorCustomer->save();
        }

        $XeOldDistributorCustomer->remain_days = 0;
        $XeOldDistributorCustomer->status = 0;
        $XeOldDistributorCustomer->status_text = '已解绑';
        $XeOldDistributorCustomer->save();

        return true;

    }


    /**
     * 发送get请求
     * @param
     * @return
     */
    public static function curlGet($url, $queryparas = array(), $timeout = 2, $header = array(), $proxy = array())
    {
        if (!empty($queryparas)) {
            if (is_array($queryparas)) {
                $postData = http_build_query($queryparas);
                $url .= strpos($url, '?') ? '' : '?';
                $url .= $postData;
            } else if (is_string($queryparas)) {
                $url .= strpos($url, '?') ? '' : '?';
                $url .= $queryparas;
            }
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if (!empty($header) && is_array($header)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }

        if (!empty($proxy)) {
            curl_setopt($ch, CURLOPT_PROXYAUTH, 1);
            curl_setopt($ch, CURLOPT_PROXY, $proxy['ip']);
            curl_setopt($ch, CURLOPT_PROXYPORT, $proxy['port']);
            curl_setopt($ch, CURLOPT_PROXYTYPE, 0);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);
        curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        $output = curl_exec($ch);
        if (is_array(json_decode($output, true))) {
            $output = json_decode($output, true);
        }

        $result['status_code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $result['body'] = $output;

        curl_close($ch);
        return $result;
    }

    /**
     * 发送post请求
     * @param
     * @return
     */
    public static function curlPost($url, $postdata = array(), $queryparas = array(), $header = array(), $timeout = 20, $proxy = array())
    {
        if (!empty($queryparas)) {
            if (is_array($queryparas)) {
                $postData = http_build_query($queryparas);
                $url .= strpos($url, '?') ? '' : '?';
                $url .= $postData;
            } else if (is_string($queryparas)) {
                $url .= strpos($url, '?') ? '' : '?';
                $url .= $queryparas;
            }
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if (!empty($header) && is_array($header)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);
        curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($proxy)) {
            curl_setopt($ch, CURLOPT_PROXYAUTH, 1);
            curl_setopt($ch, CURLOPT_PROXY, $proxy['ip']);
            curl_setopt($ch, CURLOPT_PROXYPORT, $proxy['port']);
            curl_setopt($ch, CURLOPT_PROXYTYPE, 0);
        }
        curl_setopt($ch, CURLOPT_POST, TRUE);
        if (!empty($header)) {
            $header_str = implode('', $header);
            if (strpos($header_str, "application/x-www-form-urlencoded") !== false) {
                $postdata = http_build_query($postdata);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
            }
        } else {
            curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type:application/json"]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postdata));
        }

        $output = curl_exec($ch);
        if (is_array(json_decode($output, true))) {
            $output = json_decode($output, true);
        }

        $result['status_code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $result['body'] = $output;

        curl_close($ch);
        return $result;
    }

    /**
     * 发送Del请求
     * @param
     * @return
     */
    public static function curlDel($url, $queryparas = array(), $postdata = array(), $header = array(), $timeout = 2, $proxy = array())
    {
        if (!empty($queryparas)) {
            if (is_array($queryparas)) {
                $postData = http_build_query($queryparas);
                $url .= strpos($url, '?') ? '' : '?';
                $url .= $postData;
            } else if (is_string($queryparas)) {
                $url .= strpos($url, '?') ? '' : '?';
                $url .= $queryparas;
            }
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if (!empty($header) && is_array($header)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);
        curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        if (!empty($proxy)) {
            curl_setopt($ch, CURLOPT_PROXYAUTH, 1);
            curl_setopt($ch, CURLOPT_PROXY, $proxy['ip']);
            curl_setopt($ch, CURLOPT_PROXYPORT, $proxy['port']);
            curl_setopt($ch, CURLOPT_PROXYTYPE, 0);
        }

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);

        $output = curl_exec($ch);
        if (is_array(json_decode($output, true))) {
            $output = json_decode($output, true);
        }

        $result['status_code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $result['body'] = $output;

        curl_close($ch);
        return $result;
    }


    /**
     * 同步小鹅数据到幸福学社
     */
    public function sync_xe_xfxs($type)
    {

        switch ($type) {

            case 'rpush_add_vip_user':
                self::rpush_add_vip_user();
                break;

            case 'lpop_add_vip_user':
                self::lpop_add_vip_user();
                break;

            case 'rpush_add_vip_user_inviter':
                self::rpush_add_vip_user_inviter();
                break;

            case 'lpop_add_vip_user_inviter':
                self::lpop_add_vip_user_inviter();
                break;

            case 'rpush_vip_user_bind':
                self::rpush_vip_user_bind();
                break;

            case 'rpush_one_vip_user_bind':
                self::rpush_one_vip_user_bind();
                break;

            case 'lpop_vip_user_bind':

                self::lpop_vip_user_bind();
                break;

            case 'clear_vip':
                self::clear_vip();
                break;

            case 'set_distributor_base_phone':
                self::set_distributor_base_phone();
                break;
        }

        return true;
    }

    //php artisan TestJobb rpush_add_vip_user
    public static function rpush_add_vip_user(){

        var_dump('rpush_add_vip_user');

        self::set_distributor_base_phone();

        $redis_key = 'xe_distributor_to_vip_user';

        $list = XeDistributor::query()->from(XeDistributor::DB_TABLE . ' as XeDistributor')
            ->select('XeDistributor.base_phone','XeDistributor.group_id','XeDistributor.id', 'XeUser.phone', 'XeUser.phone_collect', 'XeUser.nickname', 'XeUser.xe_user_id', 'XeUser.user_id')
            ->leftJoin(XeUser::DB_TABLE . ' as XeUser', 'XeUser.xe_user_id', 'XeDistributor.xe_user_id')
            ->where('XeDistributor.status', 1)
//            ->where('XeDistributor.base_phone', 15188768848)
            ->where('XeDistributor.group_id','<>', 59524)
            ->where('XeDistributor.remark','=', '')
            ->where('XeDistributor.base_phone','<>', '')
            ->get()->toArray();

        foreach ($list as $k => $distributor) {
            var_dump($k);
            var_dump($distributor);
            $json_str = json_encode($distributor);
            Redis::rpush($redis_key, $json_str);
        }
    }


    //php artisan TestJobb lpop_add_vip_user
    public static function lpop_add_vip_user(){

        var_dump('lpop_add_vip_user');

        $redis_key = 'xe_distributor_to_vip_user';

        $flag = True;

        while ($flag) {

            $data = Redis::lpop($redis_key);

            var_dump($data);

            if ($data) {

                $distributor = json_decode($data, true);
                if (!$distributor) {
                    continue;
                }

                $group_id = 1;
                $xe_group_id = $distributor['group_id'];
                $id = $distributor['id'];
                $xe_user_id = $distributor['xe_user_id'];

                $base_phone = $distributor['base_phone'];

                //59524 9.9分销分组 不是合伙人
                if($xe_group_id==59524){
                    continue;
                }

                switch ($xe_group_id){
                    case 57419:
                        $group_id=2;
                        break;
                    case 59607:
                        $group_id=3;
                        break;
                }

                var_dump($base_phone);

                if (empty($base_phone)) {
                    continue;
                }

                $XeUser = XeUser::query()->where('xe_user_id', $xe_user_id)->first();
                if (!$XeUser) {
                    continue;
                }

                $User = User::query()->where('phone', $base_phone)->first();

                if ($User) {

                    $User->xfxs_login = 1;
                    $User->save();

                } else {

                    $User = new User();
                    $User->phone = $base_phone;
                    $User->intro = '幸福学社合伙人';
                    $User->app_project_type = 2;
                    $User->xfxs_login = 1;
                    $User->nickname = $distributor['nickname'];
                    $User->save();
                }

                $XeUser->user_id = $User->id;
                $XeUser->save();

                $VipUserModel = VipUserModel::query()->where('username', $base_phone)->where('status', 1)->first();

                if ($VipUserModel) {

                    $VipUserModel->user_id=$User->id;
                    $VipUserModel->group_id=$group_id;

                    $VipUserModel->save();

                    XeDistributor::query()->where('id', $id)->update(['user_id' => $User->id, 'remark' => '小鹅通合伙人更新-'.$VipUserModel->id.'-'.$User->phone]);

                } else {

                    //首先查询2580订单的支付时间为合伙人的开始时间 剩余的开始时间定为2023-01-01
                    $XeOrder = XeOrder::query()->where([
                        'xe_user_id' => $xe_user_id,
                        'goods_name' => '幸福学社合伙人',
                        'goods_original_total_price' => 258000,
                        'pay_state' => 1,
                        'order_state' => 4,
                    ])->first();

                    if ($XeOrder) {
                        $start_time = $XeOrder->pay_state_time;
                    } else {
                        $start_time = '2023-01-01 00:00:00';
                    }

                    $expire_time = date('Y-m-d 23:59:59', strtotime("+1 years", strtotime($start_time)));

                    $VipUserModel = new VipUserModel();
                    $VipUserModel->group_id = $group_id;
                    $VipUserModel->user_id = $User->id;
                    $VipUserModel->username = $User->phone;
                    $VipUserModel->level = 1;
                    $VipUserModel->start_time = $start_time;
                    $VipUserModel->expire_time = $expire_time;
                    $VipUserModel->channel = '小鹅通合伙人';
                    $VipUserModel->remark = '小鹅通合伙人'.date('Y-m-d');
                    $VipUserModel->save();

                    XeDistributor::query()->where('id', $id)->update(['user_id' => $User->id, 'remark' => '小鹅通合伙人同步-'.$VipUserModel->id.'-'.$User->phone]);

                }

            } else {

                $flag = false;

            }
        }
    }

    //php artisan TestJobb rpush_add_vip_user_inviter
    public static function rpush_add_vip_user_inviter(){

        var_dump('rpush_add_vip_user_inviter');

        $redis_key = 'xe_distributor_to_vip_user_inviter';

        self::set_distributor_base_phone();

        $list = XeDistributor::query()->from(XeDistributor::DB_TABLE . ' as XeDistributor')
            ->select('XeDistributor.base_phone','XeDistributor.id', 'XeUser.phone', 'XeUser.phone_collect', 'XeUser.nickname', 'XeDistributor.xe_user_id', 'XeDistributor.user_id')
            ->leftJoin(XeUser::DB_TABLE . ' as XeUser', 'XeUser.xe_user_id', 'XeDistributor.xe_user_id')
            ->leftJoin(VipUserModel::DB_TABLE.' as VipUserModel','VipUserModel.username','=','XeDistributor.base_phone')
            ->where('XeDistributor.status', 1)
            ->where('XeDistributor.group_id','<>', 59524)
            ->where('XeDistributor.base_phone','<>', '')
            ->where('VipUserModel.remark','=', '小鹅通合伙人'.date('Y-m-d'))
            ->get()->toArray();

        //根据订单的分享人来同步合伙人的上级推广员
        foreach ($list as $k => $distributor) {
            var_dump($k);
            $json_str = json_encode($distributor);
            Redis::rpush($redis_key, $json_str);
        }
    }

    //php artisan TestJobb lpop_add_vip_user_inviter
    public static function lpop_add_vip_user_inviter(){

        var_dump('lpop_add_vip_user_inviter');

        $redis_key = 'xe_distributor_to_vip_user_inviter';

        $flag = True;

        while ($flag) {

            $data = Redis::lpop($redis_key);

            var_dump($data);

            if ($data) {

                $distributor = json_decode($data, true);
                if (!$distributor) {
                    continue;
                }

                $xe_user_id = $distributor['xe_user_id'];
                $user_id = $distributor['user_id'];
                if (empty($user_id)) {
                    continue;
                }
                $base_phone = $distributor['base_phone'];


                $XeUser = XeUser::query()->where('xe_user_id', $xe_user_id)->first();
                if (!$XeUser) {
                    continue;
                }

                $User = User::query()->where('phone', $base_phone)->first();
                if (!$User) {
                    continue;
                }

                $VipUserModel = VipUserModel::query()->where('username', $base_phone)->where('status', 1)->first();
                if (!$VipUserModel) {
                    continue;
                }

                $XeOrder = XeOrder::query()->where([
                    'xe_user_id' => $xe_user_id,
                    'goods_name' => '幸福学社合伙人',
                    'goods_original_total_price' => 258000,
                    'pay_state' => 1,
                    'order_state' => 4,
                ])->first();

                if ($XeOrder && $XeOrder->share_user_id) {

                    $ShareXeDistributor = XeDistributor::query()->from(XeDistributor::DB_TABLE . ' as XeDistributor')
                        ->select('XeDistributor.base_phone','XeUser.phone', 'XeUser.phone_collect', 'XeUser.nickname', 'XeUser.xe_user_id', 'XeUser.user_id')
                        ->leftJoin(XeUser::DB_TABLE . ' as XeUser', 'XeUser.xe_user_id', 'XeDistributor.xe_user_id')
                        ->where('XeUser.user_id', '>', 0)
                        ->where('XeDistributor.status', 1)
                        ->where('XeDistributor.group_id','<>', 59524)
                        ->where('XeDistributor.xe_user_id', $XeOrder->share_user_id)
                        ->where('XeDistributor.base_phone','<>', '')
                        ->first();

                    if ($ShareXeDistributor) {

                        $ShareVipUserModel = VipUserModel::query()->where('username', $ShareXeDistributor->base_phone)->where('status', 1)->first();

                        if ($ShareVipUserModel) {

                            $VipUserModel->inviter = $ShareVipUserModel->user_id;
                            $VipUserModel->inviter_vip_id = $ShareVipUserModel->id;
                            $VipUserModel->save();


                            $VipUserBindModel = VipUserBindModel::query()
                                ->where('status', 1)
                                ->where('son', $VipUserModel->username)->first();


                            if (!$VipUserBindModel) {

                                var_dump($ShareVipUserModel->username);
                                var_dump($VipUserModel->username);

                                //创建新的关系保护
                                $VipUserBindModel = new VipUserBindModel();
                                $VipUserBindModel->parent = $ShareVipUserModel->username;
                                $VipUserBindModel->son = $VipUserModel->username;
                                $VipUserBindModel->life = 2;
                                $VipUserBindModel->begin_at = $VipUserModel->start_time;
                                $VipUserBindModel->end_at = $VipUserModel->expire_time;
                                $VipUserBindModel->channel = 6;
                                $VipUserBindModel->remark = '小鹅通合伙人同步';
                                $VipUserBindModel->save();

                            }
                        }
                    }
                }


            } else {
                $flag = false;
            }
        }
    }

    public static function rpush_vip_user_bind(){

        try {

            $page = Redis::get('rpush_vip_user_bind_page');
            if (!$page) {
                $page = 1;
            }

            Redis::set('rpush_vip_user_bind_page', $page + 1);

            //同步小鹅通客户关系表
            $list = XeDistributorCustomer::query()->from(XeDistributorCustomer::DB_TABLE . ' as XeDistributorCustomer')
                ->select(
                    'XeDistributorCustomer.id', 'XeDistributorCustomer.sub_user_id', 'XeDistributorCustomer.xe_user_id', 'XeDistributorCustomer.bind_time', 'XeDistributorCustomer.expired_at',
                    'XeUser.phone', 'XeUser.phone_collect',
                    'SubXeUser.phone as sub_phone', 'SubXeUser.phone_collect as sub_phone_collect', 'SubXeUser.nickname'
                )
                ->leftJoin(XeUser::DB_TABLE . ' as SubXeUser', 'SubXeUser.xe_user_id', 'XeDistributorCustomer.sub_user_id')
                ->leftJoin(XeUser::DB_TABLE . ' as XeUser', 'XeUser.xe_user_id', 'XeDistributorCustomer.xe_user_id')
                ->leftJoin(XeDistributor::DB_TABLE . ' as XeDistributor', 'XeDistributor.xe_user_id', 'XeDistributorCustomer.xe_user_id')
                ->where('XeDistributorCustomer.status', 1)
                ->where('XeDistributorCustomer.expired_at', '>', times())
                ->where('XeDistributor.status', 1)
                ->where('XeDistributor.group_id','<>', 59524)
                ->orderBy('XeDistributorCustomer.id', 'desc')
                ->forPage($page, 50000)
                ->get()->toArray();

            $redis_page_index_key = 'xe_distributor_customer_xfxs_bind_user';

            foreach ($list as $k => $XeDistributorCustomer) {
                var_dump($page . '-' . $k);
                $json_str = json_encode($XeDistributorCustomer);
                Redis::rpush($redis_page_index_key, $json_str);
            }

        } catch (\Exception $e) {
            var_dump($e->getMessage());
            die;
        }
    }

    public static function rpush_one_vip_user_bind(){
        try {

            $xe_user_id='u_5d538b27472fb_gbuhCZK6To';
            $k=0;

            //同步小鹅通客户关系表
            $list = XeDistributorCustomer::query()->from(XeDistributorCustomer::DB_TABLE . ' as XeDistributorCustomer')
                ->select(
                    'XeDistributorCustomer.id', 'XeDistributorCustomer.sub_user_id', 'XeDistributorCustomer.xe_user_id', 'XeDistributorCustomer.bind_time', 'XeDistributorCustomer.expired_at',
                    'XeUser.phone', 'XeUser.phone_collect',
                    'SubXeUser.phone as sub_phone', 'SubXeUser.phone_collect as sub_phone_collect', 'SubXeUser.nickname'
                )
                ->leftJoin(XeUser::DB_TABLE . ' as SubXeUser', 'SubXeUser.xe_user_id', 'XeDistributorCustomer.sub_user_id')
                ->leftJoin(XeUser::DB_TABLE . ' as XeUser', 'XeUser.xe_user_id', 'XeDistributorCustomer.xe_user_id')
                ->leftJoin(XeDistributor::DB_TABLE . ' as XeDistributor', 'XeDistributor.xe_user_id', 'XeDistributorCustomer.xe_user_id')
                ->where('XeDistributorCustomer.xe_user_id', $xe_user_id)
//                        ->where('XeDistributorCustomer.xe_user_id', '<>',$xe_user_id)
                ->where('XeDistributorCustomer.status', 1)
                ->where('XeDistributorCustomer.expired_at', '>', times())
                ->where('XeDistributor.status', 1)
                ->where('XeDistributor.group_id','<>', 59524)

//                        ->whereRaw("(XeUser.phone<>'' or XeUser.phone_collect<>'')")
//                        ->whereRaw("(SubXeUser.phone<>'' or SubXeUser.phone_collect<>'')")
                ->orderBy('XeDistributorCustomer.id', 'asc')
                ->chunk(50000, function ($list)use(&$k){

                    $redis_page_index_key = 'xe_distributor_customer_xfxs_bind_user';
                    foreach ($list as $XeDistributorCustomer) {
                        $k++;
                        var_dump( $k);
                        $json_str = json_encode($XeDistributorCustomer);
                        Redis::rpush($redis_page_index_key, $json_str);
                    }

                });


        } catch (\Exception $e) {
            var_dump($e->getMessage());
            die;
        }
    }

    public static function lpop_vip_user_bind(){

        $flag = True;

        while ($flag) {

            $data = Redis::lpop('xe_distributor_customer_xfxs_bind_user');

            var_dump($data);

            if ($data) {

                $jsonArr = json_decode($data, true);
                if (!$jsonArr) {
                    continue;
                }

                $id = $jsonArr['id'];
                $bind_time = $jsonArr['bind_time'];
                $expired_at = $jsonArr['expired_at'];
                $nickname = $jsonArr['nickname'];

                $phone = $jsonArr['phone'] ?? '';
                $phone_collect = $jsonArr['phone_collect'] ?? '';
                $phone = $phone ? $phone : $phone_collect;
                if(empty($phone)){
                    XeDistributorCustomer::query()->where('id', $id)->update(['remark' => '合伙人手机号不存在']);
                    continue;
                }

                $sub_phone = $jsonArr['sub_phone'] ?? '';
                $sub_phone_collect = $jsonArr['sub_phone_collect'] ?? '';
                $sub_phone = $sub_phone ? $sub_phone : $sub_phone_collect;
                if(empty($sub_phone)){
                    XeDistributorCustomer::query()->where('id', $id)->update(['remark' => '客户手机号不存在']);
                    continue;
                }

                try {

                    $User = User::query()->where('phone', $sub_phone)->first();
                    if ($User) {
                        $User->xfxs_login = 1;
                        $User->save();
                    }

                    $VipUserBindModel = VipUserBindModel::query()
                        ->where('status', 1)
                        ->where('son', $sub_phone)->first();

                    if($VipUserBindModel){

                        if($VipUserBindModel->parent==$phone){
                            XeDistributorCustomer::query()->where('id', $id)->update(['parent_phone'=>$phone,'son_phone'=>$sub_phone,'remark' => '小鹅通关系保护同步']);
                        }else{
                            XeDistributorCustomer::query()->where('id', $id)->update(['parent_phone'=>$phone,'son_phone'=>$sub_phone,'remark' => '已被保护']);
                        }

                    }else{

                        if (empty($User)) {
                            $User = new User();
                            $User->phone = $sub_phone;
                            $User->intro = '幸福学社客户';
                            $User->app_project_type = 2;
                            $User->xfxs_login = 1;
                            $User->nickname = $nickname;
                            $User->save();
                        }

                        //创建新的关系保护
                        $VipUserBindModel = new VipUserBindModel();
                        $VipUserBindModel->parent = $phone;
                        $VipUserBindModel->son = $sub_phone;
                        $VipUserBindModel->life = 2;
                        $VipUserBindModel->begin_at = $bind_time;
                        $VipUserBindModel->end_at = $expired_at;
                        $VipUserBindModel->channel = 7;
                        $VipUserBindModel->remark = '小鹅通关系保护同步';
                        $VipUserBindModel->save();

                        XeDistributorCustomer::query()->where('id', $id)->update(['parent_phone'=>$phone,'son_phone'=>$sub_phone,'remark' => '小鹅通关系保护同步']);

                    }

                } catch (\Exception $e) {

                    var_dump($e->getMessage());
                    XeDistributorCustomer::query()->where('id', $id)->update(['remark' => $e->getMessage()]);

                }

            } else {

                $flag = false;

            }

        }
    }


    public static function set_distributor_base_phone()
    {

        //同步手机号
        $list = XeDistributor::query()->from(XeDistributor::DB_TABLE . ' as XeDistributor')
            ->select('XeDistributor.id', 'XeUser.phone', 'XeUser.phone_collect', 'XeUser.nickname', 'XeUser.xe_user_id', 'XeUser.user_id')
            ->leftJoin(XeUser::DB_TABLE . ' as XeUser', 'XeUser.xe_user_id', 'XeDistributor.xe_user_id')
            ->where('XeDistributor.status', 1)
            ->whereRaw("(XeUser.phone<>'' or XeUser.phone_collect<>'')")
            ->where('base_phone', '')
            ->get();

        foreach ($list as $k => $XeDistributor) {
//            var_dump($k);
            $phone = $XeDistributor->phone ? $XeDistributor->phone : $XeDistributor->phone_collect;
            $XeDistributor->base_phone = $phone;
            $XeDistributor->save();

        }
    }

    //清除9.9分销 合伙人
    public static function clear_vip(){

        self::set_distributor_base_phone();

        //同步手机号
        $list = XeDistributor::query()->from(XeDistributor::DB_TABLE . ' as XeDistributor')
            ->select('XeDistributor.base_phone','XeDistributor.id', 'XeUser.phone', 'XeUser.phone_collect', 'XeUser.nickname', 'XeUser.xe_user_id', 'XeUser.user_id')
            ->leftJoin(XeUser::DB_TABLE . ' as XeUser', 'XeUser.xe_user_id', 'XeDistributor.xe_user_id')
            ->where('XeDistributor.status', 1)
            ->where('XeDistributor.group_id','=', 59524)
            ->where('XeDistributor.group_name','=', '9.9分销')
            ->whereRaw("(XeUser.phone<>'' or XeUser.phone_collect<>'')")
            ->where('base_phone','<>','')
            ->get();

        foreach ($list as $k=>$XeDistributor){

            $phone=$XeDistributor->base_phone;

            if($phone){
                $VipUserModel=VipUserModel::query()->where('username',$phone)->where('status',1)->first();
                if($VipUserModel){
                    $VipUserModel->status=2;
                    $VipUserModel->remark='9.9分销取消合伙人身份';
                    $VipUserModel->save();
                    var_dump('9.9分销取消合伙人身份');
                }
            }

        }
    }
}
