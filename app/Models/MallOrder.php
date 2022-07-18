<?php

namespace App\Models;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class MallOrder extends Base
{

    protected $table = 'nlsg_mall_order';

    //检擦用户在规定时间内参加过秒杀活动
    public function getUserSecKillOrderNew($params)
    {
        if (empty($params['user_id'])) {
            return [];
        }

        $query = DB::table('nlsg_mall_order as nmo')
            ->join('nlsg_special_price as nsp', 'nmo.sp_id', '=', 'nsp.id')
            ->where('nmo.user_id', '=', $params['user_id'])
            ->where('nsp.type', '=', 2)
            ->where('nmo.is_stop', '=', 0);

        if ($params['begin_time'] ?? false) {
            $query->where('nmo.created_at', '>=', $params['begin_time']);
        }
        if ($params['end_time'] ?? false) {
            $query->where('nmo.created_at', '<=', $params['end_time']);
        }

        $query->groupBy('nsp.id');

        $order = $query->select(['nmo.sp_id'])->get();

        if ($order->isEmpty()) {
            return [];
        }

        $order = $order->toArray();

        $sp_list = array_column($order, 'sp_id');

        return $sp_list;
    }

    public function getUserSecKillOrder($params)
    {
        if (empty($params['user_id'])) {
            return [];
        }

        $this->getSqlBegin();

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

        $this->getSql();

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
    public function getSkuSP($goods_id, $sku_number)
    {
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
                if ($trv->stock > 0 && ($trv->use_stock >= $trv->stock)) {
                    continue;
                }
                $sp_list[] = $trv;
            }
        }
        return $sp_list;
    }

    //获取sku_list,并校验商品信息和推客信息
    public function getOrderSkuList($params, $user_id)
    {

        if ($params['from_cart'] == 2) {
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
                ->get();
            if ($sku_list->isEmpty()) {
                return ['code' => false, 'msg' => '购物车参数错误 '];
            }
            $sku_list = $sku_list->toArray();
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
                $sku_list[$k]['min_buy_num'] = $check_temp_res->min_buy_num;
            }

            if (empty($v['inviter'])) {
                $sku_list[$k]['inviter_info'] = [];
            } else {

                $temp_inviter_info = User::where('status', '=', 1)
                    ->select(['level', 'expire_time', 'is_staff'])
                    ->find($v['inviter']);

                if ($temp_inviter_info) {
                    $sku_list[$k]['inviter_info'] = $temp_inviter_info->toArray();
                } else {
                    $sku_list[$k]['inviter_info'] = [];
                }

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

    public function skuListExplode($sku_list)
    {
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
     * @param $uid 用户id
     * @param $type 1:普通商品订单 2:售后 3:虚拟shill
     * @return string
     */
    public static function createOrderNumber($uid, $type)
    {
        $now = time();
        $d = date('ymd', $now);
        $u = str_pad($uid, 8, 0, STR_PAD_LEFT);
        $s = $now - strtotime(date('y-m-d', $now));
        return $d . $u . str_pad($s, 5, 0, STR_PAD_LEFT) .
            rand(10, 99) . str_pad($type, 2, 0, STR_PAD_LEFT);
    }

    //检查下单参数是否正确
    public function checkParams(&$params)
    {

        if (empty($params['sku'])) {
            return ['code' => false, 'msg' => '参数错误', 'ps' => 'sku'];
        }

        if (!is_array($params['sku'])) {
            $params['sku'] = explode(',', $params['sku']);
        }

        if (!in_array($params['from_cart'], [1, 2])) {
            return ['code' => false, 'msg' => '参数错误', 'ps' => 'from_cart=1,2'];
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
    public function prepareCreateOrder($params, $user)
    {
        $sku_list = $this->createOrderTool($params, $user);
        return $sku_list;
    }

    //普通订单  --下单
    public function createOrder($params, $user)
    {
        $now = time();
        $now_date = date('Y-m-d H:i:s', $now);
        $dead_time = ConfigModel::getData(12);
        $dead_time = date('Y-m-d H:i:03', ($now + $dead_time * 60));

        if (!in_array($params['pay_type'], [1, 2, 3])) {
            return ['code' => false, 'msg' => '请选择支付方式', 'ps' => 'pay_type error'];
        }

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
        $order_data['normal_cut'] = $data['price_list']['normal_cut_money'];
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
        $order_data['dead_time'] = $dead_time;
        $order_data['pay_type'] = $params['pay_type'];
        $order_data['live_id'] = $params['live_id'];
        $order_data['live_info_id'] = $params['live_info_id'];

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
            $temp_datails_data['sp_id'] = $v['sp_id'] ?? 0;
            $temp_datails_data['t_money'] = $v['t_money'] ?? 0;
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
            if (!is_array($params['sku'])) {
                $cart_sku = explode(',', $params['sku']);
            } else {
                $cart_sku = $params['sku'];
            }

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
    public function createOrderTool($params, $user, $check_sub = false)
    {
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
                if ($vv['sku_number'] === $sl_v['sku_number']) {
                    //初始推客金额(不是活动单独设定)
                    $sku_list[$sl_k]['t_money'] = 0;
                    if (!empty($sl_v['inviter']) && !empty($sl_v['inviter_info'])) {
                        if ($sl_v['inviter_info']['is_staff'] == 1) {
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
            $temp_v['goods_id'] = $v['goods_id'];
            $temp_v['sku_number'] = $v['sku_number'];
            $temp_v['sp_id'] = $v['sp_id'] ?? 0;

            if ($v['num'] > $v['stock']) {
                return ['code' => false, 'msg' => '库存不足', 'ps' => $v['num'] . '-' . $v['stock']];
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
        $normal_cut_money = 0;//普通优惠金额
        $sp_cut_money = 0; //活动优惠金额
        $coupon_money = 0; //优惠券金额
        $coupon_freight = 0; //是否免邮券
        $freight_free_flag = false; //是否免邮
        if (($user['new_vip']['level'] ?? 0) == 1) {
            $freight_free_flag = true;
        }
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

//            $temp_normal_cut = GetPriceTools::PriceCalc('-',$v['original_price'],$v['price']);
//            $normal_cut_money = GetPriceTools::PriceCalc('+',$normal_cut_money,
//                GetPriceTools::PriceCalc('*',$temp_normal_cut,$v['actual_num'])
//            );

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
                if ($v['level_price'] < $v['price']) {
                    $temp_vip_cut = GetPriceTools::PriceCalc('-', $v['original_price'], $v['level_price']);
                    $vip_cut_money = GetPriceTools::PriceCalc(
                        '+',
                        $vip_cut_money,
                        GetPriceTools::PriceCalc(
                            '*', $temp_vip_cut, $v['actual_num']
                        )
                    );
                } else {
                    $temp_normal_cut = GetPriceTools::PriceCalc('-', $v['original_price'], $v['price']);
                    $normal_cut_money = GetPriceTools::PriceCalc(
                        '+',
                        $normal_cut_money,
                        GetPriceTools::PriceCalc(
                            '*', $temp_normal_cut, $v['actual_num']
                        )
                    );
                }
            }
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
        $address_list = $addressModel->getList($user['id'], 0);

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
                foreach ($sku_list as $k => $v) {
                    $temp_freight_money = FreightTemplate::getFreightMoney(
                        $v, $used_address
                    );
                    $sku_list[$k]['freight_money'] = $temp_freight_money['price'];
                    $sku_list[$k]['add_freight_money'] = $temp_freight_money['add_price'];
                }
            }

            foreach ($sku_list as $v) {
                if ($v['add_freight_money'] ?? 0 > $add_freight_money) {
                    $add_freight_money = $v['add_freight_money'];
                }
                if (($v['freight_money'] ?? 0) > $freight_money) {
                    $freight_money = $v['freight_money'];
                }
            }
        } else {
            $freight_money = 0;
        }

        $order_price = GetPriceTools::PriceCalc('-', $all_price, $coupon_money);

        if ($freight_money > 0 && $order_price >= ConfigModel::getData(1)) {
            //$freight_money = $freight_money - ConfigModel::getData(7);
            $freight_money = $add_freight_money;
            if ($freight_money < 0) {
                $freight_money = 0;
            }
            if ($freight_money == 0) {
                $freight_free_flag = true;
            }
        }

        $order_price = GetPriceTools::PriceCalc('+', $order_price, $freight_money);

//        if ($vip_cut_money>0){
//            $vip_cut_money = GetPriceTools::PriceCalc('-',$vip_cut_money,$normal_cut_money);
//        }
//
//        if ($sp_cut_money>0){
//            $sp_cut_money = GetPriceTools::PriceCalc('-',$sp_cut_money,$normal_cut_money);
//        }


        if (intval($freight_money) == 0 && $params['coupon_freight_id'] ==0) {
            $coupon_list['coupon_freight'] = [];
        }

        $price_list = [
            'all_original_price' => $all_original_price,
            'all_price' => $all_price,
            'freight_money' => $freight_money,
            'vip_cut_money' => $vip_cut_money,
            'normal_cut_money' => $normal_cut_money,
            'sp_cut_money' => $sp_cut_money,
            'coupon_money' => $coupon_money,
            'freight_free_flag' => $freight_free_flag,
            'order_price' => $order_price,
        ];

        $price_list_new = [
            ['key' => '商品总额', 'value' => $all_original_price],
            ['key' => '运费', 'value' => $freight_money],
            ['key' => '优惠金额', 'value' => $normal_cut_money],
//            ['key' => '权益立减', 'value' => GetPriceTools::PriceCalc('-', 0, $vip_cut_money)],
            ['key' => '权益立减', 'value' => $vip_cut_money],
            ['key' => '活动立减', 'value' => $sp_cut_money],
            ['key' => '优惠券总额', 'value' => $coupon_money],
        ];

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

        $ftModel = new FreightTemplate();
        $shop_address_list = $ftModel->listOfShop(2);

        if (is_array($used_address) && empty($used_address)) {
            $used_address = new class {
            };
        }

        $res = [
            'user' => User::onlySimpleInfo($user),
            'sku_list' => $sku_list_show,
            'price_list' => $price_list,
            'price_list_new' => $price_list_new,
            'address_list' => $address_list,
            'shop_address_list' => $shop_address_list,
            'coupon_list' => $coupon_list,
            'used_address' => $used_address,
            'from_cart' => $params['from_cart'],
            'token' => CacheTools::orderToken($user['id'], 1, 'set'),
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
    public function orderPaySuccess($params, $pay_type = 1)
    {
        //1 微信端 2app微信 3app支付宝 4ios
        $now = time();
        $now_date = date('Y-m-d H:i:s', $now);
        switch ($pay_type) {
            case 1:
//                $params['out_trade_no'] = '200623001689346563156199999';
                $total_fee = $params['total_fee'];
                $transaction_id = $params['transaction_id'];
                $out_trade_no = substr($params['out_trade_no'], 0, -5);
                break;
            default :
                return ['code' => false, 'msg' => '支付方式错误'];
        }

        $order_obj = self::where('ordernum', '=', $out_trade_no)
            ->where('status', '=', 1)
            ->first();

        DB::beginTransaction();
        //修改订单支付状态
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
            DB::rollBack();
            return ['code' => false, 'msg' => '修改订单状态错误'];
        }

        //添加支付记录
        $payRecordModel = new PayRecord();
        $payRecordModel->ordernum = $out_trade_no;
        $payRecordModel->price = $total_fee;
        $payRecordModel->transaction_id = $transaction_id;
        $payRecordModel->user_id = $order_obj->user_id;
        $payRecordModel->type = $pay_type;
        $payRecordModel->order_type = 10;
        $payRecordModel->status = 1;
        $pr_res = $payRecordModel->save();
        if (!$pr_res) {
            DB::rollBack();
            return ['code' => false, 'msg' => '修改支付记录错误'];
        }


        //如果是拼团订单  需要查看拼团订单是否成功
        if ($order_obj->order_type == 3) {
            $temp_data = DB::table('nlsg_mall_group_buy_list')
                ->where('user_id', '=', $order_obj->user_id)
                ->where('order_id', '=', $order_obj->id)
                ->first();
            if (!$temp_data) {
                DB::rollBack();
                return ['code' => false, 'msg' => '拼团信息错误'];
            }
            $group_buy_id = $temp_data->group_buy_id;
            $sp_info = DB::table('nlsg_special_price')
                ->find($group_buy_id);
            $need_num = $sp_info->group_num;

            $now_num = DB::table('nlsg_mall_group_buy_list')
                ->where('group_key', '=', $order_obj->group_key)
                ->count();

            if ($now_num >= $need_num) {
                $gb_res = MallOrderGroupBuy::where(
                    'group_key', '=', $order_obj->group_key
                )->update(
                    [
                        'is_success' => 1,
                        'success_at' => $now_date
                    ]
                );
                if (!$gb_res) {
                    DB::rollBack();
                    return ['code' => false, 'msg' => '拼团信息错误'];
                }
            }
        }

        //收益表

        DB::commit();
        return ['code' => true, 'msg' => '修改成功'];
    }

    //订单状态修改
    public function statusChange($id, $flag, $user_id)
    {
        $check = MallOrder::where('user_id', '=', $user_id)
            ->find($id);

        if (!$check) {
            return ['code' => false, 'msg' => '订单不存在'];
        }
        $now_date = date('Y-m-d H:i:s', time());

        switch (strtolower($flag)) {
            case 'stop':
                if ($check->status === 1 || $check->status === 10 || ($check->post_type === 2 && $check->status === 20)) {
                    DB::beginTransaction();
                    //未支付的可以直接取消
                    $check->is_stop = 1;
                    $check->stop_by = $user_id;
                    $check->stop_at = $now_date;
                    $update_res = $check->save();
                    if (!$update_res) {
                        DB::rollBack();
                        return ['code' => false, 'msg' => '失败', 'ps' => 'order error'];
                    }

                    //库存加 销量减
                    $order_detail = MallOrderDetails::where('order_id', '=', $check->id)
                        ->get();
                    foreach ($order_detail as $od_v) {
                        MallSku::where('sku_number', '=', $od_v->sku_number)->increment('stock', $od_v->num);
                        MallSku::where('sku_number', '=', $od_v->sku_number)->decrement('sales_num', $od_v->num);
                        MallGoods::whereId($od_v->goods_id)->decrement('sales_num', $od_v->num);

                    }

                    //如果是秒杀订单,额外需要修改秒杀数量
                    if ($check->order_type === 2 && $check->sp_id !== 0) {
                        $check_details = MallOrderDetails::where('order_id', '=', $check->id)
                            ->select(['id', 'sp_id', 'num'])->get();

                        foreach ($check_details as $cd_v) {
                            SpecialPriceModel::whereId($cd_v->sp_id)->decrement('use_stock', $cd_v->num);
                        }
                    }

                    if ($check->status === 10 || $check->status === 20) {
                        //订单状态修改-写入后台审核
                        $refund_data['service_num'] = MallOrder::createOrderNumber($user_id, 2);
                        $refund_data['order_id'] = $id;
                        $refund_data['order_detail_id'] = 0;
                        $refund_data['type'] = 4;
                        $refund_data['pay_type'] = $check->pay_type;
                        $refund_data['refe_price'] = $check->pay_price;
                        $refund_data['price'] = $check->pay_price;
                        $refund_data['status'] = 40;
                        $refund_data['user_id'] = $user_id;
                        $refund_data['created_at'] = $now_date;
                        $refund_data['updated_at'] = $now_date;
                        $refund_data['run_refund'] = 1;
                        $refund_res = DB::table('nlsg_mall_refund_record')
                            ->insert($refund_data);
                        if (!$refund_res) {
                            DB::rollBack();
                            return ['code' => false, 'msg' => '失败', 'ps' => 'refund'];
                        }
                    }

                    //返还优惠券
                    if ($check->coupon_id > 0) {
                        $check_coupon = Coupon::whereId($check->coupon_id)->first();
                        $check_coupon->status = 1;
                        $check_coupon->order_id = 0;
                        $coupon_res = $check_coupon->save();
                        if ($coupon_res === false) {
                            DB::rollBack();
                            return ['code' => false, 'msg' => '失败', 'ps' => 'coupon'];
                        }
                    }

                    //返还免邮券
                    if ($check->coupon_freight_id > 0) {
                        $check_coupon = Coupon::whereId($check->coupon_freight_id)->first();
                        $check_coupon->status = 1;
                        $check_coupon->order_id = 0;
                        $coupon_res = $check_coupon->save();
                        if ($coupon_res === false) {
                            DB::rollBack();
                            return ['code' => false, 'msg' => '失败', 'ps' => 'coupon'];
                        }
                    }

                    //erp队列
                    MallErpList::addList($id);

                    DB::commit();
                    return ['code' => true, 'msg' => '成功'];

                } else {
                    return ['code' => false, 'msg' => '订单状态错误',
                        'ps' => '只有待支付和待发货可以取消'];
                }
                break;
            case 'del':
                //已经取消或者已经完成的订单可以删除
                if ($check->is_stop === 1 || $check->status === 30) {
                    $check->is_del = 1;
                    $check->del_at = $now_date;
                    $update_res = $check->save();
                    if ($update_res) {
                        return ['code' => true, 'msg' => '成功'];
                    } else {
                        return ['code' => false, 'msg' => '失败'];
                    }
                } else {
                    return ['code' => false, 'msg' => '订单状态错误',
                        'ps' => '只有已完成和已取消的可以删除'];
                }
                break;
            case 'receipt':
                //确认收货
                if ($check->status === 20) {
                    $check->status = 30;
                    $check->receipt_at = $now_date;

                    DB::beginTransaction();

                    $update_res = $check->save();
                    if (!$update_res) {
                        DB::rollBack();
                        return ['code' => false, 'msg' => '失败'];
                    }

                    if ($check->post_type == 1) {
                        $child_res = DB::table('nlsg_mall_order_child')
                            ->where('order_id', '=', $check->id)
                            ->update(
                                [
                                    'status' => 2,
                                    'receipt_at' => $now_date
                                ]
                            );
                        if (!$child_res) {
                            DB::rollBack();
                            return ['code' => false, 'msg' => '失败'];
                        }
                    }

                    //erp队列
                    MallErpList::addList($id);

                    DB::commit();

                    return ['code' => true, 'msg' => '成功'];
                } else {
                    return ['code' => false, 'msg' => '订单状态错误',
                        'ps' => '只有待收货订单可以收货'];
                }
                break;
            default:
                return ['code' => false, 'msg' => '参数错误', 'ps' => 'flag'];
        }
    }

    //用户普通订单列表
    public function userOrderList($params, $user, $flag = false)
    {
        $now = time();
        $now_date = date('Y-m-d H:i:s', $now);
        $user_id = $user['id'];
        $params['page'] = $params['page'] ?? 1;
        $params['size'] = $params['size'] ?? 10;
        //库数据:订单状态 1待付款  10待发货 20待收货 30已完成
        //列表tab栏:全部0,待付款1,待发货10,待签收20,已完成30,已取消99
        //展示数据:订单编号,状态,商品列表,价格,数量,取消时间,金额

        $query = self::from('nlsg_mall_order as nmo')
            ->where('user_id', '=', $user_id)
            ->whereIn('order_type', [1, 2])
            ->where('is_del', '=', 0)
            ->limit($params['size'])
            ->offset(($params['page'] - 1) * $params['size']);

        if (!empty($params['ordernum'])) {
            $query->where('nmo.ordernum', '=', $params['ordernum']);
        }

        $query->where(DB::raw('(case when `status` <> 1 then TRUE WHEN `status` = 1 AND dead_time >= "' .
            $now_date . '" then TRUE ELSE FALSE END)'), '=', true);

        switch (intval($params['status'] ?? 0)) {
            case 1:
                $query->where('nmo.status', '=', 1)->where('nmo.is_stop', '=', 0);
                break;
            case 10:
                $query->where('nmo.status', '=', 10)->where('nmo.is_stop', '=', 0);
                break;
            case 20:
                $query->where('nmo.status', '=', 20)->where('nmo.is_stop', '=', 0);
                break;
            case 30:
                $query->where('nmo.status', '=', 30)->where('nmo.is_stop', '=', 0);
                break;
            case 99:
                $query->where('nmo.is_stop', '=', 1);
                break;
        }

        $field = [
            'id', 'ordernum', 'price', 'dead_time', DB::raw('unix_timestamp(dead_time) as dead_timestamp'),
            DB::raw('(case when is_stop = 1 then 99 ELSE `status` END) `status`'), 'created_at', 'pay_price',
            'price', 'post_type', 'pay_type', 'normal_cut',
        ];
        $with = ['orderDetails', 'orderDetails.goodsInfo'];
        $with[] = 'orderChild';
        $with[] = 'orderChild.expressInfoForList';

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

//        $query->whereRaw('(case when `status` = 1 AND dead_time < "' .$now_date . '" then FALSE ELSE TRUE END) ');

        $query->orderBy('id', 'desc');

        $list = $query->with($with)->select($field)->get();

        foreach ($list as $v) {
            $v->goods_count = 0;
            foreach ($v->orderDetails as $vv) {
                $v->goods_count += $vv->num;
                $vv->sku_history = json_decode($vv->sku_history);
            }
            $v->address_history = json_decode($v->address_history);

            $temp_express_list = [];
            foreach ($v->orderChild as $ocv) {
                $temp_express = $ocv->expressInfoForList;
                $temp_express_list[] = $temp_express;
            }
            $v->express_list = $temp_express_list;
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

    public function userInfo()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id')
            ->select(['id', 'phone', 'nickname', 'headimg','is_test_pay']);
    }

    //关联拼团表 一对一
    public function groupBuy(){
        return $this->hasOne(MallGroupBuyList::class, 'order_id','id');
    }

    //后台商品列表使用的关联售后查询
    public function refundRecord(){
        return $this->hasMany(MallRefundRecord::class,'order_id','id');
    }

    public function orderChild()
    {
        return $this->hasMany('App\Models\MallOrderChild', 'order_id', 'id')
//            ->groupBy('express_info_id')
            ->groupBy('order_id')
            ->select([
                'status', 'order_id',
                'express_info_id',
                DB::raw('GROUP_CONCAT(order_detail_id) order_detail_id')
            ]);
    }

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

            if (isset($v1['express_info']['history']) && !empty($v1['express_info']['history'])) {
                $v1['express_info']['history'] = json_decode($v1['express_info']['history']);
            } else {
                $v1['express_info']['history'] = new class {
                };
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

        $bill_info = [];
        $bill_info['bill_type'] = $data['bill_type'];
        $bill_info['bill_title'] = $data['bill_title'];
        $bill_info['bill_number'] = $data['bill_number'];
        $bill_info['bill_format'] = $data['bill_format'];

        $data['price_info'] = $price_info;
        $data['bill_info'] = $bill_info;

        $temp_data = [];
        $temp_data['status'] = 0;
        $temp_data['order_id'] = $data['id'];
        $temp_data['express_info_id'] = 0;
        $temp_data['express_num'] = '';
        $temp_data['express_info'] = [];
        $temp_data['order_detail_id'] = [];
        $temp_data['order_details'] = [];

        foreach ($data['order_details'] as &$d2c_v) {
            $in_flag = false;
            foreach ($data['order_child'] as &$d2c_vv) {
                if (in_array($d2c_v['details_id'], $d2c_vv['order_detail_id'])) {
                    array_push($d2c_vv['order_details'], $d2c_v);
                    $in_flag = true;
                }
            }
            if ($in_flag == false) {
                array_push($temp_data['order_details'], $d2c_v);
            }
        }

        $data['order_child'][] = $temp_data;


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
                        $doc['express_info']['express_id'] ?? 0, 3
                    );
                    if ($doc['express_info']['history']) {
                        $doc['express_info']['history']->express_phone = $doc['express_info']['express_phone'];
                    }
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
//            ,$data['order_details']
        );

        return $data;
    }

    public static function orderParamsName($type, $id)
    {
        $id = intval($id);
        $res = '';
        if ($type == 1) {
            switch ($id) {
                case 1:
                case 2:
                    $res = '微信';
                    break;
                case 3:
                    $res = '支付宝';
                    break;
                case 4:
                    $res = '余额';
                    break;
            }

        } elseif ($type == 2) {
            switch ($id) {
                case 1:
                    $res = '个人';
                    break;
                case 2:
                    $res = '公司';
                    break;
                default:
                    $res = '不开发票';
            }
        }

        return $res;
    }

    public function commentList($user_id, $params = [])
    {

        $query = DB::table('nlsg_mall_order as nmo')
            ->join('nlsg_mall_order_detail as nmod', 'nmo.id', '=', 'nmod.order_id')
            ->join('nlsg_mall_goods as nmg', 'nmod.goods_id', '=', 'nmg.id')
            ->where('nmo.user_id', '=', $user_id);

        //1已评价   2未评价  3全部
        switch (intval($params['flag'] ?? 0)) {
            case 1:
                $query->where('nmod.comment_id', '>', 0);
                break;
            case 2:
                $query->where('nmod.comment_id', '=', 0);
                break;
            case 3:
                break;
            default :
                return ['code' => false, 'msg' => '参数错误'];
        }

        //订单id筛选
        if ($params['order_id'] ?? 0) {
            $query->where('nmo.id', '=', $params['order_id']);
        }

        $query->where('nmo.status', '=', 30)
            ->where('nmod.num', '>', 'nmod.after_sale_used_num')
            ->where('nmo.is_del', '=', 0)
            ->select(['nmo.id as order_id', 'nmo.ordernum',
                'nmod.id as order_detail_id', 'nmod.sku_history',
                'nmg.name', 'nmg.subtitle', 'nmod.comment_id', 'nmg.picture']);

        $list = $query->get();

        foreach ($list as $v) {
            $temp = json_decode($v->sku_history);
            $v->sku_value = $temp->sku_value;
            unset($v->sku_history);
        }

        return $list;
    }

    public function subComment($params, $user)
    {
        $order_detial_id = $params['order_detail_id'] ?? 0;
        if (empty($order_detial_id)) {
            return ['code' => false, 'msg' => '参数错误'];
        }
        //校验是否能评价
        $check = DB::table('nlsg_mall_order_detail as nmod')
            ->join('nlsg_mall_order as nmo', 'nmod.order_id', '=', 'nmo.id')
            ->where('nmod.id', '=', $order_detial_id)
            ->where('nmo.user_id', '=', $user['id'])
            ->where('nmo.status', '=', 30)
            ->where('nmod.comment_id', '=', 0)
            ->where('nmod.num', '>', 'nmod.after_sale_used_num')
            ->where('nmo.is_del', '=', 0)
            ->first();

        if (empty($check)) {
            return ['code' => false, 'msg' => '参数错误'];
        }

        $now = time();
        $now_date = date('Y-m-d H:i:s', $now);

        $c_data = [];
        $c_data['star'] = intval($params['star'] ?? 0);
        if ($c_data['star'] < 1 || $c_data['star'] > 5) {
            return ['code' => false, 'msg' => '参数错误', 'ps' => 'star:1->5'];
        }

        $c_data['picture'] = $params['picture'] ?? '';
        $c_data['issue_type'] = $params['issue_type'] ?? '';

        $c_data['user_id'] = $user['id'];
        $c_data['content'] = $params['content'] ?? '';
        $c_data['order_id'] = $check->order_id;
        $c_data['order_detail_id'] = $order_detial_id;
        $c_data['goods_id'] = $check->goods_id;
        $c_data['sku_number'] = $check->sku_number;
        $c_data['pid'] = 0;
        $c_data['status'] = 1;
        $c_data['created_at'] = $now_date;
        $c_data['updated_at'] = $now_date;

        DB::beginTransaction();

        $c_res = DB::table('nlsg_mall_comment')->insertGetId($c_data);

        if (!$c_res) {
            DB::rollBack();
            return ['code' => false, 'msg' => '评价失败', 'ps' => '写入评论失败'];
        }

        $od = MallOrderDetails::find($order_detial_id);
        $od->comment_id = $c_res;
        $d_res = $od->save();
        if (!$d_res) {
            DB::rollBack();
            return ['code' => false, 'msg' => '评价失败', 'ps' => '订单错误'];
        }

        //好评有礼 五星且有内容 送优惠券一张
        if ($c_data['star'] == 5 && strlen($c_data['content']) > 10) {
            $coupon_rule_id = ConfigModel::getData(18);
            $coupon_rule_id = explode(',', $coupon_rule_id);
            shuffle($coupon_rule_id);
            $coupon_rule_id = $coupon_rule_id[0];

            if ($coupon_rule_id) {

                $coupon_rule = CouponRule::where('status', '=', 1)
                    ->whereIN('use_type', [3, 4])
                    ->find($coupon_rule_id);
                if (!$coupon_rule) {
                    DB::rollBack();
                    return ['code' => false, 'msg' => '评价失败', 'ps' => '服务器错误,请重试'];
                }

                $coupon_data = [];
                $today_time = date('Y-m-d 00:00:00');
                $coupon_data['name'] = $coupon_rule->name;
                $coupon_data['number'] = Coupon::createCouponNum($coupon_rule->buffet, $coupon_rule->id);
                $coupon_data['type'] = $coupon_rule->use_type;
                $coupon_data['price'] = $coupon_rule->price;
                $coupon_data['full_cut'] = $coupon_rule->full_cut;
                $coupon_data['explain'] = $coupon_rule->remarks;
                if ($coupon_rule->use_time_begin) {
                    $coupon_data['begin_time'] = $coupon_rule->use_time_begin;
                } else {
                    $coupon_data['begin_time'] = $today_time;
                }
                if ($coupon_rule->use_time_end) {
                    $coupon_data['end_time'] = $coupon_rule->use_time_end;
                } else {
                    $coupon_data['end_time'] = date('Y-m-d H:i:s',
                        strtotime($today_time) + ($coupon_rule->past + 1) * 86400 - 1);
                }
                $coupon_data['get_way'] = 1;
                $coupon_data['user_id'] = $user['id'];
                $coupon_data['cr_id'] = $coupon_rule->id;

                $coupon_res = DB::table('nlsg_coupon')->insertGetId($coupon_data);
                if (!$coupon_res) {
                    return ['code' => false, 'msg' => '评价失败', 'ps' => '服务器错误,请重试'];
                }

                $coupon = Coupon::where('id', '=', $coupon_res)
                    ->select(['id', 'number', 'name', 'type',
                        DB::raw('cast(price as signed) as price'),
                        DB::raw('cast(full_cut as signed) as full_cut'),
                        'explain', 'begin_time', 'end_time',
                        DB::raw('unix_timestamp(begin_time) as begin_timestamp'),
                        DB::raw('unix_timestamp(end_time) as end_timestamp')
                    ])
                    ->first();
            }
        }

        if (!($coupon ?? 0)) {
            $coupon = new class {
            };
        }

        DB::commit();
        return ['code' => true, 'msg' => '评价成功', 'coupon' => $coupon];
    }

    //mall_refund_record使用
    public function infoOrderDetail()
    {
        return $this->hasMany('App\Models\MallOrderDetails', 'order_id', 'id')
            ->select(['id', 'order_id', 'goods_id', 'sku_history']);
    }

    //清理超时普通订单
    public static function clear()
    {
        $now = time();
        $time_line = date('Y-m-d H:i:s', $now - 60);
        $now_date = date('Y-m-d H:i:s', $now);

        DB::table('nlsg_mall_order')
            ->where('order_type', '=', 1)
            ->where('status', '=', 1)
            ->where('is_stop', '=', 0)
            ->where('dead_time', '<=', $time_line)
            ->update(['is_stop' => 1, 'stop_by' => 0, 'stop_at' => $now_date]);

    }

    //自动收货
    public static function receipt()
    {
        DB::table('nlsg_mall_order as o')
            ->join('nlsg_mall_order_child as c', 'o.id', '=', 'c.order_id')
            ->where('o.status', '=', 20)
            ->where('o.is_stop', '=', 0)
            ->where('o.is_del', '=', 0)
            ->whereRaw('TIMESTAMPDIFF(DAY,c.created_at,NOW()) > 3')
            ->update([
                'o.status' => 30,
                'o.receipt_at' => date('Y-m-d H:i:s'),
            ]);
    }
}
