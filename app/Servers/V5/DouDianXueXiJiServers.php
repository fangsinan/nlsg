<?php

namespace App\Servers\V5;

use AccessTokenBuilder;
use App\Models\ConfigModel;
use App\Models\DouDian\DouDianOrder;
use App\Models\DouDian\DouDianOrderDecryptQuota;
use App\Models\DouDian\DouDianOrderList;
use App\Models\DouDian\DouDianOrderLog;
use App\Models\DouDian\DouDianOrderLogistics;
use App\Models\DouDian\DouDianOrderStatus;
use App\Models\DouDian\DouDianProductList;
use App\Models\DouDian\DouDianSkuList;
use GlobalConfig;
use OrderBatchDecryptParam;
use OrderBatchDecryptRequest;
use OrderSearchListParam;
use OrderSearchListRequest;
use ProductListV2Param;
use ProductListV2Request;
use SkuListParam;
use SkuListRequest;

class DouDianXueXiJiServers
{
    protected $shopId;
    protected $pageSize = 50;//100以内

    public function __construct()
    {
        GlobalConfig::getGlobalConfig()->appKey         = config('env.DOU_DIAN_XXJ_APP_KEY');
        GlobalConfig::getGlobalConfig()->appSecret      = config('env.DOU_DIAN_XXJ_APP_SECRET');
        GlobalConfig::getGlobalConfig()->accessTokenStr = ConfigModel::getData(78, 1);
        $this->shopId                                   = ConfigModel::getData(77, 1);
        if ($this->shopId === '0') {
            exit('没有设置店铺ID');
        }
    }

    //同步商品任务和sku 10分钟一次
    public function productListJob(): bool
    {
        $time_flag = ConfigModel::getData(81, 1);

        if (!empty($time_flag) && strtotime($time_flag)) {
            $begin_time = strtotime($time_flag);
            $end_time   = $begin_time + 86400;

            ConfigModel::query()
                ->where('id', 81)
                ->update([
                    'value' => date('Y-m-d H:i:00', $end_time)
                ]);

        } else {
            $end_time   = time();
            $begin_time = $end_time - 3600;
        }
        $this->productList($begin_time, $end_time);
        $this->skuList($begin_time);

        return true;
    }

    //解密任务 1分一次
    public function decryptJob(): bool
    {
        $decrypt_quota = DouDianOrderDecryptQuota::query()
            ->where('dou_dian_type', '=', 2)
            ->orderBy('id', 'desc')
            ->first();

        $now_date   = date('Y-m-d H:i:s');
        $check_flag = 0;//解密任务中,0表示正常请求  1表示单条请求

        if (!empty($decrypt_quota) && $decrypt_quota->flag !== 2) {
            //如果check是1 会继续解密
            //如果check是2 表示真上限,任务会校验expire时间决定是否继续

            if (
                $decrypt_quota->flag == 1
                &&
                $decrypt_quota->check === 2
                &&
                $decrypt_quota->expire > $now_date
            ) {
                return true;
            }

            if ($decrypt_quota->flag == 1
                &&
                $decrypt_quota->check === 1
                &&
                $decrypt_quota->expire > $now_date
            ) {
                $check_flag = 1;
            }
        }
        //手机解密
        $this->toDecrypt(0, $this->pageSize, $check_flag);
        if (date('i') % 2 === 1) {
            //姓名解密
            $this->toDecrypt(1, $this->pageSize, $check_flag);
        } else {
            //地址解密
            $this->toDecrypt(2, $this->pageSize, $check_flag);
        }

//        if (date('H') > 12) {
//
//            //当前分钟数如果是奇数执行1 如果偶数执行2
//            if (date('i') % 2 === 1) {
//                //姓名解密
//                $this->toDecrypt(1, $this->pageSize, $check_flag);
//            } else {
//                //地址解密
//                $this->toDecrypt(2, $this->pageSize, $check_flag);
//            }
//
//        }

        return true;
    }

    //拉订单任务 5分钟一次
    public function getOrderJob($type): bool
    {

        $time_flag = ConfigModel::getData(80, 1);

        if (!empty($time_flag) && strtotime($time_flag)) {

            $begin_time = strtotime($time_flag);
            $begin_time = min(time() - 7200, $begin_time);
            $end_time   = $begin_time + 7200;

            ConfigModel::query()
                ->where('id', 80)
                ->update([
                    'value' => date('Y-m-d H:i:00', $end_time)
                ]);

        } else {
            $end_time   = time();
            $begin_time = $end_time - 3600;
        }

        $this->orderSearchList($begin_time, $end_time, $type);
        return true;
    }

    //sku列表
    public function skuList($begin_create_time)
    {
        $begin_created_at = date('Y-m-d H:i:s', $begin_create_time);

        $product_list = DouDianProductList::query()
            ->where('create_time', '>', $begin_create_time)
            ->orWhere('created_at', '>', $begin_created_at)
            ->select(['id', 'product_id'])
            ->get();

        $request = new SkuListRequest();

        foreach ($product_list as $product) {
            $param = new SkuListParam();
            $request->setParam($param);
            $param->product_id  = $product->product_id;
            $response           = $request->execute('');
            $response->job_type = 4;
            DouDianOrderLog::query()->create((array)$response);

            foreach ($response->data as $sku) {
                DouDianSkuList::query()->updateOrCreate(
                    [
                        'id' => $sku->id
                    ],
                    (array)$sku
                );
            }

        }

    }

    //商品列表
    public function productList($begin_time, $end_time)
    {

        $page       = 1;
        $while_flag = true;

        $request = new ProductListV2Request();
        $param   = new ProductListV2Param();

        while ($while_flag) {
            $request->setParam($param);
            $param->page              = $page;
            $param->size              = $this->pageSize;
            $param->update_start_time = $begin_time;
            $param->update_end_time   = $end_time;

            $response           = $request->execute('');
            $response->job_type = 3;
            $response->page     = $response->data->page ?? 0;
            $response->size     = $response->data->size ?? 0;
            $response->total    = $response->data->total ?? 0;

            if ($response->size < $this->pageSize || empty($response->data->data)) {
                $while_flag = false;
            }
            DouDianOrderLog::query()->create((array)$response);

            if ($response->code !== 10000) {
                if (in_array($response->err_no, [30001, 30002, 30005, 30007])) {
                    $this->accessTokenJob();
                }
                return true;
            }

            foreach ($response->data->data as $item) {
                DouDianProductList::query()->updateOrCreate(
                    [
                        'product_id'    => $item->product_id,
                        'dou_dian_type' => 2
                    ],
                    (array)$item
                );
            }

            $page++;
            if ($page > 100) {
                $while_flag = false;
            }
            sleep(1);
        }

    }

    //订单
    public function orderSearchList(int $begin, int $end, $type)
    {

        $page       = 0;
        $while_flag = true;

        $request = new OrderSearchListRequest();
        $param   = new OrderSearchListParam();

        if ($type == 1) {
            $order_by = 'create_time';
            $job_type = 11;
        } else {
            $order_by = 'update_time';
            $job_type = 12;
        }


        while ($while_flag) {
            $request->setParam($param);
            $param->update_time_start = $begin;
            $param->update_time_end   = $end;
            $param->size              = $this->pageSize;
            $param->page              = $page;
            $param->order_by          = $order_by;
            $param->order_asc         = false;

            $response           = $request->execute('');
            $response->job_type = $job_type;
            $response->page     = $response->data->page ?? 0;
            $response->size     = $response->data->size ?? 0;
            $response->total    = $response->data->total ?? 0;

            if ($response->size < $this->pageSize || empty($response->data->shop_order_list)) {
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

                if ($order->order_status === 4) {
                    $order->decrypt_step    = 9;
                    $order->decrypt_err_no  = 0;
                    $order->decrypt_err_msg = $order->order_status_desc;
                }
                $order->dou_dian_type = 2;
                DouDianOrder::query()->updateOrCreate(
                    [
                        'order_id' => $order->order_id
                    ],
                    (array)$order
                );

                foreach ($order->sku_order_list as $sku) {
                    $sku->after_sale_info_status        = $sku->after_sale_info->after_sale_status ?? 0;
                    $sku->after_sale_info_type          = $sku->after_sale_info->after_sale_type ?? 0;
                    $sku->after_sale_info_refund_status = $sku->after_sale_info->refund_status ?? 0;
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

    public function orderStatusData()
    {

        $main_status = DouDianOrder::query()
            ->select(['main_status', 'main_status_desc'])
            ->groupBy('main_status')
            ->get();

        foreach ($main_status as $ms) {
            DouDianOrderStatus::query()->firstOrCreate(
                [
                    'key'  => $ms->main_status,
                    'type' => 2
                ], [
                    'value' => $ms->main_status_desc
                ]
            );
        }

        $order_status = DouDianOrder::query()
            ->select([
                'order_status', 'order_status_desc',
            ])
            ->groupBy('order_status')
            ->get();

        foreach ($order_status as $os) {
            DouDianOrderStatus::query()->firstOrCreate(
                [
                    'key'  => $os->order_status,
                    'type' => 1
                ], [
                    'value' => $os->order_status_desc
                ]
            );
        }

    }

    //解密
    public function toDecrypt($step = 0, int $size = 50, $check_flag = 0)
    {

        if (!in_array($step, [0, 1, 2])) {
            return true;
        }

        $list = DouDianOrder::query()
            ->where('decrypt_step', $step)
            ->whereNotIn('order_status', [1, 4])
//            ->where('order_id', '>', '4933714072054765432')
            ->where('dou_dian_type', '=', 2)
            ->select([
                'order_id', 'order_status', 'order_status_desc', 'decrypt_step',
                'encrypt_post_tel', 'encrypt_post_receiver', 'encrypt_post_addr_detail',
            ])
            ->limit($size)
            ->orderBy('order_id', 'desc')
            ->get();

        if ($list->isEmpty()) {
            return true;
        }

        $list = $list->toArray();

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

        if ($check_flag === 0) {
            $param->cipher_infos = $cipher_infos;
            $response            = $request->execute('');

            $response->job_type     = 2;
            $response->decrypt_text = json_encode($response);
            DouDianOrderLog::query()->create((array)$response);

            if ($response->code !== 10000) {
                if (in_array($response->err_no, [30001, 30002, 30005, 30007])) {
                    $this->accessTokenJob();
                }

                //flag 状态 1表示已经到达解密上限 2表示重置任务,会继续执行
                //check 1是假上限,会继续使用单条尝试.  2是真上限,任务会暂停
                //err_type 1是解密配额  2是安全风险
                //dou_dian_type 默认1能量时光 2是学习机

                //80000 您的环境存在安全风险，请稍后再试  暂停半小时
                if ($response->code === 80000 || $response->err_no === 300008) {
                    $this->DecryptQuotaInsert(1, 2, 2);
                }

                //90000或50002 已达到店铺解密上限 暂停五小时,申请配额后可在后台人工重置 (原来是5002)
                if ($response->code === 90000 || $response->code === 50002) {
                    $this->DecryptQuotaInsert(1, 1, 2);
                }
                return true;
            }

            $decrypt_infos = $response->data->decrypt_infos;

            $err_no_300008_count = 0;

            foreach ($decrypt_infos as $decrypt_info) {

                if ($decrypt_info->err_no === 300008) {
                    $err_no_300008_count++;
                    continue;
                }

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
                    if ($decrypt_info->err_no !== 300008) {
                        $check_order->decrypt_step    = 9;
                        $check_order->decrypt_err_no  = $decrypt_info->err_no;
                        $check_order->decrypt_err_msg = $decrypt_info->err_msg;
                    }
                }

                $check_order->save();

            }

            if ($err_no_300008_count > 30) {
                $this->DecryptQuotaInsert(1, 2, 2);

            }

        } else {
            foreach ($cipher_infos as $ci) {
                $param->cipher_infos = [$ci];
                $response            = $request->execute('');

                $response->job_type     = 2;
                $response->decrypt_text = json_encode($response);
                DouDianOrderLog::query()->create((array)$response);

                if ($response->code !== 10000) {
                    if (in_array($response->err_no, [30001, 30002, 30005, 30007])) {
                        $this->accessTokenJob();
                    }

                    //80000 您的环境存在安全风险，请稍后再试  暂停半小时
                    if ($response->code === 80000 || $response->err_no === 300008) {
                        $this->DecryptQuotaInsert(1, 2, 2);
                    }

                    //90000或50002 已达到店铺解密上限 暂停五小时,申请配额后可在后台人工重置 (原来是5002)
                    if ($response->code === 90000 || $response->code === 50002) {
                        $this->DecryptQuotaInsert(1, 1, 2);
                    }
                    break;
                }

                $decrypt_infos = $response->data->decrypt_infos;

                foreach ($decrypt_infos as $decrypt_info) {

                    if ($decrypt_info->err_no === 300008) {
                        $this->DecryptQuotaInsert(1, 2, 2);
                        continue;
                    }

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
                        if ($decrypt_info->err_no !== 300008) {
                            $check_order->decrypt_step    = 9;
                            $check_order->decrypt_err_no  = $decrypt_info->err_no;
                            $check_order->decrypt_err_msg = $decrypt_info->err_msg;
                        }
                    }

                    $check_order->save();
                    sleep(1);
                }
            }

        }


    }

    //解密配额记录
    public function DecryptQuotaInsert($check, $err_type, $dou_dian_type)
    {
        //如果err_type=1 暂停2小时  如果是2 暂停半小时
        $time = $err_type == 1 ? 7200 : 1800;

        DouDianOrderDecryptQuota::query()
            ->create([
                'flag'          => 1,
                'expire'        => date('Y-m-d H:i:s', time() + $time),
                'check'         => $check,
                'err_type'      => $err_type,
                'dou_dian_type' => $dou_dian_type,
            ]);

    }

    public function accessTokenJob($job = 1)
    {

        if ($job == 1) {
            $accessToken = AccessTokenBuilder::build($this->shopId, ACCESS_TOKEN_SHOP_ID);
            if ($accessToken->isSuccess()) {
                ConfigModel::query()->where('id', 78)
                    ->update(['value' => $accessToken->getAccessToken()]);

                ConfigModel::query()->where('id', 79)
                    ->update(['value' => $accessToken->getRefreshToken()]);
            }
            return $accessToken;

        } else {
            $old_token     = ConfigModel::getData(78, 1);
            $refresh_token = ConfigModel::getData(79, 1);

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
