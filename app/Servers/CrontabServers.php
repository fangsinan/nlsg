<?php


namespace App\Servers;


use App\Models\MallRefundRecord;
use EasyWeChat\Factory;
use Psy\Util\Str;
use Yansongda\Pay\Log;
use Yansongda\Pay\Pay;

class CrontabServers
{
    public function mallRefund()
    {

        $list = MallRefundRecord::from('nlsg_mall_refund_record as mrr')
            ->join('nlsg_mall_order as mo', 'mrr.order_id', '=', 'mo.id')
            ->join('nlsg_pay_record as pr', 'pr.ordernum', '=', 'mo.ordernum')
            ->where('mrr.run_refund', '=', 1)
            ->where('pr.order_type', '=', 10)
            ->select(['mrr.id as service_id', 'service_num', 'mrr.order_id',
                'mrr.order_detail_id', 'mrr.type', 'mrr.pay_type',
                'mrr.status as service_status', 'mrr.user_id', 'pr.transaction_id',
                'pr.ordernum', 'pr.price as all_price', 'mrr.price as refund_price'])
            ->get();

        foreach ($list as $v){
            switch ($v->pay_type){
                case 1:
                    //微信公众号
                    break;
                case 2:
                    //微信app
                    $config = Config('wechat.payment.default');
                    $order = [
                        'refund_account' => 'REFUND_SOURCE_RECHARGE_FUNDS',
                        'nonce_str' => \Illuminate\Support\Str::random(16), //随机字符串
                        'out_refund_no' => $v->service_num, //商户退款单号
                        'refund_fee' => $v->refund_price * 100, //退款金额
                        'total_fee' => $v->all_price * 100, //订单金额
                        'transaction_id' => $v->transaction_id, //微信订单号
                    ];


                    $res = Pay::wechat($config)->refund($order);

                    dd($res);
                    break;
                case 3:
                    //支付宝app
                    $config = Config('pay.alipay');
                    break;
            }
        }


        dd($list->toArray());
    }
}
