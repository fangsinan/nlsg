<?php


namespace App\Servers;


use App\Models\ConfigModel;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class ChannelServers
{

    //推送到创业天下
    private function pushToCytx($order_data)
    {
        if (is_object($order_data)){
            $order_data = json_decode(json_encode($order_data),true);
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

        if ($list->isNotEmpty()){
            $list = $list->toArray();
            foreach ($list as $v) {
                $this->pushToCytx($v);
            }
        }

    }

    //todo 抖音订单拉取
    public function getDouyinOrder(){

        $min = date('i');
        if ($min%2 === 0){
            $begin_date = date('Y-m-d H:i:00',strtotime("-30 minutes"));
        }else{
            $begin_date = date('Y-m-d H:i:00',strtotime("-1470 minutes"));
        }

        $end_date = date('Y-m-d H:i:00',strtotime("$begin_date +10 minutes"));
        dd([$begin_date,$end_date]);

        $go_on = true;
        $page = 1;
        $size = 100;
        $args = [];



//        while ($go_on){
//            $temp_res = $this->douyinQuery($args);

//        }
    }

    private function douyinQuery($args){

    }


    //todo 抖音开通


}
