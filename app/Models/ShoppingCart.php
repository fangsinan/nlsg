<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

/**
 * Description of ShoppingCart
 *
 * @author wangxh
 */
class ShoppingCart extends Base {

    protected $table = 'nlsg_mall_shoppingcar';

    public function create($params, $user_id) {
        $goods_id = $params['goods_id'] ?? 0;
        $sku_number = $params['sku_number'] ?? 0;
        $num = $params['num'] ?? 0;
        $inviter = $params['inviter'] ?? 0;

        if (empty($goods_id) || empty($sku_number) || empty($num)) {
            return ['code' => false, 'msg' => '参数错误'];
        }

        //校验商品信息
        $check_sku = MallSku::getSkuStock($goods_id, $sku_number);
        if ($check_sku === false) {
            return ['code' => false, 'msg' => '参数错误'];
        } else {
            if ($num > $check_sku) {
                $num = $check_sku;
            }
        }

        if (!empty(($params['id'] ?? 0))) {
            $temp = self::where('user_id', '=', $user_id)
                    ->find($params['id']);
            if (!$temp) {
                return ['code' => false, 'msg' => 'id错误'];
            }
        } else {
            $temp = new self();
        }

        $temp->goods_id = $goods_id;
        $temp->sku_number = $sku_number;
        $temp->user_id = $user_id;
        $temp->num = $num;
        $temp->inviter = $inviter;

        $res = $temp->save();

        if ($res) {
            return ['code' => true, 'msg' => '成功'];
        } else {
            return ['code' => false, 'msg' => '失败'];
        }
    }

    public function getList($user_id) {
        
    }

    public function statusChange($id, $flag, $user_id) {
        $temp = self::where('user_id', '=', $user_id)
                ->find($id);
        if (!$temp) {
            return ['code' => false, 'msg' => 'id错误'];
        }

        switch ($flag) {
            case 'del':
                $res = $temp->delete();
                if ($res) {
                    return ['code' => true, 'msg' => '成功'];
                } else {
                    return ['code' => false, 'msg' => '失败'];
                }
                break;
            default:
                return ['code' => false, 'msg' => '参数错误'];
        }
    }

}
