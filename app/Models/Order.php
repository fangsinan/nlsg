<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;

use App\Servers\LiveInfoServers;
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
        'pay_price', 'city', 'vip_order_type', 'send_type', 'send_user_id', 'remark', 'sales_id', 'sales_bind_id','protect_user_id','live_admin_id',

    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function twitter()
    {
        return $this->belongsTo(User::class, 'twitter_id', 'id');
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


    public function mallAddress()
    {
        return $this->hasOne(MallAddress::class, 'user_id', 'user_id')->where([
            'is_del' => 0,
            'is_default' => 1,
        ]);
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
            case 15:
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
                $result = Live::select("id","title",'describe','cover_img','teacher_img','banner_img','msg','content','user_id','reason','profit_sharing','twitter_money','price','playback_price','is_del',
                'rank','type','begin_at','end_at','password','helper','is_free','is_show','can_push','check_time','is_finish','is_playback','order_num','need_virtual','need_virtual_num','virtual_online_num',
                'is_forb','is_join','relation_live','status','is_test','flag','cover_img as cover_images'
                        )->where(['id' => $liveinfo['live_pid']])->get()->toArray();
                break;
            case 13:
                $result = [];
                break;
            case 14:
                $result = OfflineProducts::select('id','title','subtitle','describe','total_price','price','cover_img','image','video_url','str_time','is_del','is_show','describe_type','url','off_line_pay_type',
                'column_id','cover_vertical_img as cover_images')->where(['id' => $relation_id])->get()->toArray();
                break;
            case 16:
                $result[] = [
                    'id' => 1, 'type' => 6, 'text' => '幸福360会员',
                    'img' => '/nlsg/works/20210105102849884378.png', 'price' => 360.00,
                    'cover_images' => '/nlsg/works/20210105102849884378.png',
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
            case 19:
                $model = new Lists();
                $result = $model->getIndexListWorks([$relation_id],[10]);
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

    public function remarkSubInfo(){
        return $this->hasMany(Subscribe::class,'user_id','user_id')
            ->where('type','=',3)
            ->where('status','=',1);
    }

    public function orderInLive($params, $this_user = [])
    {
        $params = array_filter($params);
        krsort($params);
        $size = $params['size'] ?? 10;
        $page = $params['page'] ?? 1;
        $now_date = date('Y-m-d H:i:s');
        $excel_flag = (int)($params['excel_flag'] ?? 0);

        //9精品课  10直播  14 线下产品(门票类)   15讲座  16新vip
        $query = self::query();

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
//            $created_at[0] = date('Y-m-d 00:00:00', strtotime($created_at[0]));
            if (empty($created_at[1] ?? '')) {
                $created_at[1] = $now_date;
            }
//            else {
//                $created_at[1] = date('Y-m-d 23:59:59', strtotime($created_at[1]));
//            }
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
        if ($this_user['live_role'] === 21) {
            $live_user_id = $this_user['user_id'];
            $query->where('live_id','>',52);
            $query->whereHas('live', function ($q) use ($live_user_id) {
                $q->where('user_id', '=', $live_user_id);
            });
        } elseif ($this_user['live_role'] === 23) {
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
                switch ((int)$params['type']){
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

        if ($excel_flag === 1){
            $list = $query->orderBy('id', 'desc')
                ->limit($size)
                ->offset(($page - 1) * $size)
                ->get();
        }else{
            $list = $query->orderBy('id', 'desc')->paginate($size);
        }


        foreach ($list as &$v) {
            $goods = [];
            switch ((int)$v->type) {
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

        if ($excel_flag === 1){
            return $list;
        }

        $total_money = collect(['total_money'=>$list_money]);
        return $total_money->merge($list);
    }

    //渠道 过滤type=15 relation=8
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

    public function   inviterLiveListNew($params, $this_user = []) {
        /**
         * select * from nlsg_user where phone=15278464531;
         * select * from nlsg_subscribe where type=3 and user_id=5171282;
         * select * from nlsg_order where type=10 and user_id=5171282 and status=1;
         *
         * 这一单如果在销售列表的数据就应该是
         * 直播标题是live238标题  商品标题也是live238标题  推荐人是order表的twitter
         * 源直播是remark的live232标题  源推荐人应该是sub表relation=232的twitter
         */
        $size     = $params['size'] ?? 10;
        $page     = $params['page'] ?? 1;
        $now_date = date('Y-m-d H:i:s');

        $max_id = 341864;
        if ($this_user['username'] == '16612348888'){
//            $max_id = Order::query()
//                ->where('pay_time','>=','2022-04-06 00:00:00')
//                ->min('id');
//
//            if (!$max_id){
//                $max_id = Order::query()->max('id');
//            }
            $max_id = 1890445;
        }

        $query = self::query()
            ->where('id', '>', $max_id)
            ->whereIn('type', [10, 14])
            ->where('status', '=', 1)
//            ->where('live_id', '<>', 0)
            ->where('is_shill', '=', 0)
            ->where('pay_price', '>', 0.01)
            ->select([
                'id', 'type', 'relation_id', 'pay_time', 'price', 'user_id', 'pay_price', 'pay_type', 'ordernum',
                'live_id', 'os_type', 'remark', 'status', 'twitter_id','protect_user_id',
            ])
            ->orderBy('id', 'desc');

        if ($this_user['username'] == '16612348888'){
            $query->where('pay_price','=',49.9);
        }

        if (!empty($params['id'] ?? 0)) {
            $query->where('id', '=', $params['id']);
        }

        if ($this_user['role_id'] !== 1) {
            $query->where('live_id', '<>', 0);
            $query->where('relation_id', '<>', 8);
            $query->where('channel_show', '=', 1);
            $liServers       = new LiveInfoServers();
            $twitter_id_list = $liServers->twitterIdList($this_user['username']);
            if ($twitter_id_list !== null) {
//                $query->whereIn('twitter_id', $twitter_id_list);
                $query->whereIn('protect_user_id', $twitter_id_list);
            }
        }

        $query->with([
            'offline:id,title,subtitle,price,cover_img,image',
            'payRecord:ordernum,price,type,created_at',
            'live:id,title,describe,begin_at,cover_img,user_id,price',
            'liveRemark:id,title',
            'user:id,phone,nickname',
            'twitter:id,phone,nickname',
            'twitter.getLName:id,son_id,son_flag',
            'pay_record_detail:id,type,ordernum,user_id,price',
            'pay_record_detail.user:id,phone,nickname',
            'remarkSubInfo:id,user_id,type,relation_id,twitter_id',
            'remarkSubInfo.twitterUser:id,phone,nickname',
        ]);

        //订单编号
        if (!empty($params['ordernum'] ?? '')) {
            $query->where('ordernum', 'like', '%' . $params['ordernum'] . '%');
        }
        //直播标题
        if (!empty($params['title'] ?? '')) {
            $temp_id_list = Live::query()
                ->where('title', 'like', "%" . $params['title'] . "%")
                ->pluck('id')
                ->toArray();
            $query->whereIn('live_id', $temp_id_list);
        }
        //用户账号
        if (!empty($params['phone'] ?? '')) {
            $phone = $params['phone'];
            if(strlen($phone) === 11){
                $query->whereHas('user', function ($q) use ($phone) {
                    $q->where('phone', '=', $phone);
                });
            }else{
                $where_user_id = User::query()
                    ->where('phone','like',"%$phone%")
                    ->pluck('id')
                    ->toArray();
                $query->whereIn('user_id',$where_user_id);
            }
        }
        //订单来源
        if (!empty($params['os_type'] ?? 0)) {
            $query->where('os_type', '=', $params['os_type']);
        }
        //订单类型 商品标题
        if (!empty($params['type'] ?? 0)) {
            $query->where('type', '=', $params['type']);
            if (!empty($params['goods_title'] ?? '')) {
                $goods_title = trim($params['goods_title']);
                if ((int)$params['type'] === 14) {
                    $query->whereHas('offline', function ($q) use ($goods_title) {
                        $q->where('title', 'like', "%$goods_title%");
                    });
                }
            }
        }

        //支付方式
        if (!empty($params['pay_type'] ?? 0)) {
            $query->where('pay_type', '=', $params['pay_type']);
        }
        //下单时间
        if (!empty($params['created_at'] ?? '')) {
            $created_at    = explode(',', $params['created_at']);
            $created_at[0] = date('Y-m-d 00:00:00', strtotime($created_at[0]));
            if (empty($created_at[1] ?? '')) {
                $created_at[1] = $now_date;
            } else {
                $created_at[1] = date('Y-m-d 23:59:59', strtotime($created_at[1]));
            }
            $query->whereBetween('created_at', [$created_at[0], $created_at[1]]);
        }

        //源直播
        if (!empty($params['t_title'] ?? '')) {
            $t_title = $params['t_title'];
            $query->whereHas('liveRemark', function ($q) use ($t_title) {
                $q->where('title', 'like', "%$t_title%");
            });
        }
        //源账户 order表的twitter
        if (!empty($params['t_phone'] ?? '')) {
            $t_phone = $params['t_phone'];
            $query->whereHas('twitter', function ($q) use ($t_phone) {
                $q->where('phone', 'like', "%$t_phone%");
            });
        }
        //源推荐账户  sub表的twitter
//        if (!empty($params['t_live_phone'] ?? '')) {
//            $t_live_phone = $params['t_live_phone'];
//            $query->whereHas('twitter', function ($q) use ($t_live_phone) {
//                $q->where('phone', 'like', "%$t_live_phone%");
//            });
//        }
        if (($params['excel_flag'] ?? 0)) {
            $list = $query->limit($size)->offset(($page - 1) * $size)->get();
        } else {
            $list = $query->paginate($size);
        }

        foreach ($list as &$v) {
            $v->remarkSub = [];
            if (!empty($v->remark??'') && !empty($v->remarkSubInfo??[])){
                foreach ($v->remarkSubInfo as $vv){
                    if ((int)$vv['relation_id'] === (int)$v->remark){
                        $v->remarkSub = [
                            'twitter_id' => $vv->twitter_id ?? 0,
                            'twitter_phone'=>$vv->twitterUser->phone ?? '',
                            'twitter_nickname'=>$vv->twitterUser->nickname ?? '',
                        ];
                    }
                }
            }
            unset($v->remarkSubInfo);

            //为什么这么取值?
            $temp_inviter             = [];
            $temp_inviter['user_id']  = $v->twitter->id ?? 0;
            $temp_inviter['username'] = $v->twitter->phone ?? '';
            $temp_inviter['nickname'] = $v->twitter->nickname ?? '';
            $temp_inviter['son_flag'] = $v->twitter->getLName->son_flag ?? '';
            $temp_inviter['live_id']  = $v->liveRemark->id ?? 0;
            $temp_inviter['title']    = $v->liveRemark->title ?? '';
            $v->inviter_info          = $temp_inviter;

            $v->t_live_user_id  = $v->twitter->id ?? 0;
            $v->t_live_phone    = $v->twitter->phone ?? '';
            $v->t_live_nickname = $v->twitter->nickname ?? '';
            $goods              = [];
            switch ($v->type) {
                case 10:
                    $goods['goods_id']   = $v->live->id ?? 0;
                    $goods['title']      = $v->live->title ?? '数据错误';
                    $goods['subtitle']   = '';
                    $goods['cover_img']  = $v->live->cover_img ?? '';
                    $goods['detail_img'] = '';
                    $goods['price']      = $v->live->price ?? '价格数据错误';
                    break;
                case 14:
                    $goods['goods_id']   = $v->offline->id ?? 0;
                    $goods['title']      = $v->offline->title ?? '数据错误';
                    $goods['subtitle']   = $v->offline->subtitle ?? '';
                    $goods['cover_img']  = $v->offline->cover_img ?? '';
                    $goods['detail_img'] = $v->offline->image ?? '';
                    $goods['price']      = $v->offline->price ?? '价格数据错误';
                    break;
            }
            $v->goods = $goods;
            unset($v->offline, $v->liveGoods);
        }
        return $list;

    }

    public function addressInfo(){
        return $this->hasOne(MallAddress::class,'id','address_id');
    }
    public function textbookInfo(){
        return $this->hasOne(Textbook::class,'id','textbook_id');
    }


    public static function  getSendInfo($order){

        $expressInfo = ExpressInfo::find($order['express_info_id']);
        $address = MallAddress::find($order['address_id']);
        $textbook = Textbook::find($order['textbook_id']);


        if(!empty($address)){
            $details_string = MallAddress::getNameById($address->province).
                MallAddress::getNameById($address->city).
                MallAddress::getNameById($address->area);
            $address->details = $details_string.$address->details;

        }
        if(!empty($expressInfo)){
            $expressInfo = $expressInfo->toArray();

            $expressInfo['updated_at'] = date('Y-m-d',strtotime($expressInfo['updated_at'])) ;
            $expressInfo['express_name'] = ExpressCompany::onlyGetName(
                $expressInfo['express_id'] ?? 0
            );
        }

        $res = [
            'expressInfo' => $expressInfo??(object)[],
            'address' => $address??(object)[],
            'textbook' => $textbook??(object)[],
        ];

        return $res;
    }

    public function pushErpInfo(){
        return $this->hasOne(OrderErpList::class,'order_id','id');
    }

    public function expressInfo(){
        return $this->hasOne(ExpressInfo::class,'id','express_info_id');
    }

    public function payRefundInfo(){
        return $this->hasOne(OrderPayRefund::class,'order_id','ordernum');
    }

    public function orderRefundLogInfo(){
        return $this->hasOne(OrderRefundLog::class,'ordernum','ordernum');
    }

    //校验下单业务是否有效
    // 5 打赏 6分享赚钱  9精品课  10直播    13能量币充值  14 线下产品(门票类)   15讲座  16新vip  17 赠送下单   18训练营
    public static function CheckAddOrder($relation_id,$order_type,$user,$os_type,$live_id){
        //$live_id 在那个直播间购买
        $uid = $user['id'];
        $phone = $user['phone'];

        if(empty($uid)){
            return ['code'=>0, 'msg'=>'用户id为空'];
        }

        //一个校验过程 进行一个if判断


        //  虚拟用户校验的类型
        if( isset($order_type) && in_array($order_type,[10, 14]) ){
            if($os_type ==3 && (empty($phone) || substr($phone,0,1) == 2) ){
                return ['code'=>4000, 'msg'=>'请修改手机号'];
            }
        }

        //  下单物品是否售空 类型  直播和线下产品
        if( isset($order_type) && in_array($order_type,[10, 14, 18]) ){
            //Push
            $push_type = 0;
            switch($order_type){
                case 10: $push_type = 9;    break;
                case 14: $push_type = 4;    break;
                case 18: $push_type = 11;   break;

            }
            $pushdata = LivePush::where(['live_id'=>$live_id,'push_type'=>$push_type,'push_gid'=>$relation_id,'is_sell_short'=>1])->first();
            if( !empty($pushdata) ){
                return ['code'=>4001, 'msg'=>'该商品已售空'];
            }

        }



        return ['code'=>true, 'msg'=>''];
    }

    //最新一条跟进记录
    public function offlineLastLog(){
        return $this->hasOne(OfflineProductsOrderLog::class,'order_id','id')
            ->orderBy('log_date','desc')
            ->orderBy('id','desc');
    }

    /**
     * get_show_image_type 根据订单类型校验显示尺寸
     *
     * @param     $order_type
     * @param int $relation_id
     *
     * @return int|string  $res_type 1 竖图  2方图  3横图
     */
    public static function get_show_image_type($order_type, $relation_id=0){
        $res_type =1;
        // if($order_type == 14){ //线下课
        //     $res_type =3;
        //     // $of_type = OfflineProducts::where("id",$relation_id)->value("type");
        //     // if($of_type != 3){
        //     //
        //     // }
        // }

        if($order_type == 10){ //直播
            $res_type = 3;
        }
        return $res_type;
    }

    /**
     * getAdminIDByLiveID   通过直播id 获取客服id
     *
     * @param $user_id
     * @param $live_id
     *
     * @return int|mixed
     */
    public static function getAdminIDByLiveID($user_id,$live_id){
        $subId = Subscribe::where([
            "type" => 3,
            "user_id"=>$user_id,
            "relation_id"=>$live_id,
            "status"=>1,
        ])->value("id");
        $admin_id = DB::table('crm_live_order')
            ->where('sub_id','=',$subId)
            ->where('status','=',1)->value("admin_id");

        return $admin_id ??0;
    }
}
