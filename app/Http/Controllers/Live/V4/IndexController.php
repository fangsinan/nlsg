<?php

namespace App\Http\Controllers\Live\V4;

use App\Http\Controllers\ControllerBackend;

use App\Http\Controllers\Controller;
use App\Models\Live;
use App\Models\LiveConsole;
use App\Models\LiveUserPrivilege;
use App\Models\Order;
use App\Models\PayRecordDetail;
use App\Models\Subscribe;
use App\Models\LiveLogin;
use App\Models\Wiki;
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
            })
            ->when($start && $end, function ($query) use ($start, $end) {
                $query->whereBetween('created_at', [
                    Carbon::parse($start)->startOfDay()->toDateTimeString(),
                    Carbon::parse($end)->endOfDay()->toDateTimeString(),
                ]);
            });

        $lists = $query->select('id', 'user_id', 'title', 'price',
            'order_num', 'status', 'created_at', 'cover_img')
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
        $list = Live::select('id', 'title', 'begin_at')->where('id', $liveId)->first();
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
            'live'          => $list,
            'subscribe_num' => $subscribeNum > 0 ? float_number($subscribeNum) : 0,
            'watch_num'     => $watchNum > 0 ? float_number($watchNum) : 0,
            'unwatch_num'   => $unwatchNum > 0 ? float_number($unwatchNum) : 0,
            'order_num'     => $orderNum > 0 ? float_number($orderNum) : 0,
            'order_income'  => $orderIncome > 0 ? float_number($orderIncome) : 0
        ];

        //折线图数据


        return success($data);

    }

    /**
     * @api {post} api/live_v4/index/create 直播创建/编辑
     * @apiVersion 4.0.0
     * @apiName  index/data
     * @apiGroup 直播后台-直播创建/编辑
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/live_v4/index/create
     * @apiDescription  直播创建/编辑
     *
     * @apiParam {number} id 直播id
     * @apiParam {string} title 标题
     * @apiParam {string} cover 封面
     * @apiParam {number} user_id 主播账号
     * @apiParam {number} begin_at 开始时间
     * @apiParam {number} end_at  结束时间
     * @apiParam {number} price   价格
     * @apiParam {number} twitter_money 分销金额
     * @apiParam {number} helper 直播助手
     * @apiParam {string} content 直播内容
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
    public function create(Request $request)
    {
        $input = $request->all();
        $cover = ! empty($input['cover']) ? covert_img($input['cover']) : '';
        $title = $input['title'] ?? '';
        $userId = $input['user_id'] ?? 0;
        $begin_at = $input['begin_at'] ?? date('Y-m-d H:i:s', time());
        $end_at = $input['end_at'] ?? date('Y-m-d H:i:s', time());
        $price = $input['price'] ?? 0;
        $twitter = $input['twitter_money'] ?? 0;
        $helper = $input['helper'] ?? '';
        $content = $input['content'] ?? '';
        if ( ! $title) {
           return error(1000, '标题不能为空');
        }
        if ( ! $begin_at) {
           return error(1000, '开始时间不能为空');
        }

        $data = [
            'user_id'       => $userId,
            'cover_img'     => $cover,
            'title'         => $title,
            'begin_at'      => $begin_at,
            'end_at'        => $end_at,
            'price'         => $price,
            'twitter_money' => $twitter,
            'helper'        => $helper,
            'content'       => $content
        ];

        if ( ! empty($input['id'])) {
            Live::where('id', $input['id'])->update($data);
        } else {
            Live::create($data);
        }
        return success();
    }

    /**
     * @api {get} api/live_v4/index/check_helper 检验助手
     * @apiVersion 4.0.0
     * @apiName  index/check_helper
     * @apiGroup 直播后台-检验助手
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/live_v4/index/check_helper
     * @apiDescription  检验助手
     *
     * @apiParam {number} helper 检验助手
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
    public function checkHelper(Request $request)
    {
        $params = $request->input();
        $model = new LiveConsole();
        $res = $model->checkHelper($params, 1);

        return $res;

    }

    /**
     * @api {get} api/live_v4/index/live_users 主播账号
     * @apiVersion 4.0.0
     * @apiName  index/live_users
     * @apiGroup 直播后台 -主播账号
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/live_v4/index/live_users
     * @apiDescription  主播账号
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
    public function getLiveUsers()
    {
        $users = LiveUserPrivilege::with('user:id,nickname')
            ->select('id', 'user_id')
            ->where('pri_level', 1)
            ->where('privilege', 2)
            ->where('is_del', 0)
            ->get()
            ->toArray();
        return success($users);
    }

}
