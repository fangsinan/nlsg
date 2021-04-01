<?php

namespace App\Http\Controllers\Live\V4;

use App\Http\Controllers\ControllerBackend;
use App\Models\BackendLiveRole;
use App\Models\Live;
use App\Models\LiveConsole;
use App\Models\LiveInfo;
use App\Models\LiveLogin;
use App\Models\LiveNumber;
use App\Models\LiveUserPrivilege;
use App\Models\Order;
use App\Models\Subscribe;
use App\Models\User;
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
            'watch_num' => float_number($watchNum),
            'order_num' => float_number($orderNum),
            'order_income' => float_number($orderIncome)
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
     * @apiParam {number} status   1未开始 2已结束 3正在直播
     * @apiParam {number} start   开始时间
     * @apiParam {number} end   结束时间
     *
     * @apiSuccess {string} title 标题
     * @apiSuccess {number} price 价格
     * @apiSuccess {number} order_num 预约人数
     * @apiSuccess {number} live_status 直播状态 1未开始 2已结束 3正在直播
     * @apiSuccess {number} live_price_sum 直播收益
     * @apiSuccess {number} live_twitter_price_sum 推客收益
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
                $query->where('title', 'like', '%' . $title . '%');
            })
            ->when($start && $end, function ($query) use ($start, $end) {
                $query->whereBetween('created_at', [
                    Carbon::parse($start)->startOfDay()->toDateTimeString(),
                    Carbon::parse($end)->endOfDay()->toDateTimeString(),
                ]);
            });

        if ($this->user['live_role'] == 21) {
            $query->where('user_id', '=', $this->user['user_id']);
        }elseif ($this->user['live_role'] == 23) {
            $blrModel = new BackendLiveRole();
            $son_user_id = $blrModel->getDataUserId($this->user['username']);
            $query->whereIn('user_id',$son_user_id);
        }

        if (!empty($status)) {
            if ($status == 1) {
                $query->whereHas('liveInfo', function ($q) use ($status) {
                    $q->where('is_begin', 0)->where('is_finish', 0);
                });
            } elseif ($status == 2) {
                $query->whereHas('liveInfo', function ($q) use ($status) {
                    $q->where('is_begin', 0)->where('is_finish', 1);
                });
            } elseif ($status == 3) {
                $query->whereHas('liveInfo', function ($q) use ($status) {
                    $q->where('is_begin', 1)->where('is_finish', 0);
                });
            }
        }

        $lists = $query->select('id', 'user_id', 'title', 'price',
            'order_num', 'status', 'created_at', 'cover_img')
            ->where('is_del', 0)
            ->where('status', 4)
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->toArray();


        //  直播收益   直播推广收益
        foreach ($lists['data'] as &$val) {

            $channel = LiveInfo::where('live_pid', $val['id'])
                ->where('status', 1)
                ->orderBy('id', 'desc')
                ->first();
            if ($channel) {
                if ($channel->is_begin == 0 && $channel->is_finish == 0) {
                    $val['live_status'] = 1;
                } elseif ($channel->is_begin == 1 && $channel->is_finish == 0) {
                    $val['live_status'] = 3;
                } elseif ($channel->is_begin == 0 && $channel->is_finish == 1) {
                    $val['live_status'] = 2;
                }
            }

            //直播收益
            $val['live_price_sum'] = Order::where([
                'type' => 10,
                'live_id' => $val['id'],
                'status' => 1,
            ])->sum('pay_price');

            $val['live_twitter_price_sum'] = Order::join('nlsg_pay_record_detail as d', 'd.ordernum', '=',
                'nlsg_order.ordernum')
                ->where([
                    'nlsg_order.type' => 10,
                    'nlsg_order.live_id' => $val['id'],
                    'nlsg_order.status' => 1,
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
        if (!$list) {
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
            'live' => $list,
            'subscribe_num' => $subscribeNum > 0 ? float_number($subscribeNum) : 0,
            'watch_num' => $watchNum > 0 ? float_number($watchNum) : 0,
            'unwatch_num' => $unwatchNum > 0 ? float_number($unwatchNum) : 0,
            'order_num' => $orderNum > 0 ? float_number($orderNum) : 0,
            'order_income' => $orderIncome > 0 ? float_number($orderIncome) : 0
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
        $cover = !empty($input['cover']) ? covert_img($input['cover']) : '';
        $title = $input['title'] ?? '';
        $userId = $input['user_id'] ?? 0;
        $begin_at = $input['begin_at'] ?? date('Y-m-d H:i:s', time());
        $end_at = $input['end_at'] ?? date('Y-m-d H:i:s', time());
        $price = $input['price'] ?? 0;
        $twitter = $input['twitter_money'] ?? 0;
        $helper = $input['helper'] ?? '';
        $content = $input['content'] ?? '';
        $now = time();
        $now_date = date('Y-m-s H:i:s');

        if (!$title) {
            return error(1000, '标题不能为空');
        }
        if (!$begin_at) {
            return error(1000, '开始时间不能为空');
        }

        $data = [
            'user_id' => $userId,
            'cover_img' => $cover,
            'title' => $title,
            'begin_at' => $begin_at,
            'end_at' => $end_at,
            'price' => $price,
            'twitter_money' => $twitter,
            'helper' => $helper,
            'content' => $content,
            'is_free' => $price < '0.01' ? 1 : 0,
        ];

        //todo 临时添加
        $lcModel = new LiveConsole();
        $temp_push_end_time = date('Y-m-d H:i:s',
            strtotime($end_at . " +1 days")
        );
        $temp_get_url = $lcModel->getPushUrl(
            rand(100, 999) . $userId . $now, strtotime($temp_push_end_time)
        );

        $live_info_data = [];
        $live_info_data['push_live_url'] = $temp_get_url['push_url'];
        $live_info_data['live_url'] = $temp_get_url['play_url'];
        $live_info_data['live_url_flv'] = $temp_get_url['play_url_flv'];
        $live_info_data['push_end_time'] = $temp_push_end_time;
        $live_info_data['user_id'] = $userId;
        $live_info_data['status'] = 1;
        $live_info_data['length'] = 5;

        if (!empty($input['id'])) {
            Live::where('id', $input['id'])->update($data);
            $live_info_id = LiveInfo::where('live_pid', '=', $input['id'])->value('id');

            $live_info_data['id'] = $input['id'];
            $live_info_data['live_pid'] = $input['id'];

            LiveInfo::where('id', '=', $live_info_id)->update($live_info_data);

        } else {
            $Live_res = Live::create($data);

            $live_info_data['live_pid'] = $Live_res->id;
            $live_info_data['id'] = $Live_res->id;
            DB::table('nlsg_live_info')->insert($live_info_data);
        }
        return success();
    }

    /**
     * @api {post} api/live_v4/live/delete 直播删除
     * @apiVersion 4.0.0
     * @apiName  live/delete
     * @apiGroup 直播后台-直播删除
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/live_v4/live/delete
     * @apiDescription  直播删除
     *
     * @apiParam {number} id 直播id
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
    public function delete(Request $request)
    {
        $liveId = $request->input('id');
        $live = Live::where('id', $liveId)->first();
        if (!$live) {
            return error(1000, '直播不存在');
        }
        $res = Live::where('id', $liveId)->update(['is_del' => 1]);
        if ($res) {
            return success();
        }
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
        $model = new User();
        $res = $model->checkHelper($params);

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


    /**
     * @api {get} api/live_v4/index/statistics_img_data 折线图数据
     * @apiVersion 4.0.0
     * @apiName  index/statistics_img_data
     * @apiGroup 直播后台
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/live_v4/index/statistics_img_data
     * @apiDescription  折线图数据
     *
     * @apiParam {number} str_time 开始时间
     * @apiParam {number} end_time 结束时间
     * @apiParam {int} live_id 直播id
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
    public function statistics_img_data(Request $request)
    {
        $input = $request->all();
        $live_id = $input['live_id'] ?? 0;
        $str_time = $input['str_time'] ?? 0;
        $end_time = $input['end_time'] ?? 0;

        $live_ids = [];
        if($this->user['live_role'] == 21){
            $live_user_id = $this->user['user_id'];
            $live_ids = Live::select("*")->where([
                'user_id' => $live_user_id
            ])->get()->toArray();
            $live_ids = array_column($live_ids,'id');

        }elseif ($this->user['live_role'] == 23) {
            $blrModel = new BackendLiveRole();
            $son_user_id = $blrModel->getDataUserId($this->user['username']);
            $live_ids = Live::select("*")->whereIn([
                'user_id' => $son_user_id
            ])->get()->toArray();
            $live_ids = array_column($live_ids,'id');
        }



        if (empty($live_id)) { // 不指定直播id时  计算时间
            if (empty($str_time) || empty($end_time)) {
                $ageFrom = strtotime("-1 month"); //1月前的时间
                $ageTo = time();
            } else {
                $ageFrom = $str_time;
                $ageTo = $end_time;
            }
        }


        if (!empty($live_id)) {
            //按分钟统计
            $m = 60;
        } else {  //按天统计
            $m = 3600;
        }
        $query = LiveNumber::select('live_id', DB::raw('max(count) as 在线人数,from_unixtime(time - time % ' . $m . ') as 日期'));
        if (!empty($live_id)) {
            $query->where('live_id', $live_id);
        }

        if(!empty($live_ids)){  // 校长和老师  只能看指定的直播数据
            $query->whereIn('live_id', $live_ids);
        }

        if (!empty($ageFrom) && !empty($ageTo)) {
            $query->whereRaw('time BETWEEN ' . $ageFrom . ' AND ' . $ageTo . '');

        }
        $number = $query->groupBy('日期')
            ->orderBy('日期', 'asc')
            ->get()->toArray();


        $res['columns'] = ["日期", "在线人数"];// 前端折线图插件需要的参数
        $res['rows'] = $number;

        return success($res);


    }


    /**
     * @api {get} api/live_v4/live/info 直播详情
     * @apiVersion 4.0.0
     * @apiName  live/info
     * @apiGroup 直播后台 -直播详情
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/live_v4/live/info
     * @apiDescription  直播详情
     *
     * @apiParam {number} id 直播间id
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
    public function info(Request $request)
    {
        $id = $request->get('id');
        $live = Live::select('id', 'title', 'cover_img', 'user_id', 'begin_at', 'end_at', 'price', 'twitter_money', 'helper', 'content')
            ->where('id', $id)->first();
        if (!$live) {
            return error('直播不存在');
        }

        return success($live);
    }
}
