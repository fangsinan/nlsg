<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use App\Models\Column;
use App\Models\MallOrderDetails;
use App\Models\Order;
use App\Models\MallOrder;
use App\Models\User;
use EasyWeChat\Factory;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Request;
use Yansongda\Pay\Log;
use Yansongda\Pay\Pay;

class PayController extends Controller {

    /**
     * @api {get} api/v4/pay/wechat_pay   微信支付-统一下单
     * @apiName wechat_pay
     * @apiVersion 1.0.0
     * @apiGroup pay
     *
     * @apiParam {int} id 订单id
     * @apiParam {int} type  1专栏 2会员 5打赏 9精品课 听课  11直播 12预约回放
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
      {
      "code": 200,
      "msg": "成功",
      "data": {
      "return_code": "SUCCESS",
      "return_msg": "OK",
      "appid": "wx3296e2b7430df182",
      "mch_id": "1460495202",
      "nonce_str": "mUXLVUSyafnOzjA4",
      "sign": "729C4C7B8D489945637D0BF61B333316",
      "result_code": "SUCCESS",
      "prepay_id": "wx1819455494088084c893b29c1290375800",
      "trade_type": "APP"
      }
      }
     */
    public function prePay(Request $request) {

        //1专栏 2会员 5打赏 9精品课 听课  11直播 12预约回放 8商城
        $attach = $request->input('type', 0);
        $order_id = $request->input('id', 0);
        $is_h5 = $request->input('is_h5', 0);
        $openid = $request->input('open_id', '');


        if (empty($order_id) || empty($attach)) { //订单id有误
            return $this->error(0, '订单信息为空');
        }

        $pay_info = $this->getPayInfo($order_id, $attach);
        if ($pay_info == false) {
            return $this->error(0, '订单信息错误');
        }
        $config = Config('wechat.payment.default');

        if($is_h5 == 1 || $is_h5 == 2 ){ // 公众号openid
            $config = Config('wechat.payment.wx_wechat');
        }
        $app = Factory::payment($config);

//dd([
//    'body' => $pay_info['body'],
//    'out_trade_no' => $pay_info['ordernum'],
//    'total_fee' => 3    ,
//    'trade_type' => 'APP', // 请对应换成你的支付方式对应的值类型
//    'attach' => $attach,
//    'openid' => $pay_info['openid'],
//]);

        if($openid){  //如果传参的话  优先用传递的openid
            $pay_info['openid'] = $openid;
        }
        $trade_type = 'APP';
        if($is_h5 == 1){
            $trade_type = 'MWEB';
        }else if($is_h5 == 2){
            $trade_type = 'JSAPI';
        }else{
            //app 支付不需要openid
            $pay_info['openid'] = '';
        }
        $pay_info['price'] = '0.01';

        $result = $app->order->unify([
            'body' => $pay_info['body'],
            'out_trade_no' => $pay_info['ordernum'],
            'total_fee' => $pay_info['price']*100,
            'trade_type' => $trade_type, // 请对应换成你的支付方式对应的值类型
            'attach' => $attach,
            'openid' => $pay_info['openid'],
        ]);


        if( $result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS'){
            if($is_h5 == 1 ){
                //h5  直接返回
                return $this->success($result);
            }else if($is_h5 == 2){
                $result = $app->jssdk->bridgeConfig($result['prepay_id'],false);//第二次签名
                return $this->success($result);
            }else{
                $result = $app->jssdk->appConfig($result['prepay_id']);//第二次签名
                return $this->success($result);
            }
        }else{
            Log::error('微信支付签名失败:'.var_export($result,1));
            return $this->error(0,$result['err_code_des']);

        }

//        if( $result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS'){
//            $result = $app->jssdk->appConfig($result['prepay_id']);//第二次签名
//
//            return $this->success($result);
//        }else{
//            Log::error('微信支付签名失败:'.var_export($result,1));
//            return false;
//        }

//        $result['partnerid']=$config = Config('wechat.payment.default.mch_id');
//        $result['package']='Sign=WXPay';
//        $result['now'] = time();
        return $this->success($result);
    }

    function getPayInfo($order_id, $attach) {

        $body = '';
        if (in_array($attach, [1, 2, 5, 9, 11, 14, 8])) { //1专栏 2会员 5打赏 9精品课 听课
            if ($attach == 8) {
                $OrderInfo = MallOrder::where('status', '=', 1)
                        ->where('is_stop', '=', 0)
                        ->where('is_del', '=', 0)
                        ->where('dead_time', '>', date('Y-m-d H:i:s'))
                        ->find($order_id);
            } else {
                $OrderInfo = Order::where('status', '=', 0)->find($order_id);
            }

            if (empty($OrderInfo)) { //订单有误
                return false;
            }

            $OrderInfo = $OrderInfo->toArray();
            if ($attach == 1) {
                $ColumnInfo = Column::find($OrderInfo['relation_id']);
                $body = "能量时光-专栏购买-" . $ColumnInfo['name'];
            } else if ($attach == 2) {
                $body = "能量时光-微信端会员";
            } else if ($attach == 5) {
                $body = "能量时光-打赏-" . $OrderInfo['ordernum'];
            } else if ($attach == 9) {
                $body = "能量时光-精品课购买-" . $OrderInfo['ordernum'];
            } else if ($attach == 11) {
                $body = "能量时光-直播购买-" . $OrderInfo['ordernum'];
            } else if ($attach == 14) {
                $body = "能量时光-线下课购买-" . $OrderInfo['ordernum'];
            } else if ($attach == 8) {
                $body = "能量时光-电商订单-" . $OrderInfo['ordernum'];
                //todo 临时:商城支付一分
                $OrderInfo['price'] = 0.01;
            }
        } else {
            return false;
        }

        $userInfo = User::find($OrderInfo['user_id']);

        return [
            'body' => $body,
            'price' => $OrderInfo['price'],
            'ordernum' => $OrderInfo['ordernum'],
            'openid' => $userInfo['openid'],
        ];
    }

    /**
     * @api {get} api/v4/pay/ali_pay   支付宝支付-预下单
     * @apiName ali_pay
     * @apiVersion 1.0.0
     * @apiGroup pay
     *
     * @apiParam {int} type 类型 1专栏 2会员 5打赏 9精品课 听课  11直播 12预约回放
     * @apiParam {int} id 订单id
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     * {
      "code": 200,
      "msg": "成功",
      "data": [
      ]
      }
     */
    public function aliPay(Request $request) {



        //1专栏 2会员 5打赏 9精品课 听课  11直播 12预约回放
        $attach = $request->input('type', 0);
        $order_id = $request->input('id', 0);
        $is_h5 = $request->input('is_h5', 0);

        if (empty($order_id) || empty($attach)) { //订单id有误
            return $this->error(0, '订单信息为空');
        }

        $pay_info = $this->getPayInfo($order_id, $attach);
        $pay_info['price'] = '0.01';
        if ($pay_info == false) {
            return $this->error(0, '订单信息错误');
        }
        $config = Config('pay.alipay');
        $order = [
            'out_trade_no' => $pay_info['ordernum'],
            'total_amount' => $pay_info['price'],
            'subject' => $pay_info['body'],
            'passback_params' => $attach,
            'goods_type' => $attach
        ];

//
//        $order = [
//            'out_trade_no' => time(),
//            'total_amount' => '1',
//            'subject' => 'test subject - 测试',
//        ];

        if($is_h5){
            $alipay = Pay::alipay($config)->web($order);
            return $alipay;
        }else{
            $alipay = Pay::alipay($config)->app($order);
            //return $alipay; // laravel 框架中请直接 `return $alipay`
            return $this->success($alipay->getContent());
        }

    }

    /**
     * @api {get} api/v4/pay/order_find   下单查询接口
     * @apiName order_find
     * @apiVersion 1.0.0
     * @apiGroup pay
     *
     * @apiParam {int} type 类型 1专栏 2会员 5打赏 9精品课 听课  11直播 12预约回放 8商品订单
     * @apiParam {int} id 订单id
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     * {
      "code": 200,
      "msg": "成功",
      "data": [
      ]
      }
     */
    public function OrderFind(Request $request) {
        $id = $request->input('id', 0);
        $type = $request->input('type',0);
        if($type == 8){
            $orderData = MallOrder::find($id);
        }else{
            $orderData = Order::find($id);
        }
        if (!$orderData) {
            return $this->error(0, '订单有误');
        }

        //订单号码不存在时报错 捕获异常处理
        try {
            if ($orderData['pay_type'] == 2) {
                //微信
                $config = Config('wechat.payment.default');
                $app = Factory::payment($config);
                $res = $app->order->queryByOutTradeNumber($orderData['ordernum']); //"商户系统内部的订单号（out_trade_no）"
                if($res['return_code'] == 'SUCCESS'){
                    if($res['trade_state'] == 'SUCCESS'){
                        $res['pay_type'] = 2;
                    }
                }else{
                    return $res;
                }

            } elseif ($orderData['pay_type'] == 3) {
                //支付宝
                $config = Config('pay.alipay');
                $res = Pay::alipay($config)->find(['out_trade_no' => $orderData['ordernum']]);
                $res = json_decode(json_encode($res),true);
                $temp_attach = substr($res['out_trade_no'],-2);
                switch ($temp_attach){
                    //todo 其他类型也需要配一下
                    case '01':
                        $res['attach'] = 8;
                        break;
                }
                if($res['trade_status'] == 'TRADE_SUCCESS'){
                    $res['pay_type'] = 3;
                }else{
                    return $res;
                }
            }

            //todo 临时用
            WechatPay::PayStatusUp($res);

            return $this->success($res);
        } catch (\Exception $e) {
            return $this->error(0, 'error');
        }
    }

    /**
     * @api {get} api/v4/pay/apple_pay   苹果支付验证接口 [ 苹果端 能量币充值 ]
     * @apiName apple_pay
     * @apiVersion 1.0.0
     * @apiGroup pay
     *
     * @apiParam {int} ordernum 订单号
     * @apiParam {int} receipt-data 苹果支付返回信息
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     * {
      "code": 200,
      "msg": "成功",
      "data": [
      ]
      }
     */
    public function ApplePay(Request $request) {
        $params = $request->input();
        if (empty($params['ordernum']) || empty($params['receipt-data'])) {
            return $this->error(0, 'ordernum 或者 receipt-data 为空');
        }

        //正式环境
        $endpoint = 'https://buy.itunes.apple.com/verifyReceipt';
        $check_data = $this->CheckApple($endpoint, $params);
        if ($check_data['error'] == 0) {
            //21007  收据信息是测试用（sandbox），但却被发送到产品环境中验证
            if ($check_data['code'] == '21007') {
                //沙箱环境
                $endpoint = 'https://sandbox.itunes.apple.com/verifyReceipt';
                $check_data = $this->CheckApple($endpoint, $params);
                if ($check_data['error'] == 0) {
                    return $this->error($check_data['code'], $check_data['msg']);
                }
            } else {
                return $this->error($check_data['code'], $check_data['msg']);
            }
        }
        $data = $check_data['data'];
        //成功后获取数据
        preg_match('/(\d)+/', $data->receipt->product_id, $arr);
        $money = $arr[0];
        $orderNum = Order::find($params['ordernum']);
        //校验完成支付后  修改订单内容(类似于支付宝或微信的回调)
        $Paydata = [
            'out_trade_no' => $orderNum->ordernum, //获取订单号
            'total_fee' => $money, //价格
            'transaction_id' => $data->receipt->transaction_id, //交易单号
            'attach' => 13, //能量币
            'pay_type' => 4, //支付方式 1 微信端 2app微信 3app支付宝  4ios
        ];
        $res = WechatPay::PayStatusUp($Paydata);  //回调
        if ($res == false) {
            return $this->error($check_data['code'], 'fail:系统订单有误，重试');
        }
        return $this->success();
    }

    //验证苹果支付
    public function CheckApple($endpoint, $params) {

        $client = new Client();
//        $client->post($endpoint,[
//            RequestOptions::JSON =>['receipt-data'=> $params['receipt-data'] ]
//        ]);
        $data = $client->request('PUT', $endpoint, ['json' => ['receipt-data' => $params['receipt-data']]]);

        //判断返回的数据是否是对象
        if (!is_object($data)) {
            return ['error' => 0, 'code' => 0, 'msg' => 'Invalid response data', 'data' => $data];
        }
        //判断购不成功状态
        if (!isset($data->status) || $data->status != 0) {
            $code = $data->status;
            // 状态码仅限于ios支付
            $messagearr[21000] = "App Store无法读取你提供的JSON数据";
            $messagearr[21002] = "收据数据不符合格式";
            $messagearr[21003] = "收据无法被验证";
            $messagearr[21004] = "你提供的共享密钥和账户的共享密钥不一致";
            $messagearr[21005] = "收据服务器当前不可用";
            $messagearr[21006] = "收据是有效的，但订阅服务已经过期。当收到这个信息时，解码后的收据信息也包含在返回内容中";
            $messagearr[21007] = "收据信息是测试用（sandbox），但却被发送到产品环境中验证";
            $messagearr[21008] = "收据信息是产品环境中使用，但却被发送到测试环境中验证";

            return ['error' => 0, 'code' => $code, 'msg' => $messagearr[$code], 'data' => $data];
        }
        return ['error' => 1, 'data' => $data];
    }

    /**
     * @api {get} api/v4/pay/pay_coin   能量币支付回调
     * @apiName pay_coin
     * @apiVersion 1.0.0
     * @apiGroup pay
     *
     * @apiParam {int} user_id user_id
     * @apiParam {int} order_id order_id
     * @apiParam {int} pay_type 当类型为[1 专栏 2 会员 5 打赏  9精品课] 传1   类型为[1 月卡 2 季卡 3押金 4 违约金 5退押金]传2
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     * {
      "code": 200,
      "msg": "成功",
      "data": [
      ]
      }
     */
    public function PayCoin(Request $request) {
        $uid = $request->input('user_id', 0);
        $order_id = $request->input('order_id', 0);
        $pay_type = $request->input('pay_type', 0);

        if (empty($order_id)) {
            return $this->error(0, 'order_id 为空');
        }

        //  1 专栏 2 会员 5 打赏  9精品课      pay_type = 1
        //  1 月卡 2 季卡 3押金 4 违约金 5退押金      pay_type = 2
        if ($pay_type == 1) {
            //验证能量币是否充足
            $order = Order::find($order_id);
            $ordernum = $order['ordernum'];
            $money = $order['price'];
            $type = $order['type'];
            $attach = $type;
            //  1 专栏 2 会员 5 打赏  9精品课
            //  10直播回放 14 线下产品(门票类)

            if ($type == 10) { //type 与order_deposit 的 $attach重复了
                $attach = 11;
            }
            if (!in_array($type, [1, 2, 5, 9, 10, 14])) {
                //商品不支持能量币支付
                return $this->error(0, '当前产品不支持能量币支付');
            }
        } else if ($pay_type == 2) {
            $OrderDepositObj = new OrderDeposit();
            $order = $OrderDepositObj->getOne($OrderDepositObj::$table, ['id' => $order_id], '*');

            $ordernum = $order['ordernum'];
            $money = $order['price'];
            $type = $order['type'];  //支付类型
            $attach = 10;  //回调类型 押金
            //  1 月卡 2 季卡 3押金 4 违约金 5退押金
            if (!in_array($type, [1, 2, 3, 4])) {
                return $this->error(0, '当前产品不支持能量币支付');
            }
        }
        if (empty($orderNum)) {
            //商品不支持能量币支付
            return $this->error(0, '订单有误');
        }
        $user = User::find($uid);
        if (empty($user) || $user['ios_balance'] < $money) {
            //商品不支持能量币支付
            return $this->error(0, '能量币不足,请先充值');
        }

        //校验完成支付后  修改订单内容(类似于支付宝或微信的回调)
        $Paydata = [
            'out_trade_no' => $ordernum, //获取订单号
            'total_fee' => $money, //价格
            'transaction_id' => 0, //交易单号
            'attach' => $attach, //支付类型
            'pay_type' => 4, //支付方式 1 微信端 2app微信 3app支付宝  4ios能量币支付
        ];

        $res = WechatPay::PayStatusUp($Paydata);  //回调
        if ($res == false) {
            return $this->error(0, 'fail:系统订单有误，重试');
        }
        return $this->Success();
    }

}
