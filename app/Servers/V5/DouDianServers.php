<?php

namespace App\Servers\V5;

use AccessTokenBuilder;
use App\Models\ConfigModel;
use GlobalConfig;
use OrderSearchListParam;
use OrderSearchListRequest;

class DouDianServers
{
    protected $appKey = '6857846430543906317';
    protected $appSecret = '2f3af110-3aef-4bf0-8641-f00840b8e87f';
    protected $shopId;

    public function __construct() {
        GlobalConfig::getGlobalConfig()->appKey         = $this->appKey;
        GlobalConfig::getGlobalConfig()->appSecret      = $this->appSecret;
        GlobalConfig::getGlobalConfig()->accessTokenStr = ConfigModel::getData(68, 1);
        $this->shopId                                   = ConfigModel::getData(67, 1);
    }

    public function test() {

        $begin_time = strtotime('2022-05-06 00:00:00');
        $end_time   = strtotime('2022-05-06 20:00:00');

        $request = new OrderSearchListRequest();
        $param   = new OrderSearchListParam();
        $request->setParam($param);
//        $param->product = "";
//        $param->b_type = 2;
//        $param->after_sale_status_desc = "all";
//        $param->tracking_no = "";
//        $param->presell_type = 1;
//        $param->order_type = 1;
//        $param->create_time_start = $begin_time;
//        $param->create_time_end = $end_time;
//        $param->abnormal_order = 1;
//        $param->trade_type = 1;
        $param->update_time_start = $begin_time;
        $param->update_time_end   = $end_time;
        $param->size              = 20;
        $param->page              = 0;
        $param->order_by          = "update_time";
        $param->order_asc         = false;

        $response = $request->execute('');
        dd($response);
        if ($response->code !== 10000) {
            //TODO: 错误记录

            if (in_array($response->err_no, [30001, 30002, 30005, 30007])) {
                $this->accessTokenJob();
            }
            return true;
        }


        dd($response);
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
