<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class MallOrder extends Base {

    protected $table = 'nlsg_mall_order';

    //检擦用户在规定时间内参加过秒杀活动
    public function getUserSecKillOrder($params) {
        if (empty($params['user_id'])) {
            return [];
        }

        $query = DB::table('nlsg_mall_order as nmo')
                ->leftJoin('nlsg_mall_order_detail as nmod',
                        'nmo.id', '=', 'nmod.order_id')
                ->where('nmod.user_id', '=', $params['user_id']);

        if ($params['begin_time'] ?? false) {
            $query->where('nmo.created_at', '>=', $params['begin_time']);
        }
        if ($params['end_time'] ?? false) {
            $query->where('nmo.created_at', '<=', $params['end_time']);
        }

        //nmo.status > 0 避免多次下未支付订单
        $list = $query
                ->whereRaw('FIND_IN_SET(2,nmod.special_price_type)')
                ->where('nmo.status', '>', 0)
                ->where('nmo.is_stop', '=', 0)
                ->select(['nmod.sku_number'])
                ->get();


        $sku_list = [];
        foreach ($list as $v) {
            $sku_list[] = $v->sku_number;
        }
        return array_unique($sku_list);
    }

    /**
     * 获取普通优惠列表(折扣)
     * @param array $goods_id
     * @param array $sku_number
     */
    public function getSkuSP($goods_id, $sku_number) {
        $goods_id = array_unique($goods_id);
        $sku_number = array_unique($sku_number);
        $now_date = date('Y-m-d H:i:s', time());
        $spModel = new SpecialPriceModel();
        $sp_list = [];
        foreach ($goods_id as $gid) {
            $temp_res = $spModel->getSpData($gid, 1);
            foreach ($temp_res as $trv) {
                if (!in_array($trv->type, [1])) {
                    continue;
                }
                if (!in_array($trv->sku_number, $sku_number)) {
                    continue;
                }
                if ($trv->begin_time > $now_date || $trv->end_time < $now_date) {
                    continue;
                }
                if ($trv->stock > 0 && ($trv->use_stock >= $trv->stock )) {
                    continue;
                }
                $sp_list[] = $trv;
            }
        }
        return $sp_list;
    }

    //获取sku_list,并校验商品信息和推客信息
    public function getOrderSkuList($params, $user_id) {

        if ($params['from_cart'] === 2) {
            $temp = [];
            $temp['cart_id'] = 0;
            $temp['sku_number'] = $params['sku'][0];
            $temp['goods_id'] = $params['goods_id'];
            $temp['num'] = $params['buy_num'];
            $temp['inviter'] = $params['inviter'];
            $sku_list = [$temp];
        } else {
            $sku_list = ShoppingCart::where('user_id', '=', $user_id)
                            ->whereIn('sku_number', $params['sku'])
                            ->select(['id as cart_id', 'sku_number',
                                'goods_id', 'num', 'inviter'])
                            ->get()->toArray();
            if (count($params['sku']) !== count($sku_list)) {
                return ['code' => false, 'msg' => '购物车参数错误'];
            }
        }

        foreach ($sku_list as $k => $v) {
            $check_temp_res = MallSku::checkSkuCanBuy(
                            $v['goods_id'], $v['sku_number']
            );
            if ($check_temp_res === false) {
                return ['code' => false, 'msg' => '商品参数错误'];
            } else {
                $sku_list[$k]['sku_id'] = $check_temp_res->id;
                $sku_list[$k]['original_price'] = $check_temp_res->original_price;
                $sku_list[$k]['price'] = $check_temp_res->price;
                $sku_list[$k]['name'] = $check_temp_res->name;
                $sku_list[$k]['subtitle'] = $check_temp_res->subtitle;
                $sku_list[$k]['freight_id'] = $check_temp_res->freight_id;
                $sku_list[$k]['stock'] = $check_temp_res->stock;
                $sku_list[$k]['weight'] = $check_temp_res->weight;
                $sku_list[$k]['volume'] = $check_temp_res->volume;
                $sku_list[$k]['sku_value'] = $check_temp_res->sku_value;
                $sku_list[$k]['sales_num'] = $check_temp_res->sales_num;
                $sku_list[$k]['picture'] = $check_temp_res->sku_picture ?? $check_temp_res->goods_picture;
            }

            if (empty($v['inviter'])) {
                $sku_list[$k]['inviter_info'] = [];
            } else {

                $temp_inviter_info = User::where('status', '=', 1)
                                ->select(['level', 'expire_time', 'is_staff'])
                                ->find($v['inviter'])->toArray();

                $sku_list[$k]['inviter_info'] = $temp_inviter_info;

                if ($temp_inviter_info['is_staff'] == 0) {
                    //如果不是内部员工,则校验推客有效期
                    if ($temp_inviter_info['level'] > 0) {
                        if ($temp_inviter_info['expire_time'] < date('Y-m-d H:i:s')) {
                            $sku_list[$k]['inviter_info'] = [];
                            $sku_list[$k]['inviter'] = 0;
                        }
                    }
                }
            }
        }

        return $sku_list;
    }

    public function skuListExplode($sku_list) {
        $temp_list = [];
        foreach ($sku_list as $v) {
            $temp_num = $v['num'];
            $v['cou_dan'] = 0;
            if ($temp_num > 1) {
                for ($i = 0; $i < $temp_num; $i++) {
                    $v['num'] = 1;
                    $temp_list[] = $v;
                }
            } else {
                $temp_list[] = $v;
            }
        }
        return $temp_list;
    }

    /**
     * 生成订单编号
     * @param type $uid 用户id
     * @param type $type 1:普通订单
     */
    public static function createOrderNumber($uid, $type) {
        $now = time();
        $d = date('ymd', $now);
        $u = str_pad($uid, 8, 0, STR_PAD_LEFT);
        $s = $now - strtotime(date('y-m-d', $now));
        return $d . $u . str_pad($s, 5, 0, STR_PAD_LEFT) . rand(10, 99) . $type;
    }

    //检查下单参数是否正确
    public function checkParams(&$params) {

        if (empty($params['sku'])) {
            return ['code' => fasle, 'msg' => '参数错误', 'ps' => 'sku'];
        }

        if (!is_array($params['sku'])) {
            $params['sku'] = explode(',', $params['sku']);
        }

        if (!in_array($params['from_cart'], [1, 2])) {
            return ['code' => false, 'msg' => '参数错误', 'ps' => 'from_cart=1,0'];
        }

        if (!in_array($params['os_type'], [1, 2, 3])) {
            return ['code' => false, 'msg' => '参数错误', 'ps' => 'os_type=1,2,3'];
        }

        if ($params['from_cart'] == 2) {
            if (count($params['sku']) !== 1) {
                return ['code' => false, 'msg' => '参数错误', 'ps' => 'sku数量有误'];
            }
            if (empty($params['goods_id'] ?? 0)) {
                return ['code' => false, 'msg' => '参数错误', 'ps' => 'goods_id错误'];
            }
            if ($params['buy_num'] < 1) {
                return ['code' => false, 'msg' => '参数错误', 'ps' => '购买数量有误'];
            }
        }

        if (!in_array($params['post_type'], [1, 2])) {
            return ['code' => false, 'msg' => '参数错误', 'ps' => 'post_type=1,2'];
        }

        $params['coupon_goods_id'] = intval($params['coupon_goods_id'] ?? 0);

        $params['coupon_freight_id'] = intval($params['coupon_freight_id'] ?? 0);

        $params['address_id'] = intval($params['address_id'] ?? 0);

        if ($params['post_type'] == 2 && $params['address_id'] == 0) {
            return ['code' => false, 'msg' => '参数错误',
                'ps' => '如果自提,需传自提地址address_id'];
        }
    }

    //普通订单  --预下单
    public function prepareCreateOrder($params, $user) {
        $sku_list = $this->createOrderTool($params, $user);
        return $sku_list;
    }

    //普通订单  --下单
    public function createOrder($params, $user) {
        $now = time();
        $now_date = date('Y-m-d H:i:s', $now);
        $data = $this->createOrderTool($params, $user, true);

        if (!($data['can_sub'] ?? false)) {
            return ['code' => false, 'msg' => '参数错误', 'ps' => 'can_sub'];
        }

        $order_data = [];
        $order_data['ordernum'] = MallOrder::createOrderNumber($user['id'], 1);
        $order_data['user_id'] = $user['id'];
        $order_data['order_type'] = 1;
        $order_data['status'] = 1;
        $order_data['cost_price'] = $data['price_list']['all_original_price'];
        $order_data['freight'] = $data['price_list']['freight_money'];
        $order_data['vip_cut'] = $data['price_list']['vip_cut_money'];
        $order_data['coupon_freight_id'] = $params['coupon_freight_id'] ?? 0;
        $order_data['coupon_id'] = $params['coupon_goods_id'] ?? 0;
        $order_data['coupon_money'] = $data['price_list']['coupon_money'];
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

        DB::beginTransaction();

        //********************mall_order部分********************
        $order_res = DB::table('nlsg_mall_order')->insertGetId($order_data);
        if (!$order_res) {
            DB::rollBack();
            return ['code' => false, 'msg' => '订单提交失败,请重试.', 'ps' => 'order error'];
        }

        //********************mall_order_detail部分********************
        $details_data = [];
        foreach ($data['sku_list'] as $v) {
            $temp_details_data = [];
            $temp_datails_data['order_id'] = $order_res;
            $temp_datails_data['user_id'] = $user['id'];
            $temp_datails_data['goods_id'] = $v['goods_id'];
            $temp_datails_data['sku_number'] = $v['sku_number'];
            $temp_datails_data['num'] = $v['num'];
            $temp_datails_data['created_at'] = $now_date;
            $temp_datails_data['updated_at'] = $now_date;
            $temp_datails_data['inviter'] = $v['inviter'];
            if ($v['inviter']) {
                $temp_datails_data['inviter_history'] = json_encode($v['inviter_info']);
            } else {
                $temp_datails_data['inviter_history'] = '';
            }
            $temp_sku_history = [];
            $temp_sku_history['actual_num'] = $v['actual_num'];
            $temp_sku_history['actual_price'] = $v['actual_price'];
            $temp_sku_history['original_price'] = $v['original_price'];
            $temp_sku_history['sku_value'] = $v['sku_value'];
            $temp_sku_history['stock'] = $v['stock'];
            $temp_datails_data['sku_history'] = json_encode($temp_sku_history);
            $temp_datails_data['special_price_type'] = $v['sp_type'] ?? '';

            $details_data[] = $temp_datails_data;
        }
        $detail_res = DB::table('nlsg_mall_order_detail')->insert($details_data);
        if (!$detail_res) {
            DB::rollBack();
            return ['code' => false, 'msg' => '订单提交失败,请重试.', 'ps' => 'detail error'];
        }

        //********************mall_sku库存部分********************
        foreach ($data['sku_list'] as $v) {
            $temp_sku = MallSku::find($v['sku_id']);
            $temp_sku->stock = $v['stock'] - $v['actual_num'];
            $temp_sku->sales_num = $v['sales_num'] + $v['actual_num'];
            if ($temp_sku->stock < 0) {
                DB::rollBack();
                return ['code' => false, 'msg' => '订单提交失败,请重试.',
                    'ps' => $v['sku_id'] . 'stock error'];
            }
            $sku_res = $temp_sku->save();
            if (!$sku_res) {
                DB::rollBack();
                return ['code' => false, 'msg' => '订单提交失败,请重试.',
                    'ps' => $v['sku_id'] . 'sku error'];
            }
        }
        //********************mall_goods销量部分********************
        $goods_sale_res = DB::table('nlsg_mall_goods')
                ->where('id', '=', $v['goods_id'])
                ->increment('sales_num', $v['actual_num']);
        if (!$goods_sale_res) {
            DB::rollBack();
            return ['code' => false, 'msg' => '订单提交失败,请重试.',
                'ps' => 'goods sale error'];
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

        //********************购物车********************
        if ($params['from_cart'] == 1) {
            //如果是购物车,删除
            $cart_sku = explode(',', $params['sku']);
            $cart_res = DB::table('nlsg_mall_shopping_cart')
                    ->where('user_id', '=', $user['id'])
                    ->whereIn('sku_number', $cart_sku)
                    ->delete();
            if (!$cart_res) {
                DB::rollBack();
                return ['code' => false, 'msg' => '订单提交失败,请重试.',
                    'ps' => 'cart error'];
            }
        }

        DB::commit();

        return ['order_id' => $order_res, 'ordernum' => $order_data['ordernum']];
    }

    //普通订单 下单工具
    public function createOrderTool($params, $user, $check_sub = false) {
        $now_date = date('Y-m-d H:i:s');
        $can_sub = true;
        //检查参数逻辑
        $check_params = $this->checkParams($params);
        if (($check_params['code'] ?? true) === false) {
            return $check_params;
        }
        //获取并检查sku是否合法
        $sku_list = $this->getOrderSkuList($params, $user['id']);
        if (($sku_list['code'] ?? true) === false) {
            return $sku_list;
        }

        $priceTools = new GetPriceTools();

        //获取非折扣时商品的购买价格和收益
        //level_price 当前用户的购买价格
        //t_money 当前推客的收益 没有0
        foreach ($sku_list as $sl_k => $sl_v) {
            //获取商品各规格的售价和推客收益
            $temp_sl_v = $priceTools->getGoodsPrice(
                    $sl_v, $user['level'], $user['id'], $user['is_staff'], true
            );

            foreach ($temp_sl_v->price_list->sku_price_list as $vv) {
                if ($vv->sku_number == $sl_v['sku_number']) {
                    //实际购买价格(不包括活动)
                    switch (intval($user['level'])) {
                        case 2:
                        case 3:
                            $sku_list[$sl_k]['level_price'] = $vv->level_3;
                            break;
                        case 4:
                            $sku_list[$sl_k]['level_price'] = $vv->level_4;
                            break;
                        case 5:
                            $sku_list[$sl_k]['level_price'] = $vv->level_5;
                            break;
                        default :
                            $sku_list[$sl_k]['level_price'] = $vv->price;
                    }
                }
            }

            foreach ($temp_sl_v->twitter_money_list as $vv) {
                if ($vv['sku_number'] == $sl_v['sku_number']) {
                    //初始推客金额(不是活动单独设定)
                    $sku_list[$sl_k]['t_money'] = 0;
                    if (!empty($sl_v['inviter']) && !empty($sl_v['inviter_info'])) {
                        if ($sl_v['inviter_info']['is_staff'] == 0) {
                            $sku_list[$sl_k]['t_money'] = $vv['twitter_money']['t_staff_money'];
                        } else {
                            if ($sl_v['inviter_info']['expire_time'] > $now_date) {
                                switch (intval($sl_v['inviter_info']['level'])) {
                                    //用户等级 1 早期366老会员 2 推客 3黑钻 4皇钻 5代理
                                    case 2:
                                        $sku_list[$sl_k]['t_money'] = $vv['twitter_money']['t_money'];
                                        break;
                                    case 3:
                                        $sku_list[$sl_k]['t_money'] = $vv['twitter_money']['t_money_black'];
                                        break;
                                    case 4:
                                        $sku_list[$sl_k]['t_money'] = $vv['twitter_money']['t_money_yellow'];
                                        break;
                                    case 5:
                                        $sku_list[$sl_k]['t_money'] = $vv['twitter_money']['t_money_dealer'];
                                        break;
                                    default :
                                        $sku_list[$sl_k]['t_money'] = 0;
                                }
                            }
                        }
                    }
                }
            }
        }

        //查询商品的折扣信息
        $sp_list = $this->getSkuSP(
                array_column($sku_list, 'goods_id'),
                array_column($sku_list, 'sku_number')
        );

        //如果有优惠价格
        if (!empty($sp_list)) {
            //计算优先级  1:折扣  3:几元几件(废弃)
            $sp_list_1 = [];

            foreach ($sp_list as $sp_v) {
                if ($sp_v->type == 1) {
                    $sp_list_1[] = $sp_v;
                }
            }

            foreach ($sp_list_1 as $spv) {
                foreach ($sku_list as $sl_k => $sl_v) {
                    if ($spv->sku_number == $sl_v['sku_number']) {
                        //活动的售价
                        $sku_list[$sl_k]['sp_id'] = $spv->id;
                        $sku_list[$sl_k]['sp_type'] = 1;
                        $sku_list[$sl_k]['sp_o_price'] = $spv->sku_original_price;
                        //优惠价格
                        switch (intval($user['level'])) {
                            case 2:
                            case 3:
                                $sku_list[$sl_k]['sp_price'] = $spv->sku_price_black;
                                break;
                            case 4:
                                $sku_list[$sl_k]['sp_price'] = $spv->sku_price_yellow;
                                break;
                            case 5:
                                $sku_list[$sl_k]['sp_price'] = $spv->sku_price_dealer;
                                break;
                            default :
                                $sku_list[$sl_k]['sp_price'] = $spv->sku_price;
                        }
                        //如果有推客
                        if (!empty($sl_v['inviter']) && !empty($sl_v['inviter_info'])) {

                            if ($spv->is_set_t_money == 1) {
                                switch (intval($sl_v['inviter_info']['level'])) {
                                    //用户等级 1 早期366老会员 2 推客 3黑钻 4皇钻 5代理
                                    case 2:
                                        $sku_list[$sl_k]['t_money'] = $spv->t_money;
                                        break;
                                    case 3:
                                        $sku_list[$sl_k]['t_money'] = $spv->t_money_black;
                                        break;
                                    case 4:
                                        $sku_list[$sl_k]['t_money'] = $spv->t_money_yellow;
                                        break;
                                    case 5:
                                        $sku_list[$sl_k]['t_money'] = $spv->t_money_dealer;
                                        break;
                                }
                            }
                        }
                    }
                }
            }
        }

        //****************用于显示的sku_list****************
        $sku_list_show = [];
        foreach ($sku_list as $k => $v) {
            $temp_v = [];
            $temp_v['name'] = $v['name'];
            $temp_v['subtitle'] = $v['subtitle'];
            $temp_v['picture'] = $v['picture'];
            $temp_v['sku_value_list'] = $v['sku_value'];
            if (1 && $v['num'] > $v['stock']) {
                return ['code' => false, 'msg' => $v['name'] . '库存不足', 'ps' => $v['num'] . '-' . $v['stock']];
            } else {
                $temp_v['num'] = $v['num'] > $v['stock'] ? $v['stock'] : $v['num'];
            }


            $temp_v['original_price'] = ($v['sp_o_price'] ?? 0) > 0 ? $v['sp_o_price'] : $v['original_price'];
            if (($v['sp_price'] ?? 0) > 0) {
                $temp_v['price'] = $v['sp_price'];
            } else {
                $temp_v['price'] = $v['level_price'];
            }
            $sku_list[$k]['actual_price'] = $temp_v['price'];
            $sku_list[$k]['actual_num'] = $temp_v['num'];
            $sku_list_show[] = $temp_v;
        }

        $all_original_price = 0; //所有商品的原价
        $all_price = 0; //所有商品的售价
        $freight_money = ConfigModel::getData(7); //运费
        $vip_cut_money = 0; //vip优惠金额
        $sp_cut_money = 0; //活动优惠金额
        $coupon_money = 0; //优惠券金额
        $coupon_freight = 0; //是否免邮券
        $freight_free_flag = false; //是否免邮
        //****************开始计算金额*********************
        foreach ($sku_list as $k => $v) {
            $all_original_price = GetPriceTools::PriceCalc(
                            '+',
                            $all_original_price,
                            GetPriceTools::PriceCalc(
                                    '*', $v['original_price'], $v['actual_num']
                            )
            );
            $all_price = GetPriceTools::PriceCalc(
                            '+',
                            $all_price,
                            GetPriceTools::PriceCalc(
                                    '*', $v['actual_price'], $v['actual_num']
                            )
            );
            if (($v['sp_price'] ?? 0) > 0) {
                $temp_sp_cut = GetPriceTools::PriceCalc('-', $v['original_price'], $v['actual_price']);
                $sp_cut_money = GetPriceTools::PriceCalc(
                                '+',
                                $sp_cut_money,
                                GetPriceTools::PriceCalc(
                                        '*', $temp_sp_cut, $v['actual_num']
                                )
                );
            } else {
                $temp_vip_cut = GetPriceTools::PriceCalc('-', $v['original_price'], $v['level_price']);
                $vip_cut_money = GetPriceTools::PriceCalc(
                                '+',
                                $vip_cut_money,
                                GetPriceTools::PriceCalc(
                                        '*', $temp_vip_cut, $v['actual_num']
                                )
                );
            }
            //todo 删掉
            ksort($sku_list[$k]);
        }
        //****************可用优惠券*********************
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
        //如果有选定的免邮券,则免邮
        if ($coupon_freight == 1) {
            $freight_free_flag = true;
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
                foreach ($sku_list as $k => $v) {
                    $sku_list[$k]['freight_money'] = FreightTemplate::getFreightMoney(
                                    $v, $used_address
                    );
                }
            }

            foreach ($sku_list as $v) {
                if (($v['freight_money'] ?? 0) > $freight_money) {
                    $freight_money = $v['freight_money'];
                }
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
            'vip_cut_money' => $vip_cut_money,
            'sp_cut_money' => $sp_cut_money,
            'coupon_money' => $coupon_money,
            'freight_free_flag' => $freight_free_flag,
            'order_price' => $order_price,
        ];

        $res = [
            'user' => $user,
            'sku_list' => $sku_list_show,
            'price_list' => $price_list,
            'address_list' => $address_list,
            'coupon_list' => $coupon_list,
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

    /**
     * 订单支付成功处理
     * @param type $params
     */
    public function orderPaySuccess($params, $pay_type = 1) {
        if (1) {
            $out_trade_no = '2006230016893465631561';
        } else {
            $out_trade_no = substr($params['out_trade_no'], 0, -5);
        }

        $order = self::where('ordernum', '=', $out_trade_no)
                ->where('status', '=', 1)
                ->first();

        if (!empty($order)) {
            //1:普通订单  2:秒杀订单 3:拼团订单
            switch (intval($order->order_type)) {
                case 1:
                    MallOrder::paySuccessForOrder($params, $order->id, $pay_type);
                    break;
                case 2:
                    MallOrderFlashSale::paySuccessForFlashSaleOrder($params, $order->id, $pay_type);
                    break;
                case 3:
                    MallOrderGroupBuy::paySuccessFroGroupBuyOrder($params, $order->id, $pay_type);
                    break;
            }
        }
    }

    public static function paySuccessForOrder($data, $order_id, $pay_type) {
        $now = time();
        $now_date = date('Y-m-d H:i:s', $now);
        switch ($pay_type) {
            case 1:
                $total_fee = $data['total_fee'];
                $transaction_id = $data['transaction_id'];
                break;
            default :
                return ['code' => false, 'msg' => '支付方式错误'];
        }

        DB::beginTransaction();
        //修改订单支付状态
        $order_obj = MallOrder::find($order_id);
        if ($order_obj->post_type == 1) {
            //邮寄
            $order_obj->status = 10; //待发货
        } else {
            $order_obj->status = 20; //待收货
        }
        $order_obj->pay_type = $pay_type;
        $order_obj->pay_time = $now_date;
        $order_obj->pay_price = $total_fee;
        $order_res = $order_obj->save();
        if (!$order_res) {
            return ['code' => false, 'msg' => '修改订单状态错误'];
        }


        //todo 添加支付记录
        //todo 添加收益
    }

    //todo 订单状态修改
    public function statusChange($id, $flag, $user_id) {
        
    }

}
