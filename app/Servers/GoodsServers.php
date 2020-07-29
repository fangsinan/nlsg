<?php


namespace App\Servers;


use App\Models\MallGoods;
use App\Models\MallSku;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


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
            $goods_model->number = $this->createGoodsSkuNum(1);
        }

        if (empty($params['category_id'])) {
            return ['code' => false, 'msg' => '参数错误:category_id'];
        } else {
            $goods_model->category_id = intval($params['category_id']);
        }

        if (empty($params['name'])) {
            return ['code' => false, 'msg' => '参数错误:name'];
        } else {
            $goods_model->name = $params['name'];
        }

        if (empty($params['subtitle'])) {
            return ['code' => false, 'msg' => '参数错误:subtitle'];
        } else {
            $goods_model->subtitle = $params['subtitle'];
        }

        if (empty($params['picture'])) {
            return ['code' => false, 'msg' => '参数错误:picture'];
        } else {
            $goods_model->picture = trim($params['picture'], '/');
        }

        if (empty($params['freight_id'])) {
            return ['code' => false, 'msg' => '参数错误:freight_id'];
        } else {
            $goods_model->freight_id = intval($params['freight_id']);
        }

        if (empty($params['original_price'])) {
            return ['code' => false, 'msg' => '参数错误:original_price'];
        } else {
            $goods_model->original_price = $params['original_price'];
        }
        if (empty($params['price'])) {
            return ['code' => false, 'msg' => '参数错误:price'];
        } else {
            $goods_model->price = $params['price'];
        }

        $goods_model->keywords = $params['keywords'] ?? '';

        $goods_model->content = $params['content'] ?? '';

        if (empty($params['status'])) {
            return ['code' => false, 'msg' => '参数错误:status'];
        } else {
            $goods_model->status = intval($params['status']);
            if (!in_array($goods_model->status, [1, 2])) {
                return ['code' => false, 'msg' => '参数错误:status'];
            }
        }

        DB::beginTransaction();

        //goods
        $goods_res = $goods_model->save();
        if (!$goods_res) {
            DB::rollBack();
        }

        if ($params['goods_id'] ?? 0) {
            $goods_id = $params['goods_id'];
        } else {
            $goods_id = $goods_model->id;
        }

        //goods_picture
        DB::table('nlsg_mall_picture')
            ->where('goods_id', '=', $goods_id)
            ->delete();

        $pic_arr = [];
        foreach($params['picture_list'] as $v){

        }

    }


    /**
     * 生成货号或sku编码
     * @param $type 1:商品 2:sku
     * @return string
     */
    public function createGoodsSkuNum($type)
    {
        if ($type == 1) {
            //商品
            $num = Str::random(16);
            $check = MallGoods::where('number', '=', $num)->first();
        } else {
            //sku
            $num = Str::random(10);
            $check = MallSku::where('sku_number', '=', $num)->first();
        }
        if ($check) {
            $this->createGoodsSkuNum($type);
        } else {
            return $num;
        }
    }
}
