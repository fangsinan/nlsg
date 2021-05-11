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
        //5打赏 9精品课  10直播  14 线下产品(门票类)   15讲座  16新vip
        $type = [9, 10, 14, 15, 16];
        if ($this->user['live_role'] == 21) {
            $lives = Live::where('user_id', $this->user['user_id'])
                        ->where('status', 4)
                        ->get()
                        ->toArray();

            $liveIds = array_column($lives, 'id');
            $subscribeNum =0;
            $watchNum =0;
            if (!empty($lives)){
                foreach ($lives as $v){
                    $num = Subscribe::where('type', 3)
                        ->where('status', 1)
                        ->where('relation_id', $v['id'])
                        ->count();

                    $wnum = LiveLogin::where('live_id', $v['id'])
                                        ->whereBetween('ctime', [strtotime($v['begin_at']), strtotime($v['end_at'])])
                                        ->distinct('user_id')
                                        ->count();

                    $subscribeNum += $num;
                    $watchNum += $wnum;

                }
            }

            $orderNum = Order::whereIn('type', $type)
                        ->where('user_id', $this->user['user_id'])
                        ->whereIn('live_id', $liveIds)
                        ->where('status', 1)
                        ->count();
            $orderIncome = Order::whereIn('type', $type)
                                ->where('status', 1)
                                ->where('user_id', $this->user['user_id'])
                                ->whereIn('live_id', $liveIds)
                                ->sum('pay_price');
        }elseif ($this->user['live_role'] == 23) {
            $blrModel = new BackendLiveRole();
            $son_user_id = $blrModel->getDataUserId($this->user['username']);
            $subscribeNum = Subscribe::where('type', 3)
                            ->where('status', 1)
                            ->whereIn('user_id', $son_user_id)
                            ->count();
            $watchNum = LiveLogin::whereIn('user_id', $son_user_id)
                        ->distinct('user_id')
                        ->count();
            $orderNum = Order::whereIn('type', $type)
                        ->where('status', 1)
                        ->whereIn('user_id', $son_user_id)
                        ->count();
            $orderIncome = Order::whereIn('type', $type)
                                ->where('status', 1)
                                ->whereIn('user_id', $son_user_id)
                                ->sum('pay_price');
        } else {
            $subscribeNum = Subscribe::where('type', 3)
                           ->where('status', 1)
                           ->count();
            $watchNum = LiveLogin::distinct('user_id')->count();
            $orderNum = Order::whereIn('type', $type)
                       ->where('status', 1)
                       ->count();
            $orderIncome = Order::whereIn('type', $type)
                               ->where('status', 1)
                               ->sum('pay_price');
        }

        $data = [
            'subscribe_num' => $subscribeNum > 0 ? $subscribeNum : 0,
            'watch_num'     => $watchNum > 0 ? $watchNum : 0,
            'order_num'     => $orderNum > 0 ? $orderNum : 0,
            'order_income'  => $orderIncome > 0 ? round($orderIncome, 2) : 0
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
        $live_status = $request->get('live_status');
        $status = $request->get('status');
        $start = $request->get('start');
        $end = $request->get('end');
        $query = Live::with('user:id,nickname')
            ->when($title, function ($query) use ($title) {
                $query->where('title', 'like', '%' . $title . '%');
            })
            ->when(!is_null($status), function ($query) use ($status) {
                $query->where('status', $status);
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

        if (!empty($live_status)) {
            if ($live_status == 1) {
                $query->whereHas('liveInfo', function ($q) use ($status) {
                    $q->where('is_begin', 0)->where('is_finish', 0);
                });
            } elseif ($live_status == 2) {
                $query->whereHas('liveInfo', function ($q) use ($status) {
                    $q->where('is_begin', 0)->where('is_finish', 1);
                });
            } elseif ($live_status == 3) {
                $query->whereHas('liveInfo', function ($q) use ($status) {
                    $q->where('is_begin', 1)->where('is_finish', 0);
                });
            }
        }

        if ($this->user['live_role'] != 22) {
            $query->where('begin_at', '>=','2021-05-12 00:00:00');
        }
        $lists = $query->select('id', 'user_id', 'title', 'price',
            'order_num', 'status', 'begin_at', 'cover_img')
            ->where('is_del', 0)
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->toArray();

        //  直播收益   直播推广收益
        foreach ($lists['data'] as &$val) {

            $val['live_status'] = 1;  //默认值
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

            $live_price_sum_id[] = $val['id'];
            $val['live_price_sum'] = 0;
            $val['live_twitter_price_sum'] = 0;
//            //直播收益
//            $val['live_price_sum'] = Order::where([
//                'live_id' => $val['id'],
//                'type' => 10,
//                'status' => 1,
//            ])->sum('pay_price');

//            $val['live_twitter_price_sum'] = Order::join('nlsg_pay_record_detail as d', 'd.ordernum', '=',
//                'nlsg_order.ordernum')
//                ->where([
//                    'nlsg_order.live_id' => $val['id'],
//                    'nlsg_order.type' => 10,
//                    'nlsg_order.status' => 1,
//                ])->where('nlsg_order.twitter_id', '>', 0)->sum('d.price');
        }
        //计算直播收益
        if( !empty($live_price_sum_id) ) {
            //直播收益
            $new_live_price_sum = [];
            $live_price_sum = Order::select('live_id',DB::raw('sum(pay_price) pay_price'))
                ->whereIn('live_id',$live_price_sum_id)
                ->where([
                    'type' => 10,
                    'status' => 1,
                ])->groupBy('live_id')->get()->toArray();
            foreach ( $live_price_sum as $k=>$v ) {
                $new_live_price_sum[$v['live_id']] = $v['pay_price'];
            }

            //直播推客收益
            $new_live_twitter_price_sum = [];
            $live_twitter_price_sum = Order::select('nlsg_order.live_id',DB::raw('sum(d.price) price'))->join('nlsg_pay_record_detail as d', 'd.ordernum', '=',
                'nlsg_order.ordernum')
                ->whereIn('live_id',$live_price_sum_id)
                ->where([
                    'nlsg_order.type' => 10,
                    'nlsg_order.status' => 1,
                ])->where('nlsg_order.twitter_id', '>', 0)->groupBy('live_id')->get()->toArray();
            foreach ( $live_twitter_price_sum as $k=>$v ) {
                $new_live_twitter_price_sum[$v['live_id']] = $v['price'];
            }


            foreach ($lists['data'] as &$data_v) {
                if( !empty($new_live_price_sum[$data_v['id']] )){
                    $data_v['live_price_sum'] = $new_live_price_sum[$data_v['id']];
                }
                if( !empty($new_live_twitter_price_sum[$data_v['id']] )){
                    $data_v['live_twitter_price_sum'] = $new_live_twitter_price_sum[$data_v['id']];
                }
            }
        }

        if( !empty($live_price_sum_id) ) {
            $new_live_price_sum = [];
            $live_price_sum = Order::select('live_id',DB::raw('sum(pay_price) pay_price'))->whereIn('live_id',$live_price_sum_id)->where([
                'type' => 10,
                'status' => 1,
            ])->groupBy('live_id')->get('pay_price')->toArray();

            foreach ( $live_price_sum as $k=>$v ) {
                $new_live_price_sum[$v['live_id']] = $v['pay_price'];
            }
            foreach ($lists['data'] as &$data_v) {
                if( !empty($new_live_price_sum[$data_v['id']] )){
                    $data_v['live_price_sum'] = $new_live_price_sum[$data_v['id']];
                }
            }
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
        $list = Live::select('id', 'title', 'begin_at', 'end_at')->where('id', $liveId)->first();
        if (!$list) {
            return error(1000, '直播不存在');
        }

        $type = [9, 10, 14, 15, 16];
        $subscribeNum = Subscribe::where('type', 3)
            ->where('relation_id', $liveId)
            ->where('status', 1)
            ->count();
        if (!empty($list->begin_at)){
            $watchNum = LiveLogin::where('live_id', $liveId)
                          ->whereBetween('ctime', [strtotime($list->begin_at), strtotime($list->end_at)])
                          ->distinct('user_id')
                          ->count();

             $unwatchNum = $subscribeNum - $watchNum > 0 ? intval($subscribeNum - $watchNum) : 0;
        } else {
            $watchNum   = 0;
            $unwatchNum = 0;
        }
        $popurlarNum =  LiveLogin::where('live_id', $liveId)->count();
        $orderNum = Order::whereIn('type', $type)
            ->where('live_id', $liveId)
            ->where('status', 1)
            ->count();

        $orderIncome = Order::whereIn('type', $type)
            ->where('live_id', $liveId)
            ->where('status', 1)
            ->sum('pay_price');

        $data = [
            'live' => $list,
            'subscribe_num' => $subscribeNum > 0 ? $subscribeNum : 0,
            'watch_num' => $watchNum > 0 ? $watchNum : 0,
            'unwatch_num' => $unwatchNum > 0 ? $unwatchNum : 0,
            'popurlar_num' => $popurlarNum > 0 ? $popurlarNum : 0,
            'order_num' => $orderNum > 0 ? $orderNum : 0,
            'order_income' => $orderIncome > 0 ? round($orderIncome,2) : 0
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
        $describe = $input['describe'] ?? '';
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
            'describe' => $describe,
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
        $temp_push_end_time = date('Y-m-d 23:59:59',
            strtotime($end_at . " +1 days")
        );
        $temp_get_url = $lcModel->getPushUrl(
            md5($userId . $temp_push_end_time), strtotime($temp_push_end_time)
        );

        $live_info_data = [];
        $live_info_data['push_live_url'] = $temp_get_url['push_url'];
        $live_info_data['live_url'] = $temp_get_url['play_url'];
        $live_info_data['live_url_flv'] = $temp_get_url['play_url_flv'];
        $live_info_data['push_end_time'] = $temp_push_end_time;
        $live_info_data['user_id'] = $userId;
        $live_info_data['status'] = 1;
        $live_info_data['length'] = 5;
        $live_info_data['begin_at'] = $begin_at;
        $live_info_data['end_at']   = $end_at;

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
        $query = LiveUserPrivilege::with('user:id,nickname')
            ->select('id', 'user_id');

        if ($this->user['live_role'] == 21) {
            $son_user_id = $this->user['user_id'];
            $query->where('user_id', '=', $son_user_id);
        }elseif ($this->user['live_role'] == 23) {
            $blrModel = new BackendLiveRole();
            $son_user_id = $blrModel->getDataUserId($this->user['username']);
            $query->whereIn('user_id', $son_user_id);
        }

        $users = $query
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
            $live_ids = Live::select("*")->whereIn('user_id', $son_user_id)->get()->toArray();
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
        $live = Live::select('id', 'title', 'describe', 'cover_img', 'user_id', 'begin_at', 'end_at', 'price', 'twitter_money', 'helper', 'content')
            ->with(['livePoster'])
            ->where('id', $id)->first();
        if (!$live) {
            return error('直播不存在');
        }

        return success($live);
    }
}
