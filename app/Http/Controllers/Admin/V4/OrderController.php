<?php

namespace App\Http\Controllers\Admin\V4;

use App\Http\Controllers\ControllerBackend;
use App\Models\Order;
use App\Models\Works;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends ControllerBackend
{
    /**
     * @api {get} api/admin_v4/order/list 精品课订单
     * @apiVersion 4.0.0
     * @apiName  order
     * @apiGroup 后台-虚拟订单
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/order/list
     * @apiDescription 精品课订单
     *
     * @apiParam {number} page 分页
     * @apiParam {string} title 名称
     * @apiParam {number} status   0 待支付  1已支付
     * @apiParam {string} nickname 昵称
     * @apiParam {string} phone    账号
     * @apiParam {string} ordernum 订单号
     * @apiParam {string} start  开始时间
     * @apiParam {string} end    结束时间
     * @apiParam {string} os_type  订单来源
     * @apiParam {string} pay_type  支付方式
     * @apiParam {string} level    推者类型
     *
     *
     * @apiSuccessExample  Success-Response:
     * HTTP/1.1 200 OK
     * {
     *   "code": 200,
     *   "msg" : '成功',
     *   "data": {
     *
     *    }
     * }
     */
    public function list(Request $request)
    {
        $phone = $request->get('phone');
        $nickname = $request->get('nickname');
        $ordernum = $request->get('ordernum');
        $title = $request->get('title');
        $start = $request->get('start');
        $end = $request->get('end');
        $status = $request->get('status');
        $level = $request->get('level');
        $pay_type = $request->get('pay_type');
        $os_type = $request->get('os_type');
        $sort = $request->get('sort');
        $query = Order::with(
            [
                'user:id,nickname',
                'works:id,title'
            ])
            ->when(! is_null($status), function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when(! is_null($pay_type), function ($query) use ($pay_type) {
                $query->where('pay_type', $pay_type);
            })
            ->when(! is_null($os_type), function ($query) use ($os_type) {
                $query->where('os_type', $os_type);
            })
            ->when($nickname, function ($query) use ($nickname) {
                $query->whereHas('user', function ($query) use ($nickname) {
                    $query->where('nickname', 'like', '%'.$nickname.'%');
                });
            })
            ->when($phone, function ($query) use ($phone) {
                $query->whereHas('user', function ($query) use ($phone) {
                    $query->where('phone', 'like', '%'.$phone.'%');
                });
            })
            ->when($level, function ($query) use ($level) {
                $query->whereHas('user', function ($query) use ($level) {
                    $query->where('level', 'like', '%'.$level.'%');
                });
            })
            ->when($title, function ($query) use ($title) {
                $query->whereHas('works', function ($query) use ($title) {
                    $query->where('title', 'like', '%'.$title.'%');
                });
            })
            ->when($ordernum, function ($query) use ($ordernum) {
                $query->where('ordernum', 'like', '%'.$ordernum.'%');
            })
            ->when($start && $end, function ($query) use ($start, $end) {
                $query->whereBetween('created_at', [
                    Carbon::parse($start)->startOfDay()->toDateTimeString(),
                    Carbon::parse($end)->endOfDay()->toDateTimeString(),
                ]);
            });

        $direction = $sort == 'asc' ? 'asc' : 'desc';
        $lists = $query->select('id', 'user_id', 'relation_id', 'ordernum', 'price', 'pay_price', 'os_type', 'pay_type',
            'created_at', 'status')
            ->where('type', 9)
            ->orderBy('id', $direction)
            ->paginate(10)
            ->toArray();

        $rank = Order::with('works:id,title,cover_img')
            ->select([
                DB::raw('count(*) as total'),
                'user_id',
                'relation_id'
            ])
            ->where('type', 9)
            ->where('status', 1)
            ->orderBy('total', 'desc')
            ->groupBy('relation_id')
            ->get();
        $data = [
            'lists' => $lists,
            'rank'  => $rank
        ];
        return success($data);

    }

    /**
     * @api {get} api/admin_v4/order/statistic 订单统计
     * @apiVersion 4.0.0
     * @apiName  order/statistic
     * @apiGroup 后台-虚拟订单
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/order/statistic
     * @apiDescription  订单统计
     *
     * @apiParam   {number}  type 类型 1 专栏 2 会员  3充值  4财务打款 5 打赏 6分享赚钱 7支付宝提现 8微信提现  9精品课  10直播回放 12直播预约   13能量币  14 线下产品(门票类)  16新vip
     *
     * @apiSuccess {string}  total_num     总订单数
     * @apiSuccess {string}  total_price   总订单金额
     * @apiSuccess {string}  today_num     今日订单数
     * @apiSuccess {string}  totday_price  今日订单金额
     *
     * @apiSuccessExample  Success-Response:
     * HTTP/1.1 200 OK
     * {
     *   "code": 200,
     *   "msg" : '成功',
     *   "data": {
     *
     *    }
     * }
     */
    public function getOrderStatistic(Request $request)
    {
        $type = $request->get('type') ?? 1;
        $list = Order::select([
            DB::raw('sum(pay_price) as price'),
            DB::raw('count(id) as total')
        ])
            ->where('status', 1)
            ->where('type', $type)
            ->groupBy('type')
            ->first();
        $today = Order::select([
            DB::raw('sum(pay_price) as price'),
            DB::raw('count(id) as total')
        ])
            ->where('status', 1)
            ->where('type', $type)
            ->where('created_at', '>=', Carbon::today())
            ->groupBy('type')
            ->first();
        $data = [
            'total_num'    => $list['price'] ?? 0,
            'total_price'  => $list['total'] ?? 0,
            'today_num'    => $today['total'] ?? 0,
            'totday_price' => $today['price'] ?? 0
        ];
        return success($data);

    }

    /**
     * @api {get} api/admin_v4/order/detial 精品课-订单详情
     * @apiVersion 4.0.0
     * @apiName  order/detial
     * @apiGroup 后台-虚拟订单
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/order/detial
     * @apiDescription  精品课-订单详情
     *
     * @apiParam   {number}  id 订单id
     *
     * @apiSuccess {string}  ordernum      订单号
     * @apiSuccess {string}  os_type       1 安卓 2ios 3微信
     * @apiSuccess {string}  pay_price     支付价格
     * @apiSuccess {string}  created_at    支付时间
     * @apiSuccess {string}  user           下单用户信息
     * @apiSuccess {string}  works          精品课相关
     * @apiSuccess {string}  works.user     精品课作者
     *
     * @apiSuccessExample  Success-Response:
     * HTTP/1.1 200 OK
     * {
     *   "code": 200,
     *   "msg" : '成功',
     *   "data": {
     *
     *    }
     * }
     */
    public function getOrderDetail(Request $request)
    {
        $id = $request->get('id');
        $list = Order::with(
            [
                'user:id,nickname,phone',
                'works:id,title,user_id,cover_img,price',
                'works.user:id,nickname'
            ])
            ->select('id', 'user_id', 'relation_id', 'ordernum', 'os_type', 'pay_price', 'created_at')
            ->where('id', $id)
            ->first();
        return success($list);
    }

    /**
     * @api {get} api/admin_v4/order/user 会员订单
     * @apiVersion 4.0.0
     * @apiName  order
     * @apiGroup 后台-虚拟订单
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/order/user
     * @apiDescription 会员订单
     *
     * @apiParam {number} page 分页
     * @apiParam {string} title 名称
     * @apiParam {number} status   0 待支付  1已支付
     * @apiParam {string} nickname 昵称
     * @apiParam {string} phone    账号
     * @apiParam {string} ordernum 订单号
     * @apiParam {string} start  开始时间
     * @apiParam {string} end    结束时间
     * @apiParam {string} os_type  订单来源
     * @apiParam {string} pay_type  支付方式
     * @apiParam {string} vip_order_type  1开通 2续费 3升级
     * @apiParam {string} user        用户
     * @apiParam {string} user.level  1 早期366老会员 2 推客 3黑钻 4皇钻 5代理
     *
     *
     * @apiSuccessExample  Success-Response:
     * HTTP/1.1 200 OK
     * {
     *   "code": 200,
     *   "msg" : '成功',
     *   "data": {
     *
     *    }
     * }
     */
    public function user(Request $request)
    {
        $phone = $request->get('phone');
        $nickname = $request->get('nickname');
        $ordernum = $request->get('ordernum');
        $title = $request->get('title');
        $start = $request->get('start');
        $end = $request->get('end');
        $status = $request->get('status');
        $pay_type = $request->get('pay_type');
        $os_type = $request->get('os_type');
        $sort = $request->get('sort');
        $query = Order::with(
            [
                'user:id,nickname,level',
                'works:id,title'
            ])
            ->when(! is_null($status), function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when(! is_null($pay_type), function ($query) use ($pay_type) {
                $query->where('pay_type', $pay_type);
            })
            ->when(! is_null($os_type), function ($query) use ($os_type) {
                $query->where('os_type', $os_type);
            })
            ->when($nickname, function ($query) use ($nickname) {
                $query->whereHas('user', function ($query) use ($nickname) {
                    $query->where('nickname', 'like', '%'.$nickname.'%');
                });
            })
            ->when($phone, function ($query) use ($phone) {
                $query->whereHas('user', function ($query) use ($phone) {
                    $query->where('phone', 'like', '%'.$phone.'%');
                });
            })
            ->when($title, function ($query) use ($title) {
                $query->whereHas('works', function ($query) use ($title) {
                    $query->where('title', 'like', '%'.$title.'%');
                });
            })
            ->when($ordernum, function ($query) use ($ordernum) {
                $query->where('ordernum', 'like', '%'.$ordernum.'%');
            })
            ->when($start && $end, function ($query) use ($start, $end) {
                $query->whereBetween('created_at', [
                    Carbon::parse($start)->startOfDay()->toDateTimeString(),
                    Carbon::parse($end)->endOfDay()->toDateTimeString(),
                ]);
            });

        $direction = $sort == 'asc' ? 'asc' : 'desc';
        $lists = $query->select('id', 'user_id', 'relation_id', 'ordernum', 'price', 'pay_price', 'os_type', 'pay_type',
            'vip_order_type',
            'created_at', 'status')
            ->where('type', 9)
            ->orderBy('id', $direction)
            ->paginate(10)
            ->toArray();

        $item = [
            'yellow'       => 0,
            'yellow_today' => 0,
            'black'        => 0,
            'black_today'  => 0,
            'money'        => 0,
            'money_today'  => 0,
        ];
        $orders = Order::getOrderPrice(16);
        if ($orders['relation_id'] == 1) {
            $item['yellow'] = $orders['total'];
        } elseif ($v['relation_id'] == 2) {
            $item['black'] = $orders['total'];
        }
        $item['money'] = $orders['price'];

        $today = Order::getOrderPrice(16, true);
        if ($today['relation_id'] == 1) {
            $item['yellow_today'] = $today['total'];
        } elseif ($today['relation_id'] == 2) {
            $item['black_today'] = $today['total'];
        }
        $item['money_today'] = $today['price'];
        $data = [
            'lists' => $lists,
            'rank'  => $item
        ];
        return success($data);
    }
}
