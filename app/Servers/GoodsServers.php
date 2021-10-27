<?php


namespace App\Servers;

use App\Models\MallCategory;
use App\Models\MallGoods;
use App\Models\MallSku;
use App\Models\MallSkuValue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GoodsServers
{
    public function add($params)
    {
        if (($params['goods_id'] ?? 0) !== 0) {
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
            if (is_string($params['category_id'])) {
                $params['category_id'] = explode(',', $params['category_id']);
                if (count($params['category_id']) !== 2) {
                    return ['code' => false, 'msg' => '分类必须为2级数据'];
                }
            } else {
                return ['code' => false, 'msg' => 'category_id格式错误'];
            }
            $goods_model->category_id = intval($params['category_id'][1]);
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
        if (!empty($goods_model->keywords)) {
            $reg = "/[[:punct:]]/i";
            $goods_model->keywords = preg_replace($reg, ' ', $goods_model->keywords);
            $goods_model->keywords = explode(' ', $goods_model->keywords);
            $goods_model->keywords = implode(',', array_filter($goods_model->keywords));
        }

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

        if (($params['goods_id'] ?? 0) !== 0) {
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

        $pic_res = DB::table('nlsg_mall_picture')->insert($pic_arr);

        if (!$pic_res) {
            DB::rollBack();
            return ['code' => false, 'msg' => 'pic 错误'];
        }

        //tos
        DB::table('nlsg_mall_goods_tos_bind')
            ->where('goods_id', '=', $goods_id)
            ->delete();

        if (0) {
            $tos_arr = [];
            foreach ($params['tos'] as $v) {
                $temp_tos_arr = [];
                $temp_tos_arr['goods_id'] = $goods_id;
                $temp_tos_arr['tos_id'] = $v;
                $tos_arr[] = $temp_tos_arr;
            }
            $tos_res = DB::table('nlsg_mall_goods_tos_bind')->insert($tos_arr);
            if (!$tos_res) {
                DB::rollBack();
                return ['code' => false, 'msg' => 'tos 错误'];
            }
        }

        //sku
        if (empty($params['sku_list']) || !is_array($params['sku_list'])) {
            return ['code' => false, 'msg' => 'sku_list 错误'];
        }

        //如果是编辑,把之前存在,现在没提交的sku_id删除
        if (($params['goods_id'] ?? 0) !== 0) {
            $del_sku_id_array = array_column($params['sku_list'], 'id');
            if (empty($del_sku_id_array)) {
                $del_sku_res = DB::table('nlsg_mall_sku')
                    ->where('goods_id', '=', $params['goods_id'])
                    ->whereNotIn('id', $del_sku_id_array)
                    ->update(['status' => 2]);
            } else {
                $del_sku_res = DB::table('nlsg_mall_sku')
                    ->where('goods_id', '=', $params['goods_id'])
                    ->update(['status' => 2]);
            }

            if ($del_sku_res === false) {
                DB::rollBack();
                return ['code' => false, 'msg' => '修改sku发生错误'];
            }
        }

        $edit_sku_id_arr = [];
        foreach ($params['sku_list'] as $v) {
            if (($v['id'] ?? 0) !== 0) {
                //如果有是编辑,sku_number不变
                $sku = MallSku::where('goods_id', '=', $goods_id)
                    ->find($v['id']);
                if ($sku) {
                    $edit_sku_id_arr[] = $v['id'];

                    $sku->picture = $v['picture'];
                    $sku->original_price = $v['original_price'];
                    $sku->price = $v['price'];
                    $sku->cost = $v['cost'] ?? 0;
                    $sku->promotion_cost = $v['promotion_cost'] ?? 0;
                    $sku->stock = $v['stock'];
                    $sku->warning_stock = $v['warning_stock'];
                    $sku->status = $v['status'];
                    $sku->weight = $v['weight'];
                    $sku->volume = $v['volume'];

                    DB::table('nlsg_mall_sku_value')
                        ->where('sku_id', '=', $v['id'])
                        ->delete();

                    $edit_sku_res = $sku->save();

                    if (!$edit_sku_res) {
                        DB::rollBack();
                        return ['code' => false, 'msg' => 'sku 错误'];
                    }

                    foreach ($v['value_list'] as $vv) {
                        $sku_value = new MallSkuValue();
                        $sku_value->goods_id = $goods_id;
                        $sku_value->sku_id = $v['id'];
                        $sku_value->key_name = $vv['key_name'];
                        $sku_value->value_name = $vv['value_name'];
                        $temp_sku_value_res = $sku_value->save();
                        if (!$temp_sku_value_res) {
                            DB::rollBack();
                            return ['code' => false, 'msg' => ' sku value 错误'];
                        }
                    }

                } else {
                    DB::rollBack();
                    return ['code' => false, 'msg' => 'sku:' . $v['id'] . '错误'];
                }
            } else {
                //新加
                $sku = new MallSku();
                $sku->goods_id = $goods_id;
                $sku->sku_number = $this->createGoodsSkuNum(2);
                $sku->picture = $v['picture'];
                $sku->original_price = $v['original_price'];
                $sku->price = $v['price'];
                $sku->cost = $v['cost'] ?? 0;
                $sku->promotion_cost = $v['promotion_cost'] ?? 0;
                $sku->stock = $v['stock'];
                $sku->warning_stock = $v['warning_stock'];
                $sku->status = $v['status'];
                $sku->weight = $v['weight'];
                $sku->volume = $v['volume'];
                $sku->erp_spec_no = $v['erp_enterprise_code'] ?? '';
                $sku->erp_goods_no = $v['erp_goods_code'] ?? '';
                $edit_sku_res = $sku->save();

                if (!$edit_sku_res) {
                    DB::rollBack();
                    return ['code' => false, 'msg' => 'sku 错误'];
                }

                foreach ($v['value_list'] as $vv) {
                    $sku_value = new MallSkuValue();
                    $sku_value->goods_id = $goods_id;
                    $sku_value->sku_id = $sku->id;
                    $sku_value->key_name = $vv['key_name'];
                    $sku_value->value_name = $vv['value_name'];
                    $temp_sku_value_res = $sku_value->save();
                    if (!$temp_sku_value_res) {
                        DB::rollBack();
                        return ['code' => false, 'msg' => ' sku value 错误'];
                    }
                }

            }
        }

        DB::commit();

        CacheServers::clear(1);
        return ['code' => true, 'msg' => '成功'];
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
            //$num = Str::random(16);
            $num = time() . rand(1000, 9999);
            $check = MallGoods::where('number', '=', $num)->first();
        } else {
            //sku
            //$num = Str::random(14);
            $num = time() . rand(1000, 9999);
            $check = MallSku::where('sku_number', '=', $num)->first();
        }
        if ($check) {
            $this->createGoodsSkuNum($type);
        } else {
            return $num;
        }
    }

    public function list($params)
    {
        $size = $params['size'] ?? 10;
        $flag = $params['flag'] ?? 0;

        $query = MallGoods::from('nlsg_mall_goods');

        if (!empty($params['id'])) {
            $query->where('id', '=', intval($params['id']));
        }

        if (!empty($params['category_id'])) {
            $query->where('category_id', '=', intval($params['category_id']));
        }

        if (!empty($params['name'])) {
            $query->where('name', 'like', '%' . $params['name'] . '%');
        }

        $field = ['id', 'category_id', 'name', 'subtitle', 'picture', 'number',
            'original_price', 'price', 'sales_num', 'status', 'keywords',
            'freight_id'];

        $with = [];
        if (!empty($params['id'] ?? 0) && $flag !== 'simple') {
            $field[] = 'content';
            $field[] = 'view_num';
            $field[] = 'collection_num';
            $with[] = 'tos_bind_list';
            $with[] = 'tos_bind_list.tos';
            $with[] = 'picture_list';
        }

        if ($flag !== 'simple') {
            $with[] = 'category_list';
            $with[] = 'categoryStr';
            $with[] = 'categoryStr.categoryParent';
            $with[] = 'category_list';
        }

        $with[] = 'sku_list_back';
        $with[] = 'sku_list_back.sku_value_list';

        switch ($params['ob'] ?? 'default') {
            case 'new_asc':
                $query->orderBy('created_at', 'asc');
                break;
            case 'new_desc':
                $query->orderBy('created_at', 'desc');
                break;
            case 'sales_asc':
                $query->orderBy('sales_num', 'asc');
                break;
            case 'sales_desc':
                $query->orderBy('sales_num', 'desc');
                break;
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
        }

        $query->orderBy('id', 'desc')->with($with)->select($field);

        if ($flag === 'simple') {
            $query->where('status', '=', 2);
            $list = $query->get();
        } else {
            $list = $query->paginate($size);
        }

        foreach ($list as $k => $v) {
            $v->category_string = ($v->categoryStr->categoryParent->id ?? 0) . ',' . ($v->categoryStr->id ?? 0);
            unset($list[$k]->categoryStr);
        }

        return $list;
    }

    public function categoryList()
    {
        $model = new MallCategory();
        return $model->getAllList();
    }

    public function changeStock($params)
    {
        $list = $params['list'] ?? [];
        if (empty($list)) {
            return ['code' => false, 'msg' => '参数错误'];
        }

        DB::beginTransaction();

        $flag = true;

        foreach ($list as $v) {
            $goods_id = $v['goods_id'] ?? 0;
            $sku_number = $v['sku_number'] ?? 0;

            if (empty($goods_id) || empty($sku_number) || !isset($v['stock'])) {
                return ['code' => false, 'msg' => '参数错误'];
            }

            $check_sku = MallSku::where('sku_number', '=', $sku_number)
                ->where('goods_id', '=', $goods_id)
                ->first();
            if (empty($check_sku)) {
                $flag = false;
                break;
            }

            $check_sku->stock = intval($v['stock']);
            $res = $check_sku->save();
            if ($res === false) {
                $flag = false;
            }
        }

        if ($flag === true) {
            DB::commit();
            CacheServers::clear(1);
            return ['code' => false, 'msg' => '成功'];
        } else {
            DB::rollBack();
            return ['code' => false, 'msg' => '失败'];
        }
    }

    public function changeStatus($params)
    {
        $goods_id = $params['goods_id'] ?? 0;
        $flag = $params['flag'] ?? '';
        if (empty($goods_id) || empty($flag) || !in_array($flag, ['on', 'off', 'del'])) {
            return ['code' => false, 'msg' => '参数错误'];
        }

        $check_goods = MallGoods::whereId($goods_id)->first();
        if (empty($check_goods)) {
            return ['code' => false, 'msg' => 'id错误'];
        }

        switch ($flag) {
            case 'on':
                $check_goods->status = 2;
                break;
            case 'off':
                $check_goods->status = 1;
                break;
            case 'del':
                $check_goods->status = 3;
                break;
        }

        $res = $check_goods->save();
        if ($res === false) {
            return ['code' => false, 'msg' => '失败'];
        } else {
            CacheServers::clear(1);
            return ['code' => false, 'msg' => '成功'];
        }

    }

}
