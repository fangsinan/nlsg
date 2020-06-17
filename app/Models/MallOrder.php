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

        //DB::connection()->enableQueryLog();

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
        $list = $query->whereRaw('FIND_IN_SET(2,nmod.special_price_type)')
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
     * 获取普通优惠列表(优惠,几元几件)
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

    //获取sku_list
    public function getOrderSkuList($params, $user_id) {

        if ($params['from_cart'] === 0) {
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
            //dd($check_temp_res);
            if ($check_temp_res === false) {
                return ['code' => false, 'msg' => '商品参数错误'];
            } else {
                $sku_list[$k]['original_price'] = $check_temp_res->original_price;
                $sku_list[$k]['price'] = $check_temp_res->price;
                $sku_list[$k]['name'] = $check_temp_res->name;
                $sku_list[$k]['subtitle'] = $check_temp_res->subtitle;
                $sku_list[$k]['freight_id'] = $check_temp_res->freight_id;
                $sku_list[$k]['stock'] = $check_temp_res->stock;
                $sku_list[$k]['weight'] = $check_temp_res->weight;
                $sku_list[$k]['volume'] = $check_temp_res->volume;
                $sku_list[$k]['sku_value'] = $check_temp_res->sku_value;
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

    //********************普通订单开始********************
    //检查下单参数是否正确
    public function checkParams(&$params) {
        if (empty($params['sku'])) {
            return ['code' => fasle, 'msg' => '参数错误', 'ps' => 'sku'];
        }
        if (!in_array($params['from_cart'], [1, 0])) {
            return ['code' => false, 'msg' => '参数错误', 'ps' => 'from_cart=1,0'];
        }
        if (!is_array($params['sku'])) {
            $params['sku'] = explode(',', $params['sku']);
        }
        if ($params['from_cart'] == 0) {
            if (count($params['sku']) !== 1) {
                return ['code' => false, 'msg' => '参数错误', 'ps' => 'sku数量有误'];
            }
            if (empty($params['goods_id'] ?? 0)) {
                return ['code' => false, 'msg' => '参数错误', 'ps' => 'goods_id错误'];
            }
        }
        if (!in_array($params['post_type'], [1, 2])) {
            return ['code' => false, 'msg' => '参数错误', 'ps' => 'post_type=1,2'];
        }
        $params['coupon_goods_id'] = intval($params['coupon_goods_id'] ?? 0);
        $params['coupon_freight_id'] = intval($params['coupon_freight_id'] ?? 0);
        $params['address_id'] = intval($params['address_id'] ?? 0);
        if ($params['post_type'] == 2 && $params['address_id'] == 0) {
            return ['code' => false, 'msg' => '参数错误', 'ps' => '如果自提,需传自提地址address_id'];
        }
    }

    //普通订单  --预下单
    public function prepareCreateOrder($params, $user) {
        $sku_list = $this->createOrderTool($params, $user);
        return $sku_list;
    }

    public function createOrder($params, $user) {
        $sku_list = $this->createOrderTool($params, $user, true);
        
        if(!$sku_list['can_sub']){
            return ['code'=>false,'msg'=>'参数错误'];
        }
        
        
        
        
        
        
        
        
        
        return $sku_list;
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

        $sp_list = $this->getSkuSP(
                array_column($sku_list, 'goods_id'),
                array_column($sku_list, 'sku_number')
        );

        $priceTools = new GetPriceTools();

        //获取推客金额
        foreach ($sku_list as $sl_k => $sl_v) {
            //$data, $user_level, $user_id, $is_staff = 0,$for_order = false
            $temp_sl_v = $priceTools->getGoodsPrice($sl_v,
                    $user['level'], $user['id'], $user['is_staff'], true);

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

        //如果有优惠价格
        if (!empty($sp_list)) {
            //计算优先级  1:折扣  3:几元几件
            $sp_list_1 = [];
            $sp_list_3 = [];

            foreach ($sp_list as $sp_v) {
                if ($sp_v->type == 1) {
                    $sp_list_1[] = $sp_v;
                } else {
                    $sp_list_3[] = $sp_v;
                }
            }

            foreach ($sp_list_1 as $slv) {
                foreach ($sku_list as $sl_k => $sl_v) {
                    if ($slv->sku_number == $sl_v['sku_number']) {
                        $sku_list[$sl_k]['sp_type'] = 1;
                        $sku_list[$sl_k]['sp_o_price'] = $slv->sku_original_price;
                        //优惠价格
                        switch (intval($user['level'])) {
                            case 2:
                            case 3:
                                $sku_list[$sl_k]['sp_price'] = $slv->sku_price_black;
                                break;
                            case 4:
                                $sku_list[$sl_k]['sp_price'] = $slv->sku_price_yellow;
                                break;
                            case 5:
                                $sku_list[$sl_k]['sp_price'] = $slv->sku_price_dealer;
                                break;
                            default :
                                $sku_list[$sl_k]['sp_price'] = $slv->sku_price;
                        }
                        //推客收益
                        if (!empty($sl_v['inviter']) && !empty($sl_v['inviter_info'])) {
                            foreach ($slv->twitter_money_list as $tmv) {
                                if ($tmv['sku_number'] == $sl_v['sku_number']) {
                                    $sku_list[$sl_k]['t_money'] = 0;
                                    if ($sl_v['inviter_info']['is_staff'] == 0) {
                                        $sku_list[$sl_k]['t_money'] = $tmv['twitter_money']['t_staff_money'];
                                    } else {
                                        if ($sl_v['inviter_info']['expire_time'] > $now_date) {
                                            switch (intval($sl_v['inviter_info']['level'])) {
                                                //用户等级 1 早期366老会员 2 推客 3黑钻 4皇钻 5代理
                                                case 2:
                                                    $sku_list[$sl_k]['t_money'] = $tmv['twitter_money']['t_money'];
                                                    break;
                                                case 3:
                                                    $sku_list[$sl_k]['t_money'] = $tmv['twitter_money']['t_money_black'];
                                                    break;
                                                case 4:
                                                    $sku_list[$sl_k]['t_money'] = $tmv['twitter_money']['t_money_yellow'];
                                                    break;
                                                case 5:
                                                    $sku_list[$sl_k]['t_money'] = $tmv['twitter_money']['t_money_dealer'];
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
        foreach ($sku_list as $v) {
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
        }
        //****************可用优惠券*********************
        $coupon_list = Coupon::getCouponListForOrder($user['id'], $all_price);

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
                if ($v['freight_money'] > $freight_money) {
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
            'sku_list' => $sku_list,
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
        }

        return $res;
    }

    //********************普通订单结束********************
}
