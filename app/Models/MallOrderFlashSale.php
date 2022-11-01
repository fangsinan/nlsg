<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;

use Illuminate\Support\Facades\DB;

/**
 * Description of MallOrderFlashSale
 *
 * @author wangxh
 */
class MallOrderFlashSale extends Base
{

    protected $table = 'nlsg_mall_order';

    public function prepareCreateFlashSaleOrder($params, $user)
    {
        return $this->createFlashSaleOrderTool($params, $user);
    }

    public function createFlashSaleOrder($params, $user)
    {
        $now = time();
        $now_date = date('Y-m-d H:i:s', $now);
        $data = $this->createFlashSaleOrderTool($params, $user, true);
        $dead_time = ConfigModel::getData(19);
        $dead_time = date('Y-m-d H:i:03', ($now + ($dead_time + 1) * 60));

        if (!in_array($params['pay_type'], [1, 2, 3])) {
            return ['code' => false, 'msg' => '请选择支付方式', 'ps' => 'pay_type error'];
        }

        if (!($data['can_sub'] ?? false)) {
            return ['code' => false, 'msg' => '参数错误', 'ps' => 'can_sub'];
        }

        $order_data = [];
        $order_data['ordernum'] = MallOrder::createOrderNumber($user['id'], 1);
        $order_data['user_id'] = $user['id'];
        $order_data['order_type'] = 2;
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
        $order_data['sp_id'] = $data['sku_list']['flash_sale_id'];
        $order_data['dead_time'] = $dead_time;
        $order_data['pay_type'] = $params['pay_type'];
        $order_data['live_id'] = $params['live_id'];
        $order_data['live_info_id'] = $params['live_info_id'];

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
        $details_data['sp_id'] = $data['sku_list']['flash_sale_id'];
        if ($data['sku_list']['inviter']) {
            $details_data['inviter_history'] = json_encode($data['sku_list']['inviter_info']);
        } else {
            $details_data['inviter_history'] = '';
        }
        $temp_sku_history = [];
        $temp_sku_history['actual_num'] = $data['sku_list']['num'];
        $temp_sku_history['actual_price'] = $data['sku_list']['flash_sale_price'];
        $temp_sku_history['original_price'] = $data['sku_list']['original_price'];
        $temp_sku_history['sku_value'] = $data['sku_list']['sku_value'];
        $temp_sku_history['stock'] = $data['sku_list']['stock'];
        $details_data['sku_history'] = json_encode($temp_sku_history);
        $details_data['special_price_type'] = 2;
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
            ->where('id', '=', $data['sku_list']['flash_sale_id'])
            ->increment('use_stock');
        if (!$sp_res) {
            DB::rollBack();
            return ['code' => false, 'msg' => '订单提交失败,请重试.',
                'ps' => 'sp error'];
        }

        //********************优惠券和免邮券部分********************
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

        DB::commit();

        return ['order_id' => $order_res, 'ordernum' => $order_data['ordernum']];
    }

    public function createFlashSaleOrderTool($params, $user, $check_sub = false)
    {
        $now_date = date('Y-m-d H:i:s');
        $can_sub = true;
        //检查参数逻辑
        $check_params = $this->checkFlashSaleParams($params);
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

//        $priceTools = new GetPriceTools();
//        $normal_price = $priceTools->getGoodsPrice(
//            $sku_list, $user['level'], $user['id'], $user['is_staff'], true
//        );

//        foreach ($normal_price->price_list->sku_price_list as $np_v) {
//            if ($np_v->sku_number == $sku_list['sku_number']) {
//                if ($user['expire_time'] > $now_date && !empty($user['level'])){
//                    switch (intval($user['level'])){
//                        case 2:
//                        case 3:
//                            $sku_list['normal_price'] = $np_v->level_3;
//                            break;
//                        case 4:
//                            $sku_list['normal_price'] = $np_v->level_4;
//                            break;
//                        case 5:
//                            $sku_list['normal_price'] = $np_v->level_5;
//                            break;
//                    }
//                }else{
//                    $sku_list['normal_price'] = $np_v->price;
//                }
//            }
//        }

        //校验商品秒杀状态是否可用
        $check_sku_res = $this->checkSkuCanFlashSale(
            $sku_list['goods_id'], $sku_list['sku_number']
        );

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
            $sku_list['flash_sale_max_num'] = $check_sku_res->flash_sale_max_num;
        }

        //校验用户是否参与过该次秒杀
        $check_user = $this->checkUserCanFlashSale($check_sku_res->id, $user['id']);

        if (($check_user['code'] ?? true) === false) {
            return $check_user;
        }

//        if ($sku_list['num'] > $sku_list['flash_sale_max_num']){
//            $sp_price_num = $sku_list['flash_sale_max_num'];
//            $nm_price_num = $sku_list['num'] - $sku_list['flash_sale_max_num'];
//
//            $all_sp_original_price = '';
//            $all_nm_original_price = '';
//
//            $all_sp_price = '';
//            $all_nm_price = '';
//
//        }else{
//
//        }
        $all_original_price = GetPriceTools::PriceCalc(
            '*', $sku_list['original_price'], $sku_list['num']
        );

        $all_price = GetPriceTools::PriceCalc(
            '*', $sku_list['flash_sale_price'], $sku_list['num']
        );

        $freight_money = ConfigModel::getData(7); //运费
        $coupon_freight = 0; //是否免邮券
        $freight_free_flag = false; //是否免邮
//        if (($user['new_vip']['level'] ?? 0) == 1) {
//            $freight_free_flag = true;
//        }

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
                $temp_freight_money = FreightTemplate::getFreightMoney(
                    $sku_list, $used_address
                );
                $sku_list['freight_money'] = $temp_freight_money['price'];
                $sku_list['add_freight_money'] = $temp_freight_money['add_price'];
            }
            if ($sku_list['add_freight_money'] ?? 0 > $add_freight_money) {
                $add_freight_money = $sku_list['add_freight_money'];
            }
            if (($sku_list['freight_money'] ?? 0) > $freight_money) {
                $freight_money = $sku_list['freight_money'];
            }
        } else {
            $freight_money = 0;
        }

        $order_price = $all_price;

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
            'sp_cut_money' => GetPriceTools::PriceCalc('-', $all_original_price, $all_price),
            'freight_free_flag' => $freight_free_flag,
            'order_price' => $order_price,
        ];

        $price_list_new = [
            ['key' => '商品总额', 'value' => $all_original_price],
            ['key' => '运费', 'value' => $freight_money],
//            ['key' => '活动立减', 'value' => GetPriceTools::PriceCalc('-', 0, $price_list['sp_cut_money'])],
            ['key' => '活动立减', 'value' => $price_list['sp_cut_money']],
        ];

        foreach ($price_list_new as $new_k => $new_v) {
            if (intval($new_v['value']) == 0 && !in_array($new_v['key'], ['应付金额', '实付金额'])) {
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
        $sku_list_show['price'] = $sku_list['flash_sale_price'];
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
            'coupon_list' => ['coupon_freight' => $coupon_freight_list ?? []],
            'used_address' => $used_address,
            'from_cart' => $params['from_cart'] ?? 2,
            'token' => CacheTools::orderToken($user['id'], 2, 'set'),
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

    public function checkSkuCanFlashSale($goods_id, $sku)
    {
        $now_date = date('Y-m-d H:i:s');
        $spModel = new SpecialPriceModel();
        $sp_data = $spModel->getSpData($goods_id, 1);

        if ($sp_data->isEmpty()) {
            return ['code' => false, 'msg' => '活动不存在'];
        }
        $res = [];
        foreach ($sp_data as $k => $v) {
            if ($v->type == 2) {
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

    public function checkUserCanFlashSale($flash_sale_id, $uid)
    {
        $check = MallOrder::where('user_id', '=', $uid)
            ->where('sp_id', '=', $flash_sale_id)
            ->where('status', '>', 1)
            ->where('is_stop', '=', 0)
            ->first();
        if (!empty($check)) {
            return ['code' => false, 'msg' => '无法参加'];
        } else {
            return true;
        }
    }

    public function checkFlashSaleParams(&$params)
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

        $params['coupon_freight_id'] = intval($params['coupon_freight_id'] ?? 0);

        $params['address_id'] = intval($params['address_id'] ?? 0);

        if ($params['post_type'] == 2 && $params['address_id'] == 0) {
            return ['code' => false, 'msg' => '参数错误',
                'ps' => '如果自提,需传自提地址address_id'];
        }
    }

    public function flashSalePayFail($order_id, $user_id)
    {
        $check = self::where('user_id', '=', $user_id)
            ->where('order_type', '=', 2)
            ->where('status', '=', 1)
            ->find($order_id);
        if ($check) {
            $order_details = DB::table('nlsg_mall_order_detail')
                ->where('order_id', '=', $order_id)
                ->first();

            DB::beginTransaction();

            //********************订单部分部分********************
            $check->is_del = 1;
            $check->del_at = date('Y-m-d H:i:s');
            $order_res = $check->save();
            if (!$order_res) {
                DB::rollBack();
                return ['code' => false, 'msg' => '失败', 'ps' => '订单状态修改错误'];
            }

            //********************mall_sku库存部分********************
            $sku_res = MallSku::where('sku_number', '=', $order_details->sku_number)
                ->increment('stock', $order_details->num);
            if (!$sku_res) {
                DB::rollBack();
                return ['code' => false, 'msg' => '订单提交失败,请重试.',
                    'ps' => 'sku error'];
            }

            //********************special_price部分********************
            $sp_res = DB::table('nlsg_special_price')
                ->where('id', '=', $check['sp_id'])
                ->decrement('use_stock');
            if (!$sp_res) {
                DB::rollBack();
                return ['code' => false, 'msg' => '订单提交失败,请重试.',
                    'ps' => 'sp error'];
            }

            //********************免邮券部分********************
            if ($check->coupon_freight_id) {
                $coupon_temp = Coupon::find($check->coupon_freight_id);
                $coupon_temp->status = 1;
                $coupon_temp->order_id = 0;
                $coupon_temp->used_time = null;
                $coupon_res = $coupon_temp->save();
                if (!$coupon_res) {
                    DB::rollBack();
                    return ['code' => false, 'msg' => '订单提交失败,请重试.',
                        'ps' => 'coupon freight error'];
                }
            }
            DB::commit();
            return ['code' => true, 'msg' => '成功'];
        } else {
            return ['code' => false, 'msg' => '订单状态错误'];
        }
    }

    //清理超时的秒杀订单
    public static function clear()
    {
        $now = time();
        $time_line = date('Y-m-d H:i:s', $now - 60);
        $now_date = date('Y-m-d H:i:s', $now);


        $list = DB::table('nlsg_mall_order')
            ->where('order_type', '=', 2)
            ->where('status', '=', 1)
            ->where('is_stop', '=', 0)
            ->where('dead_time', '<=', $time_line)
            ->select(['id', 'sp_id'])
            ->get();

        if ($list->isEmpty()) {
            return true;
        }

        $list = $list->toArray();

        $id_list = array_column($list, 'id');
        $sp_id_list = array_column($list, 'sp_id');
        $sp_id_list = array_count_values($sp_id_list);


        MallOrder::whereIn('id', $id_list)
            ->update(['is_stop' => 1, 'stop_by' => 0, 'stop_at' => $now_date, 'is_del' => 1, 'del_at' => $now_date]);


        foreach ($sp_id_list as $k => $v) {
            SpecialPriceModel::whereId($k)
                ->decrement('use_stock', $v);
        }
        return true;


//        DB::table('nlsg_mall_order')
//            ->where('order_type', '=', 2)
//            ->where('status', '=', 1)
//            ->where('is_stop', '=', 0)
//            ->where('dead_time', '<=', $time_line)
//            ->update(['is_stop' => 1, 'stop_by' => 0, 'stop_at' => $now_date, 'is_del' => 1, 'del_at' => $now_date]);


    }
}
