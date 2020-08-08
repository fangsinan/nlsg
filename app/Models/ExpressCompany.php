<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;

use Illuminate\Support\Facades\Cache;

/**
 * Description of ExpressCompany
 *
 * @author wangxh
 */
class ExpressCompany extends Base
{

    protected $table = 'nlsg_express_company';

    public static function onlyGetName($id = 0, $flag = 1)
    {
        if (!$id) {
            return '';
        }
        $data = self::find($id);
        if ($flag == 1) {
            return $data->name ?? '';
        } elseif ($flag == 2) {
            return $data->code ?? '';
        } elseif ($flag == 3) {
            return $data->phone ?? '';
        }
    }

    public function getPostInfo($params)
    {
        if (empty($params['express_id']) || empty($params['express_num'])) {
            return ['code' => false, 'msg' => '参数错误'];
        }

        $express_type = self::onlyGetName($params['express_id'], 2);
        if (empty($express_type)) {
            return ['code' => false, 'msg' => '参数错误'];
        }

        $check = ExpressInfo::where('express_id', '=', $params['express_id'])
            ->where('express_num', '=', $params['express_num'])
            ->first();

        if ($check->delivery_status == 3) {
            //已签收的直接返回
            return json_decode($check->history, true);
        }

        $data = $this->toQuery($params['express_num'], $express_type);
        $data['express_phone'] = ExpressCompany::onlyGetName($params['express_id'],3);

        if (!empty($data)) {
            //如果查询有结果 返回结果并存库
            $update_data = [
                'history' => json_encode($data),
                'delivery_status' => $data['deliverystatus'],
            ];

            ExpressInfo::where('express_id', '=', $params['express_id'])
                ->where('express_num', '=', $params['express_num'])
                ->update($update_data);

            return $data;
        } else {
            //直接返回库
            return json_decode($check->history, true);
        }
    }

    public function toQuery($number, $type)
    {

        $cache_key_name = 'post_info' . '_' . $type . '_' . $number;

        $expire_num = CacheTools::getExpire('post_info');
        $res = Cache::get($cache_key_name);
        if (empty($res)) {
            //如果没有,表示过期就查询.
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
            if (empty($result)) {
                return '';
            }
            $jsonarr = json_decode($result, true);
            if ($jsonarr['status'] !== 0) {
                return '';
            }
            Cache::add($cache_key_name, time(), $expire_num);
            $result = $jsonarr['result'];
            return $result;
        } else {
            return '';
        }
    }

    public function companyList()
    {
        return self::where('status', '=', 1)
            ->whereIn('show_frontend', [1, 3])
            ->orderBy('rank', 'asc')
            ->select(['id', 'name'])
            ->get();
    }

}
