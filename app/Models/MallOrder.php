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

    public function prepareCreateOrder($params, $user) {

        //检查参数逻辑
        $check_params = $this->checkParams($params);
        if (($check_params['code'] ?? true) === false) {
            return $check_params;
        }
        //todo 获取并检查sku是否合法
        $sku_list = $this->getOrderSkuList($params, $user['id']);
        if (($sku_list['code'] ?? true) === false) {
            return $sku_list;
        }

        return $sku_list;
    }

    //检查下单参数是否正确
    public function checkParams(&$params) {
        if (empty($params['sku'])) {
            return ['code' => fasle, 'msg' => '参数错误'];
        }
        if (!in_array($params['from_cart'], [1, 0])) {
            return ['code' => false, 'msg' => '参数错误'];
        }
        if (!is_array($params['sku'])) {
            $params['sku'] = explode(',', $params['sku']);
        }
        if ($params['from_cart'] == 0) {
            if (count($params['sku']) !== 1) {
                return ['code' => false, 'msg' => '参数错误'];
            }
            if (empty($params['goods_id'] ?? 0)) {
                return ['code' => false, 'msg' => '参数错误'];
            }
        }
        if (!is_array($params['coupon_id'] ?? [])) {
            $params['coupon_id'] = explode(',', $params['coupon_id']);
        }
        if (count($params['coupon_id']) > 2) {
            return ['code' => false, 'msg' => '参数错误'];
        }
    }

    //获取sku_list
    public function getOrderSkuList($params, $user_id) {
        //DB::connection()->enableQueryLog();
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

        foreach ($sku_list as $v) {
            $check_temp_res = MallSku::checkSkuCanBuy(
                            $v['goods_id'], $v['sku_number']
            );
            if (($check_temp_res['code'] ?? true) === false) {
                return $check_temp_res;
            }
        }

        //dd(DB::getQueryLog());
        return $sku_list;
    }

}
