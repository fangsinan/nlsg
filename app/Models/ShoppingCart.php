<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;

use Illuminate\Support\Facades\DB;

/**
 * Description of ShoppingCart
 *
 * @author wangxh
 */
class ShoppingCart extends Base
{

    protected $table = 'nlsg_mall_shopping_cart';

    public function create($params, $user_id)
    {
        //新增
        if (isset($params['goods_id'])) {
            $params_data = [$params];
            $is_one = true;
        } else {
            $params_data = $params;
            $is_one = false;
        }

        $all_res = true;
        //批量添加,不加事务
//        DB::beginTransaction();

        foreach ($params_data as $params) {

            $goods_id = $params['goods_id'] ?? 0;
            $sku_number = $params['sku_number'] ?? 0;
            $num = $params['num'] ?? 0;
            $inviter = $params['inviter'] ?? 0;
            $flag = $params['flag'] ?? 'replace';

            if (empty($goods_id) || empty($sku_number) || empty($num)) {
                if ($is_one) {
                    return ['code' => false, 'msg' => '参数错误'];
                } else {
                    continue;
                }
            }

            //校验商品信息
            $check_sku = MallSku::getSkuStock($goods_id, $sku_number);
            if ($check_sku === false) {
                if ($is_one) {
                    return ['code' => false, 'msg' => '参数错误'];
                } else {
                    continue;
                }
            } else {
                if ($num > $check_sku) {
                    $num = $check_sku;
                }
            }

            $old_num = 0;
            if (!empty(($params['id'] ?? 0))) {
                $temp = self::where('user_id', '=', $user_id)
                    ->find($params['id']);
                if (!$temp) {
                    if ($is_one) {
                        return ['code' => false, 'msg' => 'id错误'];
                    } else {
                        continue;
                    }
                }
                $old_num = $temp->num;
            } else {
                $check_cart = self::where('user_id', '=', $user_id)
                    ->where('goods_id', '=', $goods_id)
                    ->where('sku_number', '=', $sku_number)
                    ->first();
                if ($check_cart) {
                    $temp = $check_cart;
                    $old_num = $check_cart->num;
                } else {
                    $temp = new self();
                }
            }

            $temp->goods_id = $goods_id;
            $temp->sku_number = $sku_number;
            $temp->user_id = $user_id;
            if ($flag == 'replace') {
                $temp->num = $num;
            } else {
                $temp->num = $num + $old_num;
                if ($temp->num > $check_sku) {
                    $temp->num = $check_sku;
                }
            }
            $temp->inviter = $inviter;
            $res = $temp->save();
        }

        if ($is_one) {
            if ($res === false) {
                return ['code' => false, 'msg' => '加入购物车失败'];
            } else {
                return ['code' => true, 'msg' => '加入购物车成功'];
            }
        } else {
            return ['code' => true, 'msg' => '加入购物车成功'];
        }
    }

    public function getList($user)
    {
        $cart = self::where('user_id', '=', $user['id'])
            ->orderBy('updated_at', 'desc')
            ->select(['id', 'goods_id', 'sku_number', 'num'])
            ->get();

        if ($cart->isEmpty()) {
            return new class {
            };
        }

        $cart = $cart->toArray();

        $goods_id_list = array_column($cart, 'goods_id');

        $goodsModel = new MallGoods();

        $goods_list = $goodsModel->getList(
            [
                'ids_str' => $goods_id_list,
                'ob' => 'ids_str',
                'get_all' => 1,
                'get_sku' => 1,
                'page' => 1,
                'size' => 1,
                'invalid' => 1
            ], $user, false);


        foreach ($cart as &$v) {
            $v['invalid'] = 1;
            foreach ($goods_list as $gv) {
                if ($v['goods_id'] == $gv->id) {
                    $v['invalid'] = 0;
                    $v['goods_name'] = $gv->name;
                    $v['goods_subtitle'] = $gv->subtitle;
                    if ($gv->status != 2) {
                        $v['invalid'] = 1;
                    }
                    foreach ($gv->sku_list as $sv) {
                        if ($v['sku_number'] == $sv->sku_number) {
                            $v['original_price'] = $sv->original_price;
                            $v['price'] = $sv->price;
                            $v['sku_list'] = $sv;
                            if ($sv->status == 0 || $sv->stock < 1) {
                                $v['invalid'] = 1;
                            }
                        }
                    }
                }
            }
        }

        $list = [];
        $invalid_list = [];

        foreach ($cart as $cv) {
            if (($cv['invalid'] ?? 0) == 1) {
                $invalid_list[] = $cv;
            } else {
                $list[] = $cv;
            }
        }

        return ['list' => $list, 'invalid_list' => $invalid_list];
    }

    public function statusChange($id, $flag, $user_id)
    {

        if (!is_array($id)) {
            $id = explode(',', $id);
        }

        $temp = self::where('user_id', '=', $user_id)
            ->whereIn('id', $id)
            ->count();

        if (count($id) !== $temp) {
            return ['code' => false, 'msg' => 'id错误'];
        }

        switch ($flag) {
            case 'del':
                $res = self::where('user_id', '=', $user_id)
                    ->whereIn('id', $id)
                    ->delete();
                break;
            default:
                return ['code' => false, 'msg' => '参数错误'];
        }

        if ($res) {
            return ['code' => true, 'msg' => '成功'];
        } else {
            return ['code' => false, 'msg' => '失败'];
        }
    }

    public function getCount($uid)
    {
        if (empty($uid)) {
            return ['count' => 0];
        } else {
            $count = DB::table('nlsg_mall_shopping_cart as msc')
                ->join('nlsg_mall_goods as goods', 'msc.goods_id', '=', 'goods.id')
                ->join('nlsg_mall_sku as sku', 'msc.sku_number', '=', 'sku.sku_number')
                ->where('msc.user_id', '=', $uid)
                ->where('goods.status', '=', 2)
                ->where('sku.status', '=', 1)
                ->where('sku.stock', '>', 0)
                ->sum('msc.num');
            return ['count' => $count];
        }
    }

}
