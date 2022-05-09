<?php

namespace App\Servers\V5;

use AccessTokenBuilder;
use App\Models\ConfigModel;
use App\Models\DouDianOrder;
use App\Models\DouDianOrderList;
use App\Models\DouDianOrderLog;
use App\Models\DouDianOrderLogistics;
use GlobalConfig;
use OrderSearchListParam;
use OrderSearchListRequest;

class DouDianServers
{
    protected $appKey = '6857846430543906317';
    protected $appSecret = '2f3af110-3aef-4bf0-8641-f00840b8e87f';
    protected $shopId;
    protected $pageSize = 1;//100以内

    public function __construct() {
        GlobalConfig::getGlobalConfig()->appKey         = $this->appKey;
        GlobalConfig::getGlobalConfig()->appSecret      = $this->appSecret;
        GlobalConfig::getGlobalConfig()->accessTokenStr = ConfigModel::getData(68, 1);
        $this->shopId                                   = ConfigModel::getData(67, 1);
    }

    public function test() {

        $begin_time = strtotime('2022-05-06 00:00:00');
        $end_time   = strtotime('2022-05-06 20:00:00');
        $this->orderSearchList($begin_time, $end_time);

    }

    public function orderSearchList(int $begin, int $end) {

        $page = 0;
        $while_flag = true;


dd(__LINE__);

        $request = new OrderSearchListRequest();
        $param   = new OrderSearchListParam();


        while ($while_flag){

        }




        $request->setParam($param);
        $param->update_time_start = $begin;
        $param->update_time_end   = $end;
        $param->size              = $this->pageSize;
        $param->page              = 5;
        $param->order_by          = "update_time";
        $param->order_asc         = false;

        $response        = $request->execute('');

        $response->page  = $response->data->page ?? 0;
        $response->size  = $response->data->size ?? 0;
        $response->total = $response->data->total ?? 0;

        DouDianOrderLog::query()->create((array)$response);

        if ($response->code !== 10000) {
            if (in_array($response->err_no, [30001, 30002, 30005, 30007])) {
                $this->accessTokenJob();
            }
            return true;
        }

        foreach ($response->data->shop_order_list as $order) {

            $order->encrypt_post_addr_detail = $order->post_addr->encrypt_detail;
            $order->post_addr_detail         = $order->post_addr->detail;
            $order->post_addr_province_name  = $order->post_addr->province->name ?? '';
            $order->post_addr_city_name      = $order->post_addr->city->name ?? '';
            $order->post_addr_town_name      = $order->post_addr->town->name ?? '';
            $order->post_addr_street_name    = $order->post_addr->street->name ?? '';
            $order->post_addr_province_id    = $order->post_addr->province->id ?? '';
            $order->post_addr_city_id        = $order->post_addr->city->id ?? '';
            $order->post_addr_town_id        = $order->post_addr->town->id ?? '';
            $order->post_addr_street_id      = $order->post_addr->street->id ?? '';

            DouDianOrder::query()->updateOrCreate(
                [
                    'order_id' => $order->order_id
                ],
                (array)$order
            );

            foreach ($order->sku_order_list as $sku) {
                DouDianOrderList::query()->updateOrCreate(
                    [
                        'order_id' => $sku->order_id
                    ],
                    (array)$sku
                );
            }

            foreach ($order->logistics_info as $logistics) {
                $logistics->order_id = $order->order_id;

                DouDianOrderLogistics::query()
                    ->updateOrCreate(
                        [
                            'tracking_no' => $logistics->tracking_no,
                            'company'     => $logistics->company,
                        ],
                        (array)$logistics
                    );
            }


        }
    }

    public function accessTokenJob($job = 1) {

        if ($job == 1) {
            $accessToken = AccessTokenBuilder::build($this->shopId, ACCESS_TOKEN_SHOP_ID);
            if ($accessToken->isSuccess()) {
                ConfigModel::query()->where('id', 68)
                    ->update(['value' => $accessToken->getAccessToken()]);

                ConfigModel::query()->where('id', 69)
                    ->update(['value' => $accessToken->getRefreshToken()]);
            }
            return $accessToken;

        } else {
            $old_token     = ConfigModel::getData(68, 1);
            $refresh_token = ConfigModel::getData(69, 1);

            if (empty($old_token) || empty($refresh_token)) {
                $this->accessTokenJob(1);
            } else {
                $accessToken = AccessTokenBuilder::refresh($refresh_token);
                if ($accessToken->isSuccess()) {
                    return $accessToken;
                } else {
                    $this->accessTokenJob(1);
                }
            }
        }

    }

}
