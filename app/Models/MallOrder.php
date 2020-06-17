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
        
    }



    //********************普通订单结束********************
}
