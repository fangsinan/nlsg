<?php


namespace App\Servers;


use App\Models\MallGoods;
use App\Models\MallPicture;
use App\Models\MallSku;
use App\Models\MallTosBind;
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
            return ['code' => false, 'msg' => 'goods 错误'];
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
        foreach ($params['picture_list'] as $v) {
            $temp_pic_arr = [];
            $temp_pic_arr['url'] = $v['url'];
            $temp_pic_arr['goods_id'] = $goods_id;
            $temp_pic_arr['status'] = 1;
            $temp_pic_arr['is_main'] = $v['is_main'] ?? 0;
            $temp_pic_arr['is_video'] = $v['is_video'] ?? 0;
            $temp_pic_arr['duration'] = $v['duration'] ?? '';
            $temp_pic_arr['cover_img'] = $v['cover_img'] ?? '';
            $pic_arr[] = $temp_pic_arr;
        }

        $pic_res = MallPicture::created($pic_arr);
        if (!$pic_res) {
            DB::rollBack();
            return ['code' => false, 'msg' => 'pic 错误'];
        }

        //tos
        DB::table('nlsg_mall_goods_tos_bind')
            ->where('goods_id', '=', $goods_id)
            ->delete();
        $tos_arr = [];
        foreach ($params['tos'] as $v) {
            $temp_tos_arr = [];
            $temp_tos_arr['goods_id'] = $goods_id;
            $temp_tos_arr['tos_id'] = $v;
            $tos_arr[] = $temp_tos_arr;
        }
        $tos_res = MallTosBind::created($tos_arr);
        if (!$tos_res) {
            DB::rollBack();
            return ['code' => false, 'msg' => 'tos 错误'];
        }

        //sku

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
