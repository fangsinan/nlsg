<?php


namespace App\Http\Controllers\Api\V4;


use App\Http\Controllers\Controller;
use App\Models\Column;
use App\Models\Order;
use App\Models\User;
use EasyWeChat\Factory;
use Illuminate\Http\Request;

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
    public function prePay(Request $request){

        //1专栏 2会员 5打赏 9精品课 听课  11直播 12预约回放
        $attach = $request->input('type',0);
        $order_id = $request->input('id',0);

        if (empty($order_id) || empty($attach)) { //订单id有误
            return $this->error(0,'订单信息为空');
        }

        $pay_info = $this->getPayInfo($order_id, $attach);
        if($pay_info == false){
            return $this->error(0,'订单信息错误');
        }

        $config = Config('wechat.payment.default');
        $app = Factory::payment($config);
        $result = $app->order->unify([
            'body' => $pay_info['body'],
            'out_trade_no' => $pay_info['ordernum'],
            'total_fee' => $pay_info['price'],
            'trade_type' => 'APP', // 请对应换成你的支付方式对应的值类型
            'attach' => $attach,
            'openid' => $pay_info['openid'],
        ]);

        return $this->success($result);

    }

    function getPayInfo($order_id, $attach)
    {

        $body = '';
        if(in_array($attach,[1,2,5,9,11,14])) //1专栏 2会员 5打赏 9精品课 听课
        {
            $OrderInfo = Order::find($order_id);
            if (empty($OrderInfo)) { //订单有误
                return false;
            }
            if ($OrderInfo['status'] == 1) { //已支付
                return false;
            }
            $OrderInfo = $OrderInfo->toArray();
            if ($attach == 1) {
                $ColumnInfo = Column::find($OrderInfo['relation_id']);
                $body = "能量时光-专栏购买-".$ColumnInfo['name'];
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

            }

        }else{
            return false;
        }

        $userInfo = User::find($OrderInfo['user_id']);
        return [
            'body'      => $body,
            'price'     => $OrderInfo['price'],
            'ordernum'  => $OrderInfo['ordernum'],
            'openid'    => $userInfo['openid'],
        ];

    }
}