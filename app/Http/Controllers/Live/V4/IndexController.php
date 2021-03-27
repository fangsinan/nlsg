<?php

namespace App\Http\Controllers\Live\V4;

use App\Http\Controllers\ControllerBackend;

use App\Http\Controllers\Controller;
use App\Models\Live;
use App\Models\Order;
use App\Models\PayRecordDetail;
use App\Models\Subscribe;
use App\Models\LiveLogin;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IndexController extends ControllerBackend
{
    /**
     * @api {get} api/live_v4/index/statistics 数据统计
     * @apiVersion 4.0.0
     * @apiName  index/statistics
     * @apiGroup 直播后台-数据统计
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/live_v4/index/statistics
     * @apiDescription  数据统计
     *
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
    public function index()
    {
        $subscribeNum = Subscribe::where('type', 3)->where('status', 1)->count();
        $watchNum = LiveLogin::count();
        $orderNum = Order::where('type', 10)->where('status', 1)->count();
        $orderIncome = Order::where('type', 10)->where('status', 1)->sum('pay_price');
        $data = [
            'subscribe_num' => float_number($subscribeNum),
            'watch_num'     => float_number($watchNum),
            'order_num'     => float_number($orderNum),
            'order_income'  => float_number($orderIncome)
        ];
        return success($data);
    }

    /**
     * @api {get} api/live_v4/index/lives 直播列表
     * @apiVersion 4.0.0
     * @apiName  index/lives
     * @apiGroup 直播后台-直播列表
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/live_v4/index/lives
     * @apiDescription  直播列表
     *
     * @apiParam {number} page 分页
     * @apiParam {string} title 名称
     * @apiParam {number} status   0 待支付  1已支付
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
    public function lives(Request $request)
    {
        $title = $request->get('title');
        $status = $request->get('status');
        $start = $request->get('start');
        $end = $request->get('end');
        $query = Live::with('user:id,nickname')
            ->when($title, function ($query) use ($title) {
                $query->where('title', 'like', '%'.$title.'%');
            })
            ->when(! is_null($status), function ($query) use ($status) {
                $query->where('status', $status);
            })->when($start && $end, function ($query) use ($start, $end) {
                $query->whereBetween('created_at', [
                    Carbon::parse($start)->startOfDay()->toDateTimeString(),
                    Carbon::parse($end)->endOfDay()->toDateTimeString(),
                ]);
            });

        $lists = $query->select('id', 'user_id', 'title', 'price',
            'order_num', 'status', 'created_at')
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->toArray();


        //  直播收益   直播推广收益
        foreach ($lists['data'] as &$val) {
            //直播收益
            $val['live_price_sum'] = Order::where([
                'type'    => 10,
                'live_id' => $val['id'],
                'status'  => 1,
            ])->sum('pay_price');

            $val['live_twitter_price_sum'] = Order::join('nlsg_pay_record_detail as d', 'd.ordernum', '=',
                'nlsg_order.ordernum')
                ->where([
                    'nlsg_order.type'    => 10,
                    'nlsg_order.live_id' => $val['id'],
                    'nlsg_order.status'  => 1,
                ])->where('nlsg_order.twitter_id', '>', 0)->sum('d.price');
        }
        return success($lists);
    }

    /**
     * @api {get} api/live_v4/index/data 直播分析
     * @apiVersion 4.0.0
     * @apiName  index/data
     * @apiGroup 直播后台-分析
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/live_v4/index/data
     * @apiDescription  直播分析
     *
     * @apiParam {number} live_id 直播id
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
    public function data(Request $request)
    {
        $liveId = $request->get('live_id');
        $list = Live::select('id','title','begin_at')->where('id', $liveId)->first();
        if ( ! $list) {
            return error(1000, '直播不存在');
        }

        $subscribeNum = Subscribe::where('type', 3)
            ->where('relation_id', $liveId)
            ->where('status', 1)
            ->count();
        $watchNum = LiveLogin::where('live_id', $liveId)->distinct('user_id')->count();

        $unwatchNum = $subscribeNum - $watchNum > 0 ? intval($subscribeNum - $watchNum) : 0;
        $orderNum = Order::where('type', 10)
            ->where('live_id', $liveId)
            ->where('status', 1)
            ->count();
        $orderIncome = Order::where('type', 10)
            ->where('live_id', $liveId)
            ->where('status', 1)
            ->sum('pay_price');


        $data = [
            'live'     => $list,
            'subscribe_num' => $subscribeNum > 0 ? float_number($subscribeNum) : 0,
            'watch_num'     => $watchNum > 0 ? float_number($watchNum) : 0,
            'unwatch_num'   => $unwatchNum > 0 ? float_number($unwatchNum) : 0,
            'order_num'     => $orderNum > 0 ? float_number($orderNum) : 0,
            'order_income'  => $orderIncome > 0 ? float_number($orderIncome) : 0
        ];

        //折线图数据


        return success($data);

    }


}
