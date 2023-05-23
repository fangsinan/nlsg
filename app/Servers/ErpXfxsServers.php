<?php

namespace App\Servers;

use App\Models\GetPriceTools;
use App\Models\MallAddress;
use App\Models\Xfxs\XfxsOrder;
use App\Models\XfxsOrderErpList;
use Illuminate\Support\Facades\DB;
use WdtClient;

class ErpXfxsServers
{
    public $shop_no;
    public $sid;
    public $appkey;
    public $appsecret;
    public $trade_push;//推送订单
    public $logistics_sync_query;//物流同步查询
    public $logistics_sync_ack;//物流同步回写


    //⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇用户完善地址后同步任务⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇
    public function orderUpdateAddressId()
    {

        $list = DB::table('xfxs_order_erp_list as oel')
            ->join('xfxs_order as o', 'oel.order_id', '=', 'o.id')
            ->where('oel.flag', '=', 1)
            ->where('o.address_id', '=', 0)
            ->select(['oel.*', 'o.user_id', 'o.address_id'])
            ->limit(400)
            ->get();

        if ($list->isNotEmpty()) {
            foreach ($list as $v) {
                $temp_address = MallAddress::query()
                    ->where('user_id', '=', $v->user_id)
                    ->where('is_default', '=', 1)
                    ->where('is_del', '=', 0)
                    ->first();

                if ($temp_address) {
                    XfxsOrder::query()
                        ->where('id', '=', $v->order_id)
                        ->update([
                                     'address_id' => $temp_address->id
                                 ]);
                }
            }
        }
    }
    //⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆用户完善地址后同步任务⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆


    //⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇训练营教材订单推送⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇
    //训练营教材订单推送
    public function pushRunForOrder($order_id_list = []): bool
    {
        while (true) {
            $list_query = XfxsOrderErpList::query()
                ->with([
                           'orderInfo:id,type,user_id,status,ordernum,pay_price,created_at,pay_time,express_info_id,textbook_id,address_id,is_shill',
                           'orderInfo.addressInfo:id,name,phone,details,user_id,province,city,area',
                           'orderInfo.textbookInfo:id,erp_sku,title,sub_title,price',
                           'orderInfo.user:id,nickname,phone',
                           'orderInfo.addressInfo.area_province:id,fullname',
                           'orderInfo.addressInfo.area_city:id,fullname',
                           'orderInfo.addressInfo.area_area:id,fullname',
                       ])
                ->where('flag', '=', 1)
                ->whereHas('orderInfo', function ($q) {
                    $q->where('textbook_id', '>', 0)
                        ->where('address_id', '>', 0)
                        ->where('express_info_id', '=', 0)
                        ->where('pay_price', '>', 0.01);
                })
                ->select(['id', 'order_id', 'flag']);

            if (!empty($order_id_list)) {
                $list_query->whereIn('order_id', $order_id_list);
            }

            $list = $list_query->limit(50)->get();

            if ($list->isEmpty()) {
                break;
            }
            $list_ids = $list->pluck('id')->toArray();

            $trade_list = [];
            foreach ($list as $v) {
                $temp_trade_list = [];
                $temp_order_list = [];

                $temp_trade_list['tid']        = $v->OrderInfo->ordernum;
                $temp_trade_list['trade_time'] = date('Y-m-d H:i:s', strtotime($v->orderInfo->created_at));
                $temp_trade_list['pay_time']   = date('Y-m-d H:i:s', strtotime($v->orderInfo->pay_time));
                $temp_trade_list['buyer_nick'] = $this->filterEmoji($v->orderInfo->user->nickname);

                if (empty($v->orderInfo->is_shill)) {
                    $temp_trade_list['trade_status'] = 30;
                } else {
                    $temp_trade_list['trade_status'] = 80;
                }

                $temp_trade_list['pay_status'] = 2;
                $temp_address_name             = $this->filterEmoji($v->orderInfo->addressInfo->name);
                if (empty($temp_address_name)) {
                    $temp_trade_list['receiver_name'] = substr_replace($v->orderInfo->addressInfo->phone, '****', 3, 4);
                } else {
                    $temp_trade_list['receiver_name'] = $temp_address_name;
                }
                $temp_trade_list['receiver_mobile']  = $v->orderInfo->addressInfo->phone;
                $temp_trade_list['receiver_address'] = trim(
                    ($v->orderInfo->addressInfo->area_province->fullname ?? '') . ' ' .
                    ($v->orderInfo->addressInfo->area_city->fullname ?? '') . ' ' .
                    ($v->orderInfo->addressInfo->area_area->fullname ?? '') . ' ' .
                    $v->orderInfo->addressInfo->details
                );
                $temp_trade_list['buyer_message']    = '';
                $temp_trade_list['post_amount']      = 0;
                $temp_trade_list['delivery_term']    = 1;
                $temp_trade_list['cod_amount']       = 0;
                $temp_trade_list['ext_cod_fee']      = 0;
                $temp_order_list['oid']              = $v->order_id;


                if (empty($v->orderInfo->is_shill)) {
                    $temp_order_list['status'] = 30;//子订单状态
                } else {
                    $temp_order_list['status'] = 80;//子订单状态
                }

                $temp_order_list['refund_status'] = 0;//0是无退款

                $temp_order_list['goods_id']       = $v->orderInfo->textbook_id;//平台货品id
                $temp_order_list['spec_id']        = $v->orderInfo->textbook_id;//平台规格id
                $temp_order_list['spec_no']        = strval($v->orderInfo->textbookInfo->erp_sku);//平台货品SKU唯一码，对应ERP商家编码，goods_no和spec_no不能同时为空
                $temp_order_list['goods_name']     = strval($v->orderInfo->textbookInfo->title);//商品名称
                $temp_order_list['price']          = $v->orderInfo->textbookInfo->price;
                $temp_order_list['spec_name']      = strval($v->orderInfo->textbookInfo->sub_title);
                $temp_order_list['num']            = max($v->orderInfo->live_num ?? 1, 1);
                $temp_order_list['adjust_amount']  = '0'; //手工调整;特别注意:正的表示加价;负的表示减价
                $temp_order_list['discount']       = 0; //子订单折扣
                $temp_order_list['share_discount'] = '0';
                $temp_trade_list['paid']           = GetPriceTools::PriceCalc(
                    '*', $temp_order_list['price'], $temp_order_list['num']
                );

                $temp_trade_list['order_list'][] = $temp_order_list;
                $trade_list[]                    = $temp_trade_list;
            }

            $res = $this->pushOrderJob($trade_list);

            if (!$res['code']) {
                $error_message = json_decode($res['msg'], true);
                if (is_array($error_message)) {
                    $error_data = [];
                    foreach ($error_message as $v) {
                        $temp_error_data               = [];
                        $temp_error_data['ordernum']   = $v['tid'];
                        $temp_error_data['error']      = $v['error'];
                        $temp_error_data['type']       = 1;
                        $temp_error_data['order_type'] = 3;
                        $error_data[]                  = $temp_error_data;
                    }
                    if (!empty($error_data)) {
                        DB::table('nlsg_mall_order_erp_error')->insert($error_data);
                    }
                }
            } else {
                XfxsOrderErpList::query()
                    ->whereIn('id', $list_ids)
                    ->update(['flag' => 2]);
            }

        }
        return true;
    }
    //⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆训练营教材订单推送⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆


    //⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇公共部分⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇
    //订单推送动作
    private function pushOrderJob($trade_list)
    {
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

    public function tradeQueryJob($ordernum)
    {
        $c             = new WdtClient();
        $c->sid        = $this->sid;
        $c->appkey     = $this->appkey;
        $c->appsecret  = $this->appsecret;
        $c->gatewayUrl = 'https://api.wangdian.cn/openapi2/trade_query.php';

        $c->putApiParam('shop_no', $this->shop_no);
        $c->putApiParam('is_part_sync_able', 1);
        $c->putApiParam('limit', 100);
        $c->putApiParam('src_tid', $ordernum);
        $json = $c->wdtOpenApi();
        return json_decode($json, true);
    }

    public function __construct()
    {

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
    function filterEmoji($str)
    {
        return preg_replace_callback(
            '/./u',
            function (array $match) {
                return strlen($match[0]) >= 4 ? '' : $match[0];
            },
            $str);
    }

    //⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆公共部分⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆⬆
}
