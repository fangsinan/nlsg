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


    }
}
