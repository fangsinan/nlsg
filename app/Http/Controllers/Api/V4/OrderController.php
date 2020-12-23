<?php


namespace App\Http\Controllers\Api\V4;


use App\Http\Controllers\Controller;
use App\Models\Column;
use App\Models\Coupon;
use App\Models\History;
use App\Models\Live;
use App\Models\LiveCountDown;
use App\Models\LiveInfo;
use App\Models\MallOrder;
use App\Models\OfflineProducts;
use App\Models\Order;
use App\Models\PayRecord;
use App\Models\Subscribe;
use App\Models\User;
use App\Models\VipUser;
use App\Models\VipUserBind;
use App\Models\Works;
use Illuminate\Http\Request;


/**
 * 下单Controller
 * 虚拟作品订单操作
 */
class OrderController extends Controller
{

    /**
     * @api {get} /api/v4/order/get_coupon   获取我的优惠券
     * @apiName get_coupon
     * @apiVersion 1.0.0
     * @apiGroup order
     *
     * @apiParam {int} user_id 用户id
     * @apiParam {int} type  类型 1专栏  2会员  5课程
     * @apiParam {int} price  订单金额
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     *
     *
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": [
     * {
     * "id": 1,
     * "name": "心智优惠券",
     * "number": "12353",
     * "type": 1,                  1专栏  2会员 3商品 4免邮券 5课程
     * "user_id": 211172,
     * "status": 1,            //0 未领取 1 未使用 2已使用 3已过期  4已删除
     * "price": "10.00",           //优惠券金额
     * "full_cut": "99.00",        //满减金额
     * "explain": "",          //描述
     * "order_id": 0,
     * "flag": "",
     * "get_way": 1,
     * "cr_id": 0,
     * "created_at": null,
     * "updated_at": null,
     * "begin_time": null,             生效时间
     * "end_time": "2020-07-28 23:59:59",  失效时间
     * "used_time": null           使用时间
     * }
     * ]
     * }
     */
    public function getCoupon(Request $request)
    {
        $price = $request->input('price', 0);
        $type = $request->input('type', 0);
        $user_id = $this->user['id'] ?? 0;//->input('user_id',0);
        $where_type = [0];
        if ($type) {
            $where_type = [0, $type];
        }
        $coupon = Coupon::where([
            'status' => 1,
            'user_id' => $user_id,
        ])->whereIn('type', $where_type)
            ->where('end_time', '>=', time())
            ->where('full_cut', '<=', $price)->get();
        return $this->success($coupon);
    }

    //下单check
    protected function addOrderCheck($user_id, $tweeter_code, $target_id, $type)
    {

        //校验用户等级
        $rst = User::getLevel($user_id);
        if ($rst > 2) {
            return ['code' => 0, 'msg' => '您已是vip用户,可免费观看'];
        }

        //校验下单用户是否关注
        $is_sub = Subscribe::isSubscribe($user_id, $target_id, $type);
        if ($is_sub) {
            return ['code' => 0, 'msg' => '您已订阅过'];
        }

        //校验推客信息有效
        $tweeter_level = User::getLevel($tweeter_code);
        if ($tweeter_level > 0) {
            //推客是否订阅
            $is_sub = Subscribe::isSubscribe($tweeter_code, $target_id, $type);
            if ($is_sub == 0) {
                $tweeter_code = 0;
            }
        } else {
            $tweeter_code = 0;
        }
        return ['code' => 1, 'tweeter_code' => $tweeter_code];

    }

    /**
     * @api {post} api/v4/order/create_column_order  专栏下单
     * @apiName create_column_order
     * @apiVersion 1.0.0
     * @apiGroup order
     *
     * @apiParam {int} column_id 专栏id
     * @apiParam {int} user_id 用户id
     * @apiParam {int} coupon_id  优惠券id 默认0
     * @apiParam {int} inviter 推客id 默认0
     * @apiParam {int} os_type os_type 1 安卓 2ios
     * @apiParam {int} live_id 直播id  直播间购买时传
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     *
     *
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": { }
     * }
     */
    public function createColumnOrder(Request $request)
    {

        $params = $request->input();
        $column_id = !empty($params['column_id']) ? intval($params['column_id']) : 0;
        $coupon_id = !empty($params['coupon_id']) ? intval($params['coupon_id']) : 0;
        $tweeter_code = !empty($params['tweeter_code']) ? intval($params['tweeter_code']) : 0;
        $os_type = !empty($params['os_type']) ? intval($params['os_type']) : 1;
        $live_id = !empty($params['live_id']) ? intval($params['live_id']) : 0;
        $pay_type = !empty($params['pay_type']) ? intval($params['pay_type']) : 0;
        $user_id = $this->user['id'] ?? 0;

        //检测下单参数有效性
        $checked = $this->addOrderCheck($user_id, $tweeter_code, $column_id, 1);
        if ($checked['code'] == 0) {
            return $this->error(0, $checked['msg']);
        }
        // 校验推客id是否有效
        $tweeter_code = $checked['tweeter_code'];


        //$column_id 专栏信息
        $column_data = Column::find($column_id);
        if (empty($column_data)) {
            return $this->error(0, '专栏不存在');
        }

        //优惠券
        $coupon_price = Coupon::getCouponMoney($coupon_id, $user_id, $column_data->price, 1);
        $type = 1;
        if ($column_data['type'] == 2) {
            $type = 15;
        }
        $ordernum = MallOrder::createOrderNumber($user_id, 3);
        $data = [
            'ordernum' => $ordernum,
            'type' => $type,
            'user_id' => $user_id,
            'relation_id' => $column_id,
            'cost_price' => $column_data->price,
            'price' => ($column_data->price - $coupon_price),
            'twitter_id' => $tweeter_code,
            'coupon_id' => $coupon_id,
            'ip' => $request->getClientIp(),
            'os_type' => $os_type,
            'live_id' => $live_id,
            'pay_type' => $pay_type,

        ];
        $order = Order::firstOrCreate($data);

        return $this->success($order['id']);
    }


    /**
     * @api {post} /api/v4/order/create_works_order  精品课下单
     * @apiName create_works_order
     * @apiVersion 1.0.0
     * @apiGroup order
     *
     * @apiParam {int} work_id 课程id
     * @apiParam {int} user_id 用户id
     * @apiParam {int} coupon_id  优惠券id 默认0
     * @apiParam {int} inviter 推客id 默认0
     * @apiParam {int} os_type os_type 1 安卓 2ios
     * @apiParam {int} live_id 直播id  直播间购买时传
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": { }
     * }
     */
    public function createWorksOrder(Request $request)
    {


        $work_id = $request->input('work_id', 0);
        $coupon_id = $request->input('coupon_id', 0);
        $tweeter_code = $request->input('inviter', 0);
        $os_type = $request->input('os_type', 0);
        $live_id = $request->input('live_id', 0);
        $pay_type = $request->input('pay_type', 0);
        $activity_tag = $request->input('activity_tag', '');
        $user_id = $this->user['id'] ?? 0;

        //$work_id 课程信息
        $works_data = Works::find($work_id);
        if (empty($works_data)) {
            return $this->error(0, '当前课程不存在');
        }
        //检测下单参数有效性
        $checked = $this->addOrderCheck($user_id, $tweeter_code, $work_id, 2);
        if ($checked['code'] == 0) {
            return $this->error(0, $checked['msg']);
        }
        // 校验推客id是否有效
        $tweeter_code = $checked['tweeter_code'];

        if ($activity_tag === 'cytx') {
            $price = $works_data->cytx_price;
            //校验用户本月是否能继续花钱
            $check_this_money = PayRecord::thisMoneyCanSpendMoney($user_id,'cytx',$price);
            if($check_this_money == 0){
                return $this->error(0,'本月已超消费金额',0);
            }
            $coupon_id = 0;
        } else {
            //优惠券
            $coupon_price = Coupon::getCouponMoney($coupon_id, $user_id, $works_data->price, 3);
            $price = $works_data->price - $coupon_price;
        }

        $ordernum = MallOrder::createOrderNumber($user_id, 3);
        $data = [
            'ordernum' => $ordernum,
            'type' => 9,
            'user_id' => $user_id,
            'relation_id' => $work_id,
            'cost_price' => $works_data->price,
            'price' => $price,
            'twitter_id' => $tweeter_code,
            'coupon_id' => $coupon_id,
            'ip' => $request->getClientIp(),
            'os_type' => $os_type,
            'live_id' => $live_id,
            'pay_type' => $pay_type,
            'activity_tag' => $activity_tag,
        ];
        $order = Order::firstOrCreate($data);
        return $this->success($order['id']);

    }


    /**
     * @api {post} /api//v4/order/create_reward_order 打赏下单
     * @apiName create_reward_order
     * @apiVersion 1.0.0
     * @apiGroup order
     *
     * @apiParam {int} relation_id 打赏类型目标id
     * @apiParam {int} user_id 用户id
     * @apiParam {int} reward  //1 鲜花 2爱心 3书籍 4咖啡  默认1
     * @apiParam {int} reward_num 数量 默认1
     * @apiParam {int} reward_type 打赏类型1专栏|讲座 2课程|听书  3想法   4百科  (每个类型只需要传对应id)
     * @apiParam {int} os_type os_type 1 安卓 2ios
     * @apiParam {int} live_id 直播id
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": { }
     * }
     */
    public function createRewardOrder(Request $request)
    {
        $work_id = $request->input('work_id', 0); // 课程id
        $column_id = $request->input('column_id', 0); // 专栏id
        $commend_id = $request->input('commend_id', 0); // 评论id
        $relation_id = $request->input('relation_id', 0);   //目标id
        //$user_id    = $request->input('user_id',0);
        $reward = $request->input('reward', 1);//1 鲜花 2爱心 3书籍 4咖啡
        $reward_num = $request->input('reward_num', 1);  //数量
        $reward_type = $request->input('reward_type', 0);  //打赏类型
        $os_type = $request->input('os_type', 0);
        $pay_type = $request->input('pay_type', 0);
        $live_pid = $request->input('live_id', 0);

        $user_id = $this->user['id'];

        //检测下单参数有效性
        if (empty($user_id)) {
            return $this->error(0, '用户id有误');
        }

        if (empty($relation_id) || $relation_id == 0) {
            return $this->error(0, '打赏目标有误');
        }

        $loginUserInfo = User::find($user_id);
        if (empty($loginUserInfo)) {
            return $this->error(0, '用户有误');
        }

        //处理订单
        //礼物 1 鲜花 1   2爱心 5.21   3书籍  18.88   4咖啡  36
        //  5 送花  1元   6比心 5元  7独角兽 10元  8跑车  58元  9飞机 88元  10火箭 188元
        $price = 1;
        switch ($reward) {
            case 1:
                $price = 1;
                break;
            case 2:
                $price = 5.21;
                break;
            case 3:
                $price = 18.88;
                break;
            case 4:
                $price = 36;
                break;
            case 5:
                $price = 1;
                break;
            case 6:
                $price = 5;
                break;
            case 7:
                $price = 10;
                break;
            case 8:
                $price = 58;
                break;
            case 9:
                $price = 88;
                break;
            case 10:
                $price = 188;
                break;
        }


        $ordernum = MallOrder::createOrderNumber($user_id, 3);
        $data = [
            'ordernum' => $ordernum,
            'type' => 5,
            'user_id' => $user_id,
            'relation_id' => $relation_id,
            'price' => $price * $reward_num,
            'reward' => $reward,
            'reward_num' => $reward_num,
            'reward_type' => $reward_type,
            'ip' => $request->getClientIp(),
            'os_type' => $os_type,
            'pay_type' => $pay_type,
            'live_id' => $live_pid,
        ];
        $order = Order::firstOrCreate($data);
        return $this->success($order['id']);

    }


    /**
     * @api {post} /api//v4/order/create_coin_order //能量币充值（ios支付使用）
     * @apiName create_coin_order
     * @apiVersion 1.0.0
     * @apiGroup order
     *
     * @apiParam {int} coin_id    能量币代码 如：merchant.NLSGApplePay.6nlb
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": { }
     * }
     */
    function createCoinOrder(Request $request)
    {

        $coin_arr = Config('web.coin_arr');
        $user_id = $this->user['id'] ?? 0;
        $coin_id = $request->input('coin_id', '');

        if (empty($user_id)) {
            return $this->error(0, '用户id有误');
        }

        $price = $coin_arr[$coin_id];
        if (empty($coin_id) || empty($coin_arr[$coin_id])) {
            return $this->error(0, '产品id有误');
        }
        $loginUserInfo = User::find($user_id);
        if (empty($loginUserInfo)) {
            return $this->error(0, '用户有误');
        }

        //处理订单
        $ordernum = MallOrder::createOrderNumber($user_id, 3);
        $data = [
            'ordernum' => $ordernum,
            'type' => 13,
            'user_id' => $user_id,
            'price' => $price,        //打赏金额
            'pay_type' => 4,   //1 微信端 2app微信 3app支付宝 4ios
            'os_type' => 2,    //只有ios支持能量币
        ];

        $rst = Order::firstOrCreate($data);
        if ($rst) {
            return $this->success($data['ordernum']);
        } else {
            return $this->error(0, '添加失败');
        }
    }


    /**
     * @api {get} /api/v4/order/order_list  订单列表
     * @apiName order_list
     * @apiVersion 1.0.0
     * @apiGroup order
     *
     * @apiParam {int} type 类型  专栏 2 会员  3充值  4财务打款 5 打赏 6分享赚钱 7支付宝提现 8微信提现  9精品课  10直播    13能量币充值  14 线下产品(门票类)   15讲座  16新360会员 17赠送下单
     * @apiParam {int} status 0待支付  1 已支付 2全部
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     *
     *
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": {
     * "current_page": 1,
     * "data": [
     * {
     * "id": 58,
     * "type": 15,   类型  1、专栏  9、课程  15讲座
     * "relation_id": 1,   对应id
     * "user_id": 211172,
     * "status": 1,        0待支付 1 支付
     * "price": "10.00",       金额
     * "pay_price": "0.01",        实际支付金额
     * "coupon_id": 0,     优惠券id
     * "pay_time": null,       支付时间
     * "ordernum": "20200709104916",   订单号
     * "relation_data": [
     * {
     * "id": 1,
     * "name": "王琨专栏",
     * "title": "顶尖导师 经营能量",
     * "subtitle": "顶尖导师 经营能量",
     * "message": "",
     * "price": "99.00",
     * "cover_pic": "/wechat/works/video/161627/2017121117503851065.jpg",
     * "is_new": 1
     * }
     * ]
     * },
     * {
     * "id": 45,
     * "type": 9,
     * "relation_id": 16,
     * "user_id": 211172,
     * "status": 1,
     * "price": "10.00",
     * "pay_price": "0.00",
     * "coupon_id": 0,
     * "pay_time": null,
     * "ordernum": "20200708114026",
     * "relation_data": [
     * {
     * "id": 16,
     * "user_id": 168934,
     * "title": "如何经营幸福婚姻",
     * "cover_img": "/nlsg/works/20190822150244797760.png",
     * "subtitle": "",
     * "price": "29.90",
     * "user": {
     * "id": 168934,
     * "nickname": "chandler"
     * },
     * "is_new": 1,
     * "is_free": 1
     * }
     * ]
     * },
     * {
     * "id": 3,
     * "type": 1,
     * "relation_id": 1,
     * "user_id": 211172,
     * "status": 1,
     * "price": "99.00",
     * "pay_price": "0.01",
     * "coupon_id": 0,
     * "pay_time": null,
     * "ordernum": "202005231631148119",
     * "relation_data": [
     * {
     * "id": 1,
     * "name": "王琨专栏",
     * "title": "顶尖导师 经营能量",
     * "subtitle": "顶尖导师 经营能量",
     * "message": "",
     * "price": "99.00",
     * "cover_pic": "/wechat/works/video/161627/2017121117503851065.jpg",
     * "is_new": 1
     * }
     * ]
     * }
     * ],
     * "first_page_url": "http://nlsgv4.com/api/v4/order/order_list?page=1",
     * "from": 1,
     * "last_page": 1,
     * "last_page_url": "http://nlsgv4.com/api/v4/order/order_list?page=1",
     * "next_page_url": null,
     * "path": "http://nlsgv4.com/api/v4/order/order_list",
     * "per_page": 50,
     * "prev_page_url": null,
     * "to": 3,
     * "total": 3
     * }
     * }
     */
    public function orderList(Request $request)
    {
        $user_id = $this->user['id'] ?? 0;
        $type = $request->input('type', 0);
        $status = $request->input('status', 2);
        $where = ['user_id' => $user_id,];

        if ($type > 0) {
            $where = ['user_id' => $user_id, 'type' => $type];
        }

        $OrderObj = Order::select('id', 'type', 'relation_id', 'user_id', 'status','cost_price', 'price', 'pay_price', 'coupon_id', 'pay_time', 'ordernum', 'created_at', 'send_type', 'send_user_id')
            ->whereIn('type', [1,  9, 10, 13, 14, 15, 16, 17])
            ->where($where);

        //  订单状态
        if ($status == 2) {
            $OrderObj->whereIn('status', [0, 1]);
        } else {
            $OrderObj->where('status', $status);
        }

        $list = $OrderObj->orderBy('updated_at', 'desc')->paginate($this->page_per_page)->toArray();


        $data = $list['data'];
        foreach ($data as $key => $val) {

            $result = Order::getInfo($val['type'], $val['relation_id'], $val['send_type'], $user_id);

            if ($val['send_user_id'] > 0) {
                $userData = User::select('phone')->where(['id' => $val['send_user_id']])->first()->toArray();
                $data[$key]['send_user_phone'] = $userData['phone'];
            }
            if ($result == false) {
                $data[$key]['relation_data'] = [];
            } else {
                $data[$key]['relation_data'] = $result;
            }

            $data[$key]['created_time'] = strtotime($val['created_at']);
            $data[$key]['end_time'] = $data[$key]['created_time'] + 1800;

        }

        return $this->success($data);

    }


    /**
     * @api {get} /api/v4/order/order_detail  订单详情
     * @apiName order_detail
     * @apiVersion 1.0.0
     * @apiGroup order
     *
     * @apiParam {int} user_id 用户id
     * @apiParam {int} id  订单id
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": {
     * "id": 3,
     * "type": 1,              类型  1、专栏  9、课程  15讲座
     * "relation_id": 1,   对应的id
     * "user_id": 211172,
     * "status": 1,  0待支付  1 已支付  2取消【不展示】
     * "price": "99.00",    金额
     * "pay_price": "0.01",    实际支付金额
     * "coupon_id": 0,     优惠券id
     * "pay_time": null,  支付时间
     * "ordernum": "202005231631148119", 订单号OA
     * "created_at": "2020-07-01 10:44:35",  下单时间
     * "coupon_price": 0,  优惠券金额
     * "relation_data": [    内容信息
     * {
     * "id": 1,
     * "name": "王琨专栏",
     * "title": "顶尖导师 经营能量",
     * "subtitle": "顶尖导师 经营能量",
     * "message": "",
     * "price": "99.00",
     * "cover_pic": "/wechat/works/video/161627/2017121117503851065.jpg",
     * "is_new": 1
     * }
     * ]
     * }
     * }
     */
    public function orderDetail(Request $request)
    {
        $user_id = $this->user['id'] ?? 0;
        $order_id = $request->input('id', 0);
        $data = Order::select('id', 'type', 'relation_id', 'user_id', 'status','cost_price', 'price', 'pay_price', 'coupon_id', 'pay_time', 'ordernum', 'created_at', 'pay_type', 'send_type', 'send_user_id')
            ->where(['id' => $order_id, 'user_id' => $user_id])->first()->toArray();

        //查询优惠券金额
        $coupon = Coupon::find($data['coupon_id']);
        $data['coupon_price'] = $coupon['price'] ?? 0;
        //购买的内容详情

        $result = Order::getInfo($data['type'], $data['relation_id'], $data['send_type'], $user_id);

        if ($data['send_user_id'] > 0) {
            $userData = User::select('phone')->where(['id' => $data['send_user_id']])->first()->toArray();
            $data['send_user_phone'] = $userData['phone'];
        }


        if ($result == false) {
            $data['relation_data'] = [];
        } else {
            $data['relation_data'] = $result;
        }

        $data['created_time'] = strtotime($data['created_at']);
        $data['end_time'] = $data['created_time'] + 1800;
        return $this->success($data);

    }


    /**
     * @api {get} /api/v4/order/close_order  取消订单
     * @apiName close_order
     * @apiVersion 1.0.0
     * @apiGroup order
     *
     * @apiParam {int} user_id 用户id
     * @apiParam {int} id  订单id
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": {}
     * }
     */
    public function closeOrder(Request $request)
    {
        $user_id = $this->user['id'] ?? 0;
        $order_id = $request->input('id', 0);

        $data = Order::where(['id' => $order_id, 'user_id' => $user_id,])->first();
        if (empty($data)) {
            return $this->error(0, '订单错误');
        }

        Order::where([
            'id' => $order_id,
            'user_id' => $user_id,
        ])->update(['status' => 2]);
        return $this->success();

    }


    /**
     * @api {get} /api/v4/order/get_subscribe  我的-已购
     * @apiName get_subscribe
     * @apiVersion 1.0.0
     * @apiGroup order
     *
     * @apiParam {int} user_id 用户id
     * @apiParam {int} type  1 专栏  2作品 3直播  4会员 5线下产品  6讲座
     * @apiParam {int} is_audio_book  当type == 2(作品)时  0课程  1 听书  2全部
     *
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": {}
     * }
     */
    public function getSubscribe(Request $request)
    {
        $user_id = $this->user['id'] ?? 0;
        $type = $request->input('type', 1);
        $is_audio_book = $request->input('is_audio_book', 2);
        $data = Subscribe::where(['type' => $type, 'user_id' => $user_id,])->paginate($this->page_per_page)->toArray();
        $data = $data['data'];


        foreach ($data as $key => $val) {
            switch ($val['type']) {
                case 1:
                    $model = new Column();
                    $result = $model->getIndexColumn([$val['relation_id']],0);
                    break;
                case 2:
                    $model = new Works();
                    $result = $model->getIndexWorks([$val['relation_id']], $is_audio_book, $user_id,0);
                    break;
                case 6:
                    $model = new Column();
                    $result = $model->getIndexColumn([$val['relation_id']],0);
                    break;
            }
            if ($result == false) {
                unset($data[$key]);
            } else {
                switch ($val['type']){
                    case 1:
                        $hist_type = 1;
                        break;
                    case 2:
                        if($result[0]['is_audio_book'] == 0){
                            $hist_type = 4; // 课程
                        }else{
                            $hist_type = 3;
                        }
                        break;
                    case 6:
                        $hist_type = 2;
                        break;

                }
                //学至最新章节
                $result[0]['historyData'] = History::getHistoryData($result[0]['id'], $hist_type, $user_id);



                if ($val['type'] == 2) {
                    //专栏头衔
                    $column = Column::find($result[0]['column_id']);
                    $result[0]['column_title'] = $column['title'];
//                    //学至最新章节
//                    $history_data = History::getHistoryData($result[0]['id'], 2, $user_id);
//                    $result[0]['info_introduce'] = '';
//                    if ((array)($history_data)) {
//                        $result[0]['info_introduce'] = $history_data['introduce'] ?? '';
//                    }
                }
                $data[$key]['relation_data'] = $result;


            }
        }
        $data = array_values($data);

        return $this->success($data);

    }

    /**
     * @api {get} api/v4/order/reward/user 鼓励列表
     * @apiVersion 4.0.0
     * @apiName  getRewardUser
     * @apiGroup Order
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/order/reward/user
     * @apiParam {number} id 相关id
     * @apiParam {number} type 类型 3想法 4百科
     *
     * @apiSuccess {string} reward_num 数量
     * @apiSuccess {string} user    送花的用户
     * @apiSuccess {string} user.nickname   送花的用户昵称
     * @apiSuccess {string} user.headimg    送花的用户头像
     *
     * @apiSuccessExample  Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "code": 200,
     *       "msg" : '成功',
     *     "data": {
     *     }
     *
     */
    public function getRewardUser(Request $request)
    {
        $id = $request->input('id');
        $type = $request->input('type') ?? 3;

        $lists = Order::with('user:id,nickname,headimg')
            ->select('id', 'user_id', 'reward_num', 'pay_price')
            ->where(['type' => 5, 'reward_type' => $type, 'status' => 1,'relation_id' => $id])
            ->groupBy('user_id')
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->toArray();

        return success($lists['data']);
    }


    /**
     * @api {post} /api//v4/order/create_send_order 赠送课程下单
     * @apiName create_send_order
     * @apiVersion 1.0.0
     * @apiGroup order
     *
     * @apiParam {int} relation_id 目标id
     * @apiParam {int} send_type   目标类型   1 专栏  2课程|听书    6讲座
     * @apiParam {int} os_type os_type 1 安卓 2ios
     * @apiParam {int} live_id 直播id
     * @apiParam {int} remark 增言
     * @apiParam {int} coupon_id 优惠券id
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": { }
     * }
     */
    public function createSendOrder(Request $request)
    {
        $relation_id = $request->input('relation_id', 0);   //目标id
        $send_type = $request->input('send_type', 0);   //目标类型   1 专栏  2课程|听书    6讲座
        $os_type = $request->input('os_type', 0);
        $pay_type = $request->input('pay_type', 0);
        $live_id = $request->input('live_id', 0);
        $remark = $request->input('remark', '');
        $coupon_id = $request->input('coupon_id', 0);

        $user_id = $this->user['id'];

        //检测下单参数有效性
        if (empty($user_id)) {
            return $this->error(0, '用户id有误');
        }

        $loginUserInfo = User::find($user_id);
        if (empty($loginUserInfo)) {
            return $this->error(0, '用户有误');
        }

        if ($send_type == 1 || $send_type == 6) {
            //$column_id 专栏信息
            $column_data = Column::find($relation_id);
            if (empty($column_data)) {
                return $this->error(0, '专栏或讲座不存在');
            }
            $price = $column_data->price;
        } else if ($send_type == 2 ) {
            $works_data = Works::find($relation_id);
            if (empty($works_data)) {
                return $this->error(0, '当前课程不存在');
            }
            $price = $works_data->price;
        } else {
            return $this->error(0, '参数信息错误');

        }

        $add_order_type = $send_type ;
//        switch ($send_type){
//            case 2:
//                $add_order_type = 6;
//                break;
//            case 3:
//                $add_order_type = 2;
//                break;
//            case 4:
//                $add_order_type = 2;
//                break;
//        };


        //优惠券
        $coupon_price = Coupon::getCouponMoney($coupon_id, $user_id, $price, 6);

        if ($coupon_price == 0) {
            $coupon_id = 0;
        }

        $ordernum = MallOrder::createOrderNumber($user_id, 3);
        $data = [
            'ordernum' => $ordernum,
            'type' => 17,
            'user_id' => $user_id,
            'relation_id' => $relation_id,
            'cost_price' => $price,
            'price' => ($price - $coupon_price),
            'ip' => $request->getClientIp(),
            'os_type' => $os_type,
            'coupon_id' => $coupon_id,
            'pay_type' => $pay_type,
            'live_id' => $live_id,
            'send_type' => $add_order_type,
            'remark' => $remark,
        ];
        $order = Order::firstOrCreate($data);
        return $this->success($order['id']);

    }


    /**
     * @api {post} /api//v4/order/create_new_vip_order 幸福360下单
     * @apiName create_new_vip_order
     * @apiVersion 1.0.0
     * @apiGroup order
     *
     * @apiParam {int} level      1 360会员  2钻石合伙人
     * @apiParam {int} os_type os_type 1 安卓 2ios
     * @apiParam {int} live_id 直播id
     * @apiParam {int} inviter 推客id
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": { }
     * }
     */
    public function createNewVipOrder(Request $request)
    {
        $level = $request->input('level', 1);   //目标id
        $os_type = $request->input('os_type', 0);
        $live_id = $request->input('live_id', 0);
        $tweeter_code = $request->input('inviter', 0);  //推客id
        $user_id = $this->user['id'];


        //检测下单参数有效性
        if (empty($user_id)) {
            return $this->error(0, '用户id有误');
        }

        /*********************** 校验推客身份   *********************/
        //先校验直播预约的tweeter_code
        if ($live_id) {
            $info = LiveCountDown::where(['user_id' => $user_id, 'live_id' => $live_id,])->get('new_vip_uid');
            if (!empty($info->new_vip_uid) && $info->new_vip_uid > 0) {
                $vip_check = VipUser::where(['status' => 1, 'is_default' => 1, 'user_id' => $info->new_vip_uid])->get()->toArray();
                if ($vip_check) {
                    $tweeter_code = $info['new_vip_uid'];
                }
            }
        }


        //新会员关系保护
        $remark = '';
        $bind_user_id = VipUserBind::getBindParent($this->user['phone']);
        if ($bind_user_id == -1) {
            $remark = $tweeter_code . '->' . 0;
            $tweeter_code = 0;
        } else {
            if ($bind_user_id != 0 && $tweeter_code !== $bind_user_id) {
                $remark = $tweeter_code . '->' . $bind_user_id;
                $tweeter_code = $bind_user_id;
            }
        }


        //判断推客身份是否过期

        if (!empty($tweeter_code)) {
            $is_vip = VipUser::IsNewVip($tweeter_code);
            if(!$is_vip){
                $tweeter_code = 0;
            }
        }

//        if (!empty($tweeter_code)) {
//            // 钻石合伙人自己推广自己可以返佣!!!  其他不可以
//            //如果自己推广自己   必须是钻石合伙人
//            if ($tweeter_code == $user_id && $this->user['new_vip']['level'] < 2) {
//                $tweeter_code = 0;
//            }
//            dd($this->user);
//
//            //不是自己推广自己   必须身份 > 0
//            if ($tweeter_code != $user_id && $this->user['new_vip']['level'] < 1) {
//                $tweeter_code = 0;
//            }
//        }

        /*********************** 校验推客身份   *********************/


        if (!in_array($level, [1, 2])) {
            return $this->error(0, 'vip类型有误');
        }

        if ($level == 1) {
            $price = 360;
        } else {
            $price = 1000;
        }


        $type = 1;
        if ($this->user['new_vip']['level'] > 0) { //续费
            if ($level == 1) { //360 会员
                $type = 2;
            }
        }


        $ordernum = MallOrder::createOrderNumber($user_id, 3);
        $data = [
            'ordernum' => $ordernum,
            'type' => 16,
            'user_id' => $user_id,
            'relation_id' => $level,
            'price' => $price,
            'cost_price' => $price,

            'ip' => $request->getClientIp(),
            'os_type' => $os_type,
            'live_id' => $live_id,
            'vip_order_type' => $type,  //1开通 2续费 3升级
            'remark' => $remark,
            'twitter_id' => $tweeter_code,
        ];

        $order = Order::firstOrCreate($data);
        return $this->success($order['id']);

    }


    /**
     * @api {post} /api//v4/order/create_products_order 线下产品下单
     * @apiName create_products_order
     * @apiVersion 1.0.0
     * @apiGroup order
     *
     * @apiParam {int} product_id      产品id
     * @apiParam {int} os_type os_type 1 安卓 2ios
     * @apiParam {int} live_id 直播id
     * @apiParam {int} inviter 推客id
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": { }
     * }
     */
    public function createProductsOrder(Request $request)
    {
        $product_id = $request->input('product_id', 1);   //目标id
        $os_type = $request->input('os_type', 0);
        $live_id = $request->input('live_id', 0);
        $tweeter_code = $request->input('inviter', 0);  //推客id
        $num = $request->input('num', 0);  //推客id
        $user_id = $this->user['id'];



        $ProductInfo = OfflineProducts::find($product_id);
        //检测下单参数有效性
        if (empty($ProductInfo)) {
            return $this->error(0, '产品id有误');
        }

        $price = $ProductInfo['price'];

        $ordernum = MallOrder::createOrderNumber($user_id, 3);
        $data = [
            'ordernum' => $ordernum,
            'type' => 14,
            'user_id' => $user_id,
            'relation_id' => $product_id,
            'cost_price' => $price,
            'price' => $price,
            'ip' => $request->getClientIp(),
            'os_type' => $os_type,
            'live_id' => $live_id,
            'twitter_id' => $tweeter_code,
        ];

        $order = Order::firstOrCreate($data);
        return $this->success($order['id']);

    }

}
