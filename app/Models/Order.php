<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Description of MallCategory
 *
 * @author wangxh
 */
class Order extends Base
{

    protected $table = 'nlsg_order';

    protected $fillable = [
        'id', 'live_num', 'pay_type', 'activity_tag', 'kun_said', 'refund_no', 'is_live_order_send',
        'ordernum', 'status', 'type', 'user_id', 'relation_id', 'cost_price', 'price', 'twitter_id', 'coupon_id', 'ip',
        'os_type', 'live_id', 'reward_type', 'reward', 'service_id', 'reward_num', 'pay_time', 'start_time', 'end_time',
        'pay_price', 'city', 'vip_order_type',
        'send_type', 'send_user_id', 'remark',


    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function works()
    {
        return $this->belongsTo(Works::class, 'relation_id', 'id');
    }

    public function column()
    {
        return $this->belongsTo(Column::class, 'relation_id', 'id');
    }

    //下单check
    public function addOrderLiveCheck($user_id, $tweeter_code, $target_id, $type)
    {

//        //校验用户等级
//        $rst = User::getLevel($user_id);
//        if ($rst > 2) {
//            return ['code' => 0, 'msg' => '您已是vip用户,可免费观看'];
//        }
        //管理员不用购买
        $is_admin = LiveConsole::isAdmininLive($tweeter_code ?? 0, $target_id);
        if ($is_admin) {
            return ['code' => 1, 'tweeter_code' => $tweeter_code];
        }
        //校验下单用户是否关注
        $is_sub = Subscribe::isSubscribe($user_id, $target_id, $type);
        if ($is_sub) {
            return ['code' => 0, 'msg' => '您已订阅过'];
        }

        //推客是否订阅
        $is_sub = Subscribe::isSubscribe($tweeter_code, $target_id, $type);
        if ($is_sub == 0) {
            $tweeter_code = 0;
        }

        return ['code' => 1, 'tweeter_code' => $tweeter_code];

    }

    static function getInfo($type, $relation_id, $send_type, $user_id = 0)
    {
        $result = false;
        switch ($type) {
            case 1:
                $model = new Column();
                $result = $model->getIndexColumn([$relation_id]);
                break;
            case 9:
                $model = new Works();
                $result = $model->getIndexWorks([$relation_id], 2, $user_id);
                break;
            case 10:
                $result = Live::where(['id' => $relation_id])->get()->toArray();
                break;
            case 14:
                $result = OfflineProducts::where(['id' => $relation_id])->get()->toArray();
                break;
            case 15:
                $model = new Column();
                $result = $model->getIndexColumn([$relation_id]);
                break;
            case 16:
                $result[] = [
                    'id'  => 1, 'type' => 6, 'text' => '幸福360会员',
                    'img' => '/nlsg/works/20210105102849884378.png', 'price' => 360.00
                ];
                break;
            case 17:
                if ($send_type == 1 || $send_type == 6) {
                    $model = new Column();
                    $result = $model->getIndexColumn([$relation_id]);
                } else {
                    if ($send_type == 2) {
                        $model = new Works();
                        $result = $model->getIndexWorks([$relation_id], 2, $user_id);
                    }
                }
                break;
        }
        return $result;
    }

    /**
     * 订单超时30分钟取消
     */
    public static function clear()
    {
        $past     = Carbon::parse('-30 minutes')->toDateTimeString();
        $subHour  = now()->subHours(5);
        $res = Order::where('created_at', '<', $past)
            ->where('created_at', '>', $subHour)
            ->where('status', 0)
            ->update([
                'status' => 2
            ]);
        return success('取消成功');
    }

    //推送到创业天下
    public static function pushToCytx()
    {
        $list = DB::table('nlsg_order as o')
            ->join('nlsg_pay_record as pr', 'o.ordernum', '=', 'pr.ordernum')
            ->join('nlsg_works as w', 'o.relation_id', '=', 'w.id')
            ->join('nlsg_user as u', 'o.user_id', '=', 'u.id')
            ->where('o.activity_tag', '=', 'cytx')
            ->where('o.status', '=', 1)
            ->where('o.type', '=', 9)
            ->where('cytx_job', '<>', -1)
            ->whereRaw(DB::raw(
                'cytx_job =0 or (UNIX_TIMESTAMP(cytx_check_time)+cytx_job*600 <= UNIX_TIMESTAMP())'
            ))
            ->where('pr.price', '>', 1)
            ->where('u.is_staff', '=', 0)
            ->where('o.is_shill', '=', 0)
            ->where('cytx_job', '<', 11)
            ->select(['o.id as order_id', 'o.ordernum', 'u.phone', 'u.nickname', 'pr.price', 'o.cytx_job', 'w.title'])
            ->limit(50)
            ->get();

        dd($list);

    }

    public static function getOrderPrice($type = 16, $today = false)
    {
        $query = Order::query();
        if ($type) {
            $query->where('type', $type);
        }
        if ($today) {
            $query->where('created_at', '>=', Carbon::today());
        }
        $list = $query->select([
            DB::raw('count(*) as total'),
            DB::raw('sum(pay_price) as price'),
            'user_id',
            'relation_id'
        ])
            ->where('status', 1)
            ->orderBy('total', 'desc')
            ->groupBy('relation_id')
            ->first();
        return $list;
    }

}
