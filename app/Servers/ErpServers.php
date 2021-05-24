<?php


namespace App\Servers;
use WdtClient;

class ErpServers
{
    public function test(){
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
            'tid'              => 'LxTestTid'.time(),//原始单号
            'trade_status'     => 30,

            'delivery_term'    => 1,//写死
            'cod_amount'       => '0',//写死
            'ext_cod_fee'      => '0',//写死

            'pay_status'       => 2,//2是已付款
            'trade_time'       => '0000-00-00 00:00:00',//创建订单时间
            'pay_time'         => '0000-00-00 00:00:00', // 未付款情况下为0000-00-00 00:00:00
            'buyer_nick'       => '',//昵称

            'receiver_province'=>'北京',//不传,直接address解析
            'receiver_city'    =>'北京市',//不传,直接address解析
            'receiver_district'=>'海淀区',//不传,直接address解析

            'receiver_name'    =>'我欸额附件',//收件人姓名
            'receiver_address' =>'海淀',//省区市和详细地址

            'buyer_message'    => '测试阿斯顿',//卖家备注

            'post_amount'      => 10, //邮费
            'paid'             => 409, //已支付金额

            'order_list'       => array(
                array
                (
                    'oid'            => 'LxTestOid'.time(),//子订单id
                    'status'         => 30,//子订单状态
                    'refund_status'  => 0,//0是无退款
                    'goods_id'       => 'E166D18BAAEA420CB132E105B3B6128A',//平台货品id
                    'spec_id'        => '',//平台规格id
                    'goods_no'       => '',//平台货品编码
                    'spec_no'        => '9787533951092',//规格编码,对应erp的商家编码
                    'goods_name'     => '情商是什么？——关于生活智慧的44个故事',//商品名称
                    'spec_name'      => '',//规格名称
                    'num'            => 1,
                    'price'          => 399,
                    'adjust_amount'  => '0', //手工调整,特别注意:正的表示加价,负的表示减价
                    'discount'       => 0, //子订单折扣
                    'share_discount' => '0', //分摊优惠
                )
            )
        );

        $c->putApiParam('shop_no','nlsg2-test');
        $c->putApiParam('switch',1);
        $c->putApiParam('trade_list',json_encode($trade_list, JSON_UNESCAPED_UNICODE));
        $json = $c->wdtOpenApi();
        $json = json_decode($json,true);
        return $json;
    }
}
