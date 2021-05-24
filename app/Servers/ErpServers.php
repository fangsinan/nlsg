<?php


namespace App\Servers;

use App\Models\MallOrder;
use WdtClient;

class ErpServers
{
    public $sid;
    public $appkey;
    public $appsecret;

    //推送触发时机:支付,取消订单,确认收货
    public function startPush($id)
    {
        if (!is_array($id)) {
            $id = explode(',', $id);
        }


        $list = MallOrder::whereIn('id', $id)
            ->with(['orderDetails', 'orderDetails.skuInfo', 'userInfo'])
            ->select(['id', 'ordernum', 'pay_price', 'price', 'user_id', 'order_type', 'status', 'pay_time', 'pay_type', 'messages',
                'remark', 'post_type', 'address_id', 'address_history', 'created_at', 'updated_at', 'is_stop', 'stop_at'])
            ->get();


        $trade_list = [];

        foreach ($list as $v) {
            $temp_trade_list = [];
            $temp_order_list = [];

            $temp_trade_list['tid'] = $v->ordernum;

            $temp_trade_list['trade_time'] = $v->created_at;
            $temp_trade_list['pay_time'] = $v->pay_time;
            $temp_trade_list['buyer_nick'] = $v->userInfo->nickname;


            //todo 判断订单状态
            $temp_trade_list['trade_status'] = $v->ordernum;
            $temp_trade_list['pay_status'] = $v->ordernum;

            $temp_trade_list['delivery_term'] = 1;
            $temp_trade_list['cod_amount'] = 0;
            $temp_trade_list['ext_cod_fee'] = 0;




            $temp_trade_list['receiver_name'] = $v->ordernum;
            $temp_trade_list['receiver_address'] = $v->ordernum;//省区市和详细地址
            $temp_trade_list['buyer_message'] = $v->ordernum;
            $temp_trade_list['post_amount'] = $v->ordernum;
            $temp_trade_list['paid'] = $v->ordernum;


        }

        return [
            $this->sid,
            $this->appkey,
            $this->appsecret,
            $id,
            $list,
        ];
    }

    public function __construct()
    {
        $this->sid = env('ERP_SID');
        $this->appkey = env('ERP_APPKEY');
        $this->appsecret = env('ERP_APPSECRET');
    }

    public function test()
    {
        if (1) {
            return $this->startPush(10500);
        } else {
            $c = new WdtClient();

            $c->sid = 'apidevnew2';
            $c->appkey = 'nlsg2-test';
            $c->appsecret = '12345';
            $c->gatewayUrl = 'http://sandbox.wangdian.cn/openapi2/trade_push.php';
            /*
                    trade_status=30	已付款待发货(包含货到付款)，30只可以直接变更为70/ 80这2种状态
                    trade_status=40	部分发货(拆分发货才会出现)
                    trade_status=50	已发货(平台销售订单已经发货时推送此状态)，如果已发货在自建商城代表订单完结状态，无后续状态变更，直接推送状态值70。
                    trade_status=70	已完成（已签收），平台订单完成（客户确认收货）后，推送此状态;
                    订单为自动流转模式时，初次推送的平台订单状态直接就是已完成状态70
                    trade_status=80	已退款(付款后又全部退款推送此状态)
            */
            $trade_list[] = array
            (
                'tid' => 'LxTestTid' . time(),//原始单号
                'trade_status' => 30,

                'delivery_term' => 1,//写死
                'cod_amount' => '0',//写死
                'ext_cod_fee' => '0',//写死

                'pay_status' => 2,//2是已付款
                'trade_time' => '0000-00-00 00:00:00',//创建订单时间
                'pay_time' => '0000-00-00 00:00:00', // 未付款情况下为0000-00-00 00:00:00
                'buyer_nick' => '',//昵称

                'receiver_province' => '北京',//不传,直接address解析
                'receiver_city' => '北京市',//不传,直接address解析
                'receiver_district' => '海淀区',//不传,直接address解析

                'receiver_name' => '我欸额附件',//收件人姓名
                'receiver_address' => '海淀',//省区市和详细地址

                'buyer_message' => '测试阿斯顿',//卖家备注

                'post_amount' => 10, //邮费
                'paid' => 409, //已支付金额

                'order_list' => array(
                    array
                    (
                        'oid' => 'LxTestOid' . time(),//子订单id
                        'status' => 30,//子订单状态
                        'refund_status' => 0,//0是无退款
                        'goods_id' => 'E166D18BAAEA420CB132E105B3B6128A',//平台货品id
                        'spec_id' => '',//平台规格id
                        'goods_no' => '',//平台货品编码
                        'spec_no' => '9787533951092',//规格编码,对应erp的商家编码
                        'goods_name' => '情商是什么？——关于生活智慧的44个故事',//商品名称
                        'spec_name' => '',//规格名称
                        'num' => 1,
                        'price' => 399,
                        'adjust_amount' => '0', //手工调整,特别注意:正的表示加价,负的表示减价
                        'discount' => 0, //子订单折扣
                        'share_discount' => '0', //分摊优惠
                    )
                )
            );

            $c->putApiParam('shop_no', 'nlsg2-test');
            $c->putApiParam('switch', 1);
            $c->putApiParam('trade_list', json_encode($trade_list, JSON_UNESCAPED_UNICODE));
            $json = $c->wdtOpenApi();
            $json = json_decode($json, true);
            return $json;
        }


    }
}
