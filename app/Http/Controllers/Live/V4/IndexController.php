<?php

namespace App\Http\Controllers\Live\V4;

use App\Http\Controllers\ControllerBackend;

use App\Http\Controllers\Controller;
use App\Models\Live;
use App\Models\Order;
use App\Models\PayRecordDetail;
use App\Models\Subscribe;
use App\Models\LiveLogin;
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
        $subscribeNum =  Subscribe::where('status', 1)->count();
        $watchNum   = LiveLogin::count();

        $data = [
            'subscribe_num' => $subscribeNum,
            'watch_num'     => $watchNum,
            'order_num'     => 10,
            'order_income'  => 1000
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
        $title  = $request->get('title');
        $status = $request->get('status');
        $query = Live::with('user:id,nickname')
            ->when($title, function ($query) use ($title) {
                $query->where('title', 'like', '%'.$title.'%');
            })
            ->when(! is_null($status), function ($query) use ($status) {
               $query->where('status', $status);
            });
        $lists = $query->select('id', 'user_id', 'title', 'price',
            'order_num', 'status', 'created_at')
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->toArray();



        //  直播收益   直播推广收益
        foreach ($lists['data'] as &$val){
            //直播收益
            $val['live_price_sum'] = Order::where([
                'type'      => 10,
                'live_id'   => $val['id'],
                'status'    => 1,
            ])->sum('pay_price');

            $val['live_twitter_price_sum'] = Order::join('nlsg_pay_record_detail as d','d.ordernum','=','nlsg_order.ordernum')
                        ->where([
                        'nlsg_order.type'      => 10,
                        'nlsg_order.live_id'   => $val['id'],
                        'nlsg_order.status'    => 1,
                    ])->where('nlsg_order.twitter_id','>',0)->sum('d.price');
        }
        return success($lists);
    }



}
