<?php


namespace App\Servers;


use App\Models\ChannelOrder;
use App\Models\ChannelSku;
use App\Models\ConfigModel;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

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
        $is_test = intval(ConfigModel::getData(37));
        if ($is_test) {
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

    //创业天下订单获取
    public function cytxOrderList($order_id = 0)
    {
        $query = DB::table('nlsg_user as u')
            ->join('nlsg_order as o', 'u.id', '=', 'o.user_id')
            ->join('nlsg_pay_record as p', 'o.ordernum', '=', 'p.ordernum')
            ->join('nlsg_works as w', 'o.relation_id', '=', 'w.id');

        if ($order_id) {
            $query->where('o.id', '=', $order_id);
        }

        $query->where('o.activity_tag', '=', 'cytx')
            ->where('o.status', '=', 1)
            ->where('o.type', '=', 9)
            ->where('cytx_job', '<>', -1)
            ->where('p.price', '>', 1)
            ->where('u.is_staff', '=', 0)
            ->where('o.is_shill', '=', 0)
            ->where('cytx_job', '<', 11)
            ->whereRaw('(cytx_job = 0 or ((cytx_job*600) + UNIX_TIMESTAMP(cytx_check_time) <= UNIX_TIMESTAMP()))');

        if (empty($order_id)) {
            $query->whereRaw('(UNIX_TIMESTAMP() - UNIX_TIMESTAMP(o.pay_time) > 600)');
            $query->limit(10);
        }

        $list = $query->select([
            'o.id', 'o.ordernum', 'u.phone as username',
            'u.nickname', 'p.price', 'o.cytx_job', 'w.title', 'o.pay_time'
        ])->get();

        if ($list->isNotEmpty()) {
            $list = $list->toArray();
            foreach ($list as $v) {
                $this->pushToCytx($v);
            }
        }

    }

    //抖音订单拉取
    public function getDouYinOrder()
    {
        $begin_date = ConfigModel::getData(38, 1);
        if (empty($begin_date)) {
            $min = date('i');
            if ($min % 2 === 0) {
                $begin_date = date('Y-m-d H:i:00', strtotime("-30 minutes"));
            } else {
                $begin_date = date('Y-m-d H:i:00', strtotime("-1470 minutes"));
            }
            $end_date = date('Y-m-d H:i:00', strtotime("$begin_date +10 minutes"));
        } else {
            if (strtotime($begin_date) >= time()) {
                return true;
            }
            $begin_date = date('Y-m-d H:i:00', strtotime($begin_date));
            $end_date = date('Y-m-d H:i:00', strtotime("$begin_date +300 minutes"));
//            ConfigModel::whereId(38)->update(['value'=>$end_date]);
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
                $check_sku = ChannelSku::checkSku($vv['product_id'], 1);
                if (!$check_sku) {
                    continue;
                }

                $temp_data = ChannelOrder::firstOrCreate([
                    'channel' => 1,
                    'order_id' => $v['order_id'],
                ]);
                dd($temp_data);

                $temp_data->order_id = $v['order_id'];
                $temp_data->sku = $vv['product_id'];
                $temp_data->phone = $v['post_tel'];
                $temp_data->order_status = $v['order_status'];
                $temp_data->create_time = date('Y-m-d H:i:s', $v['create_time']);
                $temp_data->pay_time = $v['pay_time'];
                $temp_data->update_time = date('Y-m-d H:i:s', $v['update_time']);
                $temp_data->save();

//                $temp_data = [];
//                $temp_data['channel'] = 1;
//                $temp_data['order_id'] = $v['order_id'];
//                $temp_data['sku'] = $vv['product_id'];
//                $temp_data['phone'] = $v['post_tel'];
//                $temp_data['order_status'] = $v['order_status'];
//                $temp_data['create_time'] = date('Y-m-d H:i:s', $v['create_time']);
//                $temp_data['pay_time'] = $v['pay_time'];
//                $temp_data['update_time'] = date('Y-m-d H:i:s', $v['update_time']);
//                $data[] = $temp_data;
            }
        }
//        if (!empty($data)) {
//            DB::table('nlsg_channel_order')->insert($data);
//        }
    }

    //抖音订单补全(定时任务)
    public function supplementDouYinOrder()
    {
        $list = ChannelOrder::where('channel', '=', 1)
            ->where('type', '=', 0)
            ->where('status', '=', 0)
            ->with(['skuInfo' => function ($query) {
                $query->where('channel', '=', 1);
            }])
            ->limit(50)
            ->get();
        if ($list->isEmpty()) {
            return true;
        }

        foreach ($list as $v) {
            if (empty($v->skuInfo)) {
                $v->status = 9;
            } else {
                $user = User::firstOrCreate([
                    'phone' => $v->phone
                ]);
                $v->user_id = $user->id;
                $v->to_id = $v->skuInfo->to_id;
                $v->to_info_id = $v->skuInfo->to_info_id;
                $v->type = $v->skuInfo->type;
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


    //todo 抖音开通(定时任务)
    public function douYinJob()
    {
        $list = ChannelOrder::where('type', '>', 0)->where('status', '=', 0)
            ->select(['id', 'type', 'user_id', 'order_status', 'to_id', 'to_info_id'])
            ->get();

        return $list;
    }


}
