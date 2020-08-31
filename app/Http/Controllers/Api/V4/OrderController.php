<?php


namespace App\Http\Controllers\Api\V4;


use App\Http\Controllers\Controller;
use App\Models\Column;
use App\Models\Coupon;
use App\Models\History;
use App\Models\MallOrder;
use App\Models\Order;
use App\Models\Subscribe;
use App\Models\User;
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
    {
    "code": 200,
    "msg": "成功",
    "data": [
    {
    "id": 1,
    "name": "心智优惠券",
    "number": "12353",
    "type": 1,                  1专栏  2会员 3商品 4免邮券 5课程
    "user_id": 211172,
    "status": 1,            //0 未领取 1 未使用 2已使用 3已过期  4已删除
    "price": "10.00",           //优惠券金额
    "full_cut": "99.00",        //满减金额
    "explain": "",          //描述
    "order_id": 0,
    "flag": "",
    "get_way": 1,
    "cr_id": 0,
    "created_at": null,
    "updated_at": null,
    "begin_time": null,             生效时间
    "end_time": "2020-07-28 23:59:59",  失效时间
    "used_time": null           使用时间
    }
    ]
    }
     */
    public function getCoupon(Request $request){
        $price = $request->input('price',0);
        $type  = $request->input('type',0);
        $user_id = $this->user['id'] ?? 0;//->input('user_id',0);
        $where_type = [0];
        if($type){
            $where_type = [0, $type];
        }
        $coupon = Coupon::where([
           'status' => 1,
           'user_id'=> $user_id,
        ])->whereIn('type',$where_type)
        ->where('end_time','>=',time())
        ->where('full_cut','<=',$price)->get();
        return $this->success($coupon);
    }

    //下单check
    protected function addOrderCheck($user_id,$tweeter_code,$target_id,$type){

        //校验用户等级
        $rst = User::getLevel($user_id);
        if ( $rst > 2 ) {
            return ['code'=>0,'msg'=>'您已是vip用户,可免费观看'];
        }

        //校验下单用户是否关注
        $is_sub = Subscribe::isSubscribe($user_id,$target_id,$type);
        if ($is_sub) {
            return ['code'=>0,'msg'=>'您已订阅过'];
        }

        //校验推客信息有效
        $tweeter_level = User::getLevel($tweeter_code);
        if( $tweeter_level > 0 ){
            //推客是否订阅
            $is_sub = Subscribe::isSubscribe($tweeter_code,$target_id,$type);
            if($is_sub == 0){
                $tweeter_code = 0;
            }
        }else{
            $tweeter_code = 0;
        }
        return ['code'=>1, 'tweeter_code'=>$tweeter_code];

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
     * @apiParam {int} tweeter_code 推客id 默认0
     * @apiParam {int} os_type os_type 1 安卓 2ios
     * @apiParam {int} live_id 直播id  直播间购买时传
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     *
     *
        {
        "code": 200,
        "msg": "成功",
        "data": { }
        }
     */
    public function createColumnOrder(Request $request) {

        $params = $request->input();
        $column_id  = !empty($params['column_id']) ? intval($params['column_id']) : 0;
        $user_id    = !empty($params['user_id']) ? intval($params['user_id']) : 0;
        $coupon_id  = !empty($params['coupon_id']) ? intval($params['coupon_id']) : 0;
        $tweeter_code = !empty($params['tweeter_code']) ? intval($params['tweeter_code']) : 0;
        $os_type    = !empty($params['os_type']) ? intval($params['os_type']) : 1;
        $live_id    = !empty($params['live_id']) ? intval($params['live_id']) : 0;

        //检测下单参数有效性
        $checked = $this->addOrderCheck($user_id,$tweeter_code,$column_id,1);
        if( $checked['code'] == 0 ){
            return $this->error(0,$checked['msg']);
        }
        // 校验推客id是否有效
        $tweeter_code = $checked['tweeter_code'];


        //$column_id 专栏信息
        $column_data = Column::find($column_id);
        if(empty($column_data)){
            return $this->error(0,'专栏不存在');
        }

        //优惠券
        $coupon_price = Coupon::getCouponMoney($coupon_id,$user_id,$column_data->price,1);
        $type = 1;
        if($column_data['type'] == 2){
            $type = 15;
        }
        $ordernum = MallOrder::createOrderNumber($user_id,3);
        $data=[
            'ordernum'      => $ordernum,
            'type'          => $type,
            'user_id'       => $user_id,
            'relation_id'   => $column_id,
            'cost_price'    => $column_data->price,
            'price'         => ($column_data->price-$coupon_price),
            'twitter_id'    => $tweeter_code,
            'coupon_id'     => $coupon_id,
            'ip'            => $request->getClientIp(),
            'os_type'       => $os_type,
            'live_id'       => $live_id,

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
     * @apiParam {int} tweeter_code 推客id 默认0
     * @apiParam {int} os_type os_type 1 安卓 2ios
     * @apiParam {int} live_id 直播id  直播间购买时传
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
    {
        "code": 200,
        "msg": "成功",
        "data": { }
    }
     */
    public function createWorksOrder(Request $request) {


        $work_id    = $request->input('work_id',0);
        $coupon_id  = $request->input('coupon_id',0);
        $tweeter_code = $request->input('tweeter_code',0);
        $user_id    = $request->input('user_id',0);
        $os_type    = $request->input('os_type',0);
        $live_id    = $request->input('live_id',0);

        //$work_id 课程信息
        $works_data = Works::find($work_id);
        if(empty($works_data)){
            return $this->error(0,'当前课程不存在');
        }
        //检测下单参数有效性
        $checked = $this->addOrderCheck($user_id,$tweeter_code,$work_id,2);
        if( $checked['code'] == 0 ){
            return $this->error(0,$checked['msg']);
        }
        // 校验推客id是否有效
        $tweeter_code = $checked['tweeter_code'];

        //优惠券
        $coupon_price = Coupon::getCouponMoney($coupon_id,$user_id,$works_data->price,3);

        $ordernum = MallOrder::createOrderNumber($user_id,3);
        $data=[
            'ordernum'      => $ordernum,
            'type'          => 9,
            'user_id'       => $user_id,
            'relation_id'   => $work_id,
            'cost_price'    => $works_data->price,
            'price'         => ($works_data->price-$coupon_price),
            'twitter_id'    => $tweeter_code,
            'coupon_id'     => $coupon_id,
            'ip'            => $request->getClientIp(),
            'os_type'       => $os_type,
            'live_id'       => $live_id,
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
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
    {
    "code": 200,
    "msg": "成功",
    "data": { }
    }
     */
    public function createRewardOrder(Request $request)
    {
        $work_id    = $request->input('work_id',0); // 课程id
        $column_id  = $request->input('column_id',0); // 专栏id
        $commend_id = $request->input('commend_id',0); // 评论id


        $relation_id  = $request->input('relation_id',0);   //目标id

        //$user_id    = $request->input('user_id',0);
        $reward     = $request->input('reward',1);//1 鲜花 2爱心 3书籍 4咖啡
        $reward_num = $request->input('reward_num',1);  //数量
        $reward_type= $request->input('reward_type',0);  //打赏类型
        $os_type    = $request->input('os_type',0);

        $user_id    = $this->user['id'];

        //检测下单参数有效性
        if ( empty($user_id) ) {
            return $this->error(0,'用户id有误');
        }

//        switch ($reward_type) {
//            case 1:$relation_id = $column_id;break;
//            case 2:$relation_id = $work_id;break;
//            case 3:$relation_id = $commend_id;break;
//        }
        if ( empty($relation_id) || $relation_id == 0 ) {
            return $this->error(0,'打赏目标有误');
        }

        $loginUserInfo = User::find($user_id);
        if (empty($loginUserInfo)) {
            return $this->error(0,'用户有误');
        }

        //处理订单

        $price = 1;
        switch ($reward) {
            case 1:$price = 1;
                break;
            case 2:$price = 5.21;
                break;
            case 3:$price = 18.88;
                break;
            case 4:$price = 36;
                break;
        }


        $ordernum = MallOrder::createOrderNumber($user_id,3);
        $data = [
            'ordernum'      => $ordernum,
            'type'          => 5,
            'user_id'       => $user_id,
            'relation_id'   => $relation_id,
            'price'         => $price * $reward_num,
            'reward'        => $reward,
            'reward_num'    => $reward_num,
            'reward_type'   => $reward_type,
            'ip'            => $request->getClientIp(),
            'os_type'       => $os_type,
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
    {
    "code": 200,
    "msg": "成功",
    "data": { }
    }
     */
    function createCoinOrder(Request $request) {

        $coin_arr = Config('web.coin_arr');
        $user_id = $this->user['id'] ?? 0;
        $coin_id = $request->input('coin_id','');

        if (empty($user_id)) {
            return $this->error(0,'用户id有误');

        }

        $price = $coin_arr[$coin_id];
        if (empty($coin_id) || empty($coin_arr[$coin_id])) {
            return $this->error(0,'产品id有误');
        }
        $loginUserInfo = User::find($user_id);
        if (empty($loginUserInfo)) {
            return $this->error(0,'用户有误');
        }

        //处理订单
        $ordernum = MallOrder::createOrderNumber($user_id,3);
        $data = [
            'ordernum'      => $ordernum,
            'type' => 13,
            'user_id' => $user_id,
            'price' => $price,        //打赏金额
            'pay_type'=>4,   //1 微信端 2app微信 3app支付宝 4ios
            'os_type'=>2,    //只有ios支持能量币
        ];

        $rst = Order::firstOrCreate($data);
        if ($rst) {
            return $this->success($data['ordernum']);
        } else {
            return $this->error(0,'添加失败');
        }
    }




    /**
     * @api {get} /api/v4/order/order_list  订单列表
     * @apiName order_list
     * @apiVersion 1.0.0
     * @apiGroup order
     *
     * @apiParam {int} type 类型  专栏 2 会员  3充值  4财务打款 5 打赏 6分享赚钱 7支付宝提现 8微信提现  9精品课  10直播    13能量币充值  14 线下产品(门票类)   15讲座
     * @apiParam {int} status 0待支付  1 已支付 2全部
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:


    {
    "code": 200,
    "msg": "成功",
    "data": {
    "current_page": 1,
    "data": [
    {
    "id": 58,
    "type": 15,   类型  1、专栏  9、课程  15讲座
    "relation_id": 1,   对应id
    "user_id": 211172,
    "status": 1,        0待支付 1 支付
    "price": "10.00",       金额
    "pay_price": "0.01",        实际支付金额
    "coupon_id": 0,     优惠券id
    "pay_time": null,       支付时间
    "ordernum": "20200709104916",   订单号
    "relation_data": [
    {
    "id": 1,
    "name": "王琨专栏",
    "title": "顶尖导师 经营能量",
    "subtitle": "顶尖导师 经营能量",
    "message": "",
    "price": "99.00",
    "cover_pic": "/wechat/works/video/161627/2017121117503851065.jpg",
    "is_new": 1
    }
    ]
    },
    {
    "id": 45,
    "type": 9,
    "relation_id": 16,
    "user_id": 211172,
    "status": 1,
    "price": "10.00",
    "pay_price": "0.00",
    "coupon_id": 0,
    "pay_time": null,
    "ordernum": "20200708114026",
    "relation_data": [
    {
    "id": 16,
    "user_id": 168934,
    "title": "如何经营幸福婚姻",
    "cover_img": "/nlsg/works/20190822150244797760.png",
    "subtitle": "",
    "price": "29.90",
    "user": {
    "id": 168934,
    "nickname": "chandler"
    },
    "is_new": 1,
    "is_free": 1
    }
    ]
    },
    {
    "id": 3,
    "type": 1,
    "relation_id": 1,
    "user_id": 211172,
    "status": 1,
    "price": "99.00",
    "pay_price": "0.01",
    "coupon_id": 0,
    "pay_time": null,
    "ordernum": "202005231631148119",
    "relation_data": [
    {
    "id": 1,
    "name": "王琨专栏",
    "title": "顶尖导师 经营能量",
    "subtitle": "顶尖导师 经营能量",
    "message": "",
    "price": "99.00",
    "cover_pic": "/wechat/works/video/161627/2017121117503851065.jpg",
    "is_new": 1
    }
    ]
    }
    ],
    "first_page_url": "http://nlsgv4.com/api/v4/order/order_list?page=1",
    "from": 1,
    "last_page": 1,
    "last_page_url": "http://nlsgv4.com/api/v4/order/order_list?page=1",
    "next_page_url": null,
    "path": "http://nlsgv4.com/api/v4/order/order_list",
    "per_page": 50,
    "prev_page_url": null,
    "to": 3,
    "total": 3
    }
    }

     */
    public function orderList(Request $request){
        $user_id = $this->user['id']??0;
        $type    = $request->input('type',0);
        $status    = $request->input('status',2);
        $where =['user_id' =>$user_id, ];

        if($type > 0 ){
            $where =['user_id' =>$user_id,'type'=>$type ];
        }

        $OrderObj = Order::select( 'id','type','relation_id','user_id','status','price','pay_price','coupon_id', 'pay_time','ordernum','created_at')
            ->whereIn('type', [1, 5, 9, 10, 13, 15])
            ->where($where);

        //  订单状态
        if($status == 2){
            $OrderObj->whereIn('status', [0, 1]);
        }else{
            $OrderObj->where('status',$status);
        }

        $list = $OrderObj->orderBy('updated_at','desc')->paginate($this->page_per_page)->toArray();


        $data = $list['data'];
        foreach ($data as $key=>$val){
            $result = false;
            switch ($val['type']) {
                case 1:
                    $model = new Column();
                    $result = $model->getIndexColumn([$val['relation_id']]);
                    break;
                case 9:
                    $model = new Works();
                    $result = $model->getIndexWorks([$val['relation_id']],2);
                    break;
                case 15:
                    $model = new Column();
                    $result = $model->getIndexColumn([$val['relation_id']]);
                    break;
            }
            if($result == false){
                $data[$key]['relation_data'] = [];
            }else{
                $data[$key]['relation_data'] = $result;
            }


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
    {
    "code": 200,
    "msg": "成功",
    "data": {
    "id": 3,
    "type": 1,              类型  1、专栏  9、课程  15讲座
    "relation_id": 1,   对应的id
    "user_id": 211172,
    "status": 1,  0待支付  1 已支付  2取消【不展示】
    "price": "99.00",    金额
    "pay_price": "0.01",    实际支付金额
    "coupon_id": 0,     优惠券id
    "pay_time": null,  支付时间
    "ordernum": "202005231631148119", 订单号OA
    "created_at": "2020-07-01 10:44:35",  下单时间
    "coupon_price": 0,  优惠券金额
    "relation_data": [    内容信息
    {
    "id": 1,
    "name": "王琨专栏",
    "title": "顶尖导师 经营能量",
    "subtitle": "顶尖导师 经营能量",
    "message": "",
    "price": "99.00",
    "cover_pic": "/wechat/works/video/161627/2017121117503851065.jpg",
    "is_new": 1
    }
    ]
    }
    }
     */
    public function orderDetail(Request $request){
        $user_id    = $request->input('user_id',0);
        $order_id    = $request->input('id',0);
        $data = Order::select( 'id','type','relation_id','user_id','status','price','pay_price','coupon_id', 'pay_time','ordernum','created_at')
            ->where(['id' =>$order_id, 'user_id'=>$user_id])->first()->toArray();

        //查询优惠券金额
        $coupon = Coupon::find($data['coupon_id']);
        $data['coupon_price'] = $coupon['price']??0;
        //购买的内容详情
        $result = false;
        switch ($data['type']) {
            case 1:
                $model = new Column();
                $result = $model->getIndexColumn([$data['relation_id']]);
                break;
            case 9:
                $model = new Works();
                $result = $model->getIndexWorks([$data['relation_id']], 2);
                break;
            case 15:
                $model = new Column();
                $result = $model->getIndexColumn([$data['relation_id']]);
                break;
        }
        if($result == false){
            $data['relation_data'] = [];
        }else{
            $data['relation_data'] = $result;
        }



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
    {
    "code": 200,
    "msg": "成功",
    "data": {}
    }
     */
    public function closeOrder(Request $request){
        $user_id = $this->user['id']??0;
        $order_id    = $request->input('id',0);

        $data = Order::where(['id'  => $order_id, 'user_id' => $user_id,])->first();
        if(empty($data)){
            return $this->error(0,'订单错误');
        }

        Order::where([
            'id'        =>  $order_id,
            'user_id'   =>  $user_id,
        ])->update(['status' => 2 ]);
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
    {
    "code": 200,
    "msg": "成功",
    "data": {}
    }
     */
    public function getSubscribe(Request $request){
        $user_id = $this->user['id']??0;
        $type    = $request->input('type',1);
        $is_audio_book    = $request->input('is_audio_book',0);

        $data = Subscribe::where(['type'  => $type, 'user_id' => $user_id,])->paginate($this->page_per_page)->toArray();
        $data = $data['data'];


        foreach ($data as $key=>$val){
            switch ($val['type']) {
                case 1:
                    $model = new Column();
                    $result = $model->getIndexColumn([$val['relation_id']]);
                    break;
                case 2:
                    $model = new Works();
                    $result = $model->getIndexWorks([$val['relation_id']],$is_audio_book);
                    break;
                case 6:
                    $model = new Column();
                    $result = $model->getIndexColumn([$val['relation_id']]);
                    break;
            }
            if($result == false){
                unset($data[$key]);
            }else{
                if($val['type'] == 2){
                    //专栏头衔
                    $column = Column::find($result[0]['column_id']);
                    $result[0]['column_title'] = $column['title'];
                    //学至最新章节
                    $history_data = History::getHistoryData($result[0]['id'],2,$user_id);
                    $result[0]['info_introduce'] = '';
                    if((array)($history_data)){
                        $result[0]['info_introduce'] = $history_data['introduce'] ?? '';
                    }
                }
                $data[$key]['relation_data'] = $result;
            }
        }
        $data = array_values($data);

        return $this->success($data);

    }


}