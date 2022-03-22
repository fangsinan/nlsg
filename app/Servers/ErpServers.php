<?php


namespace App\Servers;

use App\Models\ExpressCompany;
use App\Models\ExpressInfo;
use App\Models\MallAddress;
use App\Models\MallErpList;
use App\Models\MallOrder;
use App\Models\MallOrderDetails;
use App\Models\Order;
use App\Models\OrderErpList;
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
    public function startPush($id) {
        if (!is_array($id)) {
            $id = explode(',', $id);
        }
        $id = array_unique($id);

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

            $temp_trade_list['tid']        = $v->ordernum;
            $temp_trade_list['trade_time'] = date('Y-m-d H:i:s', strtotime($v->created_at));
            $temp_trade_list['pay_time']   = date('Y-m-d H:i:s', strtotime($v->pay_time));
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
            $temp_address_history                = json_decode($v->address_history);
            $temp_trade_list['receiver_name']    = $temp_address_history->name;
            $temp_trade_list['receiver_mobile']  = $temp_address_history->phone;
            $temp_trade_list['receiver_address'] = trim(
                $temp_address_history->province_name . ' ' .
                $temp_address_history->city_name . ' ' .
                $temp_address_history->area_name . ' ' .
                $temp_address_history->details
            );

            $temp_trade_list['buyer_message'] = $v->messages;
            $temp_trade_list['post_amount']   = $v->freight;
            $temp_trade_list['paid']          = $v->price;//使用pay_price,如果是测试订单会金额错误.

            $temp_trade_list['delivery_term'] = 1;
            $temp_trade_list['cod_amount']    = 0;
            $temp_trade_list['ext_cod_fee']   = 0;

            foreach ($v->orderDetails as $vv) {
                $temp           = [];
                $temp['oid']    = $v->id . '_' . $vv->details_id;
                $temp['status'] = $temp_trade_list['trade_status'];//子订单状态
                if ($v->is_stop == 1) {
                    $temp['refund_status'] = 5;//0是无退款
                } else {
                    $temp['refund_status'] = 0;//0是无退款
                }
                $temp['goods_id'] = $vv->goods_id;//平台货品id
                $temp['spec_id']  = $vv->sku_number;//平台规格id
                //$temp['goods_no'] = $vv->skuInfo->erp_goods_no;//平台货品编码
                $temp['spec_no']        = strval($vv->skuInfo->erp_spec_no);//平台货品SKU唯一码，对应ERP商家编码，goods_no和spec_no不能同时为空
                $temp['goods_name']     = $vv->goodsInfo->name;//商品名称
                $temp_sku_history       = json_decode($vv->sku_history, true);
                $temp['price']          = $temp_sku_history['actual_price'];
                $temp['spec_name']      = implode(Arr::pluck($temp_sku_history['sku_value'], 'value_name'), ',');
                $temp['num']            = $vv->num;
                $temp['adjust_amount']  = '0'; //手工调整;特别注意:正的表示加价;负的表示减价
                $temp['discount']       = 0; //子订单折扣
                $temp['share_discount'] = '0';
                $temp_order_list[]      = $temp;
            }
            $temp_trade_list['order_list'] = $temp_order_list;

            $trade_list[] = $temp_trade_list;
        }

        //true
        $res = $this->pushOrderJob($trade_list);

        DB::table('wwwww')->insert([
            'vv' => json_encode($res),
            't'  => json_encode($trade_list),
        ]);

        if ($res['code'] != true) {
            $error_message = json_decode($res['msg'], true);

            $error_data = [];
            foreach ($error_message as $v) {
                $temp_error_data             = [];
                $temp_error_data['ordernum'] = $v['tid'];
                $temp_error_data['error']    = $v['error'];
                $temp_error_data['type']     = 1;
                $error_data[]                = $temp_error_data;
            }
            if (!empty($error_data)) {
                DB::table('nlsg_mall_order_erp_error')->insert($error_data);
            }
        }
        return $res;

    }

    //订单推送动作
    private function pushOrderJob($trade_list) {
        if (empty($trade_list) || !is_array($trade_list)) {
            return ['code' => false, 'msg' => '数据不正确'];
        }

        $c = new WdtClient();

        $c->sid        = $this->sid;
        $c->appkey     = $this->appkey;
        $c->appsecret  = $this->appsecret;
        $c->gatewayUrl = $this->trade_push;

        $c->putApiParam('shop_no', $this->shop_no);
        $c->putApiParam('switch', 1);
        $c->putApiParam('trade_list', json_encode($trade_list, JSON_UNESCAPED_UNICODE));
        $json = $c->wdtOpenApi();

        $error_data             = [];
        $error_data['ordernum'] = '';
        $error_data['error']    = $json;
        $error_data['type']     = 4;
        DB::table('nlsg_mall_order_erp_error')->insert($error_data);

        $json = json_decode($json, true);

        if ($json['code'] == 0) {
            return ['code' => true, 'msg' => '成功:' . $json['new_count'] . ':' . $json['chg_count']];
        } else {
            return ['code' => false, 'msg' => $json['message']];
        }
    }

    //物流同步
    public function logisticsSync() {

        $list = $this->logisticsSyncQuery();

        if (!empty($list)) {
            $expressCompany = ExpressCompany::where('status', '=', 1)
                ->select(['id', 'erp_type_id'])
                ->get()->toArray();
            $expressCompany = array_column($expressCompany, 'id', 'erp_type_id');

            $orderServers = new MallOrderServers();
            $ack_data     = [];//需要回传的id

            foreach ($list as $v) {
                $send_data_temp = [];

                $check_order_type = substr($v['tid'], -2);

                if ($check_order_type === '01') {
                    //商城部分
                    $tid = MallOrder::query()
                        ->where('ordernum', '=', $v['tid'])
                        ->select(['id'])->first();

                    if ($v['is_part_sync'] == 1) {
                        //1是拆分了,走oids
                        $oids = explode(',', $v['oids']);
                    } else {
                        //0是没拆分,走tid
                        $oids = MallOrderDetails::where('order_id', '=', $tid->id)->pluck('id')->toArray();
                        foreach ($oids as &$oidsv) {
                            $oidsv = $tid->id . '_' . $oidsv;
                        }
                    }

                    foreach ($oids as $vv) {
                        $send_data               = [];
                        $temp_oids               = explode('_', $vv);
                        $send_data['express_id'] = $expressCompany[$v['logistics_type']] ?? 0;
                        if (!$send_data['express_id']) {
                            continue;
                        }
                        $send_data['num']             = $v['logistics_no'];
                        $send_data['order_id']        = $tid->id ?? 0;
                        $send_data['order_detail_id'] = $temp_oids[1] ?? 0;
                        $send_data_temp[]             = $send_data;
                    }

                    $send_res                = $orderServers->send($send_data_temp);
                    $temp_ack_data           = [];
                    $temp_ack_data['rec_id'] = $v['rec_id'];
                    if ($send_res['code'] == true) {
                        $temp_ack_data['status']  = 0;
                        $temp_ack_data['message'] = '成功';
                    } else {
                        $temp_ack_data['status']  = 1;
                        $temp_ack_data['message'] = $send_res['msg'] ?? '失败';
                    }
                    $ack_data[] = $temp_ack_data;
                } else {
                    $now_date = date('Y-m-d H:i:s');
                    //训练营教材订单
                    $send_data['express_id']      = $expressCompany[$v['logistics_type']] ?? 0;
                    $send_data['num']             = $v['logistics_no'];
                    $send_data['order_id']        = $tid->id ?? 0;
                    $send_data['order_detail_id'] = 0;

                    $check_ex = ExpressInfo::query()
                        ->where('express_id', '=', $send_data['express_id'])
                        ->where('express_num', '=', $send_data['num'])
                        ->first();

                    if ($check_ex) {
                        $express_info_id = $check_ex->id;
                    } else {
                        $ex_data['express_id']  = $send_data['express_id'];
                        $ex_data['express_num'] = $send_data['num'];

                        $express_company_info     = ExpressCompany::query()->find($send_data['express_id']);
                        $history                  = [];
                        $history['number']        = $v['logistics_no'];
                        $history['type']          = $express_company_info->code;
                        $history['typename']      = $express_company_info->name;
                        $history['express_phone'] = $express_company_info->phone;
                        $history['logo']          = $express_company_info->logo;
                        $history['list']          = [
                            [
                                'time'   => $now_date,
                                'status' => '商家发货'
                            ]
                        ];

                        $ex_data['history'] = json_encode($history);

                        $ex_data['created_at'] = $ex_data['updated_at'] = $now_date;
                        $express_info_id       = DB::table('nlsg_express_info')->insertGetId($ex_data);
                        if (!$express_info_id) {
                            continue;
                        }
                    }

                    $order_express_info_res = Order::query()->where('ordernum', '=', $v['tid'])
                        ->update([
                            'express_info_id' => $express_info_id
                        ]);

                    $temp_ack_data           = [];
                    $temp_ack_data['rec_id'] = $v['rec_id'];
                    if ($order_express_info_res) {
                        $temp_ack_data['status']  = 0;
                        $temp_ack_data['message'] = '成功';
                    } else {
                        $temp_ack_data['status']  = 1;
                        $temp_ack_data['message'] = '失败';
                    }
                    $ack_data[] = $temp_ack_data;
                }


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
    private function logisticsSyncQuery() {
        $c             = new WdtClient();
        $c->sid        = $this->sid;
        $c->appkey     = $this->appkey;
        $c->appsecret  = $this->appsecret;
        $c->gatewayUrl = $this->logistics_sync_query;

        $c->putApiParam('shop_no', $this->shop_no);
        $c->putApiParam('is_part_sync_able', 1);
        $c->putApiParam('limit', 100);
        $json = $c->wdtOpenApi();
        $json = json_decode($json, true);

        if (!empty($json['code'])) {
            $temp_error_data['ordernum'] = 'query';
            $temp_error_data['error']    = $json['code'] . ':' . $json['message'];
            $temp_error_data['type']     = 2;
            DB::table('nlsg_mall_order_erp_error')->insert($temp_error_data);
        }

        return $json['trades'] ?? [];
    }

    //物流回写动作
    private function logisticsSyncAck($ack_data) {
        $c             = new WdtClient();
        $c->sid        = $this->sid;
        $c->appkey     = $this->appkey;
        $c->appsecret  = $this->appsecret;
        $c->gatewayUrl = $this->logistics_sync_ack;
        $c->putApiParam('logistics_list', json_encode($ack_data, JSON_UNESCAPED_UNICODE));
        $json = $c->wdtOpenApi();
        $json = json_decode($json, true);
        if (!empty($json['code'])) {
            $temp_error_data['ordernum'] = 'ack';
            $temp_error_data['error']    = $json['code'] . ':' . $json['message'];
            $temp_error_data['type']     = 3;
            DB::table('nlsg_mall_order_erp_error')->insert($temp_error_data);
        }
    }

    //商城订单推送
    public function pushRun() {
        $while_flag = true;

        while ($while_flag) {
            $list = MallErpList::where('flag', '=', 1)->limit(30)->select(['id', 'order_id'])->get();
            if ($list->isEmpty()) {
                $while_flag = false;
            } else {
                $list          = $list->toArray();
                $id_list       = array_column($list, 'id');
                $order_id_list = array_column($list, 'order_id');

                $res = $this->startPush($order_id_list);//订单推送

//                if ($res['code']) {
                MallErpList::whereIn('id', $id_list)
                    ->update([
                        'flag' => 2
                    ]);
//                }
            }
        }

        return true;
        //订单推送场景   支付成功,取消订单
//        return $this->startPush([10523,10524]);//订单推送
    }

    //训练营教材订单推送
    public function pushRunForOrder(): bool {
        $while_flag = true;

        while ($while_flag) {

            $list = OrderErpList::query()
                ->with([
                    'orderInfo:id,type,live_num,user_id,status,ordernum,pay_price,created_at,pay_time,express_info_id,textbook_id,address_id',
                    'orderInfo.addressInfo:id,name,phone,details,user_id,province,city,area',
                    'orderInfo.textbookInfo:id,erp_sku,title,sub_title',
                    'orderInfo.user:id,nickname,phone',
                    'orderInfo.addressInfo.area_province:id,fullname',
                    'orderInfo.addressInfo.area_city:id,fullname',
                    'orderInfo.addressInfo.area_area:id,fullname',
                ])
                ->where('flag', '=', 1)
                ->whereHas('orderInfo', function ($q) {
                    $q->where('textbook_id', '>', 0)
                        ->where('address_id', '>', 0)
                        ->where('express_info_id', '=', 0);
                })
                ->select(['id', 'order_id', 'flag'])
                ->limit(50)
                ->get();

            if ($list->isEmpty()) {
                return true;
            }
            $list_ids = $list->pluck('id')->toArray();

            $trade_list = [];
            foreach ($list as $v) {
                $temp_trade_list = [];
                $temp_order_list = [];

                $temp_trade_list['tid']              = $v->OrderInfo->ordernum;
                $temp_trade_list['trade_time']       = date('Y-m-d H:i:s', strtotime($v->orderInfo->created_at));
                $temp_trade_list['pay_time']         = date('Y-m-d H:i:s', strtotime($v->orderInfo->pay_time));
                $temp_trade_list['buyer_nick']       = $this->filterEmoji($v->orderInfo->user->nickname);
                $temp_trade_list['trade_status']     = 30;
                $temp_trade_list['pay_status']       = 2;
                $temp_trade_list['receiver_name']    = $v->orderInfo->addressInfo->name;
                $temp_trade_list['receiver_mobile']  = $v->orderInfo->addressInfo->phone;
                $temp_trade_list['receiver_address'] = trim(
                    ($v->orderInfo->addressInfo->area_province->fullname ?? '') . ' ' .
                    ($v->orderInfo->addressInfo->area_city->fullname ?? ''). ' ' .
                    ($v->orderInfo->addressInfo->area_area->fullname ?? '') . ' ' .
                    $v->orderInfo->addressInfo->details
                );
                $temp_trade_list['buyer_message']    = '';
                $temp_trade_list['post_amount']      = 0;
                $temp_trade_list['paid']             = 0;
                $temp_trade_list['delivery_term']    = 1;
                $temp_trade_list['cod_amount']       = 0;
                $temp_trade_list['ext_cod_fee']      = 0;

                $temp_order_list['oid']           = $v->id . '_' . $v->order_id;
                $temp_order_list['status']        = 30;//子订单状态
                $temp_order_list['refund_status'] = 0;//0是无退款

                $temp_order_list['goods_id'] = $v->orderInfo->textbook_id;//平台货品id
                $temp_order_list['spec_id']  = $v->orderInfo->textbook_id;//平台规格id

                $temp_order_list['spec_no']        = strval($v->orderInfo->textbookInfo->erp_sku);//平台货品SKU唯一码，对应ERP商家编码，goods_no和spec_no不能同时为空
                $temp_order_list['goods_name']     = strval($v->orderInfo->textbookInfo->title);//商品名称
                $temp_order_list['price']          = 0;
                $temp_order_list['spec_name']      = strval($v->orderInfo->textbookInfo->sub_title);
                $temp_order_list['num']            = max($v->orderInfo->live_num, 1);
                $temp_order_list['adjust_amount']  = '0'; //手工调整;特别注意:正的表示加价;负的表示减价
                $temp_order_list['discount']       = 0; //子订单折扣
                $temp_order_list['share_discount'] = '0';

                $temp_trade_list['order_list'][] = $temp_order_list;
                $trade_list[]                    = $temp_trade_list;
            }

            $res = $this->pushOrderJob($trade_list);

            if ($res['code'] != true) {
                $error_message = json_decode($res['msg'], true);

                $error_data = [];
                foreach ($error_message as $v) {
                    $temp_error_data               = [];
                    $temp_error_data['ordernum']   = $v['tid'];
                    $temp_error_data['error']      = $v['error'];
                    $temp_error_data['type']       = 1;
                    $temp_error_data['order_type'] = 2;
                    $error_data[]                  = $temp_error_data;
                }
                if (!empty($error_data)) {
                    DB::table('nlsg_mall_order_erp_error')->insert($error_data);
                }
            }

            OrderErpList::query()
                ->whereIn('id', $list_ids)
                ->update(['flag' => 2]);
        }
        return true;
    }

    public function orderUpdateAddressId(){

        $list = DB::table('nlsg_order_erp_list as oel')
            ->join('nlsg_order as o','oel.order_id','=','o.id')
            ->where('oel.flag','=',1)
            ->where('o.address_id','=',0)
            ->select(['oel.*','o.user_id','o.address_id'])
            ->get();

        if ($list->isEmpty()){
            return true;
        }

        foreach ($list as $v){
            $temp_address = MallAddress::query()
                ->where('user_id','=',$v->user_id)
                ->where('is_default','=',1)
                ->where('is_del','=',0)
                ->first();

            if ($temp_address){
                Order::query()
                    ->where('id','=',$v->order_id)
                    ->update([
                        'address_id'=>$temp_address->id
                    ]);
            }
        }
    }

    public function __construct() {

        $this->sid                  = config('env.ERP_SID');
        $this->shop_no              = config('env.ERP_SHOP_NO');
        $this->appkey               = config('env.ERP_APPKEY');
        $this->appsecret            = config('env.ERP_APPSECRET');
        $this->trade_push           = config('env.ERP_TRADE_PUSH');
        $this->logistics_sync_query = config('env.ERP_LOGISTICS_SYNC_QUERY');
        $this->logistics_sync_ack   = config('env.ERP_LOGISTICS_SYNC_ACK');

//        $this->sid                  = 'nlsg2';
//        $this->shop_no              = '04';
//        $this->appkey               = 'nlsg2-gw';
//        $this->appsecret            = 'c93045fe195cc51977ad8daab2e4b664';
//        $this->trade_push           = 'https://api.wangdian.cn/openapi2/trade_push.php';
//        $this->logistics_sync_query = 'https://api.wangdian.cn/openapi2/logistics_sync_query.php';
//        $this->logistics_sync_ack   = 'https://api.wangdian.cn/openapi2/logistics_sync_ack.php';

    }

    //去掉昵称的emoji
    function filterEmoji($str) {
        return preg_replace_callback(
            '/./u',
            function (array $match) {
                return strlen($match[0]) >= 4 ? '' : $match[0];
            },
            $str);
    }
}
