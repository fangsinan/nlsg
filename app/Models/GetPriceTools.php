<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class GetPriceTools extends Base {

    public $level_3_off = 0.9;
    public $level_4_off = 0.85;
    public $level_5_off = 0.65;

    public function goodsList($list, $user_level, $user_id, $is_staff) {
        foreach ($list as $v) {
            $this->getGoodsPrice($v, $user_level, $user_id, $is_staff);
        }
    }

    //获取计算价格
    public function getGoodsPrice($data, $user_level, $user_id, $is_staff = 0, $for_order = false) {
        //计算推客的常规购买价格和收益
        if ($for_order) {
            $goods_id = $data['goods_id'];
            $data = (object) array();
        } else {
            $goods_id = $data->id;
        }

        $expire_num = CacheTools::getExpire('goods_price_exprie');
        $cache_key_name = 'goods_price'; //哈希组名
        $cache_name = 'goods_' . $goods_id;
        $list = Cache::tags($cache_key_name)->get($cache_name);

        if (empty($list)) {
            $list = $this->getPriceDataFromDb($goods_id);
            Cache::tags($cache_key_name)->put($cache_name, $list, $expire_num);
        }

        //各sku的推客收益
        $temp_twitter_data = [];
        foreach ($list->sku_price_list as $tmv) {
            $temp_tmv = [];
            $temp_tmv['sku_number'] = $tmv->sku_number;
            $temp_tmv['twitter_money'] = $tmv->twitter_money;
            $temp_twitter_data[] = $temp_tmv;
        }
        $data->twitter_money_list = $temp_twitter_data;
        if ($for_order) {
            $data->price_list = $list;
            return $data;
        }

        switch (intval($user_level)) {
            case 2:
            case 3:
                $data->price = $list->level_3;
                break;
            case 4:
                $data->price = $list->level_4;
                break;
            case 5:
                $data->price = $list->level_5;
                break;
        }

        if ($data->sku_list ?? false) {
            foreach ($data->sku_list as $slv) {
                foreach ($list->sku_price_list as $splv) {
                    if ($slv->sku_number == $splv->sku_number) {
                        switch (intval($user_level)) {
                            case 2:
                            case 3:
                                $slv->price = $splv->level_3;
                                break;
                            case 4:
                                $slv->price = $splv->level_4;
                                break;
                            case 5:
                                $slv->price = $splv->level_5;
                                break;
                        }
                    }
                }
            }
        }

        //活动价格查询,有活动价格则覆盖
        $spModel = new SpecialPriceModel();
        $temp_sp_data = $spModel->getPriceByGoodsId(
                $goods_id, 1, $user_id
        );

        $sp_info['group_buy'] = []; //1表示有拼团
        $sp_info['count_buy'] = []; //1表示有几元几件
        $sp_info['sp_type'] = 0; //当前商品优先级最高的活动 0表示没有活动
        $sp_info['list'] = []; //当前商品所有可参加活动
        //需要设置活动
        if (!empty($temp_sp_data)) {

            //活动type=4是拼团 3是几元几件,只需要给出标记,并不显示在商品详情上
            foreach ($temp_sp_data as $tsdk => $tsdv) {
                array_push($sp_info['list'], $tsdv->type);
                if ($tsdv->type == 4) {
                    $sp_info['group_buy']['price'] = $tsdv->goods_price;
                    unset($temp_sp_data[$tsdk]);
                }
                if ($tsdv->type == 3) {
                    $sp_info['count_buy']['price'] = $tsdv->group_price;
                    $sp_info['count_buy']['num'] = $tsdv->group_num;
                    unset($temp_sp_data[$tsdk]);
                }
            }

            //取不是拼团的第一位
            if (!empty($temp_sp_data)) {
                $temp_sp_goods_data = reset($temp_sp_data);
                $sp_info['sp_type'] = $temp_sp_goods_data->type;
                if (floatval($temp_sp_goods_data->goods_original_price) > 0) {
                    $data->original_price = $temp_sp_goods_data->goods_original_price;
                }
                $data->price = $temp_sp_goods_data->goods_price;

                $temp_sku_list = [];
                foreach ($data->sku_list as $slv) {
                    $temp_sku_list_s = $slv;
                    foreach ($temp_sp_data as $spdv) {
                        if ($slv->sku_number == $spdv->sku_number) {
                            $temp_sku_list_s->original_price = $spdv->sku_original_price;
                            switch (intval($user_level)) {
                                case 2:
                                case 3:
                                    $temp_sku_list_s->price = $spdv->sku_price_black;
                                    break;
                                case 4:
                                    $temp_sku_list_s->price = $spdv->sku_price_yellow;
                                    break;
                                case 5:
                                    $temp_sku_list_s->price = $spdv->sku_price_dealer;
                                    break;
                                default:
                                    $temp_sku_list_s->price = $spdv->sku_price;
                            }
                        }
                    }
                    $temp_sku_list[] = $temp_sku_list_s;
                }
                $data->sku_list = $temp_sku_list;

                $temp_twitter_money_list = [];
                foreach ($data->twitter_money_list as $tmlv) {
                    $t_t = $tmlv;
                    foreach ($temp_sp_data as $spdv) {
                        if ($slv->sku_number == $spdv->sku_number) {
                            $t_t['twitter_money']['t_money'] = $spdv->t_money;
                            $t_t['twitter_money']['t_money_black'] = $spdv->t_money_black;
                            $t_t['twitter_money']['t_money_yellow'] = $spdv->t_money_yellow;
                            $t_t['twitter_money']['t_money_dealer'] = $spdv->t_money_dealer;
                        }
                    }
                    $temp_twitter_money_list[] = $t_t;
                }
                $data->twitter_money_list = $temp_twitter_money_list;
            }
        }
        $data->sp_info = $sp_info;
    }

    //计算推客的常规购买价格和收益
    public function getPriceDataFromDb($goods_id) {
        $res = DB::table('nlsg_mall_goods')
                        ->select(['id', 'price'])->find($goods_id);
        $sku_price = DB::table('nlsg_mall_sku')
                ->where('goods_id', '=', $goods_id)
                ->where('status', '=', 1)
                ->select(['id', 'sku_number', 'price', 'original_price',
                    'cost', 'promotion_cost'])
                ->get();

        $res->level_3 = self::PriceCalc('*', $res->price, $this->level_3_off);
        $res->level_4 = self::PriceCalc('*', $res->price, $this->level_4_off);
        $res->level_5 = self::PriceCalc('*', $res->price, $this->level_5_off);
        foreach ($sku_price as $v) {
            $v->level_3 = self::PriceCalc('*', $v->price, $this->level_3_off);
            $v->level_4 = self::PriceCalc('*', $v->price, $this->level_4_off);
            $v->level_5 = self::PriceCalc('*', $v->price, $this->level_5_off);
            $v->twitter_money = $this->getTwitterMoneyBySku($v);
        }
        $res->sku_price_list = $sku_price;
        return $res;
    }

    //计算返利金额
    protected function getTwitterMoneyBySku($data) {
        $Percentage = intval(($data->original_price - $data->cost) / $data->original_price * 100); //百分比
        $temp_data = [];
        if ($Percentage >= 50) {
            $temp_data['t_money_black'] = self::PriceCalc('*', $data->original_price, 0.1);
            $temp_data['t_money_yellow'] = self::PriceCalc('*', $data->original_price, 0.15);
            $temp_data['t_money_dealer'] = self::PriceCalc('*', $data->original_price, 0.25);
        } else if ($Percentage >= 30 && $Percentage < 50) {
            $temp_data['t_money_black'] = self::PriceCalc('*', $data->original_price, 0.05);
            $temp_data['t_money_yellow'] = self::PriceCalc('*', $data->original_price, 0.1);
            $temp_data['t_money_dealer'] = self::PriceCalc('*', $data->original_price, 0.15);
        } else if ($Percentage < 30) {
            $temp_data['t_money_black'] = self::PriceCalc('*', $data->original_price, 0.03);
            $temp_data['t_money_yellow'] = self::PriceCalc('*', $data->original_price, 0.05);
            $temp_data['t_money_dealer'] = self::PriceCalc('*', $data->original_price, 0.1);
        }
        $temp_data['t_money'] = $temp_data['t_money_black'];
        $temp_data['t_staff_money'] = self::PriceCalc('*', $temp_data['t_money'], 0.5);
        if ($data->promotion_cost > 0) {
            foreach ($temp_data as &$v) {
                $v = self::PriceCalc('-', $v, $data->promotion_cost);
                if ($v < 0) {
                    $v = 0;
                }
            }
        }
        return $temp_data;
    }

    //科学计算
    public static function PriceCalc($symbol, $n1, $n2, $scale = '2') {
        $res = '';
        switch ($symbol) {
            case "+"://加法
                $res = bcadd($n1, $n2, $scale);
                break;
            case "-"://减法
                $res = bcsub($n1, $n2, $scale);
                break;
            case "*"://乘法
                $res = bcmul($n1, $n2, $scale);
                break;
            case "/"://除法
                $res = bcdiv($n1, $n2, $scale);
                break;
            case "%"://求余、取模
                $res = bcmod($n1, $n2, $scale);
                break;
        }
        return $res;
    }

    //****************************DB查询 废弃****************************
    //根据商品id,sku_number获得商品实际购买价格
    public function goodsListOld(&$list, $user_level, $user_id, $is_staff) {
        foreach ($list as &$v) {
            //获取正常售价(不包括活动)
            $this->getGoodsPrice($v, $user_level, $user_id, $is_staff);
        }
    }

    //获取计算价格
    public function getGoodsPriceOld(&$data, $user_level, $user_id, $is_staff) {
        //计算推客的常规购买价格和收益
        $goods_id = $data->id;
        $expire_num = CacheTools::getExpire('goods_price_exprie');
        $cache_key_name = 'goods_price'; //哈希组名
        $cache_name = 'goods_' . $goods_id;
        $list = Cache::tags($cache_key_name)->get($cache_name);

        if (true || empty($list)) {
            $list = $this->getPriceDataFromDb($goods_id);
            Cache::tags($cache_key_name)->put($cache_name, $list, $expire_num);
        }

        $temp_twitter_data = [];
        foreach ($list->sku_price_list as $tmv) {
            $temp_tmv = [];
            $temp_tmv['sku_number'] = $tmv->sku_number;
            $temp_tmv['twitter_money'] = $tmv->twitter_money;
            $temp_twitter_data[] = $temp_tmv;
        }
        $data->twitter_money_list = $temp_twitter_data;

        switch (intval($user_level)) {
            case 2:
            case 3:
                $data->price = $list->level_3;
                break;
            case 4:
                $data->price = $list->level_4;
                break;
            case 5:
                $data->price = $list->level_5;
                break;
        }

        if ($data->sku_list ?? false) {
            $temp_sku_list = [];

            foreach ($data->sku_list as $slv) {
                $temp_sku_list_s = $slv;
                foreach ($list->sku_price_list as $splv) {
                    if ($slv->sku_number == $splv->sku_number) {
                        switch (intval($user_level)) {
                            case 2:
                            case 3:
                                $temp_sku_list_s->price = $splv->level_3;
                                break;
                            case 4:
                                $temp_sku_list_s->price = $splv->level_4;
                                break;
                            case 5:
                                $temp_sku_list_s->price = $splv->level_5;
                                break;
                        }
                    }
                }
                $temp_sku_list[] = $temp_sku_list_s;
            }
            $data->sku_list = $temp_sku_list;
        }

        //活动价格查询,有活动价格则覆盖
        $spModel = new SpecialPriceModel();
        $temp_sp_data = $spModel->getPriceByGoodsId(
                $goods_id, 1, $user_id
        );

        $sp_info['group_buy'] = 0; //1表示有拼团
        $sp_info['sp_type'] = 0; //当前商品优先级最高的活动 0表示没有活动
        $sp_info['list'] = []; //当前商品所有可参加活动
        //需要设置活动
        if (!empty($temp_sp_data)) {

            //活动type=4是拼团,只需要给出标记,并不显示在商品详情上
            foreach ($temp_sp_data as $tsdk => $tsdv) {
                array_push($sp_info['list'], $tsdv->type);
                if ($tsdv->type == 4) {
                    $sp_info['group_buy'] = 1;
                    unset($temp_sp_data[$tsdk]);
                }
            }

            //取不是拼团的第一位
            if (!empty($temp_sp_data)) {
                $temp_sp_goods_data = reset($temp_sp_data);
                $sp_info['sp_type'] = $temp_sp_goods_data->type;
                if (floatval($temp_sp_goods_data->goods_original_price) > 0) {
                    $data->original_price = $temp_sp_goods_data->goods_original_price;
                }
                $data->price = $temp_sp_goods_data->goods_price;

                $temp_sku_list = [];
                foreach ($data->sku_list as $slv) {
                    $temp_sku_list_s = $slv;
                    //引用传值为什么有时不行
                    foreach ($temp_sp_data as $spdv) {
                        if ($slv->sku_number == $spdv->sku_number) {
                            switch (intval($user_level)) {
                                case 2:
                                case 3:
                                    $temp_sku_list_s->price = $spdv->sku_price_black;
                                    break;
                                case 4:
                                    $temp_sku_list_s->price = $spdv->sku_price_yellow;
                                    break;
                                case 5:
                                    $temp_sku_list_s->price = $spdv->sku_price_dealer;
                                    break;
                            }
                        }
                    }
                    $temp_sku_list[] = $temp_sku_list_s;
                }
                $data->sku_list = $temp_sku_list;

                $temp_twitter_money_list = [];
                foreach ($data->twitter_money_list as $tmlv) {
                    $t_t = $tmlv;
                    foreach ($temp_sp_data as $spdv) {
                        if ($slv->sku_number == $spdv->sku_number) {
                            $t_t['twitter_money']['t_money'] = $spdv->t_money;
                            $t_t['twitter_money']['t_money_black'] = $spdv->t_money_black;
                            $t_t['twitter_money']['t_money_yellow'] = $spdv->t_money_yellow;
                            $t_t['twitter_money']['t_money_dealer'] = $spdv->t_money_dealer;
                        }
                    }
                    $temp_twitter_money_list[] = $t_t;
                }
                $data->twitter_money_list = $temp_twitter_money_list;
            }
        }
        $data->sp_info = $sp_info;
    }

}
