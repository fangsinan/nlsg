<?php


namespace App\Http\Controllers\Api\V4;


use App\Http\Controllers\Controller;
use App\Models\Column;
use App\Models\Coupon;
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
     * @api {post} /v4/order/get_coupon   获取我的优惠券
     * @apiName get_coupon
     * @apiVersion 1.0.0
     * @apiGroup order
     *
     * @apiParam {int} user_id 用户id
     * @apiParam {int} type  类型 1专栏  2会员  4课程
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
    "name": "优惠券",
    "number": "123456",
    "type": 1,   0不限制 1专栏 2会员 3商品 4精品课 5:跨境商品
    "user_id": 211172,
    "money": 10,
    "starttime": 0,
    "deadline": 1593517938,
    "use_time": 0,
    "status": 1,
    "ctime": 0,
    "fullcut_price": 99,
    "explain": "",
    "order_id": 0,
    "flag": "",
    "get_way": 1,
    "cr_id": 0
    }
    ]
    }
     */
    public function getCoupon(Request $request){
        $price = $request->input('price',99);
        $type  = $request->input('type',0);
        $user_id = $request->input('user_id',0);
        $where_type = [0];
        if($type){
            $where_type = [0, $type];
        }
        $coupon = Coupon::where([
           'status' => 1,
           'user_id'=> $user_id,
        ])->whereIn('type',$where_type)
        ->where('end_time','>=',time())
        ->where('full_cut','>=',$price)->get();
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
     * @api {post} /v4/order/create_column_order  专栏下单
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

        $ordernum = '123';
        $data=[
            'ordernum'      => $ordernum,
            'type'          => 1,
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
     * @api {post} /v4/order/create_works_order  精品课下单
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

        $ordernum = '123';
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


}