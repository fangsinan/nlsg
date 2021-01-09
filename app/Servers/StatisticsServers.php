<?php


namespace App\Servers;


use App\Models\History;
use App\Models\Order;
use App\Models\Subscribe;
use App\Models\User;
use App\Models\WorksInfo;
use Illuminate\Support\Facades\DB;

class StatisticsServers
{
    public function kunSaid($params)
    {
        $flag = $params['flag'] ?? 0;
        $now = time();
        $now_date = date('Y-m-d H:i:s', $now);
        $today_date = date('Y-m-d 00:00:00', $now);
        $data = [];
        switch ($flag) {
            case 1:
                //直播统计
                //经营能量直播已支付
                $order_total_num = Order::where('type', '=', 10)
                    ->where('live_id', '=', 14)
                    ->where('status', '=', 1)
                    ->select([
                        DB::raw('count(id) as num'),
                        DB::raw('sum(pay_price) as price')
                    ])->first();

                //当天销量
                $order_today_num = Order::where('pay_time', '>=', $today_date)
                    ->where('type', '=', 10)
                    ->where('live_id', '=', 14)
                    ->where('status', '=', 1)
                    ->select([
                        DB::raw('count(id) as num'),
                        DB::raw('sum(pay_price) as price')
                    ])->first();

                //代理商销量
                $agent_num = Order::where('type', '=', 10)
                    ->where('live_id', '=', 14)
                    ->where('twitter_id', '>', 0)
                    ->where('status', '=', 1)
                    ->select([
                        DB::raw('count(id) as num'),
                        DB::raw('sum(pay_price) as price')
                    ])->first();

                $live_info = [
                    '总成交' => ['数量' => $order_total_num->num ?? 0, '金额' => $order_total_num->price ?? 0],
                    '当天截止' . $now_date => ['数量' => $order_today_num->daynum ?? 0, '金额' => $order_today_num->price ?? 0],
                    '代理商推荐' => ['数量' => $agent_num->num ?? 0, '金额' => $agent_num->price ?? 0]
                ];
                $data['直播'] = $live_info;
                break;
            case 2:
                //每日琨说
                $starttime = (isset($params['starttime']) && !empty($params['starttime'])) ? $params['starttime'] : 0;
                $endtime = (isset($params['endtime']) && !empty($params['endtime'])) ? $params['endtime'] : 0;
                if (empty($starttime) || empty($endtime) || strpos($starttime, '_') === false ||
                    strpos($endtime, '_') === false) {
                    return ['code' => false, 'msg' => '开始结束时间有误'];
                }
                $starttime = str_replace("_", " ", $starttime);
                $endtime = str_replace("_", " ", $endtime);

                if ($starttime === false || $endtime === false) {
                    return ['code' => false, 'msg' => '格式转换错误'];
                }

                //每日琨说所有订阅人数
                $sub_total_num = Subscribe::where('relation_id', '=', 566)
                    ->where('type', '=', 2)
                    ->where('created_at', '>=', $starttime)
                    ->where('created_at', '<=', $endtime)
                    ->count();
                //所有上线的课程
                $works_info = WorksInfo::where('pid', '=', 566)->where('status', '=', 4)
                    ->where('online_time', '>=', $starttime)
                    ->where('online_time', '<=', $endtime)
                    ->orderBy('rank', 'asc')
                    ->orderBy('id', 'desc')
                    ->select(['id', 'title as "名称"', 'view_num as "浏览量"'])
                    ->get();

                //每天的阅读量和收听人数
                $history_info = History::where('relation_id', '=', 566)
                    ->where('relation_type', '=', 4)
                    ->where('created_at', '>=', $starttime)
                    ->where('created_at', '<=', $endtime)
                    ->groupBy('info_id')
                    ->select(['info_id', DB::raw('count(id) as num')])
                    ->get();

                $history_arr = [];
                foreach ($history_info as $k => $v) {
                    $history_arr[$v->info_id] = $v->num;
                }

                //分享数量  v4表取消了
                $listenTotalNum = 0;
                $shareTotalNum = 0;
                foreach ($works_info as $key => &$val) {
                    $listennum = $history_arr[$val['id']] + 0;
                    //处理收听数量
                    $val['收听人数'] = $listennum;

                    $listenTotalNum += $listennum;
                    unset($val['id']);
                }

                $data['琨说'] = [
                    '每日琨说订阅人数' => $sub_total_num,
                    '当前收听总人数' => $listenTotalNum ?? [],
                    '当前分享总人数' => '暂无',
                    '每日琨说' => $works_info
                ];
                break;
            case 3:
                $starttime = (isset($params['starttime']) && !empty($params['starttime'])) ? $params['starttime'] : 0;
                $endtime = (isset($params['endtime']) && !empty($params['endtime'])) ? $params['endtime'] : 0;
                if (empty($starttime) || empty($endtime) || strpos($starttime, '_') === false ||
                    strpos($endtime, '_') === false) {
                    return ['code' => false, 'msg' => '开始结束时间有误'];
                }
                $starttime = str_replace("_", " ", $starttime);
                $endtime = str_replace("_", " ", $endtime);

                if ($starttime === false || $endtime === false) {
                    return ['code' => false, 'msg' => '格式转换错误'];
                }

                $data['注册人数'] = User::where('created_at', '>=', $starttime)
                    ->where('created_at', '<=', $endtime)
                    ->where('is_robot', '=', 0)
                    ->count();

                $typeArr = [1, 2, 9, 10, 12, 14, 15, 16, 17];
                $data['成交总额'] = Order::where('pay_time', '>=', $starttime)
                    ->where('pay_time', '<=', $endtime)
                    ->whereIn('type', $typeArr)
                    ->where('status', '=', 1)
                    ->sum('price');
                $data['购买用户'] = Order::where('pay_time', '>=', $starttime)
                    ->where('pay_time', '<=', $endtime)
                    ->whereIn('type', $typeArr)
                    ->where('status', '=', 1)
                    ->count('user_id');

                $order_info = Order::where('pay_time', '>=', $starttime)
                    ->where('pay_time', '<=', $endtime)
                    ->whereIn('type', $typeArr)
                    ->where('status', '=', 1)
                    ->groupBy('type')
                    ->select([
                        'type', DB::raw('count(id) as num'), DB::raw('sum(pay_price) as price')
                    ])->get();

                foreach ($order_info as $ok => $ov) {
                    switch ($ov->type) {
                        case 1:
                            $data['虚拟订单']['专栏'] = '成交数量：' . $ov->num . ' 成交金额：' . $ov->price;
                            break;
                        case 2:
                            $data['虚拟订单']['会员'] = '成交数量：' . $ov->num . ' 成交金额：' . $ov->price;
                            break;
                        case 9:
                            $data['虚拟订单']['精品课'] = '成交数量：' . $ov->num . ' 成交金额：' . $ov->price;
                            break;
                        case 10:
                            $data['虚拟订单']['直播'] = '成交数量：' . $ov->num . ' 成交金额：' . $ov->price;
                            break;
                        case 12:
                            $data['虚拟订单']['直播预约'] = '成交数量：' . $ov->num . ' 成交金额：' . $ov->price;
                            break;
                        case 14:
                            $data['虚拟订单']['线下课'] = '成交数量：' . $ov->num . ' 成交金额：' . $ov->price;
                            break;
                        case 15:
                            $data['虚拟订单']['讲座'] = '成交数量：' . $ov->num . ' 成交金额：' . $ov->price;
                            break;
                        case 16:
                            $data['虚拟订单']['360会员'] = '成交数量：' . $ov->num . ' 成交金额：' . $ov->price;
                            break;
                        case 17:
                            $data['虚拟订单']['赠送下单'] = '成交数量：' . $ov->num . ' 成交金额：' . $ov->price;
                            break;
                    }
                }

                //todo 商品订单


                $data['商品订单']='成交单量：0 成交金额：0';
                break;
        }

        return $data;
    }
}
