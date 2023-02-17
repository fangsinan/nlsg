<?php


namespace App\Http\Controllers\Api\V5;


use App\Http\Controllers\Api\V4\WechatPay;
use App\Http\Controllers\Controller;
use App\Models\Lists;
use App\Models\Live;
use App\Models\MallAddress;
use App\Models\MallOrder;
use App\Models\Order;
use App\Models\OrderErpList;
use App\Models\Xfxs\XfxsOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


/**
 * 下单Controller
 * 虚拟作品订单操作
 */
class OrderController extends Controller
{


    /**
     * @api {post} /api//v5/order/create_order_address 虚拟订单地址上报
     * @apiName create_order_address
     * @apiVersion 1.0.0
     * @apiGroup order
     *
     * @apiParam {int} address_id      地址id
     * @apiParam {int} order_id      订单
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": { }
     * }
     */
    public function addOrderAddress(Request $request)
    {
        $user_id = $this->user['id']??0;

        $address_id = $request->input('address_id', 0);
        $order_id = $request->input('order_id', 0);
        if(!empty($address_id) && !empty($order_id)){

            $address = MallAddress::where([
                'id'=>$address_id,
                'is_del' => 0,
                'user_id'=>$user_id
            ])->first('id');

            if(empty($address)){
                return $this->error(1000, '地址不存在');
            }

            $res = Order::where(['id' => $order_id,'user_id'=>$user_id ])->update([
                'address_id'=>$address_id,
            ]);

            //同时添加推送队列
            OrderErpList::query()
                ->firstOrCreate(['order_id' => $order_id,'flag'=>1]);

            return $this->success((object)[]);

        }
        return $this->error(1000, 'address_id或order_id不存在');


    }



    /**
     * @api {post} /api//v4/order/create_teacher_order 大咖讲书专题下单
     * @apiName create_teacher_order
     * @apiVersion 1.0.0
     * @apiGroup order
     *
     * @apiParam {int} lists_id      id
     * @apiParam {int} os_type      os_type 1 安卓 2ios
     * @apiParam {int} pay_type     pay_type
     * @apiParam {int} live_id      直播id
     * @apiParam {int} inviter      推客id
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": { }
     * }
     */
    public function createTeacherOrder(Request $request)
    {
        $product_id = $request->input('lists_id', 0);   //目标id
        $os_type = $request->input('os_type', 0);
        $pay_type = $request->input('pay_type', 0);
        $live_id = $request->input('live_id', 0);
        $tweeter_code = $request->input('inviter', 0);  //推客id
        $num = 1;
        $user_id = $this->user['id'];



        $lists = Lists::find($product_id);
        //检测下单参数有效性
        if (empty($lists)) {
            return $this->error(0, '产品id有误');
        }


        $price = $lists['price'];

        $ordernum = MallOrder::createOrderNumber($user_id, 3);
        $data = [
            'ordernum' => $ordernum,
            'type' => 19,
            'user_id' => $user_id,
            'relation_id' => $product_id,
            'cost_price' => $price,
            'price' => ($price*$num),
            'ip' => $this->getIp($request),
            'os_type' => $os_type,
            'pay_type' => $pay_type,
            'live_id' => $live_id,
            'live_num' => $num,
            'twitter_id' => $tweeter_code,
        ];

        $order = Order::firstOrCreate($data);
        return $this->success($order['id']);

    }

    // 直播间免费刷单
    public function freeOrder(Request $request){

        $validator = Validator::make($request->all(), [
            'type' => 'required|numeric',
            'relation_id' => 'required|numeric',
            'live_id' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return $this->error(0,$validator->messages()->first());
        }
        $this->user['nickname'] = 'as';
        $this->user['id'] = '211172';

        // orderType 101课程  102合伙人 103 训练营  104 直播打赏  105 直播预约 106 线下产品
        // redisType 16 360会员  14 线下课  18 训练营  11 直播间
        $type = $request->input("type");
        $relation_id = $request->input("relation_id");
        $live_id = $request->input("live_id");
        $os_type = $request->input("os_type");
        $user_id = $this->user['id'] ?? 0;
        // 客户端根据socket 跳转类型
        // 1 :  跳转专栏
        // 2 :  跳转精品课
        // 3 :  跳转商品
        // 7 :  跳转讲座
        // 8 :  跳转听书
        // 10 :  跳转第三方链接
        //
        // 4 :  支付订单弹窗 线下产品门票
        // 6：360会员
        // 9： 直播
        // 11：训练营
        //
        // 12：二维码

        $redis_relation_id = $relation_id;
        switch ($type){
            case 11:  //训练营
                $redis_type = 18;
                $order_type = 103;
                break;
            case 9:  //  直播
                $redis_type = 11;
                $order_type = 105;
                $relation_live = Live::where("id",$relation_id)->first();
                $redis_relation_id = $relation_live['title'];
                break;
            case 4:  //线下课
                $redis_type = 14;
                $order_type = 106;
                break;
            default:

                $redis_type = 0;
                $order_type = 0;
                break;
        }
        if($order_type == 0){
            return $this->success();
        }

        $live = Live::where('id',$live_id)->first();

        if(!empty($live)&&$live['app_project_type'] == 2){
            $ordernum = MallOrder::createOrderNumber($user_id, 3);
             $res = XfxsOrder::firstOrCreate([
                 'ordernum' => $ordernum,
                 'type' => $type,
                 'user_id' => $user_id,
                 'relation_id' => $relation_id,
                 'cost_price' => '0.01',
                 'price' => '0.01',
                 'ip' => $this->getIp($request),
                 'os_type' => $os_type,
                 'live_id' => $live_id ?? 0,
                 'pay_type' => 5,
                 'activity_tag' => $activity_tag ?? '',
             ]);
            // 添加redis
            WechatPay::LiveRedis($redis_type,$redis_relation_id, $this->user['nickname'], $live_id);
        }

        return $this->success();
    }
}
