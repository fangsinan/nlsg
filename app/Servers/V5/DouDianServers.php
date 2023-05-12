<?php

namespace App\Servers\V5;

use AccessTokenBuilder;
use App\Models\CommandJobLog;
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
use Illuminate\Support\Facades\DB;
use OrderBatchDecryptParam;
use OrderBatchDecryptRequest;
use OrderOrderDetailParam;
use OrderOrderDetailRequest;
use OrderSearchListParam;
use OrderSearchListRequest;
use ProductListV2Param;
use ProductListV2Request;
use SkuListParam;
use SkuListRequest;

class DouDianServers
{
    protected $shopId;
    protected $pageSize = 80;
    protected $runPageSize = 100;
    const DECRYPT_JOB_TYPE = 3;//1只解手机 2解手机和姓名  3接手机姓名地址

    public function __construct()
    {
        GlobalConfig::getGlobalConfig()->appKey         = config('env.DOU_DIAN_APP_KEY');
        GlobalConfig::getGlobalConfig()->appSecret      = config('env.DOU_DIAN_APP_SECRET');
        GlobalConfig::getGlobalConfig()->accessTokenStr = ConfigModel::getData(68, 1);
        $this->shopId                                   = ConfigModel::getData(67, 1);
        if ($this->shopId === '0') {
            exit('没有设置店铺ID:抖店');
        }
    }

    //同步商品任务和sku 10分钟一次
    public function productListJob(): bool
    {
        $time_flag = ConfigModel::getData(71, 1);

        if (!empty($time_flag) && strtotime($time_flag)) {
            $begin_time = strtotime($time_flag);
            $end_time   = $begin_time + 86400;

            ConfigModel::query()
                ->where('id', 71)
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
            ->where('dou_dian_type', '=', 1)
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

        $time_flag = ConfigModel::getData(70, 1);

        if (!empty($time_flag) && strtotime($time_flag)) {

            $begin_time = strtotime($time_flag);
            $begin_time = min(time() - 43200, $begin_time);
            $end_time   = $begin_time + 43200;

            ConfigModel::query()
                ->where('id', 70)
                ->update([
                    'value' => date('Y-m-d H:i:00', $end_time)
                ]);

        } else {
            //5分
            $end_time   = date('Y-m-d H:i:00');
            $begin_time = strtotime(date('Y-m-d H:i:30', strtotime($end_time.' -3 minutes')));
            $end_time = strtotime($end_time);
        }

        $res = $this->orderSearchList($begin_time, $end_time, $type);

        CommandJobLog::add('App\Console\Commands\DouDianOrder::handle', [
            'begin_time' => date('H.i.s', $begin_time),
            'end_time'   => date('H.i.s', $end_time),
            'type'       => $type,
            'total'      => is_numeric($res) ? $res : -1,
        ]);

        //临时使用  补充订单状态
//        $this->orderStatusData();

        return true;
    }


    //根据订单号获取订单详情
    public function tempGetOrderDetails()
    {
        $list = DouDianOrder::query()
            ->where('dou_dian_type', '=', 3)
            ->limit(50)
            ->select('order_id')
            ->get();

        if ($list->isEmpty()) {
            return true;
        }

        $list = $list->toArray();

        $request = new OrderOrderDetailRequest();
        $param   = new OrderOrderDetailParam();
        $request->setParam($param);


        foreach ($list as $v) {

//            echo '开始:'.$v['order_id'].' : '.date('Y-m-d H:i:s').PHP_EOL;

            $param->shop_order_id = $v['order_id'];
            $response             = $request->execute('');

            try {
                $order = $response->data->shop_order_detail;

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

                $order->dou_dian_type = 1;
                $order->created_at    = date('Y-m-d H:i:s', $order->create_time);
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
            } catch (\Exception $e) {
                print_r($e->getMessage());
                continue;
            }

            usleep(500000);

        }

    }

    //临时用于导入列表接口不能抓取的订单
    public function tempExcelAddOrder()
    {
        $list = DB::table('wwwwww_douyin')
//            ->limit(20000)
            ->get()
            ->toArray();

        $count = 0;

        foreach ($list as $v) {
            $vid = $v->id;
            $v   = trim($v->phone);

            $temp_check = DouDianOrder::query()
                ->where('order_id', '=', $v)
                ->first();

            if (empty($temp_check)) {
                DouDianOrder::query()
                    ->insert([
                        'order_id'          => $v,
                        'dou_dian_type'     => 3,
                        'order_status'      => 1,
                        'order_status_desc' => 1,
                        'main_status'       => 1,
                        'main_status_desc'  => 1,
                    ]);
                $count++;
                echo $v . '不存在,写入...' . PHP_EOL;
            }

            DB::table('wwwwww_douyin')
                ->where('id', '=', $vid)
                ->delete();

        }

        dd('写入成功:' . $count);
    }

    public function testGetOrder()
    {
        return 0;
        $order_id = '4811666301390772052';

        $request = new OrderOrderDetailRequest();
        $param   = new OrderOrderDetailParam();
        $request->setParam($param);
        $param->shop_order_id = $order_id;
        $response             = $request->execute('');

        $encrypt_post_receiver = $response->data->shop_order_detail->encrypt_post_tel;

        $cipher_infos[] = [
            'auth_id'     => $order_id,
            'cipher_text' => $encrypt_post_receiver
        ];

        $request = new OrderBatchDecryptRequest();
        $param   = new OrderBatchDecryptParam();

        $request->setParam($param);
        $param->cipher_infos = $cipher_infos;
        $response_1          = $request->execute('');


        dd([
            $response,
            $response_1
        ]);
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
                        'product_id' => $item->product_id
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
        $total_count = 0;

        while ($while_flag) {
            $request->setParam($param);

            if ($type == 1) {
                $param->create_time_start = $begin;
                $param->create_time_end   = $end;
            } else {
                $param->update_time_start = $begin;
                $param->update_time_end   = $end;
            }

            $param->size        = $this->pageSize;
            $param->page        = $page;
            $param->order_by    = $order_by;
            $param->order_asc   = false;
            $response           = $request->execute('');
            $response->job_type = $job_type;
            $response->page     = $response->data->page ?? 0;
            $response->size     = $response->data->size ?? 0;
            $response->total    = $response->data->total ?? 0;
            $total_count        = $response->data->total ?? 0;

            //echo $page, '页;共', $response->data->total, '条;', ($response->page + 1) * $response->size, PHP_EOL;

            if ($response->size < $this->pageSize || empty($response->data->shop_order_list)) {
                $while_flag = false;
            }

            DouDianOrderLog::query()->create((array)$response);

            if ($response->code !== 10000) {
                if (in_array($response->err_no ?? 0, [30001, 30002, 30005, 30007])) {
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

//                if ($order->order_status === 4) {
//                    $order->decrypt_step    = 9;
//                    $order->decrypt_err_no  = 0;
//                    $order->decrypt_err_msg = $order->order_status_desc;
//                }
                $order->dou_dian_type = 1;
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
        }
        return $total_count;
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
            ->where('dou_dian_type', '=', 1)
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
                    $this->DecryptQuotaInsert(1, 2, 1);
                }

                //90000或50002 已达到店铺解密上限 暂停五小时,申请配额后可在后台人工重置 (原来是5002)
                if ($response->code === 90000 || $response->code === 50002) {
                    $this->DecryptQuotaInsert(1, 1, 1);
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
                $this->DecryptQuotaInsert(1, 2, 1);

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
                        $this->DecryptQuotaInsert(1, 2, 1);
                        exit('环境风险,推出当前任务');
                    }

                    //90000或50002 已达到店铺解密上限 暂停五小时,申请配额后可在后台人工重置 (原来是5002)
                    if ($response->code === 90000 || $response->code === 50002) {
                        $this->DecryptQuotaInsert(1, 1, 1);
                    }
                    break;
                }

                $decrypt_infos = $response->data->decrypt_infos;

                foreach ($decrypt_infos as $decrypt_info) {

                    if ($decrypt_info->err_no === 300008) {
                        $this->DecryptQuotaInsert(1, 2, 1);
                        exit('环境风险,推出当前任务');
//                        continue;
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

    public function runDecrypt(): bool
    {
        DouDianOrder::query()
            ->where('decrypt_step', '<>', 3)
            ->where('post_tel', '<>', '')
            ->where('post_receiver', '<>', '')
            ->where('post_addr_detail', '<>', '')
            ->update([
                'decrypt_step' => 3
            ]);

        $list = DouDianOrder::query()
            ->whereNotIn('order_status', [1, 4])
            ->where('decrypt_step', '<>', 9)
            ->where('decrypt_step', '<', self::DECRYPT_JOB_TYPE)
            ->where('dou_dian_type', '=', 1)
            ->where('encrypt_post_tel', '<>', '')
            ->select([
                'order_id', 'order_status', 'decrypt_step',
                'encrypt_post_tel', 'encrypt_post_receiver', 'encrypt_post_addr_detail',
                'post_tel', 'post_receiver', 'post_addr_detail'
            ])
            ->limit($this->runPageSize)
            ->orderBy('order_id', 'desc')
            ->get();

        if ($list->isEmpty()) {
            return true;
        }

        $list = $list->toArray();

        foreach ($list as $v) {

            $can_to_decrypt = $this->canToDecrypt(1);
            if ($can_to_decrypt === 0) {
                break;
            }

            for ($i = 1; $i <= self::DECRYPT_JOB_TYPE; $i++) {
                $check_this_order = DouDianOrder::query()
                    ->where('order_id', '=', $v['order_id'])
                    ->first();

                $cipher_infos            = [];
                $cipher_infos['auth_id'] = $v['order_id'];

                if ($i === 1 && !empty($v['post_tel'])) {
                    $check_this_order->decrypt_step = 1;
                    $check_this_order->save();
                    continue;
                }

                if ($i === 2 && !empty($v['post_receiver'])) {
                    $check_this_order->decrypt_step = 2;
                    $check_this_order->save();
                    continue;
                }

                if ($i === 3 && !empty($v['post_addr_detail'])) {
                    $check_this_order->decrypt_step = 3;
                    $check_this_order->save();
                    continue;
                }

                switch ($i) {
                    case 1:
                        $cipher_infos['cipher_text'] = $v['encrypt_post_tel'];
                        break;
                    case 2:
                        $cipher_infos['cipher_text'] = $v['encrypt_post_receiver'];
                        break;
                    default:
                        $cipher_infos['cipher_text'] = $v['encrypt_post_addr_detail'];
                        break;
                }

                //解密
                $temp_res = $this->runDecryptApi([$cipher_infos]);
                if ($temp_res['code'] === false) {
                    if ($temp_res['msg'] === '配额上限') {
                        exit('配额上限,停止请求');
                    }
                    break;
                }
                $temp_res['data'] = (array)$temp_res['data'][0];

                if ($temp_res['data']['err_no'] == 0) {
                    //没错误
                    switch ($i) {
                        case 1:
                            $check_this_order->post_tel     = $temp_res['data']['decrypt_text'];
                            $check_this_order->decrypt_step = 1;
                            break;
                        case 2:
                            $check_this_order->post_receiver = $temp_res['data']['decrypt_text'];
                            $check_this_order->decrypt_step  = 2;
                            break;
                        default:
                            $check_this_order->post_addr_detail = $temp_res['data']['decrypt_text'];
                            $check_this_order->decrypt_step     = 3;
                            break;
                    }
                    $check_this_order->save();
                } else {
                    //300008包含 安全风险 和  隐私保护 ,暂定通过msg修改
                    // 基于消费者隐私保护，该订单状态禁止查看 重复请求不会改变结果,改成9
                    //安全风险 无需改成9

                    DouDianOrderStatus::query()->firstOrCreate(
                        [
                            'key'   => $temp_res['data']['err_no'],
                            'value' => $temp_res['data']['err_msg'],
                            'type'  => 3,
                        ]
                    );

                    $check_msg = mb_strstr($temp_res['data']['err_msg'], '请稍后再试');
                    if ($check_msg === false) {
                        $check_this_order->decrypt_step    = 9;
                        $check_this_order->decrypt_err_no  = $temp_res['data']['err_no'];
                        $check_this_order->decrypt_err_msg = $temp_res['data']['err_msg'];
                        $check_this_order->save();
                    } else {
                        $this->DecryptQuotaInsert(1, 2, 1);
                        exit('风控');
                    }

                    break;
                }
                //1000000  1秒
                usleep(300000);
            }
        }

        return true;
    }

    public function runDecryptApi(array $cipher_infos)
    {
        $request = new OrderBatchDecryptRequest();
        $param   = new OrderBatchDecryptParam();
        $request->setParam($param);
        $param->cipher_infos = $cipher_infos;
        $response            = $request->execute('');
        DouDianOrderLog::query()->create((array)$response);
        $response->job_type     = 2;
        $response->decrypt_text = json_encode($response);

        if (!isset($response->data)) {
            return ['code' => false, 'msg' => '结构错误', 'data' => []];
        }
        $response->err_no  = $response->data->custom_err->err_code;
        $response->err_msg = $response->data->custom_err->err_msg;
        /**
         * 之前的接口返回 error在外层
         */
        if (in_array($response->err_no, [30001, 30002, 30005, 30007])) {
            $this->accessTokenJob();
            return ['code' => false, 'msg' => 'token错误', 'data' => []];
        }

        if ($response->code === 80000 || $response->err_no === 300008) {
            $this->DecryptQuotaInsert(1, 2, 1);
            return ['code' => false, 'msg' => '安全风险', 'data' => []];
        }

        if ($response->code === 90000 || $response->code === 50002) {
            $this->DecryptQuotaInsert(1, 1, 1);
            return ['code' => false, 'msg' => '配额上限', 'data' => []];
        }

        return ['code' => true, 'msg' => '成功', 'data' => $response->data->decrypt_infos];

    }

    //抖店配额和风险改版 新任务
    public function runDecryptNew()
    {
        $search_product_type = [3];//允许解密的订单类型
        DouDianOrder::query()
            ->where('decrypt_step', '<>', 3)
            ->where('post_tel', '<>', '')
            ->where('post_receiver', '<>', '')
            ->where('post_addr_detail', '<>', '')
            ->update([
                'decrypt_step' => 3
            ]);

        $config = ConfigModel::getData(83, 1);
        $config = explode(',', $config);
        $c_s_1  = $config[0] ?? 5;
        $c_s_2  = $config[1] ?? 10;

        $limit = (int)(300 / ($c_s_2 * 3));

        if ($limit < 1) {
            $limit = 10;
        }

        $list = DouDianOrder::query()
            ->with([
                'orderList:order_id,product_id,parent_order_id',
                'orderList.productInfo:id,product_type,product_id'
            ])
            ->whereHas('orderList.productInfo', function ($query) use ($search_product_type) {
                $query->where(function ($q) use ($search_product_type) {
                    $q->where('to_decrypt', '<>', 3)
                        ->where(function ($q) use ($search_product_type) {
                            $q->whereIn('product_type', $search_product_type)
                                ->orWhere('to_decrypt', '=', 2);
                        });
                });
            })
            ->whereNotIn('order_status', [1, 4])
            ->where('decrypt_step', '<>', 9)
            ->where('decrypt_step', '<', self::DECRYPT_JOB_TYPE)
            ->where('dou_dian_type', '=', 1)
            ->where('encrypt_post_tel', '<>', '')
            ->where('create_time', '>', 1659283200)
            ->select([
                'order_id', 'order_status', 'decrypt_step',
                'encrypt_post_tel', 'encrypt_post_receiver', 'encrypt_post_addr_detail',
                'post_tel', 'post_receiver', 'post_addr_detail'
            ])
            ->limit($limit)
            ->orderBy('order_id', 'desc')
            ->get();


        if ($list->isEmpty()) {
            exit('没有需要解密的订单');
        }

        $list = $list->toArray();

        foreach ($list as $v) {
            for ($i = 1; $i <= self::DECRYPT_JOB_TYPE; $i++) {
                //0714 出现风险后,不允许短时间继续尝试
                $can_to_decrypt = $this->canToDecryptNew(1);
                if (!$can_to_decrypt) {
                    exit('任务开关关闭');
                }

                sleep(rand($c_s_1, $c_s_2));

                $check_this_order = DouDianOrder::query()
                    ->where('order_id', '=', $v['order_id'])
                    ->first();

                $cipher_infos            = [];
                $cipher_infos['auth_id'] = $v['order_id'];

                if ($i === 1 && !empty($v['post_tel'])) {
                    $check_this_order->decrypt_step = 1;
                    $check_this_order->save();
                    continue;
                }

                if ($i === 2 && !empty($v['post_receiver'])) {
                    $check_this_order->decrypt_step = 2;
                    $check_this_order->save();
                    continue;
                }

                if ($i === 3 && !empty($v['post_addr_detail'])) {
                    $check_this_order->decrypt_step = 3;
                    $check_this_order->save();
                    continue;
                }

                switch ($i) {
                    case 1:
                        $cipher_infos['cipher_text'] = $v['encrypt_post_tel'];
                        break;
                    case 2:
                        $cipher_infos['cipher_text'] = $v['encrypt_post_receiver'];
                        break;
                    default:
                        $cipher_infos['cipher_text'] = $v['encrypt_post_addr_detail'];
                        break;
                }

                if ($i === 1 && empty($cipher_infos['cipher_text'])) {
                    $check_this_order->decrypt_step = 1;
                    $check_this_order->save();
                    continue;
                }

                if ($i === 2 && empty($cipher_infos['cipher_text'])) {
                    $check_this_order->decrypt_step = 2;
                    $check_this_order->save();
                    continue;
                }

                if ($i === 3 && empty($cipher_infos['cipher_text'])) {
                    $check_this_order->decrypt_step = 3;
                    $check_this_order->save();
                    continue;
                }

                //解密
                $temp_res = $this->runDecryptApi([$cipher_infos]);

                if ($temp_res['code'] === false) {
                    break;
                }
                $temp_res['data'] = (array)$temp_res['data'][0];

                if ($temp_res['data']['err_no'] == 0) {
                    //没错误
                    switch ($i) {
                        case 1:
                            $check_this_order->post_tel     = $temp_res['data']['decrypt_text'];
                            $check_this_order->decrypt_step = 1;
                            break;
                        case 2:
                            $check_this_order->post_receiver = $temp_res['data']['decrypt_text'];
                            $check_this_order->decrypt_step  = 2;
                            break;
                        default:
                            $check_this_order->post_addr_detail = $temp_res['data']['decrypt_text'];
                            $check_this_order->decrypt_step     = 3;
                            break;
                    }
                    $check_this_order->save();
                } else {
                    //300008包含 安全风险 和  隐私保护 ,暂定通过msg修改
                    // 基于消费者隐私保护，该订单状态禁止查看 重复请求不会改变结果,改成9
                    //安全风险 无需改成9

                    DouDianOrderStatus::query()->firstOrCreate(
                        [
                            'key'   => $temp_res['data']['err_no'],
                            'value' => $temp_res['data']['err_msg'],
                            'type'  => 3,
                        ]
                    );

                    /**
                     * "基于消费者隐私保护，该订单状态禁止查看"
                     * "您当天的查看额度已用完，请明日再看"
                     */

                    //查看err_msg中是否包含 '隐私','额度','安全'

                    $err_msg = $temp_res['data']['err_msg'];
                    if (strpos($err_msg, '隐私') !== false) {
                        //隐私问题,直接9
                        $check_this_order->decrypt_step    = 9;
                        $check_this_order->decrypt_err_no  = $temp_res['data']['err_no'];
                        $check_this_order->decrypt_err_msg = $err_msg;
                        $check_this_order->save();
                        continue;
                    }

                    if (strpos($err_msg, '额度') !== false) {
                        $this->DecryptQuotaInsert(1, 1, 1);
                        exit('额度已用完');
                    }

                    if (strpos($err_msg, '安全风险') !== false) {
                        $this->DecryptQuotaInsert(1, 2, 1);
                        exit('环境风险');
                    }

                }
            }
        }

        return true;
    }

    /*public function runDecryptNew(){
        DouDianOrder::query()
            ->where('decrypt_step', '<>', 3)
            ->where('post_tel', '<>', '')
            ->where('post_receiver', '<>', '')
            ->where('post_addr_detail', '<>', '')
            ->update([
                'decrypt_step' => 3
            ]);

        $config = ConfigModel::getData(83,1);
        $config = explode(',',$config);
        $c_s_1 = $config[0] ?? 5;
        $c_s_2 = $config[1] ?? 10;

        $limit = (int)(300 / ($c_s_2 * 3));

        if ($limit < 1){
            $limit = 10;
        }

        $list = DouDianOrder::query()
            ->whereNotIn('order_status', [1, 4])
            ->where('created_at', '>', '2022-01-01 00:00:00')
            ->where('decrypt_step', '<>', 9)
            ->where('decrypt_step', '<', self::DECRYPT_JOB_TYPE)
            ->where('dou_dian_type', '=', 1)
            ->where('encrypt_post_tel','<>','')
            ->select([
                'order_id', 'order_status', 'decrypt_step',
                'encrypt_post_tel', 'encrypt_post_receiver', 'encrypt_post_addr_detail',
                'post_tel', 'post_receiver', 'post_addr_detail'
            ])
            ->limit($limit)
            ->orderBy('order_id', 'asc') //desc asc
            ->get();

        if ($list->isEmpty()) {
            exit('没有需要解密的订单');
        }

        $list = $list->toArray();

        foreach ($list as $v) {

            for ($i = 1; $i <= self::DECRYPT_JOB_TYPE; $i++) {
                //0714 出现风险后,不允许短时间继续尝试
                $can_to_decrypt = $this->canToDecryptNew(1);
                if (!$can_to_decrypt) {
                    exit('任务开关关闭');
                }

                sleep(rand($c_s_1,$c_s_2));

                $check_this_order = DouDianOrder::query()
                    ->where('order_id', '=', $v['order_id'])
                    ->first();

                $cipher_infos            = [];
                $cipher_infos['auth_id'] = $v['order_id'];

                if ($i === 1 && !empty($v['post_tel'])) {
                    $check_this_order->decrypt_step = 1;
                    $check_this_order->save();
                    continue;
                }

                if ($i === 2 && !empty($v['post_receiver'])) {
                    $check_this_order->decrypt_step = 2;
                    $check_this_order->save();
                    continue;
                }

                if ($i === 3 && !empty($v['post_addr_detail'])) {
                    $check_this_order->decrypt_step = 3;
                    $check_this_order->save();
                    continue;
                }

                switch ($i) {
                    case 1:
                        $cipher_infos['cipher_text'] = $v['encrypt_post_tel'];
                        break;
                    case 2:
                        $cipher_infos['cipher_text'] = $v['encrypt_post_receiver'];
                        break;
                    default:
                        $cipher_infos['cipher_text'] = $v['encrypt_post_addr_detail'];
                        break;
                }

                //解密
                $temp_res = $this->runDecryptApi([$cipher_infos]);
                if ($temp_res['code'] === false) {
                    break;
                }
                $temp_res['data'] = (array)$temp_res['data'][0];

                if ($temp_res['data']['err_no'] == 0) {
                    //没错误
                    switch ($i) {
                        case 1:
                            $check_this_order->post_tel     = $temp_res['data']['decrypt_text'];
                            $check_this_order->decrypt_step = 1;
                            break;
                        case 2:
                            $check_this_order->post_receiver = $temp_res['data']['decrypt_text'];
                            $check_this_order->decrypt_step  = 2;
                            break;
                        default:
                            $check_this_order->post_addr_detail = $temp_res['data']['decrypt_text'];
                            $check_this_order->decrypt_step     = 3;
                            break;
                    }
                    $check_this_order->save();
                } else {
                    //300008包含 安全风险 和  隐私保护 ,暂定通过msg修改
                    // 基于消费者隐私保护，该订单状态禁止查看 重复请求不会改变结果,改成9
                    //安全风险 无需改成9

                    DouDianOrderStatus::query()->firstOrCreate(
                        [
                            'key'   => $temp_res['data']['err_no'],
                            'value' => $temp_res['data']['err_msg'],
                            'type'  => 3,
                        ]
                    );

//                    "基于消费者隐私保护，该订单状态禁止查看"
//                    "您当天的查看额度已用完，请明日再看"
                    //查看err_msg中是否包含 '隐私','额度','安全'

                    $err_msg = $temp_res['data']['err_msg'];
                    if (strpos($err_msg,'隐私') !== false){
                        //隐私问题,直接9
                        $check_this_order->decrypt_step    = 9;
                        $check_this_order->decrypt_err_no  = $temp_res['data']['err_no'];
                        $check_this_order->decrypt_err_msg = $err_msg;
                        $check_this_order->save();
                        continue;
                    }

                    if (strpos($err_msg,'额度') !== false){
                        $this->DecryptQuotaInsert(1, 1, 1);
                        exit('额度已用完');
                    }

                    if (strpos($err_msg,'安全风险') !== false){
                        $this->DecryptQuotaInsert(1, 2, 1);
                        exit('环境风险');
                    }

                }
            }
        }

        return true;
    }*/

    //抖店配额和风险改版 新开关查询
    public function canToDecryptNew(int $dou_dian_type = 1): bool
    {
        $temp = DouDianOrderDecryptQuota::query()
            ->where('expire', '>', date('Y-m-d H:i:s'))
            ->where('flag', '=', 1)
            ->where('dou_dian_type', '=', $dou_dian_type)
            ->first();

        if ($temp) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 查询当前是否可以进行解密动作
     * @param int $dou_dian_type
     * @return int 0停止请求 1可以请求多条 2可以请求单挑
     */
    public function canToDecrypt(int $dou_dian_type = 1): int
    {
        $decrypt_quota = DouDianOrderDecryptQuota::query()
            ->where('dou_dian_type', '=', $dou_dian_type)
            ->orderBy('id', 'desc')
            ->first();

        if (empty($decrypt_quota)) {
            return 1;
        }

        $now_date = date('Y-m-d H:i:s');

        if ($decrypt_quota->flag === 2 || $decrypt_quota->expire <= $now_date) {
            return 1;
        }

        if ($decrypt_quota->check === 1) {
            return 2;
        }

        return 0;
    }

    /**
     * 解密配额记录
     * @param $check 1配额 2风险
     * @param $err_type
     * @param $dou_dian_type
     * @return void
     */
    public function DecryptQuotaInsert($check, $err_type, $dou_dian_type)
    {
        //如果err_type=1 暂停2小时  如果是2 暂停半小时
//        $time = $err_type == 1 ? 7200 : 1800;
        $time = $err_type == 1 ? 600 : 1800;

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
