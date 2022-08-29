<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use App\Models\Column;
use App\Models\Lists;
use App\Models\Live;
use App\Models\MallOrder;
use App\Models\OfflineProducts;
use App\Models\Order;
use App\Models\PayRecord;
use App\Models\User;
use EasyWeChat\Factory;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Yansongda\Pay\Log;
use Yansongda\Pay\Pay;
use Illuminate\Support\Facades\DB;

class PayController extends Controller
{

    /**
     * @api {get} api/v4/pay/wechat_pay   微信支付-统一下单
     * @apiName wechat_pay
     * @apiVersion 1.0.0
     * @apiGroup pay
     *
     * @apiParam {int} id 订单id
     * @apiParam {int} type  1专栏 2会员 5打赏 8  电商   9精品课 11直播 14线下课购买 15讲座  16幸福360购买   17赠送  18 训练营 19专题lists
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": {
     * "return_code": "SUCCESS",
     * "return_msg": "OK",
     * "appid": "wx3296e2b7430df182",
     * "mch_id": "1460495202",
     * "nonce_str": "mUXLVUSyafnOzjA4",
     * "sign": "729C4C7B8D489945637D0BF61B333316",
     * "result_code": "SUCCESS",
     * "prepay_id": "wx1819455494088084c893b29c1290375800",
     * "trade_type": "APP"
     * }
     * }
     */
    public function prePay(Request $request)
    {

        //1专栏 2会员 5打赏 9精品课 听课  11直播 12预约回放 8商城
        $attach = $request->input('type', 0);
        $order_id = $request->input('id', 0);
        $is_h5 = $request->input('is_h5', 0);
        $openid = $request->input('open_id', '');
        $activity_tag = $request->input('activity_tag', '');
        $is_given_user = $request->input('is_given_user', 0);


        if (empty($order_id) || empty($attach)) { //订单id有误
            return $this->error(0, '订单信息为空');
        }

        $pay_info = $this->getPayInfo($order_id, $attach,$is_given_user);

        if ($pay_info == false) {
            return $this->error(0, '订单信息错误');
        }

        if ($activity_tag === 'cytx') {
            Order::where('id', '=', $order_id)->update(['activity_tag' => 'cytx']);
        }

        $config = Config('wechat.payment.default');
        $config = Config('wechat.payment.old_default'); //老商户

        if ($is_h5 == 1 || $is_h5 == 2) { // 公众号openid
            $config = Config('wechat.payment.wx_wechat');
            $config = Config('wechat.payment.old_wx_wechat');
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

        if ($openid) {  //如果传参的话  优先用传递的openid
            $pay_info['openid'] = $openid;
        }
        $trade_type = 'APP';
        if ($is_h5 == 1) {
            $trade_type = 'MWEB';
            $pay_info['openid'] = '';
        } else if ($is_h5 == 2) {
            $trade_type = 'JSAPI';
        } else {
            //app 支付不需要openid
            $pay_info['openid'] = '';
        }


        $data = [
            'body' => strlen($pay_info['body']) > 120 ? mb_substr($pay_info['body'],0,33):$pay_info['body'],
            'out_trade_no' => $pay_info['ordernum'],
            'total_fee' => $pay_info['price'] * 100,
            'trade_type' => $trade_type, // 请对应换成你的支付方式对应的值类型
            'attach' => $attach,
            'openid' => $pay_info['openid'],
            'device_info' => $pay_info['device_info']
        ];

        if ($pay_info['profit_sharing'] == 1) {    //下单需要分账
            //查询  分账的直播id
            $data['profit_sharing'] = 'Y';
        }
        $result = $app->order->unify($data);

        if ($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS') {
            if ($is_h5 == 1) {
                //h5  直接返回
                return $this->success($result);
            } else if ($is_h5 == 2) {
                $result = $app->jssdk->bridgeConfig($result['prepay_id'], false);//第二次签名
                return $this->success($result);
            } else {
                $result = $app->jssdk->appConfig($result['prepay_id']);//第二次签名
                return $this->success($result);
            }
        } else {
            Log::error('微信支付签名失败:' . var_export($result, 1));
            return $this->error(0, $result['err_code_des'] ?? '微信支付签名失败',$data);

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

    function getPayInfo($order_id, $attach, $is_given_user=0)
    {

        $body = '';
        if (in_array($attach, [1, 2, 5, 9, 10, 11, 14, 8, 15, 16, 17, 18, 19])) { //1专栏 2会员 5打赏 9精品课 听课
            $device_info = '';
            if ($attach == 8) {
                $OrderInfo = MallOrder::where('status', '=', 1)
                    ->where('is_stop', '=', 0)
                    ->where('is_del', '=', 0)
                    ->where('dead_time', '>', date('Y-m-d H:i:s'))
                    ->find($order_id);
            } else {
                $OrderInfo = Order::where('status', '=', 0)->find($order_id);
                if (($OrderInfo->activity_tag ?? '') == 'cytx') {
                    $device_info = 'cytx';
                }
            }

            if (empty($OrderInfo)) { //订单有误
                return false;
            }
            $profit_sharing = 0;

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
            } else if ($attach == 11 || $attach ==10) {
                $live = Live::find($OrderInfo['live_id']);
                $profit_sharing = $live['profit_sharing'];

                $body = "能量时光-直播购买-" . $OrderInfo['ordernum'];
                if($is_given_user == 1 ){
                    $OrderInfo['price'] = 0.1;
                }
            } else if ($attach == 14) {
                $relation_str='线下课';
                if(isset($OrderInfo['relation_id']) && !empty($OrderInfo['relation_id'])) {
                    switch ($OrderInfo['relation_id']) {
                        case 1:
                            $relation_str = '经营能量线下门票';
                            break;
                        case 2:
                            $relation_str = '一代天骄线下门票';
                            break;
                        case 3:
                            $relation_str = '演说能量线下门票';
                            break;
                        case 4:
                            $relation_str = '经营能量+360套餐';
                            break;
                        case 5:
                            $relation_str = '30天智慧父母(亲子)训练营';
                            break;
                        default :
                            $Info = OfflineProducts::find($OrderInfo['relation_id']);
                            $relation_str = $Info['title'];
                            break;
                    }
                }
                $body = "能量时光-$relation_str-" . $OrderInfo['ordernum'];
            } else if ($attach == 8) {
                $body = "能量时光-电商订单-" . $OrderInfo['ordernum'];
            } else if ($attach == 15) {
                $ColumnInfo = Column::find($OrderInfo['relation_id']);
                $body = "能量时光-讲座购买-" . $ColumnInfo['name'];
            } else if ($attach == 16) {
                $body = "能量时光-幸福360购买";
            } else if ($attach == 17) {
                $body = "能量时光-赠送订单";
            } else if ($attach == 18) {
                $ColumnInfo = Column::find($OrderInfo['relation_id']);
                $body = "能量时光-训练营购买-" . $ColumnInfo['name'];
            } else if ($attach == 19) {
                $Info = Lists::find($OrderInfo['relation_id']);
                $body = "能量时光-专题购买-" . $Info['title'];
            }
        } else {
            return false;
        }



        $userInfo = User::find($OrderInfo['user_id']);
        if ($userInfo['is_test_pay'] == 1) {
//            if (empty((float)$userInfo['test_pay_price'])) {
//                $OrderInfo['price'] = 0.01;
//            }else{
//                $OrderInfo['price'] = $userInfo['test_pay_price'];
//            }
            $OrderInfo['price'] = $userInfo['test_pay_price'] > 0 ? $userInfo['test_pay_price'] : 0.01;
        }

        return [
            'body' => $body,
            'price' => $OrderInfo['price'],
            'ordernum' => $OrderInfo['ordernum'],
            'openid' => $userInfo['openid'],
            'profit_sharing' => $profit_sharing,
            'device_info' => $device_info,
        ];
    }

    /**
     * @api {get} api/v4/pay/ali_pay   支付宝支付-预下单
     * @apiName ali_pay
     * @apiVersion 1.0.0
     * @apiGroup pay
     *
     * @apiParam {int} type 类型 1专栏 2会员 5打赏 8  电商   9精品课 11直播 14线下课购买 15讲座  16幸福360购买   17赠送  18 训练营 19专题lists
     * @apiParam {int} id 订单id
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": [
     * ]
     * }
     */
    public function aliPay(Request $request)
    {


        //1专栏 2会员 5打赏 9精品课 听课  11直播 12预约回放
        $attach = $request->input('type', 0);
        $order_id = $request->input('id', 0);
        $is_h5 = $request->input('is_h5', 0);
        $return_url = $request->input('return_url', '');

        if (empty($order_id) || empty($attach)) { //订单id有误
            return $this->error(0, '订单信息为空');
        }

        $pay_info = $this->getPayInfo($order_id, $attach);

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


//        $order = [
//            'out_trade_no' => time(),
//            'total_amount' => '1',
//            'subject' => 'test subject - 测试',
//        ];

        if ($is_h5) {
            $OrderInfo = Order::where('status', '=', 0)->find($order_id);
            if ($OrderInfo->type == 10 && $OrderInfo->live_id == 1 && !empty($return_url)) {
                $config['return_url'] = $config['quit_url'] = $return_url;
            }
            $alipay = Pay::alipay($config)->wap($order);
            return $alipay;
        } else {
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
     * "code": 200,
     * "msg": "成功",
     * "data": [
     * ]
     * }
     */
    public function OrderFind(Request $request)
    {
        //回调没问题,暂时关闭
        $id = $request->input('id', 0);
        $type = $request->input('type', 0);
        if ($type == 8) {
            $orderData = MallOrder::find($id);
        } else {
            $orderData = Order::find($id);
        }
        if (!$orderData) {
            return $this->error(0, '订单有误');
        }

        //订单号码不存在时报错 捕获异常处理
        try {
            if ($orderData['pay_type'] === 2 || $orderData['pay_type'] === 1) {
                //微信
                $config = Config('wechat.payment.default');
                $app = Factory::payment($config);
                $res = $app->order->queryByOutTradeNumber($orderData['ordernum']); //"商户系统内部的订单号（out_trade_no）"
                if ($res['return_code'] === 'SUCCESS') {
                    if ($res['trade_state'] === 'SUCCESS' || $res['trade_state'] === 'REFUND') {
                        $res['pay_type'] = 2;
                        $res['total_fee'] = $res['total_fee'] / 100;
                    }else{
                        return $this->error(0, '订单有误'.__LINE__);
                    }
                } else {
                    return $res;
                }

            } elseif ($orderData['pay_type'] == 3) {
                //支付宝
                $config = Config('pay.alipay');
                $res = Pay::alipay($config)->find(['out_trade_no' => $orderData['ordernum']]);
                $res = json_decode(json_encode($res), true);
                $temp_attach = substr($res['out_trade_no'], -2);
                switch ($temp_attach) {
                    //todo 其他类型也需要配一下
                    case '01':
                        $res['attach'] = 8;
                        break;
                }
                if ($res['trade_status'] === 'TRADE_SUCCESS') {
                    $res['pay_type'] = 3;
                } else {
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
     * "code": 200,
     * "msg": "成功",
     * "data": [
     * ]
     * }
     */
    public function ApplePay(Request $request)
    {

        $params = $request->input();
        // Log::debug('ApplePay notify', [$params]);
        PayRecord::PayLog('ApplePay notify',json_encode([$params]));
        if (empty($params['ordernum']) || empty($params['receipt-data'])) {
            return $this->error(0, 'ordernum 或者 receipt-data 为空');
        }
//        $params['receipt-data']  = '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';
//        $params['ordernum'] = '20081900000001692663703';
        //正式环境
        $endpoint = 'https://buy.itunes.apple.com/verifyReceipt';
        //$endpoint = 'https://sandbox.itunes.apple.com/verifyReceipt';

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
        // 充值 记录
        DB::table('nlsg_apple_log')->insert([
            "ordernum"    => $params['ordernum'],
            "user_id"    => $params['user_id'],
            "product_id"   => $data['receipt']['in_app'][0]['product_id']??'',
            "environment"   => $data['environment']??'',
            "message"   => json_encode($data)??'',
            "receipt_data"   => $params['receipt-data']??'',
            "created_at"=> date("Y-m-d H:i:s"),
        ]);

        //成功后获取数据
        preg_match('/(\d)+/', $data['receipt']['in_app'][0]['product_id'], $arr);
        $money = $arr[0];
        //$money = $data['receipt']['in_app'][0]['transaction_id'];
        //$orderNum = Order::find($params['ordernum']);
        //校验完成支付后  修改订单内容(类似于支付宝或微信的回调)
        $Paydata = [
            'out_trade_no' => $params['ordernum'], //获取订单号
            'total_fee' => $money, //价格
            'transaction_id' => $data['receipt']['in_app'][0]['transaction_id'], //交易单号
            'attach' => 13, //能量币
            'pay_type' => 4, //支付方式 1 微信端 2app微信 3app支付宝  4ios
        ];

        $res = WechatPay::PayStatusUp($Paydata);  //回调

        if ($res == false) {
            return $this->error(0, 'fail:系统订单有误，重试');
        }
        return $this->success();
    }

    //验证苹果支付
    public function CheckApple($endpoint, $params)
    {

        $client = new Client();
//        $client->post($endpoint,[
//            RequestOptions::JSON =>['receipt-data'=> $params['receipt-data'] ]
//        ]);

//        $data = $client->request('PUT', $endpoint, ['json' => ['receipt-data'=> $params['receipt-data'] ]]);

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["receipt-data" => $params['receipt-data']]));
        $result = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($result, true);

//        //判断返回的数据是否是对象
//        if (!is_object($data)) {
//            return ['error' => 0, 'code' => 0, 'msg' => 'Invalid response data', 'data' => $data];
//        }

        //判断购不成功状态
        if (!isset($data['status']) || $data['status'] != 0) {
            $code = $data['status'] ?? '';
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
     * @apiParam {int} pay_type 当类型为[ 专栏  会员  打赏  精品课 ]时 传1   类型为[ 月卡  季卡 押金  违约金 退押金] 传2
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": [
     * ]
     * }
     */
    public function PayCoin(Request $request)
    {
        //$uid = $request->input('user_id', 0);
        $uid = $this->user['id'] ?? 0;

        $order_id = $request->input('order_id', 0);
        $pay_type = $request->input('pay_type', 0);

        if (empty($order_id)) {
            return $this->error(0, 'order_id 为空');
        }

        //  1 专栏 2 会员 5 打赏  9精品课      pay_type = 1
        //  1 月卡 2 季卡 3押金 4 违约金 5退押金      pay_type = 2
        $type = 0;
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
            if (!in_array($type, [1, 2, 5, 9, 10, 14, 15, 16, 17, 18, 19])) {
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
        if (empty($order)) {
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
        return $this->Success(['type'=>$type]);
    }

}
