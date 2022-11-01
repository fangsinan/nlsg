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
class MallOrderGroupBuy extends Base
{

    protected $table = 'nlsg_mall_order';

    public function prepareCreateGroupBuyOrder($params, $user)
    {
        return $this->createGroupBuyOrderTool($params, $user);
    }

    public function createGroupBuyOrder($params, $user)
    {
        $now = time();
        $dead_time = ConfigModel::getData(12);
        $dead_time = date('Y-m-d H:i:03', ($now + $dead_time * 60));

        $now_date = date('Y-m-d H:i:s', $now);
        if (!in_array($params['pay_type'], [1, 2, 3])) {
            return ['code' => false, 'msg' => '请选择支付方式', 'ps' => 'pay_type error'];
        }
        $data = $this->createGroupBuyOrderTool($params, $user, true);

        if (!($data['can_sub'] ?? false)) {
            return ['code' => false, 'msg' => '参数错误', 'ps' => 'can_sub'];
        }

        $order_data = [];
        $order_data['ordernum'] = MallOrder::createOrderNumber($user['id'], 1);
        $order_data['user_id'] = $user['id'];
        $order_data['order_type'] = 3;
        $order_data['status'] = 1;
        $order_data['cost_price'] = $data['price_list']['all_original_price'];
        $order_data['freight'] = $data['price_list']['freight_money'];
        $order_data['vip_cut'] = $data['price_list']['vip_cut_money'] ?? 0;
        $order_data['coupon_freight_id'] = $params['coupon_freight_id'] ?? 0;
        $order_data['coupon_id'] = 0;
        $order_data['coupon_money'] = 0;
        $order_data['special_price_cut'] = $data['price_list']['sp_cut_money'];
        $order_data['price'] = $data['price_list']['order_price'];
        $order_data['os_type'] = $params['os_type'];
        $order_data['messages'] = $params['messages'] ?? '';
        $order_data['post_type'] = $params['post_type'];
        $order_data['address_id'] = $params['address_id'];
        $order_data['address_history'] = json_encode($data['used_address']);
        $order_data['bill_type'] = $params['bill_type'] ?? 0;
        $order_data['bill_title'] = $params['bill_title'] ?? '';
        $order_data['bill_number'] = $params['bill_number'] ?? '';
        $order_data['bill_format'] = $params['bill_format'] ?? 0;
        $order_data['active_flag'] = $params['active_flag'] ?? '';
        $order_data['created_at'] = $now_date;
        $order_data['updated_at'] = $now_date;
        $order_data['sp_id'] = $data['sku_list']['group_buy_id'];
        $order_data['dead_time'] = $dead_time;
        $order_data['pay_type'] = $params['pay_type'];
        $order_data['live_id'] = $params['live_id'];
        $order_data['live_info_id'] = $params['live_info_id'];
        $order_data['gp_status'] = 1;

        DB::beginTransaction();

        //********************mall_order部分********************
        $order_res = DB::table('nlsg_mall_order')->insertGetId($order_data);
        if (!$order_res) {
            DB::rollBack();
            return [
                'code' => false, 'msg' => '订单提交失败,请重试.',
                'ps' => 'order error'
            ];
        }
        //********************mall_order_detail部分********************
        $details_data = [];
        $details_data['order_id'] = $order_res;
        $details_data['user_id'] = $user['id'];
        $details_data['goods_id'] = $data['sku_list']['goods_id'];
        $details_data['sku_number'] = $data['sku_list']['sku_number'];
        $details_data['num'] = $data['sku_list']['num'];
        $details_data['created_at'] = $now_date;
        $details_data['updated_at'] = $now_date;
        $details_data['sp_id'] = $data['sku_list']['group_buy_id'];
        $details_data['inviter'] = $data['sku_list']['inviter'];
        if ($data['sku_list']['inviter']) {
            $details_data['inviter_history'] = json_encode($data['sku_list']['inviter_info']);
        } else {
            $details_data['inviter_history'] = '';
        }
        $temp_sku_history = [];
        $temp_sku_history['actual_num'] = $data['sku_list']['num'];
        $temp_sku_history['actual_price'] = $data['sku_list']['group_buy_price'];
        $temp_sku_history['original_price'] = $data['sku_list']['original_price'];
        $temp_sku_history['sku_value'] = $data['sku_list']['sku_value'];
        $temp_sku_history['stock'] = $data['sku_list']['stock'];
        $details_data['sku_history'] = json_encode($temp_sku_history);
        $details_data['special_price_type'] = 4;
        $detail_res = DB::table('nlsg_mall_order_detail')->insert($details_data);
        if (!$detail_res) {
            DB::rollBack();
            return ['code' => false, 'msg' => '订单提交失败,请重试.', 'ps' => 'detail error'];
        }
        //********************mall_sku库存部分********************
        $temp_sku = MallSku::find($data['sku_list']['sku_id']);
        $temp_sku->stock = $data['sku_list']['stock'] - $data['sku_list']['num'];
        $temp_sku->sales_num = $data['sku_list']['sales_num'] + $data['sku_list']['num'];
        if ($temp_sku->stock < 0) {
            DB::rollBack();
            return ['code' => false, 'msg' => '订单提交失败,请重试.',
                'ps' => $data['sku_list']['sku_id'] . 'stock error'];
        }
        $sku_res = $temp_sku->save();
        if (!$sku_res) {
            DB::rollBack();
            return ['code' => false, 'msg' => '订单提交失败,请重试.',
                'ps' => $data['sku_list']['sku_id'] . 'sku error'];
        }
        //********************mall_goods销量部分********************
        $goods_sale_res = DB::table('nlsg_mall_goods')
            ->where('id', '=', $data['sku_list']['goods_id'])
            ->increment('sales_num', $data['sku_list']['num']);
        if (!$goods_sale_res) {
            DB::rollBack();
            return ['code' => false, 'msg' => '订单提交失败,请重试.',
                'ps' => 'goods sale error'];
        }
        //********************special_price部分********************
        $sp_res = DB::table('nlsg_special_price')
            ->where('id', '=', $data['sku_list']['group_buy_id'])
            ->increment('use_stock');
        if (!$sp_res) {
            DB::rollBack();
            return ['code' => false, 'msg' => '订单提交失败,请重试.',
                'ps' => 'sp error'];
        }
        //********************优惠券和免邮券部分********************
        if ($params['coupon_goods_id']) {
            $coupon_temp = Coupon::find($params['coupon_goods_id']);
            $coupon_temp->status = 2;
            $coupon_temp->order_id = $order_res;
            $coupon_res = $coupon_temp->save();
            if (!$coupon_res) {
                DB::rollBack();
                return ['code' => false, 'msg' => '订单提交失败,请重试.',
                    'ps' => 'coupon goods error'];
            }
        }

        if ($params['coupon_freight_id']) {
            $coupon_temp = Coupon::find($params['coupon_freight_id']);
            $coupon_temp->status = 2;
            $coupon_temp->order_id = $order_res;
            $coupon_temp->used_time = $now_date;
            $coupon_res = $coupon_temp->save();
            if (!$coupon_res) {
                DB::rollBack();
                return ['code' => false, 'msg' => '订单提交失败,请重试.',
                    'ps' => 'coupon freight error'];
            }
        }
        //********************拼团记录表部分********************
        $gl_data['group_buy_id'] = $data['sku_list']['group_buy_id'];
        $gl_data['group_name'] = $data['sku_list']['group_name'];
        $gl_data['order_id'] = $order_res;
        $gl_data['created_at'] = $now_date;
        $gl_data['updated_at'] = $now_date;
        $gl_data['user_id'] = $user['id'];
        if ($params['buy_type'] == 1) {
            $gl_data['is_captain'] = 1;
            $gl_data['group_key'] = $this->createGroupBuyKey($user['id']);
            //如果是开团 需要指定开团有效期
            $gl_data['begin_at'] = $now_date;
            $gl_data['end_at'] = $dead_time;
        } else {
            $gl_data['is_captain'] = 0;
            $gl_data['group_key'] = $params['group_key'];
        }

        $gl_res = DB::table('nlsg_mall_group_buy_list')->insert($gl_data);
        if (!$gl_res) {
            DB::rollBack();
            return ['code' => false, 'msg' => '订单提交失败,请重试.', 'ps' => 'group_buy_list error'];
        }

        DB::commit();

        return [
            'order_id' => $order_res,
            'ordernum' => $order_data['ordernum'],
            'group_key' => $gl_data['group_key'],
        ];
    }

    public function createGroupBuyKey($uid)
    {
        $now = time();
        $d = date('ymd', $now);
        $u = str_pad($uid, 8, 0, STR_PAD_LEFT);
        $s = $now - strtotime(date('y-m-d', $now));
        return $d . $u . str_pad($s, 5, 0, STR_PAD_LEFT) . rand(100, 999);
    }

    public function createGroupBuyOrderTool($params, $user, $check_sub = false)
    {
        $now_date = date('Y-m-d H:i:s');
        $can_sub = true;
        //检查参数逻辑
        $check_params = $this->checkGroupBuyParams($params, $user['id']);
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

        $sku_list = reset($sku_list);

        //校验商品秒杀状态是否可用
        $check_sku_res = $this->checkSkuCanGroupBuy(
            $sku_list['goods_id'], $sku_list['sku_number']
        );

        if (!is_object($check_sku_res) && (($check_sku_res['code'] ?? true) === false)) {
            return $check_sku_res;
        } else {
            $f_data = SpecialPriceModel::find($check_sku_res->id);
            if (!$f_data) {
                return ['code' => false, 'msg' => '活动错误'];
            }
            $sku_list['group_buy_price'] = $f_data->group_price;
            $sku_list['group_buy_need_num'] = $f_data->group_num;
            $sku_list['group_buy_id'] = $f_data->id;
            $sku_list['group_life'] = $f_data->group_life;
            $sku_list['group_name'] = $f_data->group_name;
        }

        $all_original_price = GetPriceTools::PriceCalc(
            '*', $sku_list['original_price'], $sku_list['num']
        );

        $all_price = GetPriceTools::PriceCalc(
            '*', $sku_list['group_buy_price'], $sku_list['num']
        );

        $temp_sp_cut = GetPriceTools::PriceCalc('-', $sku_list['original_price'], $sku_list['group_buy_price']);
        $sp_cut_money = GetPriceTools::PriceCalc('*', $temp_sp_cut, $sku_list['num']);

        $freight_money = ConfigModel::getData(7); //运费
        $coupon_freight = 0; //是否免邮券
        $freight_free_flag = false; //是否免邮
//        if (($user['new_vip']['level'] ?? 0) == 1) {
//            $freight_free_flag = true;
//        }

        $coupon_money = 0;
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


        //**********************************
        $goods_id_list = array_column($sku_list, 'goods_id');

        //需要排除限定商品得优惠券
        $coupon_list = Coupon::getCouponListForOrder(
            $user['id'], $all_price, $goods_id_list
        );

        if ($params['coupon_goods_id']) {
            foreach ($coupon_list['coupon_goods'] as $cv) {
                if ($params['coupon_goods_id'] == $cv['id']) {
                    $coupon_money = $cv['price'];
                }
            }
            if ($coupon_money == 0) {
                return ['code' => false, 'msg' => '优惠券信息错误'];
            }
        }

        if ($params['coupon_freight_id']) {
            foreach ($coupon_list['coupon_freight'] as $cv) {
                if ($params['coupon_freight_id'] == $cv['id']) {
                    $coupon_freight = 1;
                }
            }
            if ($coupon_freight == 0) {
                return ['code' => false, 'msg' => '优惠券信息错误'];
            }
        }

        //**********************************

        if ($freight_free_flag) {
            $coupon_list['coupon_freight'] = [];
            $params['coupon_freight_id'] = 0;
        } else {
            if ($params['coupon_freight_id']) {
                foreach ($coupon_list['coupon_freight'] as $cv) {
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

        if ($params['post_type'] == 1) {
            if ($params['address_id']) {
                foreach ($address_list as $av) {
                    if ($params['address_id'] == $av->id) {
                        $used_address = $av->toArray();
                    }
                }
                if (empty($used_address)) {
                    return ['code' => false, 'msg' => '地址信息错误'];
                }
            } else {
                if ($address_list[0] ?? '') {
                    $used_address = $address_list[0]->toArray();
                }
            }
        } else {
            if (empty($params['address_id'])) {
                return ['code' => false, 'msg' => '自提地址信息错误'];
            }
            $check_shop_address = FreightTemplate::where('type', '=', '2')
                ->where('status', '=', 1)
                ->find($params['address_id']);
            if (!$check_shop_address) {
                return ['code' => false, 'msg' => '自提地址信息错误'];
            }

            $used_address = $check_shop_address->toArray();
            $used_address['phone'] = $used_address['admin_phone'];
            $used_address['province_name'] = MallAddress::getNameById($used_address['province']);
            $used_address['city_name'] = MallAddress::getNameById($used_address['city']);
            $used_address['area_name'] = MallAddress::getNameById($used_address['area']);
            $freight_free_flag = true;
        }

        //****************运费模板*********************
        $add_freight_money = 0;
        if ($freight_free_flag === false) {
            if (!empty($used_address)) {
//                $sku_list['freight_money'] = FreightTemplate::getFreightMoney(
//                    $sku_list, $used_address
//                );
                $temp_freight_money = FreightTemplate::getFreightMoney(
                    $sku_list, $used_address
                );
                $sku_list['freight_money'] = $temp_freight_money['price'];
                $sku_list['add_freight_money'] = $temp_freight_money['add_price'];

            }
//            if (($sku_list['freight_money'] ?? 0) > $freight_money) {
//                $freight_money = $sku_list['freight_money'];
//            }
            if ($sku_list['add_freight_money'] ?? 0 > $add_freight_money) {
                $add_freight_money = $sku_list['add_freight_money'];
            }
            if (($sku_list['freight_money'] ?? 0) > $freight_money) {
                $freight_money = $sku_list['freight_money'];
            }
        } else {
            $freight_money = 0;
        }

//        $add_freight_money = 0;
//        if ($freight_free_flag === false) {
//            if (!empty($used_address)) {
//                foreach ($sku_list as $k => $v) {
//                    $temp_freight_money = FreightTemplate::getFreightMoney(
//                        $v, $used_address
//                    );
//                    $sku_list[$k]['freight_money'] = $temp_freight_money['price'];
//                    $sku_list[$k]['add_freight_money'] = $temp_freight_money['add_price'];
//                }
//            }
//
//            foreach ($sku_list as $v) {
//                if ($v['add_freight_money'] ?? 0 > $add_freight_money) {
//                    $add_freight_money = $v['add_freight_money'];
//                }
//                if (($v['freight_money'] ?? 0) > $freight_money) {
//                    $freight_money = $v['freight_money'];
//                }
//            }
//        } else {
//            $freight_money = 0;
//        }

        $order_price = GetPriceTools::PriceCalc('-', $all_price, $coupon_money);

//        if ($freight_money > 0 && $order_price > ConfigModel::getData(1)) {
//            $freight_money = $freight_money - ConfigModel::getData(7);
//            if ($freight_money < 0) {
//                $freight_money = 0;
//            }
//            if ($freight_money == 0) {
//                $freight_free_flag = true;
//            }
//        }

        if ($freight_money > 0 && $order_price > ConfigModel::getData(1)) {
            $freight_money = $add_freight_money;
            if ($freight_money < 0) {
                $freight_money = 0;
            }
            if ($freight_money == 0) {
                $freight_free_flag = true;
            }
        }

        $order_price = GetPriceTools::PriceCalc('+', $order_price, $freight_money);

        $price_list = [
            'all_original_price' => $all_original_price,
            'all_price' => $all_price,
            'freight_money' => $freight_money,
            'vip_cut_money' => 0,
            'sp_cut_money' => $sp_cut_money,
            'coupon_money' => $coupon_money,
            'freight_free_flag' => $freight_free_flag,
            'order_price' => $order_price,
        ];

        $price_list_new = [
            ['key' => '商品总额', 'value' => $all_original_price],
            ['key' => '运费', 'value' => $freight_money],
            ['key' => '活动立减', 'value' => $sp_cut_money],
            ['key' => '优惠券总额', 'value' => $coupon_money],
        ];

        foreach ($price_list_new as $new_k => $new_v) {
            if ($new_v['value'] == 0 && !in_array($new_v['key'], ['应付金额', '实付金额'])) {
                unset($price_list_new[$new_k]);
            }
        }

        foreach ($price_list_new as &$new_v) {
            if (in_array($new_v['key'], ['权益立减', '活动立减', '优惠券总额'])) {
                $new_v['value'] = '- ¥' . $new_v['value'];
            } elseif (in_array($new_v['key'], ['运费'])) {
                $new_v['value'] = '+ ¥' . $new_v['value'];
            } else {
                $new_v['value'] = '¥' . $new_v['value'];
            }
        }

        $price_list_new = array_values($price_list_new);

        $sku_list_show = [];
        $sku_list_show['goods_id'] = $sku_list['goods_id'];
        $sku_list_show['name'] = $sku_list['name'];
        $sku_list_show['subtitle'] = $sku_list['subtitle'];
        $sku_list_show['picture'] = $sku_list['picture'];
        $sku_list_show['sku_value_list'] = $sku_list['sku_value'];
        $sku_list_show['num'] = $sku_list['num'];
        $sku_list_show['original_price'] = $sku_list['original_price'];
        $sku_list_show['price'] = $sku_list['group_buy_price'];
        $sku_list_show['sku_number'] = $sku_list['sku_number'];

        $ftModel = new FreightTemplate();
        $shop_address_list = $ftModel->listOfShop(2);

        if (is_array($used_address) && empty($used_address)) {
            $used_address = new class {
            };
        }

        $res = [
            'user' => User::onlySimpleInfo($user),
            'sku_list' => [$sku_list_show],
            'price_list' => $price_list,
            'price_list_new' => $price_list_new,
            'address_list' => $address_list,
            'shop_address_list' => $shop_address_list,
            'coupon_list' => $coupon_list ?? [],
            'used_address' => $used_address,
            'from_cart' => $params['from_cart'] ?? 2,
            'group_key' => $params['group_key'],
            'token' => CacheTools::orderToken($user['id'], 3, 'set'),
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

    public function checkGroupBuyParams(&$params, $user_id)
    {
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
            $check_key = MallGroupBuyList::checkGroupKeyCanUse($params['group_key'], $user_id);
            if (($check_key['code'] ?? true) === false) {
                return $check_key;
            }
        }
    }

    public function checkSkuCanGroupBuy($goods_id, $sku)
    {
        $now_date = date('Y-m-d H:i:s');

        $sp_data = SpecialPriceModel::where('goods_id', '=', $goods_id)
            ->where('sku_number', '=', $sku)
            ->where('type', '=', 4)
            ->where('status', '=', 1)
            ->where('goods_type', '=', 1)
            ->where('begin_time', '<=', $now_date)
            ->where('end_time', '>=', $now_date)
            ->get();

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

    /**
     * 团购商品的订单数据
     * @param type $params
     * @param type $user
     */
    public function groupByTeamList($params, $user)
    {
        $user_id = $user['id'] ?? 0;
        $group_buy_id = $params['group_buy_id'] ?? 0;
        $flag = $params['flag'] ?? 1; //1两条  2全部
        $group_key = $params['group_key'] ?? 0;
        if (empty($group_buy_id)) {
            return ['code' => false, 'msg' => '参数错误'];
        }
        $now = time();
        $now_date = date('Y-m-d H:i:s', $now);

        //开团列表 所差人数  剩余时间
        $query = MallGroupBuyList::where('nlsg_mall_group_buy_list.group_name', '=', $group_buy_id);

        if (($params['show_self'] ?? 0) == 0) {
            $query->where('nlsg_mall_group_buy_list.user_id', '<>', $user_id);
        }

        //已经成团的是否要过滤
        //$query->where('is_success', '=', 0);

        $query->where('is_captain', '=', 1)
            ->where('is_fail', '=', 0)
            ->where('end_at', '>', $now_date);

        if ($group_key) {
            $query->where('group_key', '=', $group_key);
        }

        $query->join('nlsg_mall_order as nmo',
            'nlsg_mall_group_buy_list.order_id', '=', 'nmo.id')
            ->join('nlsg_user as nuser',
                'nlsg_mall_group_buy_list.user_id', '=', 'nuser.id')
            ->join('nlsg_special_price as nsp',
                'nlsg_mall_group_buy_list.group_buy_id', '=', 'nsp.id')
            ->where('nmo.status', '>', 1)
            ->select(['nlsg_mall_group_buy_list.id',
                'nlsg_mall_group_buy_list.group_name',
                'nlsg_mall_group_buy_list.order_id',
                'nlsg_mall_group_buy_list.created_at',
                'nlsg_mall_group_buy_list.user_id',
                'nlsg_mall_group_buy_list.is_success',
                'nlsg_mall_group_buy_list.success_at',
                'nlsg_mall_group_buy_list.begin_at',
                'nlsg_mall_group_buy_list.end_at',
                DB::raw('UNIX_TIMESTAMP(nlsg_mall_group_buy_list.end_at) as end_timestamp'),
                DB::raw('UNIX_TIMESTAMP(nlsg_mall_group_buy_list.begin_at) as begin_timestamp'),
                DB::raw('UNIX_TIMESTAMP(nlsg_mall_group_buy_list.created_at) as created_timestamp'),
                DB::raw('(case when nlsg_mall_group_buy_list.user_id = ' . $user_id . ' then 1 else 0 end) as is_self'),
                'nuser.nickname', 'nuser.headimg',
                'nsp.group_num', 'nlsg_mall_group_buy_list.group_key'])
            ->with(['teamOrderCount'])
            ->orderBy('is_success', 'asc')
            ->orderBy('nlsg_mall_group_buy_list.id', 'asc');

        if ($flag == 1) {
            $query->limit(2);
        }

        $team_list = $query->get();

        foreach ($team_list as $k => $v) {
            $team_list[$k]->order_count = $v->teamOrderCount->counts;
            //临时 待优化   过滤掉用户已经参加的队伍
            if ($user_id) {
                $check_show = DB::table('nlsg_mall_group_buy_list as g')
                    ->join('nlsg_mall_order as o', 'o.id', '=', 'g.order_id')
                    ->where('g.group_key', '=', $v->group_key)
                    ->where('g.user_id', '=', $user_id)
                    ->where('g.is_fail', '=', 0)
                    ->where('o.is_stop', '=', 0)
                    ->where('o.status', '>', 1)
                    ->select(['g.id'])
                    ->first();
                if ($check_show) {
                    unset($team_list[$k]);
                    continue;
                }
            }
//            $v->order_count = $v->teamOrderCount->counts ?? 0;
            unset($team_list[$k]->teamOrderCount);
        }

        $res = [];
        foreach ($team_list as $v) {
            $res[] = $v;
        }

        return $res;
    }

    //用户拼团订单列表
    public function userOrderList($params, $user, $flag = false)
    {
        $now = time();
        $now_date = date('Y-m-d H:i:s', $now);
        $user_id = $user['id'];
        $params['page'] = $params['page'] ?? 1;
        $params['size'] = $params['size'] ?? 10;
        //库数据:订单状态 1待付款  10待发货 20待收货 30已完成
        //全部0,拼团中95,待付款1,代发货10,待签收20,已完成30,已取消99
        //展示数据:订单编号,状态,商品列表,价格,数量,取消时间,金额

        $query = self::from('nlsg_mall_order as nmo')
            ->join('nlsg_mall_group_buy_list as gbl', 'nmo.id', '=', 'gbl.order_id')
            ->where('nmo.user_id', '=', $user_id)
            ->where('nmo.order_type', '=', 3)
            ->where('nmo.is_del', '=', 0)
            ->limit($params['size'])
            ->offset(($params['page'] - 1) * $params['size']);

        if (!empty($params['ordernum'])) {
            $query->where('nmo.ordernum', '=', $params['ordernum']);
        }

        //(全部0,待付款1,待发货10,待签收20,已完成30,已取消99,拼团中95)
        switch (intval($params['status'] ?? 0)) {
            case 1:
                $query->where('nmo.status', '=', 1)
                    ->where('nmo.is_stop', '=', 0)
                    ->whereRaw('(`status` = 1 AND dead_time >= "' . $now_date . '" ) ');
                break;
            case 10:
                $query->where('nmo.status', '=', 10)
                    ->where('nmo.is_stop', '=', 0)
                    ->where('gbl.is_success', '=', 1);
                break;
            case 20:
                $query->where('nmo.status', '=', 20)
                    ->where('nmo.is_stop', '=', 0)
                    ->where('gbl.is_success', '=', 1);
                break;
            case 30:
                $query->where('nmo.status', '=', 30)
                    ->where('nmo.is_stop', '=', 0)
                    ->where('gbl.is_success', '=', 1);
                break;
            case 95:
                $query->where('nmo.status', '=', 10)
                    ->where('nmo.is_stop', '=', 0)
                    ->where('gbl.is_success', '=', 0);
                break;
            case 99:
                $query->where(function ($q) use ($now_date) {
                    $q->where('nmo.is_stop', '=', 1)
                        ->orwhereRaw('(`status` = 1 AND dead_time < "' .
                            $now_date . '" ) ');
                });
                break;
        }

        $field = [
            'nmo.id', 'nmo.ordernum', 'nmo.price', 'nmo.dead_time', 'gbl.group_key',
            DB::raw('unix_timestamp(nmo.dead_time) as dead_timestamp'),
            DB::raw('(case when nmo.is_stop = 1
                then 99 when nmo.`status` = 1 then 1
                when is_success = 0 then 95  when ( nmo.is_stop = 0 AND nmo.`status` = 1 and dead_time < "' .
                $now_date . '" ) then 99 ELSE nmo.`status` END) `status`'),
            'nmo.created_at', 'nmo.pay_price', 'nmo.price', 'nmo.post_type', 'nmo.pay_type', 'nmo.normal_cut'
        ];
        $with = ['orderDetails', 'orderDetails.goodsInfo', 'groupList' => function ($q) use ($user_id) {
            $q->orderBy('is_captain', 'desc')
                ->orderByRaw('FIELD(user_id,' . $user_id . ') desc');
        }];
        //$with = ['orderDetails', 'orderDetails.goodsInfo', 'groupList'];
        $with[] = 'groupList.userInfo';
        $with[] = 'orderChild';
        $with[] = 'orderChild.expressInfoForList';
        $with[] = 'groupListInfo';
        $with[] = 'groupListInfo.spInfo';

        if ($flag) {
            $field[] = 'address_history';
            $field[] = 'cost_price';
            $field[] = 'freight';
            $field[] = 'vip_cut';
            $field[] = 'coupon_money';
            $field[] = 'special_price_cut';
            $field[] = 'price';
            $field[] = 'pay_time';
            $field[] = 'messages';
            $field[] = 'post_type';
            $field[] = 'bill_type';
            $field[] = 'bill_title';
            $field[] = 'bill_number';
            $field[] = 'bill_format';
            $with[] = 'orderChild.expressInfo';
        }

//        $query->whereRaw('(case when `status` = 1 AND dead_time < "' .
//            $now_date . '" then FALSE ELSE TRUE END) ');

        $query->orderBy('id', 'desc');

//$this->getSqlBegin();
        $list = $query->with($with)->select($field)->get();
//$this->getSql();

        foreach ($list as $k => $v) {
            $v->goods_count = 0;
            foreach ($v->orderDetails as $vv) {
                $v->goods_count += $vv->num;
                $vv->sku_history = json_decode($vv->sku_history);
            }
            $v->address_history = json_decode($v->address_history);

            $headimg = [];

            $headimg_count = $v->groupListInfo->spInfo['group_num'] ?? 2;

            foreach ($v->groupList as $glv) {
                if (count($headimg) < $headimg_count) {
                    $headimg[] = $glv->userInfo->headimg ?? '';
                } else {
                    break;
                }
            }

            $v->headimg_list = $headimg;
            unset($list[$k]->groupList);

            $temp_express_list = [];
            foreach ($v->orderChild as $ocv) {
                $temp_express = $ocv->expressInfoForList;
                $temp_express_list[] = $temp_express;
            }
            $v->express_list = $temp_express_list;
            if ($v->status == 95) {
                $v->dead_time = $v->groupListInfo->end_at;
                $v->dead_timestamp = strtotime($v->groupListInfo->end_at);
            }
        }

        return $list;
    }

    public function orderDetails()
    {
        return $this->hasMany('App\Models\MallOrderDetails', 'order_id', 'id')
            ->select([
                'status', 'goods_id', 'num', 'id as details_id',
                'order_id', 'sku_history', 'comment_id', 'sku_number'
            ]);
    }

    public function groupList()
    {
        return $this->hasMany('App\Models\MallGroupBuyList', 'group_key', 'group_key')
            ->select(['group_key', 'user_id', 'is_captain']);
    }

    public function orderChild()
    {
        return $this->hasMany('App\Models\MallOrderChild', 'order_id', 'id')
            ->groupBy('order_id')
            ->select([
                'status', 'order_id',
                'express_info_id',
                DB::raw('GROUP_CONCAT(order_detail_id) order_detail_id')
            ]);
    }

    public function groupListInfo()
    {
        return $this->hasOne('App\Models\MallGroupBuyList', 'order_id', 'id')
            ->select([
                'id', 'group_buy_id', 'group_name', 'group_key', 'order_id', 'is_captain', 'is_success', 'success_at',
                'is_fail', 'fail_at', 'begin_at', 'end_at'
            ]);
    }

    //订单详情
    public function orderInfo($user_id, $ordernum)
    {
        if (empty($ordernum)) {
            return ['code' => false, 'msg' => '参数错误'];
        }

        $getData = $this->userOrderList(
            ['ordernum' => $ordernum],
            ['id' => $user_id],
            true
        );

        if ($getData->isEmpty()) {
            return ['code' => false, 'msg' => '订单不存在'];
        }

        $data = $getData[0]->toArray();

        //如果已经支付,倒计时为成团倒计时
        if ($data['status'] > 1) {
            $data['dead_time'] = $data['group_list_info']['end_at'];
            $data['dead_timestamp'] = strtotime($data['dead_time']);
        }

        foreach ($data['order_details'] as &$odv) {
            $temp_odv = [];
            $temp_odv['goods_id'] = $odv['goods_id'];
            $temp_odv['num'] = $odv['num'];
            $temp_odv['sku_value'] = $odv['sku_history']->sku_value;
            $temp_odv['price'] = $odv['sku_history']->actual_price;
            $temp_odv['original_price'] = $odv['sku_history']->original_price;
            $temp_odv['name'] = $odv['goods_info']['name'];
            $temp_odv['picture'] = $odv['goods_info']['picture'];
            $temp_odv['subtitle'] = $odv['goods_info']['subtitle'];
            $temp_odv['details_id'] = $odv['details_id'];
            $temp_odv['order_details_id'] = $odv['details_id'];
            $temp_odv['order_detail_id'] = $odv['details_id'];
            $temp_odv['comment_id'] = $odv['comment_id'];
            $temp_odv['sku_number'] = $odv['sku_number'];
            $odv = $temp_odv;
        }

        foreach ($data['order_child'] as &$v1) {
            $v1['order_detail_id'] = explode(',', $v1['order_detail_id']);

            if (isset($v1['express_info']['history'])) {
                $v1['express_info']['history'] = json_decode($v1['express_info']['history']);
            }

            $v1['order_details'] = [];
        }

        $price_info = [];
        $price_info['cost_price'] = $data['cost_price'];
        $price_info['freight'] = $data['freight'];
        $price_info['vip_cut'] = $data['vip_cut'];
        $price_info['coupon_money'] = $data['coupon_money'];
        $price_info['special_price_cut'] = $data['special_price_cut'];
        $price_info['pay_time'] = $data['pay_time'];
        $price_info['pay_type'] = $data['pay_type'];
        $price_info['price'] = $data['price'];
        $price_info['pay_price'] = $data['pay_price'];

        $bill_info = [];
        $bill_info['bill_type'] = $data['bill_type'];
        $bill_info['bill_title'] = $data['bill_title'];
        $bill_info['bill_number'] = $data['bill_number'];
        $bill_info['bill_format'] = $data['bill_format'];

        $data['price_info'] = $price_info;
        $data['bill_info'] = $bill_info;

        //拼团队员列表
        $data['team_user_list'] = $this->getTeamUserListbyOrderId($data['id']);

        if (empty($data['order_child'])) {
            $temp_data = [];
            $temp_data['status'] = 0;
            $temp_data['order_id'] = $data['id'];
            $temp_data['express_info_id'] = 0;
            $temp_data['express_num'] = '';
            $temp_data['order_detail_id'] = [];
            $temp_data['order_details'] = $data['order_details'];
            $data['order_child'] = [$temp_data];
        } else {
            foreach ($data['order_child'] as $k => &$v) {
                foreach ($data['order_details'] as $vv) {
                    if (in_array($vv['details_id'], $v['order_detail_id'])) {
                        array_push($v['order_details'], $vv);
                    }
                }
            }
        }

        $about_order = [];
        //订单编号,下单时间,支付方式,支付时间,发票信息,备注信息
        $about_order[] = ['key' => '订单编号', 'value' => $data['ordernum']];
        $about_order[] = ['key' => '下单时间', 'value' => $data['created_at']];
        if ($data['status'] > 1) {
            $about_order[] = ['key' => '支付方式', 'value' => MallOrder::orderParamsName(1, $data['pay_type'])];
            $about_order[] = ['key' => '支付时间', 'value' => $data['pay_time']];
        }
        $about_order[] = ['key' => '发票信息', 'value' => MallOrder::orderParamsName(2, $data['bill_type'])];
        $about_order[] = ['key' => '备注信息', 'value' => $data['messages']];
        //商品总额,权益立减,活动立减,运费,实付金额

        $price_list_new = [
            ['key' => '商品总额', 'value' => $data['cost_price']],
            ['key' => '优惠金额', 'value' => $data['normal_cut']],
            ['key' => '权益立减', 'value' => $data['vip_cut']],
            ['key' => '活动立减', 'value' => $data['special_price_cut']],
            ['key' => '运费', 'value' => $data['freight']],
            ['key' => '优惠券总额', 'value' => $data['coupon_money']],
        ];

        if ($data['status'] == 1) {
            $price_list_new[] = ['key' => '应付金额', 'value' => $data['price']];
        } else {
            $price_list_new[] = ['key' => '实付金额', 'value' => $data['pay_price']];
        }

//        foreach ($price_list_new as $new_k => $new_v) {
//            if ($new_v['value'] == 0 && !in_array($new_v['key'], ['应付金额', '实付金额'])) {
//                unset($price_list_new[$new_k]);
//            }
//            if (in_array($new_v['key'], ['权益立减', '活动立减', '优惠券总额'])) {
//                $new_v['value'] = '-¥' . $new_v['value'];
//            }
//            if (in_array($new_v['key'], ['运费'])) {
//                $new_v['value'] = '+¥' . $new_v['value'];
//            }
//        }

        foreach ($price_list_new as $new_k => $new_v) {
            if ($new_v['value'] == 0 && !in_array($new_v['key'], ['应付金额', '实付金额'])) {
                unset($price_list_new[$new_k]);
            }
        }

        foreach ($price_list_new as &$new_v) {
            if (in_array($new_v['key'], ['权益立减', '活动立减', '优惠券总额', '优惠金额'])) {
                $new_v['value'] = '- ¥' . $new_v['value'];
            } elseif (in_array($new_v['key'], ['运费'])) {
                $new_v['value'] = '+ ¥' . $new_v['value'];
            } else {
                $new_v['value'] = '¥' . $new_v['value'];
            }
        }

        $price_list_new = array_values($price_list_new);

        $data['about_order'] = $about_order;
        $data['about_price'] = $price_list_new;

        $temp_o_c = [];
        foreach ($data['order_child'] as $doc) {
            if (!empty($doc['order_details'])) {

                if (!empty($doc['express_info'])) {
                    $doc['express_info']['express_phone'] = ExpressCompany::onlyGetName(
                        $doc['express_info']['express_id'], 3
                    );
                    $doc['express_info']['history']->express_phone = $doc['express_info']['express_phone'];
                } else {
                    $doc['express_info'] = new class {
                    };
                }

                $temp_o_c[] = $doc;
            }
        }
        $data['order_child'] = $temp_o_c;

        unset(
            $data['cost_price'], $data['freight'],
            $data['vip_cut'], $data['price'],
            $data['coupon_money'], $data['special_price_cut'],
            $data['pay_time'],
            $data['bill_type'], $data['bill_title'],
            $data['bill_number'], $data['bill_format']
        );

        return $data;
    }

    public function getTeamUserListbyOrderId($order_id)
    {

        $get_info = MallGroupBuyList::where('order_id', '=', $order_id)
            ->first();

        $group_key = $get_info->group_key;
        $user_id = $get_info->user_id;

        $list = DB::table('nlsg_mall_group_buy_list as gbl')
            ->leftJoin('nlsg_user as nuser', 'gbl.user_id', '=', 'nuser.id')
            ->leftJoin('nlsg_mall_order as nmo', 'gbl.order_id', '=', 'nmo.id')
            ->where('gbl.group_key', '=', $group_key)
//                ->where('nmo.is_stop', '=', 0)
//                ->where('nmo.is_del', '=', 0)
            ->orderBy('gbl.is_captain', 'desc')
            ->orderByRaw('FIELD(gbl.user_id,' . $user_id . ')', 'desc')
            ->orderBy('gbl.id', 'asc')
            ->select(['gbl.id', 'gbl.user_id', 'nuser.nickname',
                'nuser.headimg', 'gbl.is_captain'])
            ->get();

        return $list;
    }

    public function gbScrollbar($group_buy_id, $size = 10)
    {
        if (empty($group_buy_id)) {
            return ['code' => false, 'msg' => '参数错误'];
        }

        $list = DB::table('nlsg_mall_group_buy_list as gbl')
            ->join('nlsg_user as nuser', 'gbl.user_id', '=', 'nuser.id')
            ->join('nlsg_special_price as sp', 'gbl.group_buy_id', '=', 'sp.id')
            ->where('sp.group_name', '=', $group_buy_id)
            ->select(['nuser.id as user_id', 'nuser.headimg', 'nuser.nickname',
                'gbl.created_at', 'gbl.is_captain', 'gbl.is_success'])
            ->limit($size)
            ->orderBy('gbl.updated_at', 'desc')
            ->orderBy('gbl.id', 'desc')
            ->get();

        foreach ($list as $v) {
            if ($v->is_captain == 1) {
                $v->explain = $v->nickname . '发起拼团';
            } else {
                if ($v->is_success == 1) {
                    $v->explain = $v->nickname . '拼团成功';
                } else {
                    $v->explain = $v->nickname . '参加拼团';
                }
            }
        }
        return $list;
    }

    //定时清理未成功订单
    public static function clear()
    {
        //查询过期订单
        $now = time();
        $now_date = date('Y-m-d H:i:59', $now);

        $gbl_list = DB::table('nlsg_mall_group_buy_list')
            ->where('is_captain', '=', 1)
            ->where('is_success', '=', 0)
            ->where('is_fail', '=', 0)
            ->where('end_at', '<=', $now_date)
            ->select(['id', 'group_key', 'order_id'])
            ->get();

        if ($gbl_list->isEmpty()) {
            return true;
        }
        $gbl_list = $gbl_list->toArray();
        $gbl_list = array_column($gbl_list, 'group_key');

        MallGroupBuyList::whereIn('group_key', $gbl_list)
            ->update([
                'is_fail' => 1,
                'fail_at' => $now_date,
            ]);

        $list = MallGroupBuyList::whereIn('group_key', $gbl_list)
            ->select(['id', 'order_id'])
            ->get();

        foreach ($list as $v) {
            DB::beginTransaction();

            //group_buy_list部分
            //$v->is_fail = 1;
            //$v->fail_at = $now_date;
            //$v_res = $v->save();
            //if ($v_res === false) {
            //    DB::rollBack();
            //    continue;
            //}
            //mall_order
            $order_info = MallOrder::whereId($v->order_id)->where('is_stop', '=', 0)->first();
            if (empty($order_info)) {
                DB::rollBack();
                continue;
            }
            $order_info->is_stop = 1;
            $order_info->stop_by = 0;
            $order_info->stop_at = $now_date;
            $order_info->gp_status = 3;
            $order_res = $order_info->save();
            if ($order_res === false) {
                DB::rollBack();
                continue;
            }

            //归还库存
            $order_detail = MallOrderDetails::where('order_id', '=', $order_info->id)
                ->select(['sku_number', 'num'])
                ->first();
            if (empty($order_detail)) {
                DB::rollBack();
                continue;
            }

            $stock_res = MallSku::where('sku_number', '=', $order_detail->sku_number)->increment('stock', $order_detail->num);

            if ($order_info->status > 1) {
                //已经支付 需要写入 refund_record表
                $refund_data['service_num'] = MallOrder::createOrderNumber($order_info->user_id, 2);
                $refund_data['order_id'] = $order_info->id;
                $refund_data['order_detail_id'] = 0;
                $refund_data['type'] = 4;
                $refund_data['pay_type'] = $order_info->pay_type;
                $refund_data['refe_price'] = $order_info->pay_price;
                $refund_data['price'] = $order_info->pay_price;
                $refund_data['status'] = 40;
                $refund_data['user_id'] = $order_info->user_id;
                $refund_data['created_at'] = $now_date;
                $refund_data['updated_at'] = $now_date;
                $refund_data['run_refund'] = 1;
                $refund_res = DB::table('nlsg_mall_refund_record')->insert($refund_data);
                if ($refund_res === false) {
                    DB::rollBack();
                    continue;
                }
            }

            DB::commit();
            return true;
        }


    }
}
