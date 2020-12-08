<?php


namespace App\Servers;


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

    //todo 抖音订单拉取
    public function getDouYinOrder()
    {
        $min = date('i');
        if ($min % 2 === 0) {
            $begin_date = date('Y-m-d H:i:00', strtotime("-30 minutes"));
        } else {
            $begin_date = date('Y-m-d H:i:00', strtotime("-1470 minutes"));
        }
        $end_date = date('Y-m-d H:i:00', strtotime("$begin_date +10 minutes"));

        $page = 1;
        $size = 100;
        $args = [
            'start_time' => $begin_date,
            'end_time' => $end_date,
            'size' => $size,
            'order_by' => 'create_time'
        ];

        $args['page'] = 1;
        $res = $this->douYinQuery($args);
        dd($res);

        $go_on = true;
        while ($go_on) {
            $args['page'] = $page;
            $temp_res = $this->douYinQuery($args);
            var_dump($args);
            $page++;
            if ($page * $size >= $temp_res->total) {
                $go_on = false;
            }
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
        $str = 'app_key' . $APP_KEY . 'method' . $method . 'param_json' . $args_json . 'timestamp' . $now_date . 'v' . $v;
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

        //https://openapi-fxg.jinritemai.com/order/list?
        //app_key=your_app_key_here&access_token=your_accesstoken_here&method=order.list&
        //param_json={"end_time":"2018/05/31 16:01:02","is_desc":"1","page":"0","size":"10","start_time":"2018/04/01 15:03:58"}&
        //timestamp=2018-06-14%2016:06:59&v=2&sign=your_sign_here

        //access_token=258b142a-de5d-408a-8eef-e88d94771668&
        //app_key=6857846430543906317&
        //method=order.list&
        //param_json=%7B%22end_time%22%3A%222020-12-07+16%3A49%3A00%22%2C%22order_by%22%3A%22create_time%22%2C%22page%22%3A1%2C%22size%22%3A100%2C%22start_time%22%3A%222020-12-07+16%3A39%3A00%22%7D&timestamp=2020-12-08+17%3A09%3A36&v=2&sign=6300669593c287495cc74125b03a3f8f

//        dd([$base_url,$request_data,http_build_query($request_data),urldecode(http_build_query($request_data))]);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $base_url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, urldecode(http_build_query($request_data)));
        $res = curl_exec($curl);
        curl_close($curl);
        return json_decode($res, true);

//        $res = Http::get($base_url.'?'.http_build_query($request_data));
//        return json_decode($res);

//        $data = new class {
//        };
//        $data->total = 1000;
//        return $data;
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


    //todo 抖音开通


}
