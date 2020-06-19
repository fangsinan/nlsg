<?php


namespace App\Http\Controllers\Api\V4;


use App\Http\Controllers\Controller;
use EasyWeChat\Factory;

class PayController extends  Controller
{
    /**
     * @api {post} api/v4/pay/wechat_pay   微信支付-统一下单
     * @apiName wechat_pay
     * @apiVersion 1.0.0
     * @apiGroup works
     *
     * @apiParam {int} works_id 课程id
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
    public function prePay(){

        $config = Config('wechat.payment.default');
        //$config['sandbox'] = true;
        $app = Factory::payment($config);

        $result = $app->order->unify([
            'body' => '商品测试购买',
            'out_trade_no' => '202008061253461',
            'total_fee' => 88,
            //'spbill_create_ip' => '123.12.12.123', // 可选，如不传该参数，SDK 将会自动获取相应 IP 地址
            'notify_url' => 'qweqew', // 支付结果通知网址，如果不设置则会使用配置里的默认地址
            'trade_type' => 'APP', // 请对应换成你的支付方式对应的值类型
            'attach' => 1,
            'openid' => 'oVvvfwHw0EB-ZL5Iab5YrAirQVTI',
        ]);

        return $this->success($result);

    }
}