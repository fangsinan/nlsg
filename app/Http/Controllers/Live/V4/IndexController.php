<?php

namespace App\Http\Controllers\Live\V4;

use App\Http\Controllers\ControllerBackend;
use App\Models\BackendLiveDataRole;
use App\Models\BackendLiveRole;
use App\Models\ConfigModel;
use App\Models\Live;
use App\Models\LiveClassify;
use App\Models\LiveConsole;
use App\Models\LiveDeal;
use App\Models\LiveInfo;
use App\Models\LiveLogin;
use App\Models\LiveNumber;
use App\Models\LivePoster;
use App\Models\LiveStatistics;
use App\Models\LiveStreaming;
use App\Models\LiveUserPrivilege;
use App\Models\Order;
use App\Models\Qrcodeimg;
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
                ->where('begin_at', '>=', '2021-05-12 00:00:00')
                ->where('app_project_type','=',APP_PROJECT_TYPE)
                ->get()
                ->toArray();

            $liveIds = array_column($lives, 'id');
            $subscribeNum = 0;
            $watchNum = 0;
            if (!empty($lives)) {
                foreach ($lives as $v) {
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
        } elseif ($this->user['live_role'] == 23) {
            $ctime = strtotime('2021-05-12 00:00:00');
            $blrModel = new BackendLiveRole();
            $son_user_id = $blrModel->getDataUserId($this->user['username']);
            $subscribeNum = Subscribe::where('type', 3)
                ->where('status', 1)
                ->whereIn('user_id', $son_user_id)
                ->where('created_at', '>=', '2021-05-12 00:00:00')
                ->count();
            $watchNum = LiveLogin::whereIn('user_id', $son_user_id)
                ->distinct('user_id')
                ->where('ctime', '>=', $ctime)
                ->count();
            $orderNum = Order::whereIn('type', $type)
                ->where('status', 1)
                ->whereIn('user_id', $son_user_id)
                ->where('created_at', '>=', '2021-05-12 00:00:00')
                ->count();
            $orderIncome = Order::whereIn('type', $type)
                ->where('status', 1)
                ->whereIn('user_id', $son_user_id)
                ->where('created_at', '>=', '2021-05-12 00:00:00')
                ->sum('pay_price');
        } else {
//            $subscribeNum = Subscribe::where('type', 3)
//                ->where('status', 1)
//                ->count();
//            $watchNum = LiveLogin::distinct('user_id')->count();
//            $watchNum = 0;
//            $orderNum = Order::whereIn('type', $type)
//                ->where('status', 1)
//                ->count();
//            $orderIncome = Order::whereIn('type', $type)
//                ->where('status', 1)
//                ->sum('pay_price');

            $subscribeNum = $watchNum = $orderNum = $orderIncome = 0;
        }

        $data = [
            'subscribe_num' => $subscribeNum > 0 ? $subscribeNum : 0,
            'watch_num' => $watchNum > 0 ? $watchNum : 0,
            'order_num' => $orderNum > 0 ? $orderNum : 0,
            'order_income' => $orderIncome > 0 ? round($orderIncome, 2) : 0
        ];
        return success($data);
    }

    public static function getLiveRoleIdList($user)
    {

        if ($user['live_role'] === 21 || $user['live_role'] === 23) {
            $live_id_role = BackendLiveDataRole::query()
                ->where('user_id', '=', $user['user_id'])
                ->select(['live_id'])
                ->groupBy('live_id')
                ->pluck('live_id')
                ->toArray();

//            if (0) {
//                $query = Live::query();
//                if ($user['live_role'] === 21) {
//                    $query->where('user_id', '=', $user['user_id']);
//                } else {
//                    $blrModel = new BackendLiveRole();
//                    $son_user_id = $blrModel->getDataUserId($user['username']);
//                    $query->whereIn('user_id', $son_user_id);
//                }
//                $live_list = $query->pluck('id')->toArray();
//            } else {
                $live_list = [];
//            }

            return array_unique(array_merge($live_id_role, $live_list));
        }

        return null;
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
        $is_zero = $request->get('is_zero',0);
        $app_project_type = $request->get('app_project_type',0);

        $classify = $request->input('classify','');

        $query = Live::with('user:id,nickname')
            ->when($title, function ($query) use ($title) {
                $query->where('title', 'like', '%' . $title . '%');
            })
            ->when(!is_null($status), function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($start && $end, function ($query) use ($start, $end) {
                $query->whereBetween('begin_at', [
                    Carbon::parse($start)->startOfDay()->toDateTimeString(),
                    Carbon::parse($end)->endOfDay()->toDateTimeString(),
                ]);
            });

        if ($is_zero){
            $query->where('is_zero','=',$is_zero);
        }

        if ($app_project_type){
            $query->where('app_project_type','=',$app_project_type);
        }

        if (!empty($classify)){
            $query->where('classify','=',$classify);
        }
//        $query->where('app_project_type','=',APP_PROJECT_TYPE);
        //非超管角色可看live
        $live_id_role = self::getLiveRoleIdList($this->user);
        if ($live_id_role !== null) {
            if ($live_id_role === []) {
                return success([]);
            }
            $query->whereIn('id', $live_id_role);
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

        if ($this->user['live_role'] !== 0) {
            $query->where('id', '>', 51);
        }
        $lists = $query->select('id', 'user_id', 'title', 'price','classify',
                                'details_img','app_project_type','order_num', 'status',
                                'steam_begin_time as begin_at', 'cover_img','is_zero')
            ->where('is_del', 0)
            ->orderBy('sort', 'asc')
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->toArray();


//        $classify_list = [
//            '1' => [
//                'key'   => 1,
//                'value' => '交付课',
//            ],
//            '2' => [
//                'key'   => 2,
//                'value' => '公益课',
//            ],
//            '3' => [
//                'key'   => 3,
//                'value' => '分公司专场',
//            ],
//            '4' => [
//                'key'   => 4,
//                'value' => '电视渠道',
//            ],
//            '5' => [
//                'key'   => 5,
//                'value' => '其他',
//            ],
//        ];

        $classify_list = LiveClassify::query()
            ->where('type','=',1)
            ->select([
                'id as key','name as value'
            ])
            ->get()
            ->toArray();

        //  直播收益   直播推广收益
        foreach ($lists['data'] as &$val) {
//            $val['classify_name'] = $classify_list[$val['classify'] + 1]['value'] ?? '-';
            $val['classify_name'] = '-';
            foreach ($classify_list as $clv){
                if ($clv['key'] == $val['classify']){
                    $val['classify_name'] = $clv['value'];
                }
            }

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
//            $live_price_sum_id[] = $val['id'];
            $val['live_price_sum'] = 0;
            $val['live_twitter_price_sum'] = 0;
            $val['task_id'] = $channel->task_id ?? 0;

            if ($this->user['role_id'] !== 1) {
                $val['order_num'] = LiveStatistics::getCounts($val['id'],1,$this->user['id'],$this->user['username']);
            }
        }
        return success($lists);

        //计算直播收益
//        if (!empty($live_price_sum_id)) {
//            //直播收益
//            $new_live_price_sum = [];
//            $live_price_sum = Order::select('live_id', DB::raw('sum(pay_price) pay_price'))
//                ->whereIn('live_id', $live_price_sum_id)
//                ->where([
//                    'type' => 10,
//                    'status' => 1,
//                ])->groupBy('live_id')->get()->toArray();
//            foreach ($live_price_sum as $k => $v) {
//                $new_live_price_sum[$v['live_id']] = $v['pay_price'];
//            }
//
//            //直播推客收益
//            $new_live_twitter_price_sum = [];
//            $live_twitter_price_sum = Order::select('nlsg_order.live_id', DB::raw('sum(d.price) price'))->join('nlsg_pay_record_detail as d', 'd.ordernum', '=',
//                'nlsg_order.ordernum')
//                ->whereIn('live_id', $live_price_sum_id)
//                ->where([
//                    'nlsg_order.type' => 10,
//                    'nlsg_order.status' => 1,
//                ])->where('nlsg_order.twitter_id', '>', 0)->groupBy('live_id')->get()->toArray();
//            foreach ($live_twitter_price_sum as $k => $v) {
//                $new_live_twitter_price_sum[$v['live_id']] = $v['price'];
//            }
//
//
//            foreach ($lists['data'] as &$data_v) {
//                if (!empty($new_live_price_sum[$data_v['id']])) {
//                    $data_v['live_price_sum'] = $new_live_price_sum[$data_v['id']];
//                }
//                if (!empty($new_live_twitter_price_sum[$data_v['id']])) {
//                    $data_v['live_twitter_price_sum'] = $new_live_twitter_price_sum[$data_v['id']];
//                }
//            }
//        }

//        if (!empty($live_price_sum_id)) {
//            $new_live_price_sum = [];
//            $live_price_sum = Order::select('live_id', DB::raw('sum(pay_price) pay_price'))
//                ->whereIn('live_id', $live_price_sum_id)->where([
//                'type' => 10,
//                'status' => 1,
//            ])->groupBy('live_id')->get('pay_price')->toArray();
//
//            foreach ($live_price_sum as $k => $v) {
//                $new_live_price_sum[$v['live_id']] = $v['pay_price'];
//            }
//            foreach ($lists['data'] as &$data_v) {
//                if (!empty($new_live_price_sum[$data_v['id']])) {
//                    $data_v['live_price_sum'] = $new_live_price_sum[$data_v['id']];
//                }
//            }
//        }


//        return success($lists);
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

        if(0){
            $type = [9, 10, 14, 15, 16];
            $subscribeNum = Subscribe::where('type', 3)
                ->where('relation_id', $liveId)
                ->where('status', 1)
                ->count();
            if (!empty($list->begin_at)) {
//            $watchNum = LiveLogin::where('live_id', $liveId)
//                          ->whereBetween('ctime', [strtotime($list->begin_at), strtotime($list->end_at)])
//                          ->distinct('user_id')
//                          ->count();
                $watchNum = Subscribe::query()
                    ->where('relation_id', '=', $liveId)
                    ->where('type', '=', 3)
                    ->where('live_watched', '=', 1)
                    ->count();

                $unwatchNum = $subscribeNum - $watchNum > 0 ? intval($subscribeNum - $watchNum) : 0;
            } else {
                $watchNum = 0;
                $unwatchNum = 0;
            }
            $popurlarNum = LiveLogin::where('live_id', $liveId)->count();
            $orderNum = Order::whereIn('type', $type)
                ->where('live_id', $liveId)
                ->where('status', 1)
                ->count();

            $orderIncome = Order::whereIn('type', $type)
                ->where('live_id', $liveId)
                ->where('status', 1)
                ->sum('pay_price');
        }


//        $data = [
//            'live' => $list,
//            'subscribe_num' => $subscribeNum > 0 ? $subscribeNum : 0,
//            'watch_num' => $watchNum > 0 ? $watchNum : 0,
//            'unwatch_num' => $unwatchNum > 0 ? $unwatchNum : 0,
//            'popurlar_num' => $popurlarNum > 0 ? $popurlarNum : 0,
//            'order_num' => $orderNum > 0 ? $orderNum : 0,
//            'order_income' => $orderIncome > 0 ? round($orderIncome, 2) : 0
//        ];

        $data = [
            'live' => $list,
            'subscribe_num' => 0,
            'watch_num' => 0,
            'unwatch_num' => 0,
            'popurlar_num' => 0,
            'order_num' => 0,
            'order_income' => 0,
        ];

        //折线图数据


        return success($data);

    }

    public function liveQrImg(){
        $data = ConfigModel::getData(62);
        $data = array_filter(explode(';',$data));
        $res = [
            [
                'key'=>'无',
                'value'=>'',
            ]
        ];

        foreach ($data as $v){
            $v = explode(':',$v);
            array_push($res,[
                'key'=>$v[0],
                'value'=>$v[1]
            ]);
        }

        return success($res);
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
    public function create(Request $request) {
    		$input            = $request->all();
    		$cover            = !empty($input['cover']) ? covert_img($input['cover']) : '';
    		$title            = $input['title'] ?? '';
    		$describe         = $input['describe'] ?? '';
    		$userId           = (int)($input['user_id'] ?? 0);
    		$begin_at         = $input['begin_at'] ?? date('Y-m-d H:i:s');
    		$end_at           = $input['end_at'] ?? date('Y-m-d H:i:s');
    		$price            = $input['price'] ?? 0;
    		$twitter          = $input['twitter_money'] ?? 0;
    		$helper           = $input['helper'] ?? '';
    		$helper           = preg_replace('/[^0-9]/i', ',', $helper);
    		$content          = $input['content'] ?? '';
    		$playback_url     = $input['playback_url'] ?? '';
    		$back_video_url   = $input['back_video_url'] ?? '';
    		$need_virtual     = $input['need_virtual'] ?? 0;
    		$need_virtual_num = $input['need_virtual_num'] ?? 0;
    		$steam_end_time   = $input['steam_end_time'] ?? '';
    		$steam_begin_time = $input['steam_begin_time'] ?? '';
    		$pre_push_time    = $input['pre_push_time'] ?? 60;
    		$classify         = $input['classify'] ?? 0;
    		$valid_time_range = $input['valid_time_range'] ?? 0;
    		$bgp_id           = $input['bgp_id'] ?? 0;
            $service_type       = $input['service_type']??0;
            $hide_sub_count  = $input['hide_sub_count'] ?? 0;
            $is_zero = $input['is_zero'] ?? 1;
            $zero_poster_show = (int)($input['zero_poster_show'] ?? 0);
            $details_img = $input['details_img'] ?? '';
            $app_project_type = $input['app_project_type'] ?? 1;
            $qr_type = $input['qr_type'] ?? 0;
            $qr_code      = $input['qr_code'] ?? '';

    		$cover_vertical_img = !empty($input['cover_vertical_img']) ? covert_img($input['cover_vertical_img']) : '';

    		$poster_list = $input['poster'] ?? [];
    		if (is_string($poster_list)) {
    			$poster_list = explode(',', $poster_list);
    		}
    		if (empty($bgp_id)){
    			return error(1000, '底图方案不能为空');
    		}
    		if (!$title) {
    			return error(1000, '标题不能为空');
    		}
    		if (!$begin_at) {
    			return error(1000, '开始时间不能为空');
    		}

    		if (empty($classify)) {
    			return error(1000, '请选择类型');
    		}

            if (!empty($qr_code) && empty($qr_type)){
                return $this->error(1000,'选择二维码必须选择对应的类型');
            }

    		if (!empty($steam_begin_time)) {
    			$Y = substr($steam_begin_time, 0, 1);
    			if ($Y != 2) {
    				return error(1000, '拉流开始时间不能为空');
    			}
    		}

    		if (!empty($steam_end_time)) {
    			$Y = substr($steam_end_time, 0, 1);
    			if ($Y != 2) {
    				return error(1000, '拉流结束时间不能为空');
    			}
    		}

    		if (empty($steam_end_time) || empty($steam_begin_time)) {
    			return error(1000, '拉流时间范围不能为空');
    		}

    		if (empty($valid_time_range)) {
    			return error(1000, '有效统计时间范围不能为空');
    		}

    		$is_test      = (int)($input['is_test'] ?? 0);

    		$channel_show = $input['channel_show'] ?? [];

    		$data = [
    			'user_id'          => $userId,
    			'cover_img'        => $cover,
    			'title'            => $title,
    			'describe'         => $describe,
    			'begin_at'         => $begin_at,
    			'end_at'           => $end_at,
    			'price'            => $price,
    			'twitter_money'    => $twitter,
    			'helper'           => $helper,
    			'content'          => $content,
    			'need_virtual'     => $need_virtual,
    			'need_virtual_num' => $need_virtual_num,
    			'is_free'          => $price < '0.01' ? 1 : 0,
    			'is_test'          => $is_test,
    			'steam_end_time'   => $steam_end_time,
    			'steam_begin_time' => $steam_begin_time,
    			'pre_push_time'    => $pre_push_time,
    			'classify'         => $classify,
    			'valid_time_range' => $valid_time_range,

    			'cover_vertical_img' => $cover_vertical_img,
    			'bgp_id'             => $bgp_id,
                'service_type'=>$service_type,
                'hide_sub_count'=>$hide_sub_count,
                'is_zero'=>$is_zero,
                'zero_poster_show'=>$zero_poster_show,
                'details_img'=>$details_img,
                'app_project_type'=>$app_project_type,
                'qr_type'=>$qr_type,
    		];

    		$lcModel            = new LiveConsole();
    		$temp_push_end_time = date('Y-m-d 23:59:59',
    			strtotime($end_at . " +1 days")
    		);

    		$live_info_data = [];
    		$live_info_data['push_end_time']  = $temp_push_end_time;
    		$live_info_data['user_id']        = $userId;
    		$live_info_data['status']         = 1;
    		$live_info_data['length']         = 5;
    		$live_info_data['begin_at']       = $begin_at;
    		$live_info_data['end_at']         = $end_at;
    		$live_info_data['playback_url']   = $playback_url;
    		$live_info_data['back_video_url'] = $back_video_url;
            $live_info_data['app_project_type'] = $app_project_type;

        if (!empty($input['id'])) {
    		    if ($zero_poster_show == 1){
    		        Live::delOldZeroPosterShow($input['id']);
                }
    			Live::query()->where('id', $input['id'])->update($data);
    			$info_first   = LiveInfo::query()
    				->select('id', 'task_id', 'begin_at', 'end_at', 'push_end_time')
    				->where('live_pid', '=', $input['id'])
    				->first();
    			$live_info_id = $info_first['id'];
    			// 删除拉流任务
    			if ($info_first['task_id']) {
    				//当开始时间、结束时间、推流结束时间 变了修改     否则  不变
    				if (($info_first['begin_at'] !== $begin_at) || ($info_first['end_at'] !== $end_at) || ($info_first['push_end_time'] !== $temp_push_end_time)) {
    					//校验 推拉流
    					LiveInfo::liveUrlEdit('del', $live_info_id);
    				}
    			}

    			$live_info_data['id']       = $input['id'];
    			$live_info_data['live_pid'] = $input['id'];
    			LiveInfo::where('id', '=', $live_info_id)->update($live_info_data);
    		} else {
                if ($zero_poster_show == 1){
                    Live::delOldZeroPosterShow();
                }
    			$Live_res = Live::create($data);

    			$live_info_data['live_pid'] = $Live_res->id;
    			$live_info_data['id']       = $Live_res->id;
    			DB::table('nlsg_live_info')->insert($live_info_data);

    			//添加,创建对应数据库
    //            $login_table_name = 'nlsg_live_online_user_'.$live_info_data['live_pid'];
    //            $create_table_sql = "
    //CREATE TABLE $login_table_name  (
    //  `id` int(11) NOT NULL AUTO_INCREMENT,
    //  `live_id` int(11) NULL DEFAULT 0 COMMENT '直播间id',
    //  `user_id` int(11) NULL DEFAULT 0 COMMENT '用户id',
    //  `live_son_flag` int(10) NULL DEFAULT 0 COMMENT '渠道标记',
    //  `online_time` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0) COMMENT '在线时间',
    //  `online_time_str` char(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '时间',
    //  PRIMARY KEY (`id`) USING BTREE,
    //  INDEX `live_id`(`online_time_str`, `user_id`, `live_son_flag`) USING BTREE
    //) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '实时在线用户明细' ROW_FORMAT = Dynamic;
    //        ";
    //            DB::select($create_table_sql);
    		}


    		$temp_get_url                           = $lcModel->getPushUrl(
    			md5($userId . $temp_push_end_time . $live_info_data['live_pid']), strtotime($temp_push_end_time)
    		);
    		$update_live_info_data['push_live_url'] = $temp_get_url['push_url'];
    		$update_live_info_data['live_url']      = $temp_get_url['play_url'];
    		$update_live_info_data['live_url_flv']  = $temp_get_url['play_url_flv'];
    		$update_live_info_data['webrtc_url']  = $temp_get_url['webrtc_url'];
    		LiveInfo::query()->where('live_pid', '=', $live_info_data['live_pid'])
    			->update($update_live_info_data);

    		//是否弹出二维码
    		Qrcodeimg::query()
    			->where('relation_type', '=', 3)
    			->where('relation_id', '=', $live_info_data['live_pid'])
    			->update(['status' => 0]);
    		if (!empty($qr_code)) {
    			Qrcodeimg::query()->updateOrCreate(
    				[
    					'relation_type' => 3,
    					'relation_id'   => $live_info_data['live_pid'],
    					'qr_url'        => $qr_code,
                        'qr_type'=>$qr_type,
    				],
    				[
    					'status' => 1,
    				]
    			);
    		}

    		if (!is_array($channel_show)) {
    			$channel_user_id  = explode(',', $channel_show);
    		}else{
    			$channel_user_id = $channel_show;
    		}
    //        $channel_user_id = [];
    //        foreach ($channel_show as $cs_v) {
    //            $channel_user_id[] = $this->channelUserData($cs_v);
    //        }
    		$channel_user_id = array_filter($channel_user_id);

    		BackendLiveDataRole::query()
    			->where('live_id','=',$live_info_data['live_pid'])
    			->delete();

    		if (!empty($channel_user_id)) {
    			foreach ($channel_user_id as $cui) {
    				BackendLiveDataRole::query()
    					->firstOrCreate([
    						'user_id' => $cui,
    						'live_id' => $live_info_data['live_pid'],
    					]);
    			}
    		}

    		if (!empty($poster_list)){
    			LivePoster::query()
    				->where('live_id','=',$live_info_data['live_pid'])
    				->whereNotIn('image',$poster_list)
    				->update([
    					'status'=>3
    				]);

    			foreach ($poster_list as $pl_v){
    				LivePoster::query()->firstOrCreate(
    					[
    						'live_id'=>$live_info_data['live_pid'],
    						'image'=>$pl_v,
    						'status'=>1
    					]
    				);
    			}
    		}else{
    			LivePoster::query()
    				->where('live_id','=',$live_info_data['live_pid'])
    				->update([
    					'status'=>3
    				]);
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
        } elseif ($this->user['live_role'] == 23) {
            $blrModel = new BackendLiveRole();
            $son_user_id = $blrModel->getDataUserId($this->user['username']);
            $query->whereIn('user_id', $son_user_id);
        }

        $users = $query
            ->where('pri_level', 1)
            ->where('privilege', 2)
            ->where('is_del', 0)
            ->where('app_project_type','=',APP_PROJECT_TYPE)
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
//        if($this->user['live_role'] == 21){
//            $live_user_id = $this->user['user_id'];
//            $live_ids = Live::select("*")->where([
//                'user_id' => $live_user_id
//            ])->where('id','>',52)->get()->toArray();
//            $live_ids = array_column($live_ids,'id');
//
//        }elseif ($this->user['live_role'] == 23) {
//            $blrModel = new BackendLiveRole();
//            $son_user_id = $blrModel->getDataUserId($this->user['username']);
//            $live_ids = Live::select("*")->whereIn('user_id', $son_user_id)->where('id','>',52)->get()->toArray();
//            $live_ids = array_column($live_ids,'id');
//        }

        //非超管角色可看live
        if ($this->user['live_role'] === 21 || $this->user['live_role'] === 23) {
            $live_ids = BackendLiveDataRole::query()
                ->where('user_id', '=', $this->user['user_id'])
                ->pluck('live_id')
                ->toArray();
            if (empty($live_ids)) {
                return success([]);
            }
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

        if (!empty($live_ids)) {  // 校长和老师  只能看指定的直播数据
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

    public function channelSelect(Request $request,$return_type = 1){
        $role_id = $this->user['role_id'] ?? 0;
        if ($role_id !== 1){
            return [];
        }

        $res = DB::table('nlsg_backend_user as bu')
            ->join('nlsg_user as u','bu.username','=','u.phone')
            ->where('bu.channel_select_show','=',2)
            ->select(['u.id','bu.channel_select_title'])
            ->get();
        if ($res->isEmpty()){
            $res = [];
        }else{
            $res = $res->toArray();
        }

        if ($return_type === 1){
            return $this->success($res);
        }

        return $res;
    }

    public function channelUserData($key, $flag = 1)
    {
        $channel = [
            'liting' => 169209,
        ];

        if ($flag === 1) {
            return $channel[$key] ?? 0;
        }
        return array_search($key, $channel, true);
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
    public function info(Request $request) {
    		$id   = $request->get('id');
    		$live = Live::query()
    			->select('id', 'title', 'describe', 'cover_img', 'user_id', 'begin_at', 'end_at','service_type',
    				'price', 'twitter_money', 'helper', 'content', 'need_virtual', 'need_virtual_num', 'is_test','bgp_id',
    				'steam_end_time', 'steam_begin_time','pre_push_time','classify','valid_time_range','cover_vertical_img',
                    'hide_sub_count','is_zero','zero_poster_show','app_project_type','details_img','qr_type'
    			)
    //            ->with(['livePoster'])
    			->where('id', $id)->first();
    		if (!$live) {
    			return error('直播不存在');
    		}
    		//$live->playback_url = LiveInfo::where('live_pid', $id)->value('playback_url');
    		$liveInfo             = LiveInfo::query()->select('playback_url', 'back_video_url')->where('live_pid', $id)->first();
    		if (!$liveInfo){
    			return error('直播不存在');
    		}
    		$live->playback_url   = $liveInfo['playback_url'];
    		$live->back_video_url = $liveInfo['back_video_url'];
    		$live->qr_code        = Qrcodeimg::query()
    			->where('relation_type', '=', 3)
    			->where('relation_id', '=', $id)
    			->where('status', '=', 1)
    			->value('qr_url');

    		$channel_user_id = BackendLiveDataRole::query()
    			->where('live_id', '=', $id)
    			->pluck('user_id')
    			->toArray();

    //        $channel_show = [];
    //        if (!empty($channel_user_id)) {
    //            foreach ($channel_user_id as $cui) {
    //                $temp_cud = $this->channelUserData($cui, 2);
    //                if ($temp_cud) {
    //                    $channel_show[] = $temp_cud;
    //                }
    //            }
    //        }
    		$live->channel_show = $channel_user_id;

    		$live->poster_list = LivePoster::query()
    			->where('live_id','=',$id)
    			->where('status','=',1)
    			->pluck('image')
    			->toArray();

    		return success($live);
    	}



    public function getBackVideos(Request $request)
    {

        $res=LiveStreaming::query()->select(['id','title','video_url'])->where('status','=',1)->orderBy('id','desc')->get()->toArray();
        return success($res);
        //3701925925345062697 王琨老师19.9第一天新版 http://1253639599.vod2.myqcloud.com/e6c8f55bvodtransgzp1253639599/2f548dea3701925925345062697/v.f100020.mp4    674M
        //3701925925345105941 王琨老师19.9第二天改版 http://1253639599.vod2.myqcloud.com/e6c8f55bvodtransgzp1253639599/313ed4c03701925925345105941/v.f100020.mp4    838M

        //3701925925153722341 李婷老师第一天19.9   http://1253639599.vod2.myqcloud.com/e6c8f55bvodtransgzp1253639599/a8b4b43c3701925925153722341/v.f100020.mp4      437M
        //3701925925112044615 王琨老师第一天19.9改版540 http://1253639599.vod2.myqcloud.com/e6c8f55bvodtransgzp1253639599/9a845db83701925925112044615/v.f100020.mp4  669M
        //3701925919134293895 王琨老师直播第一天720  http://1253639599.vod2.myqcloud.com/787e2d3evodtranscq1253639599/9cce592f3701925919134293895/v.f100030.mp4       947M
        //3701925924026354682 王琨老师直播第一天540  http://1253639599.vod2.myqcloud.com/e6c8f55bvodtransgzp1253639599/4939b6c33701925924026354682/v.f100020.mp4      614M
        //3701925921189014962 王琨老师直播第二天720  http://1253639599.vod2.myqcloud.com/e6c8f55bvodtransgzp1253639599/1ac858843701925921189014962/v.f100030.mp4      1.33GB
        //3701925924022337528 王琨老师直播第二天540  http://1253639599.vod2.myqcloud.com/e6c8f55bvodtransgzp1253639599/152e32df3701925924022337528/v.f100020.mp4      809M
        //3701925920528809905 李婷老师直播新版720    http://1253639599.vod2.myqcloud.com/787e2d3evodtranscq1253639599/24c43b093701925920528809905/v.f100030.mp4       717.83M
        /*$res = [
//            ['title' => "王琨老师第一天直播", 'video_url' => 'http://1253639599.vod2.myqcloud.com/787e2d3evodtranscq1253639599/9cce592f3701925919134293895/v.f100030.mp4',],
            ['title' => "王琨老师第一天直播19.9", 'video_url' => 'http://1253639599.vod2.myqcloud.com/e6c8f55bvodtransgzp1253639599/2f548dea3701925925345062697/v.f100020.mp4',],
//            ['title' => "王琨老师第一天直播", 'video_url' => 'http://1253639599.vod2.myqcloud.com/e6c8f55bvodtransgzp1253639599/4939b6c33701925924026354682/v.f100020.mp4',],
//            ['title' => "王琨老师第二天直播", 'video_url' => 'http://1253639599.vod2.myqcloud.com/e6c8f55bvodtransgzp1253639599/1ac858843701925921189014962/v.f100030.mp4',],
            ['title' => "王琨老师第二天直播19.9", 'video_url' => 'http://1253639599.vod2.myqcloud.com/e6c8f55bvodtransgzp1253639599/313ed4c03701925925345105941/v.f100020.mp4',],
//            ['title' => "李婷老师直播",      'video_url' => 'http://1253639599.vod2.myqcloud.com/787e2d3evodtranscq1253639599/24c43b093701925920528809905/v.f100030.mp4',],
            ['title' => "李婷老师直播19.9", 'video_url' => 'http://1253639599.vod2.myqcloud.com/e6c8f55bvodtransgzp1253639599/a8b4b43c3701925925153722341/v.f100020.mp4',],
            ['title' => "王琨老师公益课", 'video_url' => 'http://1253639599.vod2.myqcloud.com/e6c8f55bvodtransgzp1253639599/f55a5eb1387702291257482009/v.f100020.mp4',],
            ['title' => "十商交付第一天", 'video_url' => 'http://1253639599.vod2.myqcloud.com/e6c8f55bvodtransgzp1253639599/698cac358602268011401148246/v.f100020.mp4',],
            ['title' => "十商交付第二天", 'video_url' => 'http://1253639599.vod2.myqcloud.com/e6c8f55bvodtransgzp1253639599/2bd483f98602268011431020789/v.f100020.mp4',],
            ['title' => "汤蓓老师第一天", 'video_url' => 'http://1253639599.vod2.myqcloud.com/e6c8f55bvodtransgzp1253639599/8d4e71b7387702291622984606/v.f100030.mp4',],
            ['title' => "汤蓓老师第二天", 'video_url' => 'http://1253639599.vod2.myqcloud.com/e6c8f55bvodtransgzp1253639599/a5e382be387702291629895164/v.f100030.mp4',],
        ];*/
        //十商 http://1308168117.vod2.myqcloud.com/c520858evodtranscq1308168117/d20eb67f387702291813747849/v.f100020.mp4
        $res = [
            ['title' => "王琨老师第一天直播19.9", 'video_url' => 'http://1308168117.vod2.myqcloud.com/c520858evodtranscq1308168117/cb0b514e387702291813440392/v.f100020.mp4',],
            ['title' => "王琨老师第二天直播19.9", 'video_url' => 'http://1308168117.vod2.myqcloud.com/c520858evodtranscq1308168117/42785d86387702291818025002/v.f100020.mp4',],
            ['title' => "李婷老师直播19.9", 'video_url' => 'http://1308168117.vod2.myqcloud.com/c520858evodtranscq1308168117/42798414387702291818027383/v.f100020.mp4',],
            ['title' => "王琨老师公益课", 'video_url' => 'http://1308168117.vod2.myqcloud.com/c520858evodtranscq1308168117/cd2ad1f6387702291813511887/v.f100020.mp4',],
            ['title' => "王琨老师公益课改版", 'video_url' => 'http://1308168117.vod2.myqcloud.com/c520858evodtranscq1308168117/759d778d387702296506162026/v.f100020.mp4',],
            ['title' => "王琨老师公益课0316", 'video_url' => 'http://1308168117.vod2.myqcloud.com/c520858evodtranscq1308168117/61c8e25e387702297598142528/v.f100020.mp4',],
            ['title' => "王琨老师公益课0413", 'video_url' => 'http://1308168117.vod2.myqcloud.com/c520858evodtranscq1308168117/6f393eb5387702299112478604/v.f100020.mp4',],
            ['title' => "王琨老师公益课0419新版", 'video_url' => 'http://1308168117.vod2.myqcloud.com/c520858evodtranscq1308168117/0d30d2f4387702299566202515/v.f100020.mp4',],
            ['title' => "王琨老师公益课-哈弗凌晨4点半", 'video_url' => 'http://1308168117.vod2.myqcloud.com/c520858evodtranscq1308168117/a19c3d3b387702296741996015/v.f100020.mp4',],
            ['title' => "十商交付第一天", 'video_url' => 'http://1308168117.vod2.myqcloud.com/c520858evodtranscq1308168117/cd3e6ccd387702291813524028/v.f100020.mp4',],
            ['title' => "十商交付第二天", 'video_url' => 'http://1308168117.vod2.myqcloud.com/c520858evodtranscq1308168117/3bb9b74a387702292234507161/v.f100020.mp4',],
            ['title' => "2022年十商第一天", 'video_url' => 'http://1308168117.vod2.myqcloud.com/c520858evodtranscq1308168117/17031e14387702294366503473/v.f100020.mp4',],
            ['title' => "2022年十商第一天新版", 'video_url' => 'http://1308168117.vod2.myqcloud.com/c520858evodtranscq1308168117/9a8b03a5387702294592959843/v.f100020.mp4',],
            ['title' => "2022年十商第二天", 'video_url' => 'http://1308168117.vod2.myqcloud.com/c520858evodtranscq1308168117/4671b130387702294353646033/v.f100020.mp4',],
            ['title' => "汤蓓老师第一天", 'video_url' => 'http://1308168117.vod2.myqcloud.com/c520858evodtranscq1308168117/d1fd0c94387702291813738145/v.f100030.mp4',],
            ['title' => "汤蓓老师第二天", 'video_url' => 'http://1308168117.vod2.myqcloud.com/c520858evodtranscq1308168117/d21cdad1387702291813750166/v.f100030.mp4',],
            ['title' => "汤蓓老师第一天新版", 'video_url' => 'http://1308168117.vod2.myqcloud.com/c520858evodtranscq1308168117/09b9e7ee387702296264213191/v.f100030.mp4',],
            ['title' => "汤蓓老师第二天新版", 'video_url' => 'http://1308168117.vod2.myqcloud.com/c520858evodtranscq1308168117/0bfb6b6c387702296264300714/v.f100030.mp4',],
            ['title' => "孟祥玲12.3", 'video_url' => 'http://1308168117.vod2.myqcloud.com/c520858evodtranscq1308168117/09098962387702292515310042/v.f100020.mp4',],
            ['title' => "六大能力交付第一天", 'video_url' => 'http://1308168117.vod2.myqcloud.com/c520858evodtranscq1308168117/3e5b1780387702296648557660/v.f100020.mp4',],
            ['title' => "六大能力交付第二天", 'video_url' => 'http://1308168117.vod2.myqcloud.com/c520858evodtranscq1308168117/39f66604387702296648392559/v.f100020.mp4',],
            ['title' => "六大能力交付第二天0312", 'video_url' => 'http://1308168117.vod2.myqcloud.com/c520858evodtranscq1308168117/75a8b365387702297469168737/v.f100020.mp4',],
            ['title' => "电视频道同步转播", 'video_url' => 'http://1308168117.vod2.myqcloud.com/c520858evodtranscq1308168117/d8ce9bf1387702298372242370/v.f100030.mp4',],
            ['title' => "电视频道同步转播重播版", 'video_url' => 'http://1308168117.vod2.myqcloud.com/c520858evodtranscq1308168117/577c2779387702298740716652/v.f100030.mp4',],
            ['title' => "电视频道同步3连播", 'video_url' => 'http://1308168117.vod2.myqcloud.com/c520858evodtranscq1308168117/c2e984a0387702299470727539/v.f100020.mp4',],
            ['title' => "晓东老师20220426", 'video_url' => 'http://1308168117.vod2.myqcloud.com/c520858evodtranscq1308168117/762547ac387702299859971656/v.f100020.mp4',],
            ['title' => "晓东老师20220426晚上", 'video_url' => 'http://1308168117.vod2.myqcloud.com/c520858evodtranscq1308168117/36e043bb387702299862220397/v.f100030.mp4',],
            ['title' => "晓东老师20220427下午", 'video_url' => 'http://1308168117.vod2.myqcloud.com/c520858evodtranscq1308168117/d0de2ea3387702299898572044/v.f100020.mp4',],
        ];
        return success($res);
    }

    public function orderChannelShow(Request $request) {
        $live_id = $request->input('live_id', 0);
        $flag    = $request->input('flag', '');

        if (empty($live_id) || empty($flag)) {
            return $this->getRes(['code' => false, 'msg' => '参数错误']);
        }

        if (!in_array($flag, ['show', 'hide'])) {
            return $this->getRes(['code' => false, 'msg' => '参数错误']);
        }

        $check_id = Live::query()->where('id', '=', $live_id)->first();
        if (empty($check_id) || $check_id->status <> 4) {
            return $this->getRes(['code' => false, 'msg' => '直播不存在']);
        }

        if ($check_id->is_finish <> 1) {
            return $this->getRes(['code' => false, 'msg' => '直播未结束']);
        }

        LiveDeal::query()
            ->where('live_id', '=', $live_id)
            ->update([
                'channel_show' => $flag === 'show' ? 1 : 0,
            ]);

        Order::query()
            ->where('type','=',14)
            ->where('relation_id', '<>', 8)
            ->where('status', '=', 1)
            ->where('live_id', '<>', 0)
            ->where('is_shill', '=', 0)
            ->where('pay_price', '>', 0.01)
            ->update([
                'channel_show' => $flag === 'show' ? 1 : 0,
            ]);

        return $this->getRes([
            'code' => true,
            'msg'  => '操作成功'
        ]);

    }

}
