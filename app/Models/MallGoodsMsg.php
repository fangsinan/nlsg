<?php


namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class MallGoodsMsg extends Base
{

    protected $table = 'nlsg_mall_goods_msg';

    public function add($params, $user)
    {
        $goods_id = $params['goods_id'] ?? 0;
        $sku_number = $params['sku_number'] ?? '';
        if (empty($goods_id)) {
            return ['code' => false, 'msg' => '参数错误'];
        }
        $check = self::where('user_id', '=', $user['id'])
            ->where('goods_id', '=', $goods_id)
            ->where('sku_number', '=', $sku_number)
            ->first();

        $msg = '到货后第一时间通知您~';
        if ($check) {
            if ($check->status == 2) {
                $check->status = 1;
            } else {
                $msg = '正在紧急补货中';
            }

        } else {
            $check = new self();
            $check->goods_id = $goods_id;
            $check->sku_number = $sku_number;
            $check->user_id = $user['id'];
            $check->status = 1;
        }

        $res = $check->save();
        if ($res === false) {
            return ['code' => false, 'msg' => '失败,请重试.'];
        } else {
            return ['code' => true, 'msg' => $msg];
        }
    }

}
