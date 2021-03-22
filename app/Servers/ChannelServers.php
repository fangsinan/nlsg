<?php


namespace App\Servers;


use App\Models\ChannelOrder;
use App\Models\Column;
use App\Models\ConfigModel;
use App\Models\Live;
use App\Models\LiveCountDown;
use App\Models\Order;
use App\Models\Subscribe;
use App\Models\User;
use App\Models\Works;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ChannelServers
{

    //推送到创业天下
    private function pushToCytx($order_data)
    {
        if (is_object($order_data)) {
            $order_data = json_decode(json_encode($order_data), true);
        }

        if (empty($order_data['id'] ?? 0)) {
            return true;
        }
        $is_test = intval(ConfigModel::getData(37, 1));
        if (!empty($is_test)) {
            $url = 'http://39.107.71.116:8081/v1/partner/notify';
        } else {
            $url = 'https://api.chuangyetianxia.com/v1/partner/notify';
        }

        $data['telephone'] = $order_data['username'];
        $data['name'] = $order_data['title'];
        $data['price'] = $order_data['price'];
        $data['source'] = 'nlsg';
        $data['source_id'] = $order_data['ordernum'];

        $res = Http::post($url, $data);
        $res = json_decode($res);

        $order = Order::find($order_data['id']);
        if ($res->code === 200) {
            $order->cytx_job = -1;
        } else {
            $order->cytx_job = $order->cytx_job + 1;
        }
        $order->cytx_res = json_encode($res);
        $order->cytx_check_time = date('Y-m-d H:i:s');
        $order->save();
    }

    public static function cytxJob()
    {
        $c = new self();
        $c->cytxOrderList();
    }

    //创业天下订单获取
    public function cytxOrderList($order_id = 0)
    {
        $query = DB::table('nlsg_user as u')
            ->join('nlsg_order as o', 'u.id', '=', 'o.user_id')
            ->join('nlsg_pay_record as p', 'o.ordernum', '=', 'p.ordernum');
//            ->join('nlsg_works as w', 'o.relation_id', '=', 'w.id');

        if ($order_id) {
            $query->where('o.id', '=', $order_id);
        }

        $query->where('o.activity_tag', '=', 'cytx')
            ->where('o.status', '=', 1)
            ->where('p.price','>',0.01)
            ->whereIn('o.type', [9, 15])
            ->where('cytx_job', '<>', -1);

        $is_test = intval(ConfigModel::getData(37));
        if (!$is_test) {
            $query->where('p.price', '>', 1);
        }

        $query->where('u.is_staff', '=', 0)
            ->where('o.is_shill', '=', 0)
            ->where('cytx_job', '<', 11)
            ->whereRaw('(cytx_job = 0 or ((cytx_job*600) + UNIX_TIMESTAMP(cytx_check_time) <= UNIX_TIMESTAMP()))');

        if (empty($order_id)) {
            $query->limit(1000);
        }

        $list = $query->select([
            'o.id', 'o.ordernum', 'u.phone as username', 'o.type', 'o.relation_id', 'u.nickname', 'p.price', 'o.cytx_job', 'o.pay_time'
        ])->get();

        foreach ($list as $v) {
            $v->title = '';
            if ($v->type == 9) {
                $temp_info = Works::whereId($v->relation_id)->select('id', 'title')->first();
                $v->title = $temp_info->title;
            } else {
                $temp_info = Column::whereId($v->relation_id)->select(['id', 'name'])->first();
                $v->title = $temp_info->name;
            }
        }

        if ($list->isNotEmpty()) {
            $list = $list->toArray();
            foreach ($list as $v) {
                $this->pushToCytx($v);
            }
        }

    }

    //抖音订单拉取(定时任务)
    public function getDouYinOrder()
    {
        $is_test = intval(ConfigModel::getData(37, 1));
        if (!empty($is_test)) {
            ConfigModel::where('id', '=', 49)->update([
                'value' => "测试不执行任务"
            ]);
            return true;
        }

        $begin_date = ConfigModel::getData(38, 1);
        if (empty($begin_date)) {
            $min = date('i');
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
            $end_date = date('Y-m-d H:i:00', strtotime("$begin_date +300 minutes"));
            ConfigModel::whereId(38)->update(['value' => $end_date]);
        }

        $page = 0;
        $size = 100;
        $args = [
            'start_time' => $begin_date,
            'end_time' => $end_date,
            'size' => strval($size),
            'order_by' => 'create_time'
        ];

        $go_on = true;
        while ($go_on) {
            $args['page'] = strval($page);
            $temp_res = $this->douYinQuery($args);
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
    private function insertDouYinOrder($list)
    {
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

                $temp_data->order_id = $v['order_id'];
                $temp_data->channel = 1;
                $temp_data->sku = $vv['product_id'] ?? 0;
                $temp_data->phone = $v['post_tel'] ?? 0;
                $temp_data->order_status = $v['order_status'];
                $temp_data->create_time = date('Y-m-d H:i:s', $v['create_time']);
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
    public function supplementDouYinOrder()
    {
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
                $user = User::firstOrCreate([
                    'phone' => $v->phone,
                ], [
                    'nickname' => substr_replace($v->phone, '****', 3, 4),
                    'ref' => 11,
                ]);
                $v->user_id = $user->id;
            }
            $v->save();
        }
    }

    private function douYinQuery($args)
    {

        $token = $this->getDouYinToken();
        $now_date = date('Y-m-d H:i:s');
        $host = 'https://openapi-fxg.jinritemai.com';
        $v = '2';
        $c = 'order';
        $a = 'list';
        $method = $c . '.' . $a;

        $APP_KEY = config('env.DOUYIN_APP_KEY');
        $APP_SECRET = config('env.DOUYIN_APP_SECRET');
        ksort($args);
        $args_json = json_encode($args);

        // 计算签名
        $str = 'app_key' . $APP_KEY . 'method' . $method . 'param_json' .
            $args_json . 'timestamp' . $now_date . 'v' . $v;
        $md5_str = $APP_SECRET . $str . $APP_SECRET;
        $sign = md5($md5_str);
        $base_url = $host . '/' . $c . '/' . $a;

        $request_data = [
            'access_token' => $token,
            'app_key' => $APP_KEY,
            'method' => $method,
            'param_json' => $args_json,
            'timestamp' => $now_date,
            'v' => $v,
            'sign' => $sign,
        ];

        $res = Http::get($base_url . '?' . http_build_query($request_data));
        return json_decode($res, true);
    }

    private function getDouYinToken()
    {
        $cache_key_name = 'douyin_token';
        $access_token = Cache::get($cache_key_name);

        if (empty($access_token)) {
            $APP_KEY = config('env.DOUYIN_APP_KEY');
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
    public function douYinJob()
    {
        $is_test = intval(ConfigModel::getData(37, 1));
        if (!empty($is_test)) {
            ConfigModel::where('id', '=', 49)->update([
                'value' => "测试不执行任务"
            ]);
            return true;
        }

        //抖音订单 order_status=3,5  就可以执行
        $begin_date = date('Y-m-d 00:00:00', strtotime('-20 days'));
        $now_date = date('Y-m-d H:i:s');

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
                                $temp_data = [];
                                $temp_data['type'] = 6;
                                $temp_data['user_id'] = $v->user_id;
                                $temp_data['relation_id'] = $tv;
                                $temp_data['pay_time'] = $now_date;
                                $temp_data['start_time'] = $now_date;
                                $temp_data['status'] = 1;
                                $temp_data['give'] = 15;
                                $temp_data['end_time'] = date('Y-m-d 23:59:59', strtotime('+1 years'));
                                $temp_data['channel_order_id'] = $v->order_id;
                                $temp_data['channel_order_sku'] = $v->sku;
                                $add_sub_data[] = $temp_data;
                            } else {
                                $temp_end_time = date('Y-m-d 23:59:59', strtotime("$check->end_time +1 years"));
                                $check->end_time = $temp_end_time;
                                $edit_res = $check->save();
                                if ($edit_res === false) {
                                    DB::rollBack();
                                    break;
                                }
                            }
                        }
                        $edit_res = DB::table('nlsg_channel_order')
                            ->where('id', '=', $v->id)
                            ->update([
                                'status' => 1,
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
                                $temp_data = [];
                                $temp_data['type'] = 2;
                                $temp_data['user_id'] = $v->user_id;
                                $temp_data['relation_id'] = $tv;
                                $temp_data['pay_time'] = $now_date;
                                $temp_data['start_time'] = $now_date;
                                $temp_data['status'] = 1;
                                $temp_data['give'] = 15;
                                $temp_data['end_time'] = date('Y-m-d 23:59:59', strtotime('+1 years'));
                                $temp_data['channel_order_id'] = $v->order_id;
                                $temp_data['channel_order_sku'] = $v->sku;
                                $add_sub_data[] = $temp_data;
                            } else {
                                $temp_end_time = date('Y-m-d 23:59:59', strtotime("$check->end_time +1 years"));
                                $check->end_time = $temp_end_time;
                                $edit_res = $check->save();
                                if ($edit_res === false) {
                                    DB::rollBack();
                                    break;
                                }
                            }
                        }
                        $edit_res = DB::table('nlsg_channel_order')
                            ->where('id', '=', $v->id)
                            ->update([
                                'status' => 1,
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
                        $add_sub_data = [];
                        $add_cd_data = [];
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
                                $temp_data = [];
                                $temp_data['type'] = 3;
                                $temp_data['user_id'] = $v->user_id;
                                $temp_data['relation_id'] = $tv;
                                $temp_data['pay_time'] = $now_date;
                                $temp_data['status'] = 1;
                                $temp_data['give'] = 15;
                                $temp_data['channel_order_id'] = $v->order_id;
                                $temp_data['channel_order_sku'] = $v->sku;
                                $add_sub_data[] = $temp_data;
                            }
                            $check_cd = LiveCountDown::where('user_id', '=', $v->user_id)
                                ->where('phone', '=', $v->phone)
                                ->where('live_id', '=', $tv)
                                ->first();
                            if (empty($check_cd)) {
                                $temp_cd_data = [];
                                $temp_cd_data['live_id'] = 8;
                                $temp_cd_data['user_id'] = $v->user_id;
                                $temp_cd_data['phone'] = $v->phone;
                                $add_cd_data[] = $temp_cd_data;
                            }
                        }
                        $edit_res = DB::table('nlsg_channel_order')
                            ->where('id', '=', $v->id)
                            ->update([
                                'status' => 1,
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
                        $servers->openVip($v->user_id, $v->phone);
                        DB::table('nlsg_channel_order')
                            ->where('id', '=', $v->id)
                            ->update([
                                'status' => 1,
                                'success_at' => $now_date
                            ]);

                        foreach ($v->skuInfo->to_id as $to_id) {
                            if ($to_id == 2) {
                                //如果是2  表示为1360订单 需要写入order表
                                $orderModel = new Order();
                                $orderModel->type = 14;
                                $orderModel->live_num = 1;
                                $orderModel->relation_id = 4;
                                $orderModel->live_id = 0;
                                $orderModel->user_id = $v->user_id;
                                $orderModel->status = 1;
                                $orderModel->pay_time = $now_date;
                                $orderModel->cost_price = 1360;
                                $orderModel->price = 1360;
                                $orderModel->pay_price = 1360;
                                $orderModel->ordernum = $v->order_id;
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
                }
            }

            //21-03-22 补充的课程
            Subscribe::appendSub([$v->user_id],1);
        }

        if (!empty($invalid_id_list)) {
            DB::table('nlsg_channel_order')
                ->whereIn('id', $invalid_id_list)
                ->update([
                    'status' => 9
                ]);
        }
    }


}
