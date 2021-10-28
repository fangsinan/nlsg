<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
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
        'pay_price', 'city', 'vip_order_type', 'send_type', 'send_user_id', 'remark', 'sales_id', 'sales_bind_id',


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

    public function pay_record_detail()
    {
        return $this->belongsTo(PayRecordDetail::class, 'ordernum', 'ordernum');
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
            case 18:
                $model = new Column();
                $result = $model->getIndexColumn([$relation_id]);
                break;
            case 9:
                $model = new Works();
                $result = $model->getIndexWorks([$relation_id], 2, $user_id);
                break;
            case 10:
                $liveinfo = LiveInfo::find($relation_id);
                $result = Live::where(['id' => $liveinfo['live_pid']])->get()->toArray();
                break;
            case 13:
                $result = [];
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
                    'id' => 1, 'type' => 6, 'text' => '幸福360会员',
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
    {     DB::connection()->enableQueryLog();
        $past = Carbon::parse('-30 minutes')->toDateTimeString();
//        $subHour = now()->subHours(5);
        $res = Order::where('created_at', '<', $past)
//            ->where('created_at', '>', $subHour)
            ->where('status', 0)
//            ->where('pay_check',1)
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

    public function checkLiveSub($live_id, $user_id)
    {
        $time = time();
        if (empty($live_id) || empty($user_id)) {
            return ['code' => true, 'is_sub' => 0, 'p' => $time . $user_id];
        }

//        $check = Order::where('user_id', '=', $user_id)
//            ->where('live_id', '=', $live_id)
//            ->where('type', '=', 10)
//            ->where('status', '=', 1)
//            ->first();
        $check = Subscribe::where('user_id', '=', $user_id)
            ->where('relation_id', '=', $live_id)
            ->where('type', '=', 3)
            ->where('status', '=', 1)
            ->first();
        if ($check) {
            return ['code' => true, 'is_sub' => 1, 'p' => $time . $user_id];
        } else {
            return ['code' => true, 'is_sub' => 0, 'p' => $time . $user_id];
        }

    }

    public function payRecord()
    {
        return $this->hasOne(PayRecord::class, 'ordernum', 'ordernum')
            ->where('status', '=', 1);
    }

    public function live()
    {
        return $this->belongsTo(Live::class, 'live_id', 'id');
    }

    public function liveRemark()
    {
        return $this->belongsTo(Live::class, 'remark', 'id');
    }

    public function offline()
    {
        return $this->belongsTo(OfflineProducts::class, 'relation_id', 'id');
    }

    public function liveGoods()
    {
        return $this->belongsTo(Live::class, 'live_id', 'id');
    }

    public function orderInLive($params, $this_user = [])
    {
        $params = array_filter($params);
        krsort($params);
        $size = $params['size'] ?? 10;
        $page = $params['page'] ?? 1;
        $now_date = date('Y-m-d H:i:s');

        //9精品课  10直播  14 线下产品(门票类)   15讲座  16新vip
        $query = Order::query();

        $query->where('id', '>', 341864)
            ->where('status', '=', 1)
            ->whereIn('type', [9, 10, 14, 15, 16])
            ->where('live_id', '>', 0)
            ->where('is_shill', '=', 0)
            ->where('pay_price','>',1);

        if (!empty($params['id'] ?? 0)) {
            $query->where('id', '=', $params['id']);
        }

        //订单编号
        if (!empty($params['ordernum'] ?? 0)) {
            $query->where('ordernum', 'like', '%' . $params['ordernum'] . '%');
        }
        //下单时间
        if (!empty($params['created_at'])) {
            $created_at = explode(',', $params['created_at']);
            $created_at[0] = date('Y-m-d 00:00:00', strtotime($created_at[0]));
            if (empty($created_at[1] ?? '')) {
                $created_at[1] = $now_date;
            } else {
                $created_at[1] = date('Y-m-d 23:59:59', strtotime($created_at[1]));
            }
            $query->whereBetween('created_at', [$created_at[0], $created_at[1]]);
        }

        //用户账号
        if (!empty($params['phone'] ?? '')) {
            $phone = $params['phone'];
            $temp_id_list = User::where('phone','like',"%$phone%")->pluck('id')->toArray();
            $query->whereIn('user_id',$temp_id_list);
        }

        //直播标题
        if (!empty($params['title'] ?? '')) {
            $title = $params['title'];
            $temp_id_list = Live::where('title','like',"%$title%")->pluck('id')->toArray();
            $query->whereIn('live_id',$temp_id_list);
//            $query->whereHas('live', function ($q) use ($title) {
//                $q->where('title', 'like', "%$title%");
//            });
        }

        //21是老师,只看自己
        //22是管理员,全都能看
        //23是校长,看名下老师
        if ($this_user['live_role'] == 21) {
            $live_user_id = $this_user['user_id'];
            $query->where('live_id','>',52);
            $query->whereHas('live', function ($q) use ($live_user_id) {
                $q->where('user_id', '=', $live_user_id);
            });
        } elseif ($this_user['live_role'] == 23) {
            $blrModel = new BackendLiveRole();
            $son_user_id = $blrModel->getDataUserId($this_user['username']);
            $query->where('live_id','>',52);
            $query->whereHas('live', function ($q) use ($son_user_id) {
                $q->whereIn('user_id', $son_user_id);
            });
        }

        //支付方式
        //订单来源
        if (!empty($params['pay_type'] ?? 0)) {
            $query->where('pay_type', '=', $params['pay_type']);
        }
        if (!empty($params['os_type'] ?? 0)) {
            $query->where('os_type', '=', $params['os_type']);
        }
        //商品类型
        if (!empty($params['type'] ?? 0)) {
            $query->where('type', '=', $params['type']);

            if (!empty($params['goods_title']??'')){
                $goods_title = trim($params['goods_title']);
                switch (intval($params['type'])){
                    case 9:
                        $query->whereHas('works', function ($q) use ($goods_title) {
                            $q->where('title', 'like', "%$goods_title%");
                        });
                        break;
                    case 10:
                        $query->whereHas('liveGoods', function ($q) use ($goods_title) {
                            $q->where('title', 'like', "%$goods_title%");
                        });
                        break;
                    case 14:
                        $query->whereHas('offline', function ($q) use ($goods_title) {
                            $q->where('title', 'like', "%$goods_title%");
                        });
                        break;
                    case 15:
                        $query->whereHas('column', function ($q) use ($goods_title) {
                            $q->where('title', 'like', "%$goods_title%");
                        });
                        break;
                }
            }

        }

//        if (!empty($params['id'] ?? 0)) {
            $query->with([
                'pay_record_detail:id,type,ordernum,user_id,price',
                'pay_record_detail.user:id,phone,nickname',
            ]);
//        }

        //推荐用户账号
        if (!empty($params['t_phone'] ?? '')) {
            $phone = $params['t_phone'];
            $temp_id_list = User::where('phone','like',"%$phone%")->pluck('id')->toArray();
            $query->whereHas('pay_record_detail',function($q) use($temp_id_list){
                $q->whereIn('user_id',$temp_id_list);
            });
            //$query->whereIn('user_id',$temp_id_list);
        }


        $query->with([
            'works' => function ($q) {
                $q->select(['id', 'title', 'type', 'subtitle', 'price',
                    'cover_img', 'detail_img']);
            },
            'column' => function ($q) {
                $q->select(['id', 'name as title', 'type', 'subtitle', 'price',
                    'cover_pic as cover_img', 'details_pic as detail_img']);
            },
            'offline' => function ($q) {
                $q->select(['id', 'title', 'subtitle', 'price',
                    'cover_img', 'image']);
            },
            'liveGoods' => function ($q) {
                $q->select(['id', 'title', 'describe', 'cover_img', 'price']);
            },
            'payRecord' => function ($q) {
                $q->select(['ordernum', 'price', 'type', 'created_at']);
            },
            'live' => function ($q) {
                $q->select(['id', 'title', 'describe', 'begin_at', 'cover_img']);
            },
            'user' => function ($q) {
                $q->select(['id', 'phone', 'nickname']);
            }
        ])->select(['id', 'type', 'relation_id', 'pay_time', 'price', 'user_id',
            'pay_price', 'pay_type', 'ordernum', 'live_id', 'pay_type', 'os_type', 'status']);

        $query->whereHas('live');
//        $query->whereHas('live', function ($q) {
            //老直播和现在直播id有重合,加时间区分
//            $q->where('created_at', '>', '2021-01-01 00:00:00');
//        });

        $cache_key_name = 'list_money_'.$this_user['id'].'_'.md5(serialize($params));
        $expire_num = 30;
        $list_money = Cache::get($cache_key_name);
        if (empty($list_money)) {
            $money_query = clone $query;
            $list_money = $money_query->sum('pay_price');
            Cache::put($cache_key_name, $list_money, $expire_num);
        }

        if (($params['excel_flag'] ?? 0) == 1){
            $list = $query->orderBy('id', 'desc')
                ->limit($size)
                ->offset(($page - 1) * $size)
                ->get();
        }else{
            $list = $query->orderBy('id', 'desc')->paginate($size);
        }


        foreach ($list as &$v) {
            $goods = [];
            switch (intval($v->type)) {
                case 9:
                    $goods['goods_id'] = $v->works->id ?? 0;
                    $goods['title'] = $v->works->title ?? '数据错误';
                    $goods['subtitle'] = $v->works->subtitle ?? '';
                    $goods['cover_img'] = $v->works->cover_img ?? '';
                    $goods['detail_img'] = $v->works->detail_img ?? '';
                    $goods['price'] = $v->works->price ?? '价格数据错误';
                    break;
                case 10:
                    $goods['goods_id'] = $v->liveGoods->id ?? 0;
                    $goods['title'] = $v->liveGoods->title ?? '数据错误';
                    $goods['subtitle'] = '';
                    $goods['cover_img'] = $v->liveGoods->cover_img ?? '';
                    $goods['detail_img'] = '';
                    $goods['price'] = $v->liveGoods->price ?? '价格数据错误';
                    break;
                case 14:
                    $goods['goods_id'] = $v->offline->id ?? 0;
                    $goods['title'] = $v->offline->title ?? '数据错误';
                    $goods['subtitle'] = $v->offline->subtitle ?? '';
                    $goods['cover_img'] = $v->offline->cover_img ?? '';
                    $goods['detail_img'] = $v->offline->image ?? '';
                    $goods['price'] = $v->offline->price ?? '价格数据错误';
                    break;
                case 15:
                    $goods['goods_id'] = $v->column->id ?? 0;
                    $goods['title'] = $v->column->title ?? '数据错误';
                    $goods['subtitle'] = $v->column->subtitle ?? '';
                    $goods['cover_img'] = $v->column->cover_img ?? '';
                    $goods['detail_img'] = $v->column->detail_img ?? '';
                    $goods['price'] = $v->column->price ?? '价格数据错误';
                    break;
                case 16:
                    $goods['goods_id'] = 999999;
                    $goods['title'] = '幸福360';
                    $goods['subtitle'] = '';
                    $goods['cover_img'] = '/live/recommend/360_xhc.png';
                    $goods['detail_img'] = '';
                    $goods['price'] = 360;
                    break;
            }
            $v->goods = $goods;
            unset($v->works, $v->column, $v->offline, $v->liveGoods);
        }

        if (($params['excel_flag'] ?? 0) == 1){
            return $list;
        }else{
            $total_money = collect(['total_money'=>$list_money]);
            return $total_money->merge($list);
        }

    }

    public function inviterLiveList($params,$this_user = []){

        $lu_list_query = DB::table('nlsg_order as o')
            ->join('nlsg_live as l','o.remark','=','l.id')
            ->join('nlsg_user as u','u.id','=','l.user_id')
            ->join('nlsg_live_info as li','li.live_pid','=','l.id')
            ->leftJoin('nlsg_live_count_down as cd',function($q){
                $q->on('cd.user_id','=','o.user_id')->on('cd.live_id','=','li.id');
            })
            ->leftJoin('nlsg_user as u2','cd.new_vip_uid','=','u2.id')
            ->where('o.id','>',341864)
            ->where('o.status','=',1)
            ->where('o.type','=',10)
            ->where('o.pay_price','>',0.01);

        //推荐用户账号
        if (!empty($params['t_phone'] ?? '')) {
            $t_phone = $params['t_phone'];
            $temp_id_list = User::where('phone','like',"%$t_phone%")->pluck('id')->toArray();
            $lu_list_query->whereIn('l.user_id',$temp_id_list);
        }

        //源直播间推荐用户账号
        if (!empty($params['t_live_phone'] ?? '')) {
            $t_live_phone = $params['t_live_phone'];
            $temp_id_list = User::where('phone','like',"%$t_live_phone%")->pluck('id')->toArray();
            $lu_list_query->whereIn('u2.id',$temp_id_list);
        }

        if (!empty($params['t_title'] ?? '')) {
            $t_title = $params['t_title'];
            $lu_list_query->where('l.title','like',"%$t_title%");
        }

        if ($this_user['live_role'] == 21) {
            $live_user_id = $this_user['user_id'];
            $lu_list_query->where('o.live_id','>',52);
            $lu_list_query->where('l.user_id','=',$live_user_id);
        } elseif ($this_user['live_role'] == 23) {
            $blrModel = new BackendLiveRole();
            $son_user_id = $blrModel->getDataUserId($this_user['username']);
            $lu_list_query->where('o.live_id','>',52);
            $lu_list_query->whereIn('l.user_id',$son_user_id);
        }

        $lu_list = $lu_list_query->select([
            'o.id','o.live_id','o.remark','o.user_id','u.phone','u.nickname','l.title','li.id as info_id',
            'cd.new_vip_uid as t_live_user_id','u2.phone as t_live_phone',
            DB::raw("CONCAT(o.live_id,'-',o.user_id) as sign")
        ])->get();

        if ($lu_list->isEmpty()){
            $sign_list = [];
        }else{
            $lu_list = $lu_list->toArray();
            $sign_list = array_column($lu_list,'sign');
        }

        $size = $params['size'] ?? 10;
        $page = $params['page'] ?? 1;
        $now_date = date('Y-m-d H:i:s');

        $query = Order::query();
        $query->where('id', '>', 341864)
            ->where('status', '=', 1)
            ->whereIn('type', [10,14,16])
            ->where('live_id', '>', 0)
            ->where('is_shill', '=', 0);

        if (!empty($params['id'] ?? 0)) {
            $query->where('id', '=', $params['id']);
        }

        //订单编号
        if (!empty($params['ordernum'] ?? 0)) {
            $query->where('ordernum', 'like', '%' . $params['ordernum'] . '%');
        }
        //下单时间
        if (!empty($params['created_at'])) {
            $created_at = explode(',', $params['created_at']);
            $created_at[0] = date('Y-m-d 00:00:00', strtotime($created_at[0]));
            if (empty($created_at[1] ?? '')) {
                $created_at[1] = $now_date;
            } else {
                $created_at[1] = date('Y-m-d 23:59:59', strtotime($created_at[1]));
            }
            $query->whereBetween('created_at', [$created_at[0], $created_at[1]]);
        }

        //用户账号
        if (!empty($params['phone'] ?? '')) {
            $phone = $params['phone'];
            $temp_id_list = User::where('phone','like',"%$phone%")->pluck('id')->toArray();
            $query->whereIn('user_id',$temp_id_list);
        }

        //直播标题
        if (!empty($params['title'] ?? '')) {
            $title = $params['title'];
            $temp_id_list = Live::where('title','like',"%$title%")->pluck('id')->toArray();
            $query->whereIn('live_id',$temp_id_list);
        }

        if (!empty($params['pay_type'] ?? 0)) {
            $query->where('pay_type', '=', $params['pay_type']);
        }
        if (!empty($params['os_type'] ?? 0)) {
            $query->where('os_type', '=', $params['os_type']);
        }
        //商品类型
        if (!empty($params['type'] ?? 0)) {
            $query->where('type', '=', $params['type']);

            if (!empty($params['goods_title']??'')){
                $goods_title = trim($params['goods_title']);
                switch (intval($params['type'])){
                    case 14:
                        $query->whereHas('offline', function ($q) use ($goods_title) {
                            $q->where('title', 'like', "%$goods_title%");
                        });
                        break;
                }
            }
        }

        $query->whereIn(DB::raw("CONCAT(live_id,'-',user_id)"),$sign_list);

        $query->with([
            'offline' => function ($q) {
                $q->select(['id', 'title', 'subtitle', 'price',
                    'cover_img', 'image']);
            },
            'liveGoods' => function ($q) {
                $q->select(['id', 'title', 'describe', 'cover_img', 'price']);
            },
            'payRecord' => function ($q) {
                $q->select(['ordernum', 'price', 'type', 'created_at']);
            },
            'live' => function ($q) {
                $q->select(['id', 'title', 'describe', 'begin_at', 'cover_img']);
            },
            'liveRemark' => function ($q) {
                $q->select(['id', 'title', 'describe', 'begin_at', 'cover_img']);
            },
            'user' => function ($q) {
                $q->select(['id', 'phone', 'nickname']);
            },
            'pay_record_detail:id,type,ordernum,user_id,price',
            'pay_record_detail.user:id,phone,nickname',
        ])->select(['id', 'type', 'relation_id', 'pay_time', 'price', 'user_id',
            'pay_price', 'pay_type', 'ordernum', 'live_id', 'os_type', 'status','remark',
            DB::raw("CONCAT(live_id,'-',user_id) as sign")]);

        if (($params['excel_flag'] ?? 0) == 1){
            $list = $query->orderBy('id', 'desc')
                ->limit($size)
                ->offset(($page - 1) * $size)
                ->get();
        }else{
            $list = $query->orderBy('id', 'desc')->paginate($size);
        }

        foreach ($list as &$v) {
            foreach ($lu_list as $ll_v){
                if ($ll_v->sign == $v->sign){
                    $temp_inviter = [];
                    $temp_inviter['user_id'] = $ll_v->user_id;
                    $temp_inviter['username'] = $ll_v->phone;
                    $temp_inviter['nickname'] = $ll_v->nickname;
                    $temp_inviter['live_id'] = $ll_v->live_id;
                    $temp_inviter['title'] = $ll_v->title;
                    $v->inviter_info = $temp_inviter;

                    $v->t_live_user_id = $ll_v->t_live_user_id;
                    $v->t_live_phone = $ll_v->t_live_phone;
                }
            }

            $goods = [];
            switch (intval($v->type)) {
                case 10:
                    $goods['goods_id'] = $v->liveGoods->id ?? 0;
                    $goods['title'] = $v->liveGoods->title ?? '数据错误';
                    $goods['subtitle'] = '';
                    $goods['cover_img'] = $v->liveGoods->cover_img ?? '';
                    $goods['detail_img'] = '';
                    $goods['price'] = $v->liveGoods->price ?? '价格数据错误';
                    $v->live = $v->liveRemark;
                    break;
                case 14:
                    $goods['goods_id'] = $v->offline->id ?? 0;
                    $goods['title'] = $v->offline->title ?? '数据错误';
                    $goods['subtitle'] = $v->offline->subtitle ?? '';
                    $goods['cover_img'] = $v->offline->cover_img ?? '';
                    $goods['detail_img'] = $v->offline->image ?? '';
                    $goods['price'] = $v->offline->price ?? '价格数据错误';
                    break;
                case 16:
                    $goods['goods_id'] = 999999;
                    $goods['title'] = '幸福360';
                    $goods['subtitle'] = '';
                    $goods['cover_img'] = '/live/recommend/360_xhc.png';
                    $goods['detail_img'] = '';
                    $goods['price'] = 360;
                    break;
            }
            $v->goods = $goods;
            unset($v->offline,$v->liveGoods);
        }

        return $list;
    }
}
