<?php


namespace App\Servers;


use App\Models\MallGoods;
use Illuminate\Support\Facades\DB;

class GoodsServers
{
    public function add($params)
    {
        if ($params['goods_id'] ?? 0) {
            $goods_model = MallGoods::find($params['goods_id']);
            if (!$goods_model) {
                return ['code' => false, 'msg' => 'id错误'];
            }

        } else {
            $goods_model = new MallGoods();
        }

        if (empty($params['category_id'])) {
            return ['code' => false, 'msg' => '参数错误:category_id'];
        } else {
            $goods_model->category_id = 1;
        }

        $goods_model->name = "手机紧身的繁了";
        $goods_model->subtitle = "一个还行的手机";
        $goods_model->picture = "/phone/1.jpg";
        $goods_model->freight_id = 1;
        $goods_model->original_price = 9999;
        $goods_model->price = 999;
        $goods_model->keywords = "手机,智能,安卓";
        $goods_model->content = "<p>图文简介啊发撒发撒地方</p>";
        $goods_model->status = 1;

        DB::beginTransaction();

        $goods_res = $goods_model->save();
        if (!$goods_res) {
            DB::rollBack();
        }

        if ($params['goods_id'] ?? 0) {
            $goods_id = $params['goods_id'];
        } else {
            $goods_id = $goods_model->id;
        }

        dd($goods_id);


    }


    /**
     * 校验number唯一性
     */
    //生成货号和sku   1 sku_number   2货号
//    public function checkNumberOne($type = 1) {
//        if ($type == 1) {
//            $number = rand(10000000, 99999999) + time();
//            $mallSkuModel = new MallSku();
//            $data = $mallSkuModel->getBy(['sku_number' => $number], 'id');
//        } else {
//            $range_str = range(0, 14);
//            $number = '';
//            for ($i = 0; $i < 16; $i++) {
//                $number .= array_rand($range_str);
//            }
//            $goodsObj = new MallGoods();
//            $data = $goodsObj->getBy(['number' => $number], 'id');
//        }
//
//        if (!empty($data)) {
//            return $this->checkNumberOne($type);
//        } else {
//            return $number;
//        }
//    }
}
