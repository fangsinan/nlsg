<?php


namespace App\Http\Controllers\Api\V4;


use App\Http\Controllers\Controller;
use EasyWeChat\Factory;
use Illuminate\Http\Request;

class CallbackController extends Controller
{
    //接收微信发送的异步支付结果通知
    public function Notify(Request $request){
        $config = Config('wechat.payment.default');
        $app = Factory::payment($config);
        $response = $app->handlePaidNotify(function ($message, $fail) {
            // 你的逻辑
            $data = [
                'out_trade_no'      => $message['out_trade_no'], //获取订单号
                'total_fee'         => $message['total_fee']/100, //价格
                'transaction_id'    => $message['transaction_id'], //交易单号
                'attach'            => $message['attach'],
                'pay_type'          => 2,  //支付方式 1 微信端 2app微信 3app支付宝  4ios
            ];
            $res = WechatPay::PayStatusUp($data);

            if($res == true){
                //在函数里 return true; 才代表处理完成。
                return true;
            }
            // $fail 为一个函数，触发该函数可向微信服务器返回对应的错误信息，微信会稍后重试再通知。
            $fail('Order not exists.');
        });

        return $response;

    }

}