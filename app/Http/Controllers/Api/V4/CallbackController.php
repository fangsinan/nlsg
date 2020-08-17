<?php


namespace App\Http\Controllers\Api\V4;


use App\Http\Controllers\Controller;
use EasyWeChat\Factory;
use Illuminate\Http\Request;
use Yansongda\Pay\Log;
use Yansongda\Pay\Pay;

class CallbackController extends Controller
{
    //APP端   接收微信发送的异步支付结果通知
    public function WechatNotify(Request $request){
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
                //在闭包函数里 return true; 才代表处理完成。
                return true;
            }
            // $fail 为一个函数，触发该函数可向微信服务器返回对应的错误信息，微信会稍后重试再通知。
            $fail('Order not exists.');
        });

        return $response;

    }

    //APP端   支付宝回调
    public function AliNotify(Request $request)
    {
        $config = Config('pay.alipay');
        $alipay = Pay::alipay($config);

        try{
            $res_data = $alipay->verify(); // 是的，验签就这么简单！

            // 请自行对 trade_status 进行判断及其它逻辑进行判断，在支付宝的业务通知中，只有交易通知状态为 TRADE_SUCCESS 或 TRADE_FINISHED 时，支付宝才会认定为买家付款成功。
            // 1、商户需要验证该通知数据中的out_trade_no是否为商户系统中创建的订单号；
            // 2、判断total_amount是否确实为该订单的实际金额（即商户订单创建时的金额）；
            // 3、校验通知中的seller_id（或者seller_email) 是否为out_trade_no这笔单据的对应的操作方（有的时候，一个商户可能有多个seller_id/seller_email）；
            // 4、验证app_id是否为该商户本身。
            // 5、其它业务逻辑情况
            $data = [
                'out_trade_no' => $res_data['out_trade_no'], //获取订单号
                'total_fee' => $res_data['total_amount'],    //价格
                'transaction_id' => $res_data['trade_no'],   //交易单号
                'attach' => $res_data['passback_params'],
                'pay_type' => 3,  //支付方式 1 微信端 2app微信 3app支付宝  4ios
            ];
            WechatPay::PayStatusUp($data);

            Log::debug('Alipay notify', $data->all());
            return $alipay->success();
        } catch (\Exception $e) {
            // $e->getMessage();
        }

        return $alipay->success();// laravel 框架中请直接 `return $alipay->success()`
    }

}
