<?php

namespace App\Servers\V5;

use AccessTokenBuilder;
use App\Models\ConfigModel;
use App\Models\DouDianOrder;
use App\Models\DouDianOrderList;
use App\Models\DouDianOrderLog;
use App\Models\DouDianOrderLogistics;
use GlobalConfig;
use OrderBatchDecryptParam;
use OrderBatchDecryptRequest;
use OrderSearchListParam;
use OrderSearchListRequest;

class DouDianServers
{
    protected $appKey = '6857846430543906317';
    protected $appSecret = '2f3af110-3aef-4bf0-8641-f00840b8e87f';
    protected $shopId;
    protected $pageSize = 50;//100以内

    public function __construct() {
        GlobalConfig::getGlobalConfig()->appKey         = $this->appKey;
        GlobalConfig::getGlobalConfig()->appSecret      = $this->appSecret;
        GlobalConfig::getGlobalConfig()->accessTokenStr = ConfigModel::getData(68, 1);
        $this->shopId                                   = ConfigModel::getData(67, 1);
    }

    public function test() {

    }

    //解密任务 1分一次
    public function decryptJob() {
        //0-2
        $this->toDecrypt();
        $this->toDecrypt(1);
        $this->toDecrypt(2);
    }

    //拉订单任务 十分钟一次
    public function getOrderJob() {

        $time_flag = ConfigModel::getData(70, 1);

        if (!empty($time_flag) && strtotime($time_flag)) {
            $begin_time = strtotime($time_flag);
            $end_time   = $begin_time + 3600;

            ConfigModel::query()
                ->where('id', 70)
                ->update([
                    'value' => date('Y-m-d H:i:00', $end_time)
                ]);

        } else {
            $end_time   = time();
            $begin_time = $end_time - 3600;
        }

        $this->orderSearchList($begin_time, $end_time);

    }

    //订单
    public function orderSearchList(int $begin, int $end) {

        $page       = 0;
        $while_flag = true;

        $request = new OrderSearchListRequest();
        $param   = new OrderSearchListParam();

        while ($while_flag) {
            $request->setParam($param);
            $param->update_time_start = $begin;
            $param->update_time_end   = $end;
            $param->size              = $this->pageSize;
            $param->page              = $page;
            $param->order_by          = "update_time";
            $param->order_asc         = false;

            $response           = $request->execute('');
            $response->job_type = 1;
            $response->page     = $response->data->page ?? 0;
            $response->size     = $response->data->size ?? 0;
            $response->total    = $response->data->total ?? 0;

            if ($response->size < $this->pageSize) {
                $while_flag = false;
            }

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
                $order->post_addr_province_id    = $order->post_addr->province->id ?? 0;
                $order->post_addr_city_id        = $order->post_addr->city->id ?? 0;
                $order->post_addr_town_id        = $order->post_addr->town->id ?? 0;
                $order->post_addr_street_id      = 0;

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

            $page++;
            sleep(1);
        }

    }

    //解密
    public function toDecrypt($step = 0) {

        if (!in_array($step, [0, 1, 2])) {
            return true;
        }

        $list = DouDianOrder::query()
            ->where('decrypt_step', $step)
            ->select([
                'id', 'order_id', 'order_status', 'order_status_desc', 'decrypt_step',
                'encrypt_post_tel', 'encrypt_post_receiver', 'encrypt_post_addr_detail',
            ])
            ->limit(50)
            ->get()
            ->toArray();

        $cipher_infos = [];
        foreach ($list as $item) {
            $temp_cipher_info            = [];
            $temp_cipher_info['auth_id'] = $item['order_id'];
            switch ($step) {
                case 0:
                    $temp_cipher_info['cipher_text'] = $item['encrypt_post_tel'];
                    break;
                case 1:
                    $temp_cipher_info['cipher_text'] = $item['encrypt_post_receiver'];
                    break;
                case 2:
                    $temp_cipher_info['cipher_text'] = $item['encrypt_post_addr_detail'];
                    break;
            }

            $cipher_infos[] = $temp_cipher_info;
        }

        $request = new OrderBatchDecryptRequest();
        $param   = new OrderBatchDecryptParam();
        $request->setParam($param);
        $param->cipher_infos = $cipher_infos;
        $response            = $request->execute('');

        $response->job_type = 2;
        DouDianOrderLog::query()->create((array)$response);

        if ($response->code !== 10000) {
            if (in_array($response->err_no, [30001, 30002, 30005, 30007])) {
                $this->accessTokenJob();
            }
            return true;
        }

        $decrypt_infos = $response->data->decrypt_infos;

        foreach ($decrypt_infos as $decrypt_info) {
            $check_order = DouDianOrder::query()
                ->where('order_id', $decrypt_info->auth_id)
                ->first();

            if ($decrypt_info->err_no == 0) {
                switch ($step) {
                    case 0:
                        $check_order->post_tel     = $decrypt_info->decrypt_text;
                        $check_order->decrypt_step = 1;
                        break;
                    case 1:
                        $check_order->post_receiver = $decrypt_info->decrypt_text;
                        $check_order->decrypt_step  = 2;
                        break;
                    case 2:
                        $check_order->post_addr_detail = $decrypt_info->decrypt_text;
                        $check_order->decrypt_step     = 3;
                        break;
                }
            } else {
                $check_order->decrypt_step    = 9;
                $check_order->decrypt_err_no  = $decrypt_info->err_no;
                $check_order->decrypt_err_msg = $decrypt_info->err_msg;
            }

            $check_order->save();

        }


        dd([$cipher_infos, $decrypt_infos]);
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
