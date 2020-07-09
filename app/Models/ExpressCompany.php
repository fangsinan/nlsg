<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;

use Illuminate\Support\Facades\Http;

/**
 * Description of ExpressCompany
 *
 * @author wangxh
 */
class ExpressCompany extends Base {

    protected $table = 'nlsg_express_company';

    public static function onlyGetName($id = 0, $flag = 1) {
        if (!$id) {
            return '';
        }
        $data = self::find($id);
        if ($flag == 1) {
            return $data->name ?? '';
        } else {
            return $data->code ?? '';
        }
    }

    public function getPostInfo($params, $user) {
        if (empty($params['express_id']) ||
                empty($params['order_id']) ||
                empty($params['express_num']) ||
                empty($params['type'])) {
            return ['code' => false, 'msg' => '参数错误'];
        }

        //todo 查询用户和订单匹配状态   订单是否已发货

        $data['type'] = self::onlyGetName($params['express_id'], 2);
        if (empty($data['type'])) {
            return ['code' => false, 'msg' => '参数错误'];
        }

        $mobile = '17301246549'; //公司固定发货手机号

        $data['mobile'] = $mobile;
        $data['number'] = $params['express_num'];

        $querys = http_build_query($data);

        $data = $this->toQuery($params['express_num'], $data['type']);
        return $data;
    }

    public function toQuery($number, $type) {

        $host = "http://jisukdcx.market.alicloudapi.com";
        $path = "/express/query";
        $method = "GET";
        $appcode = "cc703c76da5b4b15bb6fc4aa0c0febf9";
        $headers = array();
        array_push($headers, "Authorization:APPCODE " . $appcode);
        $querys = 'number=' . $number . '&type=' . $type;
        $bodys = "";
        $url = $host . $path . "?" . $querys;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        if (1 == strpos("$" . $host, "https://")) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }

        $result = curl_exec($curl);
        $jsonarr = json_decode($result, true);

        dd([$curl,$number,$type,$querys,$jsonarr,__LINE__]);

        $result = $jsonarr['result'];
        $result = empty($result) ? [] : $result;
        return $result;
    }

}
