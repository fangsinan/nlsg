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

    public function createGroupBuyOrder($params, $user) {
        $now = time();
        $now_date = date('Y-m-d H:i:s', $now);
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
        $gl_data['order_id'] = $order_res;
        $gl_data['created_at'] = $now_date;
        $gl_data['updated_at'] = $now_date;
        $gl_data['user_id'] = $user['id'];
        if ($params['buy_type'] == 1) {
            $gl_data['is_captain'] = 1;
            $gl_data['group_key'] = $this->createGroupBuyKey($user['id']);
            //如果是开团 需要指定开团有效期
            $gl_data['begin_at'] = $now_date;
            $gl_data['end_at'] = date('Y-m-d H:i:59',
                    ($now + $data['sku_list']['group_life'] * 60 + 60)
            );
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

    public function createGroupBuyKey($uid) {
        $now = time();
        $d = date('ymd', $now);
        $u = str_pad($uid, 8, 0, STR_PAD_LEFT);
        $s = $now - strtotime(date('y-m-d', $now));
        return $d . $u . str_pad($s, 5, 0, STR_PAD_LEFT) . rand(100, 999);
    }

    public function createGroupBuyOrderTool($params, $user, $check_sub = false) {
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

        //秒杀订单只能是一个商品
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

        $order_price = 0;
        $order_price = GetPriceTools::PriceCalc('-', $all_price, $coupon_money);
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

        $sku_list_show = [];
        $sku_list_show['goods_id'] = $sku_list['goods_id'];
        $sku_list_show['name'] = $sku_list['name'];
        $sku_list_show['subtitle'] = $sku_list['subtitle'];
        $sku_list_show['picture'] = $sku_list['picture'];
        $sku_list_show['sku_value_list'] = $sku_list['sku_value'];
        $sku_list_show['num'] = $sku_list['num'];
        $sku_list_show['original_price'] = $sku_list['original_price'];
        $sku_list_show['price'] = $sku_list['group_buy_price'];

        $res = [
            'user' => $user,
            'sku_list' => $sku_list_show,
            'price_list' => $price_list,
            'address_list' => $address_list,
            'coupon_list' => $coupon_list ?? [],
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

    public function checkGroupBuyParams(&$params, $user_id) {
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
