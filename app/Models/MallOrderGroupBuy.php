<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;

use Illuminate\Support\Facades\DB;

/**
 * Description of MallOrderGroupBuy
 *
 * @author wangxh
 */
class MallOrderGroupBuy extends Base {

    protected $table = 'nlsg_mall_order';

    public function prepareCreateGroupBuyOrder($params, $user) {
        $sku_list = $this->createGroupBuyOrderTool($params, $user);
        return $sku_list;
    }

    public function createGroupBuyOrderTool($params, $user, $check_sub = false) {
        $now_date = date('Y-m-d H:i:s');
        $can_sub = true;
        //检查参数逻辑
        $check_params = $this->checkGroupBuyParams($params);
        if (($check_params['code'] ?? true) === false) {
            return $check_params;
        }

        $params['from_cart'] = 2;
        //获取并检查sku是否合法
        $orderModel = new MallOrder();
        $sku_list = $orderModel->getOrderSkuList($params, $user['id']);
        if (($sku_list['code'] ?? true) === false) {
            return $sku_list;
        }

        //秒杀订单只能是一个商品
        $sku_list = reset($sku_list);


        //校验商品秒杀状态是否可用
        $check_sku_res = $this->checkSkuCanGroupBuy(
                $sku_list['goods_id'], $sku_list['sku_number']
        );


        dd([$params, $sku_list, $check_sku_res]);


        if (!is_object($check_sku_res) && (($check_sku_res['code'] ?? true) === false)) {
            return $check_sku_res;
        } else {
            $f_data = SpecialPriceModel::find($check_sku_res->id);
            if ($f_data->stock > 0) {
                if ($f_data->use_stock >= $f_data->stock) {
                    return ['code' => false, 'msg' => '已售罄'];
                }

                if (($f_data->stock - $f_data->use_stock) < $sku_list['num']) {
                    return ['code' => false, 'msg' => '库存不足'];
                }
            }
            $sku_list['flash_sale_price'] = $f_data->sku_price;
            $sku_list['freight_free'] = $f_data->freight_free;
            $sku_list['freight_free_line'] = $f_data->freight_free_line;
            $sku_list['flash_sale_id'] = $check_sku_res->id;
        }

        //校验用户是否参与过该次秒杀
        $check_user = $this->checkUserCanFlashSale($check_sku_res->id, $user['id']);
        if (($check_user['code'] ?? true) === false) {
            return $check_user;
        }


        $all_original_price = GetPriceTools::PriceCalc(
                        '*', $sku_list['original_price'], $sku_list['num']
        );

        $all_price = GetPriceTools::PriceCalc(
                        '*', $sku_list['flash_sale_price'], $sku_list['num']
        );
        $freight_money = ConfigModel::getData(7); //运费
        $coupon_freight = 0; //是否免邮券
        $freight_free_flag = false; //是否免邮
        //如果包邮
        if ($f_data->freight_free == 1) {

            if ($f_data->freight_free_line > 0) {
                //有金额限制
                if ($f_data->freight_free_line <= $all_price) {
                    $freight_free_flag = true;
                }
            } else {
                $freight_free_flag = true;
            }
        }

        if ($freight_free_flag) {
            $coupon_freight_list = [];
            $params['coupon_freight_id'] = 0;
        } else {
            $coupon_list = Coupon::getCouponListForOrder($user['id'], 0);
            $coupon_freight_list = $coupon_list['coupon_freight'] ?? [];
            if ($params['coupon_freight_id']) {
                foreach ($coupon_freight_list as $cv) {
                    if ($params['coupon_freight_id'] == $cv['id']) {
                        $coupon_freight = 1;
                    }
                }
                if ($coupon_freight == 0) {
                    return ['code' => false, 'msg' => '优惠券信息错误'];
                }
            }
            //如果有选定的免邮券,则免邮
            if ($coupon_freight == 1) {
                $freight_free_flag = true;
            }
        }

        //****************地址列表和校验地址*********************
        $used_address = [];
        $addressModel = new MallAddress();
        $address_list = $addressModel->getList($user['id']);
        if ($params['address_id']) {
            foreach ($address_list as $av) {
                if ($params['address_id'] == $av->id) {
                    $used_address = $av->toArray();
                }
            }
            if (empty($used_address)) {
                return ['code' => false, 'msg' => '地址信息错误'];
            }
        }
        //****************运费模板*********************
        if ($freight_free_flag === false) {
            if (!empty($used_address)) {
                $sku_list['freight_money'] = FreightTemplate::getFreightMoney(
                                $sku_list, $used_address
                );
            }
            if (($sku_list['freight_money'] ?? 0) > $freight_money) {
                $freight_money = $sku_list['freight_money'];
            }
        } else {
            $freight_money = 0;
        }

        $order_price = GetPriceTools::PriceCalc('+', $all_price, $freight_money);

        $price_list = [
            'all_original_price' => $all_original_price,
            'all_price' => $all_price,
            'freight_money' => $freight_money,
            'sp_cut_money' => GetPriceTools::PriceCalc('-', $all_original_price, $all_price),
            'freight_free_flag' => $freight_free_flag,
            'order_price' => $order_price,
        ];


        $sku_list_show = [];
        $sku_list_show['goods_id'] = $sku_list['goods_id'];
        $sku_list_show['name'] = $sku_list['name'];
        $sku_list_show['subtitle'] = $sku_list['subtitle'];
        $sku_list_show['picture'] = $sku_list['picture'];
        $sku_list_show['sku_value_list'] = $sku_list['sku_value'];
        $sku_list_show['num'] = $sku_list['num'];
        $sku_list_show['original_price'] = $sku_list['original_price'];
        $sku_list_show['price'] = $sku_list['flash_sale_price'];

        $res = [
            'user' => $user,
            'sku_list' => $sku_list_show,
            'price_list' => $price_list,
            'address_list' => $address_list,
            'coupon_freight_list' => $coupon_freight_list ?? [],
            'used_address' => $used_address,
        ];

        if ($params['post_type'] == 1 && empty($used_address)) {
            $can_sub = false;
        }

        if ($check_sub) {
            $res['can_sub'] = $can_sub;
            $res['sku_list'] = $sku_list;
        }

        return $res;
    }

    public function checkGroupBuyParams(&$params) {
        if (empty($params['sku'])) {
            return ['code' => false, 'msg' => '参数错误', 'ps' => 'sku'];
        }

        if (!is_array($params['sku'])) {
            $params['sku'] = explode(',', $params['sku']);
        }

        if (!in_array($params['os_type'], [1, 2, 3])) {
            return ['code' => false, 'msg' => '参数错误', 'ps' => 'os_type=1,2,3'];
        }

        if (count($params['sku']) !== 1) {
            return ['code' => false, 'msg' => '参数错误', 'ps' => 'sku数量有误'];
        }
        if (empty($params['goods_id'] ?? 0)) {
            return ['code' => false, 'msg' => '参数错误', 'ps' => 'goods_id错误'];
        }
        if ($params['buy_num'] < 1) {
            return ['code' => false, 'msg' => '参数错误', 'ps' => '购买数量有误'];
        }

        if (!in_array($params['post_type'], [1, 2])) {
            return ['code' => false, 'msg' => '参数错误', 'ps' => 'post_type=1,2'];
        }

        if (!in_array($params['buy_type'], [1, 2])) {
            return ['code' => false, 'msg' => '参数错误', 'ps' => 'buy_type=1,2'];
        }

        $params['coupon_freight_id'] = intval($params['coupon_freight_id'] ?? 0);
        $params['coupon_goods_id'] = intval($params['coupon_goods_id'] ?? 0);
        $params['address_id'] = intval($params['address_id'] ?? 0);

        if ($params['post_type'] == 2 && $params['address_id'] == 0) {
            return ['code' => false, 'msg' => '参数错误',
                'ps' => '如果自提,需传自提地址address_id'];
        }

        if ($params['buy_type'] == 1 && !empty($params['group_key'])) {
            //开团
            return ['code' => false, 'msg' => '参数错误', 'ps' => 'buy_type和group_key冲突'];
        }

        if ($params['buy_type'] == 2 && empty($params['group_key'])) {
            //参团
            return ['code' => false, 'msg' => '参数错误', 'ps' => 'buy_type和group_key冲突'];
        }

        if (!empty($params['group_key'])) {
            $check_key = MallGroupBuyList::checkGroupKeyCanUse($params['group_key']);
            if (($check_key['code'] ?? true) === false) {
                return $check_key;
            }
        }
    }

    public function checkSkuCanGroupBuy($goods_id, $sku) {
        $now_date = date('Y-m-d H:i:s');
        $spModel = new SpecialPriceModel();
        $sp_data = $spModel->getSpData($goods_id, 1);

        if ($sp_data->isEmpty()) {
            return ['code' => false, 'msg' => '活动不存在'];
        }

        $res = [];
        foreach ($sp_data as $k => $v) {
            if ($v->type == 4) {
                if ($v->begin_time > $now_date) {
                    unset($sp_data[$k]);
                }
                if ($v->end_time < $now_date) {
                    unset($sp_data[$k]);
                }
                if ($v->sku_number != $sku) {
                    unset($sp_data[$k]);
                }
                if (isset($sp_data[$k])) {
                    $res = $sp_data[$k];
                    break;
                }
            } else {
                unset($sp_data[$k]);
            }
        }

        if (empty($res)) {
            return ['code' => false, 'msg' => '活动不存在'];
        } else {
            return $res;
        }
    }

}
