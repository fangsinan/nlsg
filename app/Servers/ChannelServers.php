<?php


namespace App\Servers;


use App\Models\ChannelOrder;
use App\Models\ChannelWorksList;
use App\Models\Column;
use App\Models\ConfigModel;
use App\Models\Live;
use App\Models\LiveCountDown;
use App\Models\Order;
use App\Models\Subscribe;
use App\Models\User;
use App\Models\VipUserBind;
use App\Models\Works;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ChannelServers {
    //************************创业天下改版************************
    public function cytxPost($type, $data) {
        //0正式 1测试 2预发布
        $is_test = (int)ConfigModel::getData(37, 1);
        switch ($is_test) {
            case 0:
                $url = [
                    'push'   => 'https://cytxapi.chuangyetianxia.com/partner/instant-score',
                    'refund' => 'https://cytxapi.chuangyetianxia.com/partner/refund-score'
                ];
                break;
            case 1:
                $url = [
                    'push'   => 'http://39.107.71.116:8084/partner/instant-score',
                    'refund' => 'http://39.107.71.116:8084/partner/refund-score'
                ];
                break;
            default:
                $url = [
                    'push'   => 'https://cytx-stage-new-api.chuangyetianxia.com/partner/instant-score',
                    'refund' => 'https://cytx-stage-new-api.chuangyetianxia.com/partner/refund-score'
                ];
        }

        $url = $url[$type] ?? '';
        if (empty($url)) {
            return false;
        }
        $res = Http::post($url, $data);
        return json_decode($res, false);
    }

    public function pushToCytxV2($order_data): bool {
        if (is_object($order_data)) {
            $order_data = json_decode(json_encode($order_data), true);
        }

        if (empty($order_data['id'] ?? 0)) {
            return true;
        }

        $data              = [];
        $data['telephone'] = $order_data['username'];
        $data['source']    = 'nlsg';
        $data['source_id'] = $order_data['ordernum'];
        $data['price']     = $data['score'] = (float)$order_data['price'];
        $data['name']      = $order_data['title'];

        $res = $this->cytxPost('push', $data);

        $order = Order::query()->find($order_data['id']);
        if ($res->code === 200) {
            $order->cytx_job = -1;
        } else {
            ++$order->cytx_job;
        }
        $order->cytx_res        = json_encode($res);
        $order->cytx_check_time = date('Y-m-d H:i:s');
        $order->save();
        return true;
    }

    public function refundCytxV2($order_id, $order_num): bool {
        if (empty($order_id) || empty($order_num)) {
            return false;
        }
        $check = Order::query()->where('id', '=', $order_id)
            ->where('ordernum', '=', $order_num)
            ->where('activity_tag', '=', 'cytx')
            ->where('status', '=', 1)
            ->where('is_shill', '=', 1)
            ->first();

        if (empty($check)) {
            return false;
        }

        $data                    = [];
        $data['source']          = 'nlsg';
        $data['source_id']       = $order_num;
        $res                     = $this->cytxPost('refund', $data);
        $check->cytx_refund_code = $res->code;
        $check->cytx_refund_msg  = $res->message;

        if ($res->code === 200) {
            $check->cytx_refund = 1;
            $check->save();
            return true;
        }

        $check->save();
        return false;
    }

    public function cytxOrderCheck($params) {
        $phone     = $params['telephone'] ?? 0;
        $source    = $params['source'] ?? '';
        $order_num = $params['source_id'] ?? '';
        $price     = $params['price'] ?? 0;
        $score     = $params['score'] ?? 0;
return ['code' => true, 'msg' => '成功'];
        if ($source !== 'cytx') {
            return ['code' => false, 'msg' => '信息错误'];
        }
        if (empty($phone) || empty($order_num) || empty($price)) {
            return ['code' => false, 'msg' => '信息错误'];
        }
        if ($price !== $score) {
            return ['code' => false, 'msg' => '信息错误'];
        }

        $check = DB::table('nlsg_order as o')
            ->join('nlsg_user as u', 'o.user_id', '=', 'u.id')
            ->where('o.ordernum', '=', $order_num)
            ->where('u.phone', '=', $phone)
            ->where('o.status', '=', 1)
            ->where('o.activity_tag', '=', 'cytx')
            ->where('o.pay_price', '=', $price)
            ->first();
        if ($check) {
            Order::query()->where('ordernum', '=', $order_num)
                ->update([
                    'cytx_call_back_time' => date('Y-m-d H:i:s')
                ]);
            return ['code' => true, 'msg' => '成功'];
        }

        return ['code' => false, 'msg' => '失败'];
    }
    //**********************************************************

    //推送到创业天下
    private function pushToCytx($order_data) {
        if (is_object($order_data)) {
            $order_data = json_decode(json_encode($order_data), true);
        }

        if (empty($order_data['id'] ?? 0)) {
            return true;
        }
        $is_test = (int)ConfigModel::getData(37, 1);
        if (!empty($is_test)) {
            $url = 'http://39.107.71.116:8081/v1/partner/notify';
        } else {
            $url = 'https://api.chuangyetianxia.com/v1/partner/notify';
        }

        $data['telephone'] = $order_data['username'];
        $data['name']      = $order_data['title'];
        $data['price']     = $order_data['price'];
        $data['source']    = 'nlsg';
        $data['source_id'] = $order_data['ordernum'];

        $res = Http::post($url, $data);
        $res = json_decode($res, false);

        $order = Order::find($order_data['id']);
        if ($res->code === 200) {
            $order->cytx_job = -1;
        } else {
            ++$order->cytx_job;
        }
        $order->cytx_res        = json_encode($res);
        $order->cytx_check_time = date('Y-m-d H:i:s');
        $order->save();
    }

    public static function cytxJob() {
        $c = new self();
        $c->cytxOrderList();
    }

    //创业天下订单获取
    public function cytxOrderList($order_id = 0) {
        $query = DB::table('nlsg_user as u')
            ->join('nlsg_order as o', 'u.id', '=', 'o.user_id')
            ->join('nlsg_pay_record as p', 'o.ordernum', '=', 'p.ordernum');
//            ->join('nlsg_works as w', 'o.relation_id', '=', 'w.id');

        if ($order_id) {
            $query->where('o.id', '=', $order_id);
        }

        //是否推送直播预约订单
        $type_config = ConfigModel::getData(53, 1);
        if ($type_config == 1) {
            $type_list = [9, 15, 10];
        } else {
            $type_list = [9, 15];
        }

        $query->where('o.activity_tag', '=', 'cytx')
            ->where('o.status', '=', 1)
            ->whereIn('o.type', $type_list)
            ->where('cytx_job', '<>', -1);

        $is_test = (int)ConfigModel::getData(37, 1);
        if ($is_test === 0) {
            $query->where('p.price', '>', 0.01)
                ->where('u.is_staff', '=', 0)
                ->where('p.price', '>', 1);
        }

        $query
            ->where('o.is_shill', '=', 0)
            ->where('cytx_job', '<', 11)
            ->whereRaw('(cytx_job = 0 or ((cytx_job*600) + UNIX_TIMESTAMP(cytx_check_time) <= UNIX_TIMESTAMP()))');

        if (empty($order_id)) {
            $query->limit(1000);
        }

        $list = $query->select([
            'o.id', 'o.ordernum', 'u.phone as username', 'o.type', 'o.relation_id',
            'u.nickname', 'p.price', 'o.cytx_job', 'o.pay_time'
        ])->get();

        foreach ($list as $v) {
            $v->title = '';
            if ($v->type == 9) {
                $temp_info = Works::whereId($v->relation_id)->select('id', 'title')->first();
                $v->title  = $temp_info->title;
            } elseif ($v->type == 15) {
                $temp_info = Column::whereId($v->relation_id)->select(['id', 'name'])->first();
                $v->title  = $temp_info->name;
            } elseif ($v->type == 10) {
                $temp_info = Live::whereId($v->relation_id)->select(['id', 'title'])->first();
                $v->title  = $temp_info->title;
            }
        }

        if ($list->isNotEmpty()) {
            $list = $list->toArray();
            foreach ($list as $v) {
//                $this->pushToCytx($v);
                $this->pushToCytxV2($v);
            }
        }

    }

    //抖音订单拉取(定时任务)
    public function getDouYinOrder() {
        $is_test = intval(ConfigModel::getData(37, 1));
        if (!empty($is_test)) {
            ConfigModel::where('id', '=', 49)->update([
                'value' => "测试不执行任务"
            ]);
            return true;
        }

        $begin_date = ConfigModel::getData(38, 1);
        if (empty($begin_date)) {
            $min      = date('i');
            $job_type = $min % 3;
            switch ($job_type) {
                case 0:
                    $begin_date = date('Y-m-d H:i:00', strtotime("-30 minutes"));
                    break;
                case 1:
                    $begin_date = date('Y-m-d H:i:00', strtotime("-300 minutes"));
                    break;
                case 2:
                    $begin_date = date('Y-m-d H:i:00', strtotime("-1470 minutes"));
                    break;
                default:
                    $begin_date = date('Y-m-d H:i:00', strtotime("-30 minutes"));
            }

            $end_date = date('Y-m-d H:i:00', strtotime("$begin_date +20 minutes"));
            ConfigModel::where('id', '=', 49)->update([
                'value' => "$begin_date - $end_date"
            ]);
        } else {
            if (strtotime($begin_date) >= time()) {
                return true;
            }
            $begin_date = date('Y-m-d H:i:00', strtotime($begin_date));
            $end_date   = date('Y-m-d H:i:00', strtotime("$begin_date +300 minutes"));
            ConfigModel::whereId(38)->update(['value' => $end_date]);
        }

        $page = 0;
        $size = 100;
        $args = [
            'start_time' => $begin_date,
            'end_time'   => $end_date,
            'size'       => strval($size),
            'order_by'   => 'create_time'
        ];

        $go_on = true;
        while ($go_on) {
            $args['page'] = strval($page);
            $temp_res     = $this->douYinQuery($args);
            $page++;
            if (empty($temp_res['err_no'])) {
                $this->insertDouYinOrder($temp_res['data']['list']);
                if ($page * $size >= $temp_res['data']['total']) {
                    $go_on = false;
                }
            } else {
                $go_on = false;
            }
        }
    }

    //抖音订单入库
    private function insertDouYinOrder($list) {
        if (!is_array($list) || empty($list)) {
            return true;
        }

        foreach ($list as $v) {
            foreach ($v['child'] as $vv) {
                //不过滤sku先
//                $check_sku = ChannelSku::checkSku($vv['product_id'], 1);
//                if (!$check_sku) {
//                    continue;
//                }

                $temp_data = ChannelOrder::where('order_id', '=', $v['order_id'])
                    ->where('channel', '=', 1)
                    ->first();
                if (empty($temp_data)) {
                    $temp_data = new ChannelOrder();
                }

                $temp_data->order_id     = $v['order_id'];
                $temp_data->channel      = 1;
                $temp_data->sku          = $vv['product_id'] ?? 0;
                $temp_data->phone        = $v['post_tel'] ?? 0;
                $temp_data->order_status = $v['order_status'];
                $temp_data->create_time  = date('Y-m-d H:i:s', $v['create_time']);
                if (empty($v['pay_time'])) {
                    $temp_data->pay_time = null;
                } else {
                    $temp_data->pay_time = $v['pay_time'];
                }
                $temp_data->update_time = date('Y-m-d H:i:s', $v['update_time']);
                $temp_data->save();
            }
        }
    }

    //抖音订单补全(定时任务)
    public function supplementDouYinOrder() {
        $is_test = intval(ConfigModel::getData(37, 1));
        if (!empty($is_test)) {
            ConfigModel::where('id', '=', 49)->update([
                'value' => "测试不执行任务"
            ]);
            return true;
        }

        $list = ChannelOrder::where('user_id', '=', 0)
            ->where('status', '=', 0)
            ->with(['skuInfo'])
            ->limit(50)
            ->get();
        if ($list->isEmpty()) {
            return true;
        }


        foreach ($list as $v) {
            if (empty($v->skuInfo) || $v->order_status == 4) {
                $v->status = 9;
            } else {
                $user       = User::firstOrCreate([
                    'phone' => $v->phone,
                ], [
                    'nickname' => substr_replace($v->phone, '****', 3, 4),
                    'ref'      => 11,
                ]);
                $v->user_id = $user->id;
            }
            $v->save();
        }
    }

    private function douYinQuery($args) {

        $token    = $this->getDouYinToken();
        $now_date = date('Y-m-d H:i:s');
        $host     = 'https://openapi-fxg.jinritemai.com';
        $v        = '2';
        $c        = 'order';
        $a        = 'list';
        $method   = $c . '.' . $a;

        $APP_KEY    = config('env.DOUYIN_APP_KEY');
        $APP_SECRET = config('env.DOUYIN_APP_SECRET');
        ksort($args);
        $args_json = json_encode($args);

        // 计算签名
        $str      = 'app_key' . $APP_KEY . 'method' . $method . 'param_json' .
            $args_json . 'timestamp' . $now_date . 'v' . $v;
        $md5_str  = $APP_SECRET . $str . $APP_SECRET;
        $sign     = md5($md5_str);
        $base_url = $host . '/' . $c . '/' . $a;

        $request_data = [
            'access_token' => $token,
            'app_key'      => $APP_KEY,
            'method'       => $method,
            'param_json'   => $args_json,
            'timestamp'    => $now_date,
            'v'            => $v,
            'sign'         => $sign,
        ];

        $res = Http::get($base_url . '?' . http_build_query($request_data));
        return json_decode($res, true);
    }

    private function getDouYinToken() {
        $cache_key_name = 'douyin_token';
        $access_token   = Cache::get($cache_key_name);

        if (empty($access_token)) {
            $APP_KEY    = config('env.DOUYIN_APP_KEY');
            $APP_SECRET = config('env.DOUYIN_APP_SECRET');

            $url = "https://openapi-fxg.jinritemai.com/oauth2/access_token?" .
                "app_id=" . $APP_KEY . "&" . "app_secret=" . $APP_SECRET .
                "&grant_type=authorization_self";

            $token = Http::get($url);
            $token = json_decode($token, true);
            if (empty($token['err_no']) && !empty($token['data']['access_token'])) {
                $access_token = $token['data']['access_token'];
                Cache::put($cache_key_name, $access_token, $token['data']['expires_in'] - 100);
            }
        }
        return $access_token;
    }

    //抖音开通(定时任务)
    public function douYinJob() {
        $is_test = intval(ConfigModel::getData(37, 1));
        if (!empty($is_test)) {
            ConfigModel::where('id', '=', 49)->update([
                'value' => "测试不执行任务"
            ]);
            return true;
        }

        //抖音订单 order_status=3,5  就可以执行
        $begin_date = date('Y-m-d 00:00:00', strtotime('-20 days'));
        $now_date   = date('Y-m-d H:i:s');

        $list = ChannelOrder::where('create_time', '>', $begin_date)
            ->where('user_id', '>', 0)
            ->whereIn('order_status', [3, 5])
            ->where('channel', '=', 1)
            ->where('status', '=', 0)
            ->with('skuInfo')
            ->select(['id', 'user_id', 'phone', 'order_status', 'order_id', 'sku'])
            ->limit(100)
            ->get();

        $invalid_id_list = [];

        foreach ($list as $v) {
            if (empty($v->skuInfo)) {
                $invalid_id_list[] = $v->id;
            } else {
                if (!is_array($v->skuInfo->to_id)) {
                    $v->skuInfo->to_id = explode(',', $v->skuInfo->to_id);
                }
                $v->skuInfo->to_id = array_filter($v->skuInfo->to_id);
                //1:讲座 2:课程 3:直播  4:360会员
                switch (intval($v->skuInfo->type)) {
                    case 1:
                        $add_sub_data = [];
                        DB::beginTransaction();
                        foreach ($v->skuInfo->to_id as $tv) {
                            $check = Subscribe::where('user_id', '=', $v->user_id)
                                ->where('created_at', '>', '2021-01-05')
                                ->where('relation_id', '=', $tv)
                                ->where('end_time', '>=', $now_date)
                                ->where('type', '=', 6)
                                ->where('status', '=', 1)
                                ->first();
                            if (empty($check)) {
                                $temp_data                      = [];
                                $temp_data['type']              = 6;
                                $temp_data['user_id']           = $v->user_id;
                                $temp_data['relation_id']       = $tv;
                                $temp_data['pay_time']          = $now_date;
                                $temp_data['start_time']        = $now_date;
                                $temp_data['status']            = 1;
                                $temp_data['give']              = 15;
                                $temp_data['end_time']          = date('Y-m-d 23:59:59', strtotime('+1 years'));
                                $temp_data['channel_order_id']  = $v->order_id;
                                $temp_data['channel_order_sku'] = $v->sku;
                                $add_sub_data[]                 = $temp_data;
                            } else {
                                $temp_end_time   = date('Y-m-d 23:59:59', strtotime("$check->end_time +1 years"));
                                $check->end_time = $temp_end_time;
                                $edit_res        = $check->save();
                                if ($edit_res === false) {
                                    DB::rollBack();
                                    break;
                                }
                            }
                        }
                        $edit_res = DB::table('nlsg_channel_order')
                            ->where('id', '=', $v->id)
                            ->update([
                                'status'     => 1,
                                'success_at' => $now_date
                            ]);
                        if ($edit_res === false) {
                            DB::rollBack();
                            break;
                        }
                        if (!empty($add_sub_data)) {
                            $add_res = DB::table('nlsg_subscribe')->insert($add_sub_data);
                            if ($add_res === false) {
                                DB::rollBack();
                                break;
                            }
                        }
                        DB::commit();
                        break;
                    case 2:
                        $add_sub_data = [];
                        DB::beginTransaction();
                        foreach ($v->skuInfo->to_id as $tv) {
                            $check = Subscribe::where('user_id', '=', $v->user_id)
                                ->where('created_at', '>', '2021-01-05')
                                ->where('relation_id', '=', $tv)
                                ->where('end_time', '>=', $now_date)
                                ->where('type', '=', 2)
                                ->where('status', '=', 1)
                                ->first();
                            if (empty($check)) {
                                $temp_data                      = [];
                                $temp_data['type']              = 2;
                                $temp_data['user_id']           = $v->user_id;
                                $temp_data['relation_id']       = $tv;
                                $temp_data['pay_time']          = $now_date;
                                $temp_data['start_time']        = $now_date;
                                $temp_data['status']            = 1;
                                $temp_data['give']              = 15;
                                $temp_data['end_time']          = date('Y-m-d 23:59:59', strtotime('+1 years'));
                                $temp_data['channel_order_id']  = $v->order_id;
                                $temp_data['channel_order_sku'] = $v->sku;
                                $add_sub_data[]                 = $temp_data;
                            } else {
                                $temp_end_time   = date('Y-m-d 23:59:59', strtotime("$check->end_time +1 years"));
                                $check->end_time = $temp_end_time;
                                $edit_res        = $check->save();
                                if ($edit_res === false) {
                                    DB::rollBack();
                                    break;
                                }
                            }
                        }
                        $edit_res = DB::table('nlsg_channel_order')
                            ->where('id', '=', $v->id)
                            ->update([
                                'status'     => 1,
                                'success_at' => $now_date
                            ]);
                        if ($edit_res === false) {
                            DB::rollBack();
                            break;
                        }
                        if (!empty($add_sub_data)) {
                            $add_res = DB::table('nlsg_subscribe')->insert($add_sub_data);
                            if ($add_res === false) {
                                DB::rollBack();
                                break;
                            }
                        }
                        DB::commit();
                        break;
                    case 3:
                        //21-03-22 补充的课程
                        Subscribe::appendSub([$v->user_id], 1);
                        $add_sub_data = [];
                        $add_cd_data  = [];
                        DB::beginTransaction();
                        foreach ($v->skuInfo->to_id as $tv) {
                            Live::where('id', $tv)->increment('order_num');
                            $check = Subscribe::where('user_id', '=', $v->user_id)
                                ->where('created_at', '>', '2021-01-05')
                                ->where('relation_id', '=', $tv)
                                ->where('type', '=', 3)
                                ->where('status', '=', 1)
                                ->first();
                            if (empty($check)) {
                                $temp_data                      = [];
                                $temp_data['type']              = 3;
                                $temp_data['user_id']           = $v->user_id;
                                $temp_data['relation_id']       = $tv;
                                $temp_data['pay_time']          = $now_date;
                                $temp_data['status']            = 1;
                                $temp_data['give']              = 15;
                                $temp_data['channel_order_id']  = $v->order_id;
                                $temp_data['channel_order_sku'] = $v->sku;
                                $add_sub_data[]                 = $temp_data;
                            }
                            $check_cd = LiveCountDown::where('user_id', '=', $v->user_id)
                                ->where('phone', '=', $v->phone)
                                ->where('live_id', '=', $tv)
                                ->first();
                            if (empty($check_cd)) {
                                $temp_cd_data            = [];
                                $temp_cd_data['live_id'] = 8;
                                $temp_cd_data['user_id'] = $v->user_id;
                                $temp_cd_data['phone']   = $v->phone;
                                $add_cd_data[]           = $temp_cd_data;
                            }
                        }
                        $edit_res = DB::table('nlsg_channel_order')
                            ->where('id', '=', $v->id)
                            ->update([
                                'status'     => 1,
                                'success_at' => $now_date
                            ]);
                        if ($edit_res === false) {
                            DB::rollBack();
                            break;
                        }

                        if (!empty($add_sub_data)) {
                            $add_res = DB::table('nlsg_subscribe')->insert($add_sub_data);
                            if ($add_res === false) {
                                DB::rollBack();
                                break;
                            }
                            $add_cd_res = DB::table('nlsg_live_count_down')->insert($add_cd_data);
                            if ($add_cd_res === false) {
                                DB::rollBack();
                                break;
                            }

                            //添加关系保护
                            $check_bind = VipUserBind::getBindParent($v->phone);
                            if ($check_bind == 0) {
                                //没有绑定记录,则绑定
                                $bind_data = [
                                    'parent'   => '18512378959',
                                    'son'      => $v->phone,
                                    'life'     => 2,
                                    'begin_at' => date('Y-m-d H:i:s'),
                                    'end_at'   => date('Y-m-d 23:59:59', strtotime('+1 years')),
                                    'channel'  => 3
                                ];
                                DB::table('nlsg_vip_user_bind')->insert($bind_data);
                            }

                        }
                        DB::commit();
                        try {
                            $easySms = app('easysms');
                            $easySms->send($v->phone, [
                                'template' => 'SMS_210996538',
                            ], ['aliyun']);
                        } catch (\Exception $e) {

                        }
                        break;
                    case 4:
                        $servers = new VipServers();
                        $servers->openVip($v->user_id, $v->phone, 'douyin');
                        DB::table('nlsg_channel_order')
                            ->where('id', '=', $v->id)
                            ->update([
                                'status'     => 1,
                                'success_at' => $now_date
                            ]);

                        foreach ($v->skuInfo->to_id as $to_id) {
                            if ($to_id == 2) {
                                //如果是2  表示为1360订单 需要写入order表
                                $orderModel               = new Order();
                                $orderModel->type         = 14;
                                $orderModel->live_num     = 1;
                                $orderModel->relation_id  = 4;
                                $orderModel->live_id      = 0;
                                $orderModel->user_id      = $v->user_id;
                                $orderModel->status       = 1;
                                $orderModel->pay_time     = $now_date;
                                $orderModel->cost_price   = 1360;
                                $orderModel->price        = 1360;
                                $orderModel->pay_price    = 1360;
                                $orderModel->ordernum     = $v->order_id;
                                $orderModel->activity_tag = 'dy_1360';
                                $orderModel->save();
                            }
                        }

                        try {
                            $easySms = app('easysms');
                            $easySms->send($v->phone, [
                                'template' => 'SMS_211026570',
                            ], ['aliyun']);
                        } catch (\Exception $e) {

                        }

                        break;
                    case 5:
                        $live_id_list = [];
                        foreach ($v->skuInfo->to_id as $tv) {
                            $temp_live_id = Live::teamInfo($tv, 1, 1);
                            if (!empty($temp_live_id)) {
                                foreach ($temp_live_id as $tli) {
                                    $live_id_list[] = $tli->id;
                                }
                            }
                        }
                        $live_id_list = array_unique($live_id_list);

                        $add_sub_data = [];
                        $add_cd_data  = [];
                        $add_sms_data = [];
                        DB::beginTransaction();
                        foreach ($live_id_list as $tv) {
                            Live::where('id', $tv)->increment('order_num');

                            $live_data                  = Live::where('id', $tv)->select(['id', 'begin_at'])->first();
                            $temp_add_sms_data          = [];
                            $temp_add_sms_data['phone'] = $v->phone;
                            $temp_add_sms_data['time']  = date('m月d日', strtotime($live_data->begin_at));
                            $add_sms_data[]             = $temp_add_sms_data;

                            $check = Subscribe::where('user_id', '=', $v->user_id)
                                ->where('created_at', '>', '2021-01-05')
                                ->where('relation_id', '=', $tv)
                                ->where('type', '=', 3)
                                ->where('status', '=', 1)
                                ->first();
                            if (empty($check)) {
                                $temp_data                      = [];
                                $temp_data['type']              = 3;
                                $temp_data['user_id']           = $v->user_id;
                                $temp_data['relation_id']       = $tv;
                                $temp_data['pay_time']          = $now_date;
                                $temp_data['status']            = 1;
                                $temp_data['give']              = 15;
                                $temp_data['channel_order_id']  = $v->order_id;
                                $temp_data['channel_order_sku'] = $v->sku;
                                $add_sub_data[]                 = $temp_data;
                            }
                            $check_cd = LiveCountDown::where('user_id', '=', $v->user_id)
                                ->where('phone', '=', $v->phone)
                                ->where('live_id', '=', $tv)
                                ->first();
                            if (empty($check_cd)) {
                                $temp_cd_data            = [];
                                $temp_cd_data['live_id'] = 8;
                                $temp_cd_data['user_id'] = $v->user_id;
                                $temp_cd_data['phone']   = $v->phone;
                                $add_cd_data[]           = $temp_cd_data;
                            }
                        }

                        $edit_res = DB::table('nlsg_channel_order')
                            ->where('id', '=', $v->id)
                            ->update([
                                'status'     => 1,
                                'success_at' => $now_date
                            ]);
                        if ($edit_res === false) {
                            DB::rollBack();
                            break;
                        }

                        if (!empty($add_sub_data)) {
                            $add_res = DB::table('nlsg_subscribe')->insert($add_sub_data);
                            if ($add_res === false) {
                                DB::rollBack();
                                break;
                            }
                            $add_cd_res = DB::table('nlsg_live_count_down')->insert($add_cd_data);
                            if ($add_cd_res === false) {
                                DB::rollBack();
                                break;
                            }

                            //添加关系保护
                            $check_bind = VipUserBind::getBindParent($v->phone);
                            if ($check_bind == 0) {
                                //没有绑定记录,则绑定
                                $bind_data = [
                                    'parent'   => '18512378959',
                                    'son'      => $v->phone,
                                    'life'     => 2,
                                    'begin_at' => date('Y-m-d H:i:s'),
                                    'end_at'   => date('Y-m-d 23:59:59', strtotime('+1 years')),
                                    'channel'  => 3
                                ];
                                DB::table('nlsg_vip_user_bind')->insert($bind_data);
                            }
                        }

                        DB::commit();
                        try {
                            if (!empty($add_sms_data)) {
                                $easySms = app('easysms');
                                foreach ($add_sms_data as $sms_v) {
                                    $easySms->send($sms_v['phone'], [
                                        'template' => 'SMS_218028527',
                                        'data'     => [
                                            'time' => $sms_v['time'],
                                        ],
                                    ], ['aliyun']);
                                }
//                                $easySms->send($v->phone, [
//                                    'template' => 'SMS_210996538',
//                                ], ['aliyun']);
                            }

                        } catch (\Exception $e) {

                        }
                        break;
                }
            }

        }

        if (!empty($invalid_id_list)) {
            DB::table('nlsg_channel_order')
                ->whereIn('id', $invalid_id_list)
                ->update([
                    'status' => 9
                ]);
        }
    }

    //抖音课程列表
    public function getList($params) {
        $size = $params['size'] ?? 10;

        $query = ChannelWorksList::where('status', '=', 1)
            ->where('channel_type', '=', 1)
            ->with([
                'column',
                'works',
                'categoryBind',
                'categoryBind.categoryName',
            ]);

        //视频,音频过滤
        if (!empty($params['works_type'] ?? '')) {
            $query->where('type', '=', $params['works_type']);
        }

        //筛选 分类,视频/音频
        if (!empty($params['category_id'] ?? '')) {
            $category_id = $params['category_id'];
            $query->whereHas('categoryBind', function ($q) use ($category_id) {
                $q->where('category_id', '=', $category_id);
            });
        }

        //标题搜索
        if (!empty($params['title'] ?? '')) {
            $title        = $params['title'];
            $w_id_list    = Works::where('title', 'like', "%$title%")
                ->where('status', '=', 4)->where('type', '=', 2)
                ->pluck('id')->toArray();
            $c_id_list    = Column::where('title', 'like', "%$title%")
                ->where('status', '=', 1)->where('type', '=', 2)
                ->pluck('id')->toArray();
            $temp_id_list = array_unique(array_merge($w_id_list, $c_id_list));

            $query->whereIn('works_id', $temp_id_list);

        }

        //最多学习,最新上架,价格
//        switch (strtolower($params['ob']??'')) {
//            case 'view_num_asc':
//                $query->orderBy('subscribe_num', 'asc');
//                break;
//            case 'view_num_desc':
//                $query->orderBy('subscribe_num', 'desc');
//                break;
//            case 'created_asc':
//                $query->orderBy('created_at', 'asc');
//                break;
//            case 'created_desc':
//                $query->orderBy('created_at', 'desc');
//                break;
//            case 'price_asc':
//                $query->orderBy('price', 'asc');
//                break;
//            case 'price_desc':
//                $query->orderBy('price', 'desc');
//                break;
//        }

        $list = $query->orderBy('rank', 'asc')
            ->orderBy('id', 'asc')
            ->paginate($size);

        foreach ($list as $k => $v) {
            $temp_res               = [];
            $temp_res['id']         = $v['id'];
            $temp_res['rank']       = $v['rank'];
            $temp_res['works_id']   = $v['works_id'];
            $temp_res['works_type'] = $v['type'];
            $temp_res['price']      = $v['price'];
            $temp_res['view_num']   = $v['view_num'];
            $temp_res['created_at'] = $v['created_at'];
            $temp_res['is_buy']     = ($v['check_sub_count'] > 0) ? 1 : 0;

            $temp_res['category_info'] = [];
            foreach ($v['categoryBind'] as $cbv) {
                if (!empty($cbv['categoryName'] ?? '')) {
                    $temp_res['category_info'][] = $cbv['categoryName'];
                }
            }

            if ($v['type'] == 1) {
                if (empty($v['column'])) {
                    continue;
                }
                $temp_res['title']         = $v['column']['title'];
                $temp_res['subtitle']      = $v['column']['subtitle'];
                $temp_res['cover_img']     = $v['column']['cover_img'];
                $temp_res['detail_img']    = $v['column']['cover_img'];
                $temp_res['type']          = 1;
                $temp_res['column_type']   = $v['column']['column_type'];
                $temp_res['user_id']       = $v['column']['user_id'];
                $temp_res['subscribe_num'] = $v['column']['subscribe_num'];
                $temp_res['info_num']      = $v['column']['info_num'];
            } else if ($v['type'] == 2) {
                if (empty($v['works'])) {
                    continue;
                }
                $temp_res['title']         = $v['works']['title'];
                $temp_res['subtitle']      = $v['works']['subtitle'];
                $temp_res['cover_img']     = $v['works']['cover_img'];
                $temp_res['detail_img']    = $v['works']['cover_img'];
                $temp_res['type']          = $v['works']['type'];
                $temp_res['column_type']   = 1;
                $temp_res['user_id']       = $v['works']['user_id'];
                $temp_res['subscribe_num'] = $v['works']['subscribe_num'];
                $temp_res['info_num']      = $v['works']['info_num'];
            } else {
                continue;
            }

            $temp_res['user_info'] = User::getTeacherInfo($temp_res['user_id']);
            $list[$k]              = $temp_res;
        }

        return $list;
    }

    public function rank($params) {
        $id   = $params['id'] ?? 0;
        $rank = $params['rank'] ?? 0;
        if (empty($id) || empty($rank)) {
            return ['code' => false, 'msg' => '参数错误'];
        }

        $check = ChannelWorksList::where('id', '=', $id)->first();

        if (empty($check)) {
            return ['code' => false, 'msg' => 'id错误'];
        }

        $before_arr = ChannelWorksList::where('id', '<>', $id)
            ->where('rank', '<=', $rank)
            ->where('rank', '<>', 99)
            ->limit($rank - 1)
            ->orderBy('rank', 'asc')
            ->orderBy('id', 'asc')
            ->pluck('id')
            ->toArray();

        $rank = $rank > (count($before_arr) + 1) ? (count($before_arr) + 1) : $rank;

        $after_arr = ChannelWorksList::where('id', '<>', $id)
            ->where('rank', '<>', 99)
            ->whereNotIn('id', $before_arr)
            ->orderBy('rank', 'asc')
            ->orderBy('id', 'asc')
            ->pluck('id')
            ->toArray();

        DB::beginTransaction();
        $r1 = ChannelWorksList::where('id', '=', $id)->update([
            'rank' => $rank
        ]);
        if ($r1 === false) {
            DB::rollBack();
            return ['code' => false, 'msg' => '失败'];
        }


        $r2 = true;
        foreach ($before_arr as $k => $v) {
            $temp_r2 = ChannelWorksList::where('id', '=', $v)
                ->update([
                    'rank' => $k + 1
                ]);
            if ($temp_r2 === false) {
                $r2 = false;
            }
        }
        if ($r2 === false) {
            DB::rollBack();
            return ['code' => false, 'msg' => '失败'];
        }

        $r3 = true;
        foreach ($after_arr as $k => $v) {
            $temp_r3 = ChannelWorksList::where('id', '=', $v)
                ->update([
                    'rank' => $k + 1 + $rank
                ]);
            if ($temp_r3 === false) {
                $r3 = false;
            }
        }
        if ($r3 === false) {
            DB::rollBack();
            return ['code' => false, 'msg' => '失败'];
        }

        DB::commit();
        return ['code' => true, 'msg' => '成功'];

    }

}
