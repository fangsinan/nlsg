<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

/**
 * Description of MallAddress
 *
 * @author wangxh
 */
class MallAddress extends Model {

    protected $table = 'nlsg_mall_address';

    public function create($params, $user_id) {
        $province = $params['province'] ?? 0;
        $city = $params['city'] ?? 0;
        $area = $params['area'] ?? 0;
        $details = $params['details'] ?? '';
        $name = $params['name'] ?? '';
        $phone = $params['phone'] ?? '';
        if (empty($province) || empty($city) ||
                empty($details) || empty($name) ||
                empty($phone)) {
            return ['code' => false, 'msg' => '参数错误'];
        }

        if (empty($params['id'] ?? 0)) {
            $address = self::where('id', '=', $params['id'])
                    ->where('user_id', '=', $user_id)
                    ->get();
        } else {
            $address = new self();
        }
    }

}
