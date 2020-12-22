<?php

namespace App\Http\Controllers\Admin\V4;

use App\Http\Controllers\ControllerBackend;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends ControllerBackend
{
    /**
     * @api {get} api/admin_v4/order/list 虚拟订单列表
     * @apiVersion 4.0.0
     * @apiName  order
     * @apiGroup 后台-虚拟订单
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/order/list
     * @apiDescription 虚拟订单列表
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

        return success($lists);

    }

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
            'total_num'   => $list['price'] ?? 0,
            'total_price' => $list['total'] ?? 0,
            'today_num'   => $today['total'] ?? 0,
            'totday_price'=> $today['price'] ?? 0
        ];
        return success($data);

    }
}
