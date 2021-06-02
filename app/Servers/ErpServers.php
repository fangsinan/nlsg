<?php


namespace App\Servers;

use App\Models\ExpressCompany;
use App\Models\MallErpError;
use App\Models\MallErpList;
use App\Models\MallOrder;
use App\Models\MallOrderDetails;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use WdtClient;

class ErpServers
{
    public $shop_no;
    public $sid;
    public $appkey;
    public $appsecret;
    public $trade_push;//推送订单
    public $logistics_sync_query;//物流同步查询
    public $logistics_sync_ack;//物流同步回写

    //推送触发时机:支付,取消订单,确认收货
    public function startPush($id)
    {
        if (!is_array($id)) {
            $id = explode(',', $id);
        }

        $list = MallOrder::whereIn('id', $id)
            ->with(['orderDetails', 'orderDetails.skuInfo', 'orderDetails.goodsInfo', 'userInfo'])
            ->select([
                'id', 'ordernum', 'pay_price', 'price', 'user_id', 'order_type', 'status',
                'pay_time', 'pay_type', 'messages', 'remark', 'post_type', 'freight',
                'address_id', 'address_history', 'created_at', 'updated_at', 'is_stop', 'stop_at'
            ])
            ->get();

        $trade_list = [];

        foreach ($list as $v) {
            if (!in_array($v->status, [10, 20, 30])) {
                continue;
            }

            $temp_trade_list = [];
            $temp_order_list = [];

            $temp_trade_list['tid'] = $v->ordernum;
            $temp_trade_list['trade_time'] = date('Y-m-d H:i:s', strtotime($v->created_at));
            $temp_trade_list['pay_time'] = date('Y-m-d H:i:s', strtotime($v->pay_time));
            $temp_trade_list['buyer_nick'] = $this->filterEmoji($v->userInfo->nickname);

            /**
             * trade_status=30    已付款待发货(包含货到付款)，30只可以直接变更为70/ 80这2种状态
             * trade_status=40    部分发货(拆分发货才会出现)
             * trade_status=50    已发货(平台销售订单已经发货时推送此状态)，如果已发货在自建商城代表订单完结状态，无后续状态变更，直接推送状态值70。
             * trade_status=70    已完成（已签收），平台订单完成（客户确认收货）后，推送此状态;
             * 订单为自动流转模式时，初次推送的平台订单状态直接就是已完成状态70
             * trade_status=80    已退款(付款后又全部退款推送此状态)
             */
            if ($v->is_stop == 1) {
                $temp_trade_list['trade_status'] = 80;
            } else {
                switch (intval($v->status)) {
                    //订单状态 1待付款  10待发货 20待收货 30已完成
                    case 10:
                        $temp_trade_list['trade_status'] = 30;
                        break;
                    case 20:
                        $temp_trade_list['trade_status'] = 50;
                        break;
                    case 30:
                    default:
                        $temp_trade_list['trade_status'] = 70;
                }
            }

            //平台订单付款状态:0:未付款,1:部分付款,2:已付款
            $temp_trade_list['pay_status'] = 2;

            //省区市和详细地址
            $temp_address_history = json_decode($v->address_history);
            $temp_trade_list['receiver_name'] = $temp_address_history->name;
            $temp_trade_list['receiver_mobile'] = $temp_address_history->phone;
            $temp_trade_list['receiver_address'] = trim(
                $temp_address_history->province_name . ' ' .
                $temp_address_history->city_name . ' ' .
                $temp_address_history->area_name . ' ' .
                $temp_address_history->details
            );

            $temp_trade_list['buyer_message'] = $v->messages;
            $temp_trade_list['post_amount'] = $v->freight;
            $temp_trade_list['paid'] = $v->price;//使用pay_price,如果是测试订单会金额错误.

            $temp_trade_list['delivery_term'] = 1;
            $temp_trade_list['cod_amount'] = 0;
            $temp_trade_list['ext_cod_fee'] = 0;

            foreach ($v->orderDetails as $vv) {
                $temp = [];
                $temp['oid'] = $v->id . '_' . $vv->details_id;
                $temp['status'] = $temp_trade_list['trade_status'];//子订单状态
                if ($v->is_stop == 1) {
                    $temp['refund_status'] = 5;//0是无退款
                } else {
                    $temp['refund_status'] = 0;//0是无退款
                }
                $temp['goods_id'] = $vv->goods_id;//平台货品id
                $temp['spec_id'] = $vv->sku_number;//平台规格id
                $temp['goods_no'] = $vv->skuInfo->erp_goods_no;//平台货品编码
                $temp['spec_no'] = $vv->skuInfo->erp_spec_no;//平台货品SKU唯一码，对应ERP商家编码，goods_no和spec_no不能同时为空
                $temp['goods_name'] = $vv->goodsInfo->name;//商品名称
                $temp_sku_history = json_decode($vv->sku_history, true);
                $temp['price'] = $temp_sku_history['actual_price'];
                $temp['spec_name'] = implode(Arr::pluck($temp_sku_history['sku_value'], 'value_name'), ',');
                $temp['num'] = $vv->num;
                $temp['adjust_amount'] = '0'; //手工调整;特别注意:正的表示加价;负的表示减价
                $temp['discount'] = 0; //子订单折扣
                $temp['share_discount'] = '0';
                $temp_order_list[] = $temp;
            }
            $temp_trade_list['order_list'] = $temp_order_list;

            $trade_list[] = $temp_trade_list;
        }

        //true
        $res = $this->pushOrderJob($trade_list);

        if ($res['code'] != true) {
            $error_message = json_decode($res['msg'], true);

            $error_data = [];
            foreach ($error_message as $v) {
                $temp_error_data = [];
                $temp_error_data['ordernum'] = $v['tid'];
                $temp_error_data['error'] = $v['error'];
                $temp_error_data['type'] = 1;
                $error_data[] = $temp_error_data;
            }
            if (!empty($error_data)) {
                DB::table('nlsg_mall_order_erp_error')->insert($error_data);
            }
        }
        return $res;

    }

    //订单推送动作
    private function pushOrderJob($trade_list)
    {
        if (empty($trade_list) || !is_array($trade_list)) {
            return ['code' => false, 'msg' => '数据不正确'];
        }
        $c = new WdtClient();

        $c->sid = $this->sid;
        $c->appkey = $this->appkey;
        $c->appsecret = $this->appsecret;
        $c->gatewayUrl = $this->trade_push;

        $c->putApiParam('shop_no', $this->shop_no);
        $c->putApiParam('switch', 1);
        $c->putApiParam('trade_list', json_encode($trade_list, JSON_UNESCAPED_UNICODE));
        $json = $c->wdtOpenApi();
        $json = json_decode($json, true);

        if ($json['code'] == 0) {
            return ['code' => true, 'msg' => '成功:' . $json['new_count'] . ':' . $json['chg_count']];
        } else {
            return ['code' => false, 'msg' => $json['message']];
        }
    }

    //物流同步
    public function logisticsSync()
    {

        $list = $this->logisticsSyncQuery();

        if (!empty($list)) {
            $expressCompany = ExpressCompany::where('status', '=', 1)
                ->select(['id', 'erp_type_id'])
                ->get()->toArray();
            $expressCompany = array_column($expressCompany, 'id', 'erp_type_id');

            $orderServers = new MallOrderServers();
            $ack_data = [];//需要回传的id

            foreach ($list as $v) {
                $send_data_temp = [];
                $tid = MallOrder::where('ordernum', '=', $v['tid'])->select(['id'])->first();

                if ($v['is_part_sync'] == 1) {
                    //1是拆分了,走oids
                    $oids = explode(',', $v['oids']);
                } else {
                    //0是没拆分,走tid
                    $oids = MallOrderDetails::where('order_id', '=', $tid->id)
                        ->pluck('id')->toArray();
                    foreach ($oids as &$oidsv) {
                        $oidsv = $tid->id . '_' . $oidsv;
                    }
                }

                foreach ($oids as $vv) {
                    $send_data = [];
                    $temp_oids = explode('_', $vv);
                    $send_data['express_id'] = $expressCompany[$v['logistics_type']] ?? 0;
                    if (!$send_data['express_id']) {
                        continue;
                    }
                    $send_data['num'] = $v['logistics_no'];
                    $send_data['order_id'] = $tid->id ?? 0;
                    $send_data['order_detail_id'] = $temp_oids[1] ?? 0;
                    $send_data_temp[] = $send_data;
                }

                $send_res = $orderServers->send($send_data_temp);
                $temp_ack_data = [];
                $temp_ack_data['rec_id'] = $v['rec_id'];
                if ($send_res['code'] == true) {
                    $temp_ack_data['status'] = 0;
                    $temp_ack_data['message'] = '成功';
                } else {
                    $temp_ack_data['status'] = 1;
                    $temp_ack_data['message'] = $send_res['msg'] ?? '失败';
                }
                $ack_data[] = $temp_ack_data;
            }
            if (!empty($ack_data)) {
                $this->logisticsSyncAck($ack_data);
            }
            $this->logisticsSync();
        } else {
            return true;
        }

    }

    //物流查询动作
    private function logisticsSyncQuery()
    {
        $c = new WdtClient();
        $c->sid = $this->sid;
        $c->appkey = $this->appkey;
        $c->appsecret = $this->appsecret;
        $c->gatewayUrl = $this->logistics_sync_query;

        $c->putApiParam('shop_no', $this->shop_no);
        $c->putApiParam('is_part_sync_able', 1);
        $c->putApiParam('limit', 100);
        $json = $c->wdtOpenApi();
        $json = json_decode($json, true);

        if (!empty($json['code'])) {
            $temp_error_data['ordernum'] = 'query';
            $temp_error_data['error'] = $json['code'] . ':' . $json['message'];
            $temp_error_data['type'] = 2;
            DB::table('nlsg_mall_order_erp_error')->insert($temp_error_data);
        }

        return $json['trades'] ?? [];
    }

    //物流回写动作
    private function logisticsSyncAck($ack_data)
    {
        $c = new WdtClient();
        $c->sid = $this->sid;
        $c->appkey = $this->appkey;
        $c->appsecret = $this->appsecret;
        $c->gatewayUrl = $this->logistics_sync_ack;
        $c->putApiParam('logistics_list', json_encode($ack_data, JSON_UNESCAPED_UNICODE));
        $json = $c->wdtOpenApi();
        $json = json_decode($json, true);
        if (!empty($json['code'])) {
            $temp_error_data['ordernum'] = 'ack';
            $temp_error_data['error'] = $json['code'] . ':' . $json['message'];
            $temp_error_data['type'] = 3;
            DB::table('nlsg_mall_order_erp_error')->insert($temp_error_data);
        }
    }

    public function pushRun()
    {
        $while_flag = true;

        while ($while_flag){
            $list = MallErpList::where('flag', '=', 1)->limit(30)->select(['id', 'order_id'])->get();
            if ($list->isEmpty()) {
                $while_flag = false;
            }else{
                $list = $list->toArray();
                $id_list = array_column($list, 'id');
                $order_id_list = array_column($list, 'order_id');

                $res = $this->startPush($order_id_list);//订单推送

                if ($res['code']) {
                    MallErpList::whereIn('id', $id_list)
                        ->update([
                            'flag' => 2
                        ]);
                }
            }
        }

        return true;
        //订单推送场景   支付成功,取消订单
//        return $this->startPush([10523,10524]);//订单推送
    }

    public function __construct()
    {
        //= config('env.ALI_APP_ID');
        $this->sid = config('env.ERP_SID');
        $this->shop_no = config('env.ERP_SHOP_NO');
        $this->appkey = config('env.ERP_APPKEY');
        $this->appsecret = config('env.ERP_APPSECRET');
        $this->trade_push = config('env.ERP_TRADE_PUSH');
        $this->logistics_sync_query = config('env.ERP_LOGISTICS_SYNC_QUERY');
        $this->logistics_sync_ack = config('env.ERP_LOGISTICS_SYNC_ACK');
    }

    //去掉昵称的emoji
    function filterEmoji($str)
    {
        return preg_replace_callback(
            '/./u',
            function (array $match) {
                return strlen($match[0]) >= 4 ? '' : $match[0];
            },
            $str);
    }
}
