<?php

namespace App\Http\Controllers\Admin\V4;

use App\Http\Controllers\ControllerBackend;
use App\Models\Column;
use App\Models\Comment;
use App\Models\Live;
use App\Models\Order;
use App\Models\Wiki;
use App\Models\Works;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends ControllerBackend
{
    /**
     * @api {get} api/admin_v4/order/list 精品课订单
     * @apiVersion 4.0.0
     * @apiName  order/list
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
     * @apiParam {string} rank     排行列表
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
    public function list(Request $request, $flag = 1)
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
        $activity_tag = $request->get('activity_tag', '');
        $is_shill = (int)($request->get('is_shill', -1));
        $page = $request->input('page', 1);
        $size = $request->input('size', 10);
        $teacher_name = $request->input('teacher_name','');
        $query = Order::with(
            [
                'user:id,nickname,phone',
                'works:id,title,user_id',
                'works.user:id,nickname,phone'
            ])
            ->when(!is_null($status), function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when(!is_null($pay_type), function ($query) use ($pay_type) {
                $query->where('pay_type', $pay_type);
            })
            ->when(!is_null($os_type), function ($query) use ($os_type) {
                $query->where('os_type', $os_type);
            })
            ->when($nickname, function ($query) use ($nickname) {
                $query->whereHas('user', function ($query) use ($nickname) {
                    $query->where('nickname', 'like', '%' . $nickname . '%');
                });
            })
            ->when($phone, function ($query) use ($phone) {
                $query->whereHas('user', function ($query) use ($phone) {
                    $query->where('phone', 'like', '%' . $phone . '%');
                });
            })
            ->when($level, function ($query) use ($level) {
                $query->whereHas('user', function ($query) use ($level) {
                    $query->where('level', 'like', '%' . $level . '%');
                });
            })
            ->when($title, function ($query) use ($title) {
                $query->whereHas('works', function ($query) use ($title) {
                    $query->where('title', 'like', '%' . $title . '%');
                });
            })
            ->when($ordernum, function ($query) use ($ordernum) {
                $query->where('ordernum', 'like', '%' . $ordernum . '%');
            })
            ->when($start && $end, function ($query) use ($start, $end) {
                $query->whereBetween('created_at', [
                    Carbon::parse($start)->startOfDay()->toDateTimeString(),
                    Carbon::parse($end)->endOfDay()->toDateTimeString(),
                ]);
            })
            ->whereHas('user', function ($q) {
                $q->where('is_test_pay', '=', 0);
            });

        if (!empty($teacher_name)){
            $query->whereHas('works.user',function($q)use($teacher_name){
                $q->where('nickname','like',"%$teacher_name%");
            });
        }

        if ($activity_tag === 'cytx_on') {
            $query->where('activity_tag', '=', 'cytx');
        }
        if ($activity_tag === 'cytx_off') {
            $query->where('activity_tag', '<>', 'cytx');
        }
        if ($is_shill === 0) {
            $query->where('is_shill', '=', 0);
        }
        if ($is_shill === 1) {
            $query->where('is_shill', '=', 1);
        }

        $direction = $sort === 'asc' ? 'asc' : 'desc';
        $query->select('id', 'user_id', 'relation_id', 'ordernum', 'price', 'pay_price',
            'os_type', 'pay_type', 'created_at', 'status', 'activity_tag', 'is_shill')
            ->where('type', 9)->orderBy('id', $direction);

        if ($flag === 1) {
            $lists = $query
                ->paginate($size)
                ->toArray();
        } else {
            $lists = $query->limit($size)->offset(($page - 1) * $size)->get();
            if ($lists->isEmpty()) {
                return [];
            }
            return $lists->toArray();

        }

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
            ->limit(10)
            ->get();

        $data = [
            'lists' => $lists,
            'rank' => $rank
        ];
        return success($data);

    }

    public function listExcel(Request $request)
    {

        $columns = ['订单编号', '购买人账号', '购买人昵称', '课程名称','老师', '支付金额', '支付时间', '支付方式', '渠道', '是否退款', '订单状态', '订单来源'];

        $fileName = date('Y-m-d H:i') . '-' . random_int(10, 99) . '.csv';
        header('Content-Description: File Transfer');
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header("Access-Control-Allow-Origin: *");
        $fp = fopen('php://output', 'a');//打开output流
        mb_convert_variables('GBK', 'UTF-8', $columns);
        fputcsv($fp, $columns);     //将数据格式化为CSV格式并写入到output流中

        $while_flag =true;
        $page = 1;
        $size = 500;

        while ($while_flag){

            $request->offsetSet('page',$page);
            $request->offsetSet('size',$size);

            $list = $this->list($request, 2);


            foreach ($list as $v) {
                $v = json_decode(json_encode($v), true);
                $temp_v = [
                    $v['ordernum'],$v['user']['phone'],$v['user']['nickname'],
                    $v['works']['title'],$v['works']['user']['nickname'],$v['pay_price'],$v['created_at']
                ];

                switch ((int)$v['os_type']){
                    case 1:
                        $temp_v[] = '安卓';
                        break;
                    case 2:
                        $temp_v[] = '苹果';
                        break;
                    case 3:
                        $temp_v[] = '微信';
                        break;
                    default:
                        $temp_v[] = '-';
                }

                if ($v['activity_tag'] === 'cytx'){
                    $temp_v[] = '创业天下';
                }else{
                    $temp_v[] = '-';
                }
                if ($v['is_shill'] === 1){
                    $temp_v[] = '已退款';
                }else{
                    $temp_v[] = '未退款';
                }
                if ($v['status'] === 1){
                    $temp_v[] = '已支付';
                }else{
                    $temp_v[] = '未支付';
                }

                mb_convert_variables('GBK', 'UTF-8', $temp_v);
                fputcsv($fp, $temp_v);
                ob_flush();     //刷新输出缓冲到浏览器
                flush();        //必须同时使用 ob_flush() 和flush() 函数来刷新输出缓冲。
            }

            if (empty($list)){
                $while_flag = false;
            }
        }

        fclose($fp);
        exit();
    }

    public function colListExcel(Request $request){
        $columns = ['订单编号', '购买人账号', '购买人昵称', '课程名称','老师', '支付金额', '支付时间', '支付方式', '渠道', '是否退款', '订单状态', '订单来源'];

        $fileName = date('Y-m-d H:i') . '-' . random_int(10, 99) . '.csv';
        header('Content-Description: File Transfer');
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header("Access-Control-Allow-Origin: *");
        $fp = fopen('php://output', 'ab');//打开output流
        mb_convert_variables('GBK', 'UTF-8', $columns);
        fputcsv($fp, $columns);     //将数据格式化为CSV格式并写入到output流中

        $while_flag =true;
        $page = 1;
        $size = 500;

        while ($while_flag){

            $request->offsetSet('page',$page);
            $request->offsetSet('size',$size);

            $list = $this->colList($request, 2);


            foreach ($list as $v) {
                $v = json_decode(json_encode($v), true);
                $temp_v = [
                    $v['ordernum'],$v['user']['phone'],$v['user']['nickname'],
                    $v['column']['title'],$v['column']['user']['nickname'],$v['pay_price'],$v['created_at']
                ];


                switch ((int)$v['os_type']){
                    case 1:
                        $temp_v[] = '安卓';
                        break;
                    case 2:
                        $temp_v[] = '苹果';
                        break;
                    case 3:
                        $temp_v[] = '微信';
                        break;
                    default:
                        $temp_v[] = '-';
                }

                if ($v['activity_tag'] === 'cytx'){
                    $temp_v[] = '创业天下';
                }else{
                    $temp_v[] = '-';
                }
                if ($v['is_shill'] === 1){
                    $temp_v[] = '已退款';
                }else{
                    $temp_v[] = '未退款';
                }
                if ($v['status'] === 1){
                    $temp_v[] = '已支付';
                }else{
                    $temp_v[] = '未支付';
                }

                mb_convert_variables('GBK', 'UTF-8', $temp_v);
                fputcsv($fp, $temp_v);
                ob_flush();     //刷新输出缓冲到浏览器
                flush();        //必须同时使用 ob_flush() 和flush() 函数来刷新输出缓冲。
            }

            if (empty($list)){
                $while_flag = false;
            }
        }

        fclose($fp);
        exit();

    }

    public function colList(Request $request,$flag = 1)
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
        $activity_tag = $request->get('activity_tag', '');
        $is_shill = (int)($request->get('is_shill', -1));
        $page = $request->input('page', 1);
        $size = $request->input('size', 10);
        $teacher_name = $request->input('teacher_name','');

        $query = Order::with(
            [
                'user:id,nickname,phone',
                'column' => function ($q) {
                    $q->select(['id', 'name as title', 'name', 'cover_pic as cover_img','user_id']);
                },
                'column.user:id,nickname,phone'
            ])
            ->whereHas('column', function ($q) {
                $q->where('type', '=', 2);
            })
            ->when(!is_null($status), function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when(!is_null($pay_type), function ($query) use ($pay_type) {
                $query->where('pay_type', $pay_type);
            })
            ->when(!is_null($os_type), function ($query) use ($os_type) {
                $query->where('os_type', $os_type);
            })
            ->when($nickname, function ($query) use ($nickname) {
                $query->whereHas('user', function ($query) use ($nickname) {
                    $query->where('nickname', 'like', '%' . $nickname . '%');
                });
            })
            ->when($phone, function ($query) use ($phone) {
                $query->whereHas('user', function ($query) use ($phone) {
                    $query->where('phone', 'like', '%' . $phone . '%');
                });
            })
            ->when($level, function ($query) use ($level) {
                $query->whereHas('user', function ($query) use ($level) {
                    $query->where('level', 'like', '%' . $level . '%');
                });
            })
            ->when($title, function ($query) use ($title) {
                $query->whereHas('column', function ($query) use ($title) {
                    $query->where('name', 'like', '%' . $title . '%');
                });
            })
            ->when($ordernum, function ($query) use ($ordernum) {
                $query->where('ordernum', 'like', '%' . $ordernum . '%');
            })
            ->when($start && $end, function ($query) use ($start, $end) {
                $query->whereBetween('created_at', [
                    Carbon::parse($start)->startOfDay()->toDateTimeString(),
                    Carbon::parse($end)->endOfDay()->toDateTimeString(),
                ]);
            });

        if (!empty($teacher_name)){
            $query->whereHas('column.user',function($q)use($teacher_name){
                $q->where('nickname','like',"%$teacher_name%");
            });
        }
        if ($activity_tag === 'cytx_on') {
            $query->where('activity_tag', '=', 'cytx');
        }
        if ($activity_tag === 'cytx_off') {
            $query->where('activity_tag', '<>', 'cytx');
        }
        if ($is_shill === 0) {
            $query->where('is_shill', '=', 0);
        }
        if ($is_shill === 1) {
            $query->where('is_shill', '=', 1);
        }

        $direction = $sort == 'asc' ? 'asc' : 'desc';
        $query->select('id', 'user_id', 'relation_id', 'ordernum', 'price', 'pay_price', 'os_type', 'pay_type',
            'created_at', 'status', 'activity_tag','is_shill')
            ->where('type', 15)
            ->orderBy('id', $direction);

        if ($flag === 1) {
            $lists = $query
                ->paginate($size)
                ->toArray();
        } else {
            $lists = $query->limit($size)->offset(($page - 1) * $size)->get();
            if ($lists->isEmpty()) {
                return [];
            }
            return $lists->toArray();
        }

        $rank = Order::with(['column' => function ($q) {
            $q->select(['id', 'name as title', 'name', 'cover_pic as cover_img']);
        }])->whereHas('column', function ($q) {
            $q->where('type', '=', 2);
        })->select([
            DB::raw('count(*) as total'),
            'user_id',
            'relation_id'
        ])->where('type', 15)
            ->where('status', 1)
            ->orderBy('total', 'desc')
            ->groupBy('relation_id')
            ->limit(10)
            ->get();

        $data = [
            'lists' => $lists,
            'rank' => $rank
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
            'total_num' => $list['total'] ?? 0,
            'total_price' => $list['price'] ?? 0,
            'today_num' => $today['total'] ?? 0,
            'totday_price' => $today['price'] ?? 0
        ];
        return success($data);

    }

    /**
     * @api {get} api/admin_v4/order/detail 精品课-订单详情
     * @apiVersion 4.0.0
     * @apiName  order/detial
     * @apiGroup 后台-虚拟订单
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/order/detail
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
        $check = Order::where('id', '=', $id)->first();
        if (empty($check)) {
            return success([]);
        } else {
            if ($check->type == 15) {
                $list = Order::with(
                    [
                        'user:id,nickname,phone',
                        'column:id,name as title,user_id,cover_pic as cover_img,price',
                        'column.user:id,nickname'
                    ])
                    ->select('id', 'user_id', 'relation_id', 'vip_order_type', 'ordernum', 'os_type', 'pay_type', 'pay_price', 'created_at')
                    ->where('id', $id)
                    ->first();
                $list->works = $list->column;
            } else {
                $list = Order::with(
                    [
                        'user:id,nickname,phone',
                        'works:id,title,user_id,cover_img,price',
                        'works.user:id,nickname'
                    ])
                    ->select('id', 'user_id', 'relation_id', 'vip_order_type', 'ordernum', 'os_type', 'pay_type', 'pay_price', 'created_at')
                    ->where('id', $id)
                    ->first();
            }
            return success($list);
        }


    }

    /**
     * @api {get} api/admin_v4/order/user 会员订单
     * @apiVersion 4.0.0
     * @apiName  order/user
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
     * @apiParam {string} level  1 早期366老会员 2 推客 3黑钻 4皇钻 5代理
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
        $vip_order_type = $request->get('vip_order_type');
        $os_type = $request->get('os_type');
        $sort = $request->get('sort');
        $level = $request->get('level') ?? 0;
        $query = Order::with(
            [
                'user:id,nickname,level',
                'works:id,title'
            ])
            ->when(!is_null($status), function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when(!is_null($pay_type), function ($query) use ($pay_type) {
                $query->where('pay_type', $pay_type);
            })
            ->when(!is_null($vip_order_type), function ($query) use ($vip_order_type) {
                $query->where('vip_order_type', $vip_order_type);
            })
            ->when(!is_null($os_type), function ($query) use ($os_type) {
                $query->where('os_type', $os_type);
            })
            ->when($nickname, function ($query) use ($nickname) {
                $query->whereHas('user', function ($query) use ($nickname) {
                    $query->where('nickname', 'like', '%' . $nickname . '%');
                });
            })
            ->when(!is_null($level), function ($query) use ($level) {
                $query->whereHas('user', function ($query) use ($level) {
                    $query->where('level', $level);
                });
            })
            ->when($phone, function ($query) use ($phone) {
                $query->whereHas('user', function ($query) use ($phone) {
                    $query->where('phone', 'like', '%' . $phone . '%');
                });
            })
            ->when($title, function ($query) use ($title) {
                $query->whereHas('works', function ($query) use ($title) {
                    $query->where('title', 'like', '%' . $title . '%');
                });
            })
            ->when($ordernum, function ($query) use ($ordernum) {
                $query->where('ordernum', 'like', '%' . $ordernum . '%');
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
            'yellow' => 0,
            'yellow_today' => 0,
            'black' => 0,
            'black_today' => 0,
            'money' => 0,
            'money_today' => 0,
        ];
        $orders = Order::getOrderPrice(16);
        if ($orders['relation_id'] == 1) {
            $item['yellow'] = $orders['total'];
        } elseif ($orders['relation_id'] == 2) {
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
            'rank' => $item
        ];
        return success($data);
    }

    /**
     * @api {get} api/admin_v4/order/lecture 讲座订单
     * @apiVersion 4.0.0
     * @apiName  order/lecture
     * @apiGroup 后台-虚拟订单
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/order/lecture
     * @apiDescription 讲座订单
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
    public function lecture(Request $request)
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
                'column:id,name'
            ])
            ->when(!is_null($status), function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when(!is_null($pay_type), function ($query) use ($pay_type) {
                $query->where('pay_type', $pay_type);
            })
            ->when(!is_null($os_type), function ($query) use ($os_type) {
                $query->where('os_type', $os_type);
            })
            ->when($nickname, function ($query) use ($nickname) {
                $query->whereHas('user', function ($query) use ($nickname) {
                    $query->where('nickname', 'like', '%' . $nickname . '%');
                });
            })
            ->when($phone, function ($query) use ($phone) {
                $query->whereHas('user', function ($query) use ($phone) {
                    $query->where('phone', 'like', '%' . $phone . '%');
                });
            })
            ->when($level, function ($query) use ($level) {
                $query->whereHas('user', function ($query) use ($level) {
                    $query->where('level', 'like', '%' . $level . '%');
                });
            })
            ->when($title, function ($query) use ($title) {
                $query->whereHas('works', function ($query) use ($title) {
                    $query->where('title', 'like', '%' . $title . '%');
                });
            })
            ->when($ordernum, function ($query) use ($ordernum) {
                $query->where('ordernum', 'like', '%' . $ordernum . '%');
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
            ->where('type', 15)
            ->orderBy('id', $direction)
            ->paginate(10)
            ->toArray();

        $rank = Order::with('column:id,name,cover_pic')
            ->select([
                DB::raw('count(*) as total'),
                'user_id',
                'relation_id'
            ])
            ->where('type', 15)
            ->where('status', 1)
            ->orderBy('total', 'desc')
            ->groupBy('relation_id')
            ->get();

        $data = [
            'lists' => $lists,
            'rank' => $rank
        ];
        return success($data);
    }

    /**
     * @api {get} api/admin_v4/order/reward  打赏订单
     * @apiVersion 4.0.0
     * @apiName  order/lecture
     * @apiGroup 后台-虚拟订单
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/order/reward
     * @apiDescription 打赏订单
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
     * @apiParam {string} reward_type  打赏类型  1专栏  2课程  3想法 4 百科  5直播礼物
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
    public function reward(Request $request)
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
        $reward_type = $request->get('reward_type');
        $os_type = $request->get('os_type');
        $sort = $request->get('sort');

        $query = Order::when(!is_null($status), function ($query) use ($status) {
            $query->where('status', $status);
        })
            ->when(!is_null($pay_type), function ($query) use ($pay_type) {
                $query->where('pay_type', $pay_type);
            })
            ->when(!is_null($os_type), function ($query) use ($os_type) {
                $query->where('os_type', $os_type);
            })
            ->when(!is_null($reward_type), function ($query) use ($reward_type) {
                $query->where('reward_type', $reward_type);
            })
            ->when($nickname, function ($query) use ($nickname) {
                $query->whereHas('user', function ($query) use ($nickname) {
                    $query->where('nickname', 'like', '%' . $nickname . '%');
                });
            })
            ->when($phone, function ($query) use ($phone) {
                $query->whereHas('user', function ($query) use ($phone) {
                    $query->where('phone', 'like', '%' . $phone . '%');
                });
            })
            ->when($level, function ($query) use ($level) {
                $query->whereHas('user', function ($query) use ($level) {
                    $query->where('level', 'like', '%' . $level . '%');
                });
            })
            ->when($title, function ($query) use ($title) {
                $query->whereHas('works', function ($query) use ($title) {
                    $query->where('title', 'like', '%' . $title . '%');
                });
            })
            ->when($ordernum, function ($query) use ($ordernum) {
                $query->where('ordernum', 'like', '%' . $ordernum . '%');
            })
            ->when($start && $end, function ($query) use ($start, $end) {
                $query->whereBetween('created_at', [
                    Carbon::parse($start)->startOfDay()->toDateTimeString(),
                    Carbon::parse($end)->endOfDay()->toDateTimeString(),
                ]);
            });

        $direction = $sort == 'asc' ? 'asc' : 'desc';
        $lists = $query->select('id', 'user_id', 'relation_id', 'ordernum', 'price', 'pay_price', 'os_type', 'pay_type',
            'created_at', 'status', 'reward_type', 'reward_num', 'reward')
            ->where('type', 5)
            ->orderBy('id', $direction)
            ->paginate(20)
            ->toArray();
        if ($lists['data']) {
            foreach ($lists['data'] as &$v) {
                if ($v['reward_type'] == 1) {
                    $v['title'] = Column::where('id', $v['relation_id'])->value('name');
                    $v['type'] = '讲座';
                } elseif ($v['reward_type'] == 2) {
                    $v['title'] = Works::where('id', $v['relation_id'])->value('title');
                    $v['type'] = '课程';
                } elseif ($v['reward_type'] == 3) {
                    $v['title'] = Comment::where('id', $v['relation_id'])->value('content');
                    $v['type'] = '想法';
                } elseif ($v['reward_type'] == 4) {
                    $v['title'] = Wiki::where('id', $v['relation_id'])->value('name');
                    $v['type'] = '百科';
                } elseif ($v['reward_type'] == 5) {
                    $v['title'] = Live::where('id', $v['relation_id'])->value('title');
                    $v['type'] = '直播';
                }
            }
        }

        return success($lists);

    }

    /**
     * @api {get} api/admin_v4/order/vip  360订单
     * @apiVersion 4.0.0
     * @apiName  order/vip
     * @apiGroup 后台-虚拟订单
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/order/vip
     * @apiDescription 360订单
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
    public function vip(Request $request)
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
                'user:id,nickname'
            ])
            ->when(!is_null($status), function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when(!is_null($pay_type), function ($query) use ($pay_type) {
                $query->where('pay_type', $pay_type);
            })
            ->when(!is_null($os_type), function ($query) use ($os_type) {
                $query->where('os_type', $os_type);
            })
            ->when($nickname, function ($query) use ($nickname) {
                $query->whereHas('user', function ($query) use ($nickname) {
                    $query->where('nickname', 'like', '%' . $nickname . '%');
                });
            })
            ->when($phone, function ($query) use ($phone) {
                $query->whereHas('user', function ($query) use ($phone) {
                    $query->where('phone', 'like', '%' . $phone . '%');
                });
            })
            ->when($level, function ($query) use ($level) {
                $query->whereHas('user', function ($query) use ($level) {
                    $query->where('level', 'like', '%' . $level . '%');
                });
            })
            ->when($title, function ($query) use ($title) {
                $query->whereHas('works', function ($query) use ($title) {
                    $query->where('title', 'like', '%' . $title . '%');
                });
            })
            ->when($ordernum, function ($query) use ($ordernum) {
                $query->where('ordernum', 'like', '%' . $ordernum . '%');
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
            ->where('type', 16)
            ->orderBy('id', $direction)
            ->paginate(10)
            ->toArray();

        return success($lists);
    }

}
