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
        $appcode = "635e0f54d03e443989140f0163260408";
        $headers = [
            "Authorization:APPCODE " . $appcode,
            "Content-Type" . ":" . "application/json; charset=UTF-8"
        ];

        $host = "https://jisukdcx.market.alicloudapi.com";
        $path = "/express/query";
        $querys = "number=YT4538526006366&type=YTO";
        $url = $host . $path . "?" . $querys;


        $response = Http::withHeaders($headers)->get($url);

        dd($response);










        $host = "https://jisukdcx.market.alicloudapi.com";
        $path = "/express/query";
        $method = "GET";
        $appcode = "635e0f54d03e443989140f0163260408";
        $headers = array();
        array_push($headers, "Authorization:APPCODE " . $appcode);
        //根据API的要求，定义相对应的Content-Type
        array_push($headers, "Content-Type" . ":" . "application/json; charset=UTF-8");
        $querys = "number=YT4538526006366&type=YTO";
        $bodys = "null";
        $url = $host . $path . "?" . $querys;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        if (1 == strpos("$" . $host, "https://")) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        curl_setopt($curl, CURLOPT_POSTFIELDS, $bodys);
        var_dump(curl_exec($curl));
    }

}
