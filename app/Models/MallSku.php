<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;

use Illuminate\Support\Facades\DB;

/**
 * Description of MallSku
 *
 * @author wangxh
 */
class MallSku extends Base {

    protected $table = 'nlsg_mall_sku';

    public static function getList($goods_id) {

        $sku = DB::table('nlsg_mall_sku')
                ->where('goods_id', '=', $goods_id)
                ->where('status', '=', 1)
                ->select(['id', 'goods_id', 'sku_number', 'picture',
                    'original_price', 'price', 'stock'])
                ->get();

        $sku_value = DB::table('nlsg_mall_sku_value')
                ->where('goods_id', '=', $goods_id)
                ->where('status', '=', 1)
                ->select(['sku_id', 'key_name', 'value_name'])
                ->get();

        foreach ($sku as $v) {
            if (!isset($v->sku_value)) {
                $v->sku_value = [];
            }
            foreach ($sku_value as $vv) {
                if ($v->id == $vv->sku_id) {
                    $v->sku_value[] = $vv;
                }
            }
        }

        return $sku->toArray();
    }

    public function sku_vavlue_list() {
        return $this->hasMany('App\Models\MallSkuValue', 'sku_id', 'id')
                        ->where('status', '=', 1)
                        ->select(['id', 'sku_id', 'key_name', 'value_name']);
    }

    public static function getSkuStock($goods_id, $sku_number) {
        $check_sku = DB::table('nlsg_mall_goods as nmg')
                ->join('nlsg_mall_sku as sku', 'nmg.id', '=', 'sku.goods_id')
                ->where('nmg.id', '=', $goods_id)
                ->where('sku.sku_number', '=', $sku_number)
                ->where('nmg.status', '=', 2)
                ->where('sku.status', '=', 1)
                ->select(['sku.stock'])
                ->first();

        if ($check_sku) {
            return $check_sku->stock;
        } else {
            return false;
        }
    }

}
