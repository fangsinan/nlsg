<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class GetPriceTools extends Base
{

    public $level_3_off = 1;
    public $level_4_off = 1;
    public $level_5_off = 1;

    public function goodsList($list, $user_level, $user_id, $is_staff, $hide = 0)
    {
        foreach ($list as $v) {
            $this->getGoodsPrice($v, $user_level, $user_id, $is_staff, false, $hide);
        }
    }

    //获取计算价格
    public function getGoodsPrice($data, $user_level, $user_id, $is_staff = 0, $for_order = false, $hide = 0)
    {
        //计算推客的常规购买价格和收益
        if ($for_order) {
            $goods_id = $data['goods_id'];
            $data = (object)array();
        } else {
            $goods_id = $data->id;
        }

        $expire_num = CacheTools::getExpire('goods_price_expire');
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
        $sp_info['sp_type'] = 0; //当前商品优先级最高的活动 0表示没有活动
        $sp_info['begin_time'] = '';
        $sp_info['end_time'] = '';
        $sp_info['list'] = []; //当前商品所有可参加活动

        //需要设置活动
        if (!empty($temp_sp_data)) {
            //活动type=4是拼团 只需要给出标记,并不显示在商品详情上
            foreach ($temp_sp_data as $tsdk => $tsdv) {
                array_push($sp_info['list'], $tsdv->type);
                if ($tsdv->type == 4) {
                    $sp_info['group_buy']['group_buy_id'] = $tsdv->group_name;
                    $sp_info['group_buy']['price'] = $tsdv->goods_price;
                    $sp_info['group_buy']['num'] = $tsdv->group_num;
                    $sp_info['group_buy']['begin_time'] = $tsdv->begin_time;
                    $sp_info['group_buy']['end_time'] = $tsdv->end_time;
                    $sp_info['group_buy']['begin_timestamp'] = strtotime($tsdv->begin_time);
                    $sp_info['group_buy']['end_timestamp'] = strtotime($tsdv->end_time);
                    $sp_info['group_buy']['order_num'] = MallOrder::where('sp_id', $tsdv->id)
                        ->where('status', '>', 1)
                        ->count();

                    unset($temp_sp_data[$tsdk]);
                }
            }
            //取不是拼团的第一位
            if (!empty($temp_sp_data)) {
                $temp_sp_goods_data = reset($temp_sp_data);
                $sp_info['sp_type'] = $temp_sp_goods_data->type;
                $sp_info['begin_time'] = $temp_sp_goods_data->begin_time;
                $sp_info['end_time'] = $temp_sp_goods_data->end_time;
                if (floatval($temp_sp_goods_data->goods_original_price) > 0) {
                    $data->original_price = $temp_sp_goods_data->goods_original_price;
                }
                //列表和详情显示的价格  取第一个活动的价格
                $data->price = $temp_sp_goods_data->goods_price;
                $temp_sp_data = array_reverse($temp_sp_data);
                $temp_sku_list = [];
                foreach ($data->sku_list as $slv) {
                    $slv->sp_type = 0;
                    $temp_sku_list_s = $slv;
                    foreach ($temp_sp_data as $spdv) {
                        if ($hide == 0 && $slv->sku_number == $spdv->sku_number) {
                            $temp_sku_list_s->sp_type = $spdv->type;
                            if (intval($spdv->sku_original_price) !== 0) {
                                $temp_sku_list_s->original_price = $spdv->sku_original_price;
                            }
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
        $sp_info['group_buy'] = $this->emptyA2C($sp_info['group_buy']);
        if (empty($sp_info['begin_time'])) {
            $sp_info['begin_timestamp'] = 0;
        } else {
            $sp_info['begin_timestamp'] = strtotime($sp_info['begin_time']);
        }
        if (empty($sp_info['end_time'])) {
            $sp_info['end_timestamp'] = 0;
        } else {
            $sp_info['end_timestamp'] = strtotime($sp_info['end_time']);
        }
        $sp_info['now'] = time();
        $data->sp_info = $sp_info;

    }

    //计算推客的常规购买价格和收益
    public function getPriceDataFromDb($goods_id)
    {
        $res       = DB::table('nlsg_mall_goods')
            ->select(['id', 'price'])->find($goods_id);
        $sku_price = DB::table('nlsg_mall_sku')
            ->where('goods_id', '=', $goods_id)
            ->where('status', '=', 1)
            ->select([
                'id', 'sku_number', 'price', 'original_price', 'cost', 'promotion_cost',
                'level_price_off', 'twitter_price_off'
            ])
            ->get();

        $goods_level_price_off = 0;

        foreach ($sku_price as $v) {
            if ($v->level_price_off === 0) {
                $v->level_3 = self::PriceCalc('*', $v->price, $this->level_3_off);
                $v->level_4 = self::PriceCalc('*', $v->price, $this->level_4_off);
                $v->level_5 = self::PriceCalc('*', $v->price, $this->level_5_off);
            } else {
                $goods_level_price_off = 1;
                $v->level_3 = $v->level_4 = $v->level_5 = $v->price;
            }

            $v->twitter_money = $this->getTwitterMoneyBySku($v);
        }

        if ($goods_level_price_off === 0){
            $res->level_3 = self::PriceCalc('*', $res->price, $this->level_3_off);
            $res->level_4 = self::PriceCalc('*', $res->price, $this->level_4_off);
            $res->level_5 = self::PriceCalc('*', $res->price, $this->level_5_off);
        }else{
            $res->level_3 = $res->level_4 = $res->level_5 = $res->price;
        }

        $res->sku_price_list = $sku_price;
        return $res;
    }

    //计算返利金额
    protected function getTwitterMoneyBySku($data): array
    {
        if ($data->twitter_price_off === 1) {
            return [
                't_money'        => 0,
                't_money_black'  => 0,
                't_money_yellow' => 0,
                't_money_dealer' => 0,
                't_staff_money'  => 0,
            ];
        }

        $Percentage = intval(($data->original_price - $data->cost) / $data->original_price * 100); //百分比
        $temp_data  = [];
        if ($Percentage >= 50) {
            $temp_data['t_money_black']  = self::PriceCalc('*', $data->original_price, 0.1);
            $temp_data['t_money_yellow'] = self::PriceCalc('*', $data->original_price, 0.15);
            $temp_data['t_money_dealer'] = self::PriceCalc('*', $data->original_price, 0.25);
        } else if ($Percentage >= 30) {
            $temp_data['t_money_black']  = self::PriceCalc('*', $data->original_price, 0.05);
            $temp_data['t_money_yellow'] = self::PriceCalc('*', $data->original_price, 0.1);
            $temp_data['t_money_dealer'] = self::PriceCalc('*', $data->original_price, 0.15);
        } else {
            $temp_data['t_money_black']  = self::PriceCalc('*', $data->original_price, 0.03);
            $temp_data['t_money_yellow'] = self::PriceCalc('*', $data->original_price, 0.05);
            $temp_data['t_money_dealer'] = self::PriceCalc('*', $data->original_price, 0.1);
        }
        $temp_data['t_money']       = $temp_data['t_money_black'];
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
    public static function PriceCalc($symbol, $n1, $n2, $scale = '2')
    {
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
    public function goodsListOld(&$list, $user_level, $user_id, $is_staff)
    {
        foreach ($list as &$v) {
            //获取正常售价(不包括活动)
            $this->getGoodsPrice($v, $user_level, $user_id, $is_staff);
        }
    }

    //获取计算价格
    public function getGoodsPriceOld(&$data, $user_level, $user_id, $is_staff)
    {
        //计算推客的常规购买价格和收益
        $goods_id = $data->id;
        $expire_num = CacheTools::getExpire('goods_price_expire');
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

    //权益计算
    static function Income($is_show, $level, $user_id, $type, $column_id = 0, $works_id = 0, $goods_id = 0, $sku_number = 0)
    {
        if (empty($level)) { //等级为空
            //$UserInfo = self::GetLevelInfo ($user_id); //查询用户信息
            //未登录 默认级别 0
            //$level=self::GetLevel($UserInfo); //获取用户等级

            $level = User::getLevel($user_id);
        }
        if ($is_show) { //页面显示使用
            if ($level == 0) {
                $level = 2;
            }//未登录 或已过期  默认推客权益
        } else { //收益入账
            if ($level == 0) {
                return 0;
            } //没有收益 权益已过期用户
        }
        $price = 0;
        $time = time();
        switch ($type) {
            case 1://专栏
                /*
                (2 -3)(原价)*15%-促销成本
                4  35
                5  45*/
                $Info = Column::find($column_id)->toArray();
                //$Info=self::GetColumnInfo($author_id);
                if ($level == 2 || $level == 3) {
                    $price = self::PriceCalc('*', $Info['price'], 0.15);
                } else if ($level == 4) {
                    $price = self::PriceCalc('*', $Info['price'], 0.35);
                } else if ($level == 5) {
                    $price = self::PriceCalc('*', $Info['price'], 0.45);
                }

                $Symbol = substr($Info['promotion_cost'], 0, 1);
                if (in_array($Symbol, ['-', '+'])) {
                    $price = $price . $Info['promotion_cost'];
                } else {
                    $price = $price - $Info['promotion_cost'];
                }
                break;
            case 2://精品课
                /*
                (2-3)(原价)15%-促销成本
                4  35
                5  45*/
                //$Info=self::GetWorksInfo($author_id,$works_id);
                $Info = Works::find($works_id)->toArray();
                if ($level == 2 || $level == 3) {
                    $price = self::PriceCalc('*', $Info['price'], 0.15);
                } else if ($level == 4) {
                    $price = self::PriceCalc('*', $Info['price'], 0.35);
                } else if ($level == 5) {
                    $price = self::PriceCalc('*', $Info['price'], 0.45);
                }
                $Symbol = substr($Info['promotion_cost'], 0, 1);
                if (in_array($Symbol, ['-', '+'])) {
                    $price = $price . $Info['promotion_cost'];
                } else {
                    $price = $price - $Info['promotion_cost'];
                }
                break;
            case 5://新会员
                $vip_price = 360;
                /*
                1  30
                2  50*/
                if ($level == 1) {
                    $price = self::PriceCalc('*', $vip_price, 0.30);
                } else if ($level == 2) {
                    $price = self::PriceCalc('*', $vip_price, 0.50);
                } else {
                    $price = 0;
                }
                $price = $price - 0; //没有促销成本
                break;
            case 6:   //1980产品
                $vip_price = 1980;
                if ($level == 2) {
                    $price = self::PriceCalc('*', $vip_price, 0.30);
                } else {
                    $price = 0;
                }
                $price = $price - 0; //没有促销成本
                break;
        }
        if ($price < 0) {
            $price = 0; //防止负数
        }
        return $price;
    }

    /*1.服务商成交皇钻  皇钻收益的10%给服务商  (走线下打款)  服务商有效期内
    2.服务商成交的皇钻再发展皇钻 ，最后一次皇钻收入的5%给服务商
    皇钻：
    1.皇钻A成交皇钻B   皇钻B所有收入的5% ,补贴给A  走线上    7 8月份有效*/
    //服务商额外补贴5%
    public static function ServiceIncome($out_trade_no, $type, $ProfitPrice, $twitter_id, $orderdtl_id = 0)
    { //收益金额
        $now = time();
        $time = strtotime(date('Y-m-d', time())) + 86400;
        $UserInfo = User::find($twitter_id);
        $rst = true;
        if (!empty($UserInfo) && in_array($UserInfo['level'], [3, 4]) && $UserInfo['expire_time'] > $time) { //会员
            $ReferrerInfo = UserAttribution::select('referrer_user_id', 'referrer_user_level')->where('user_id', $twitter_id)->first();
            if ($ReferrerInfo) $ReferrerInfo = $ReferrerInfo->toArray();

            if (!empty($ReferrerInfo) && $ReferrerInfo['referrer_user_level'] != 5) { //排除服务商
                //5电商推客收益  6专栏推客收益  7精品课收益 8会员收益 9菩提沙画
                $ProfitPrice = self::PriceCalc('*', $ProfitPrice, 0.05);
                $subsidy_type = 1;  //当收益为5%(0.05) 时  subsidy_type为1
                $map = ['user_id' => $ReferrerInfo['referrer_user_id'], "type" => $type, "ordernum" => $out_trade_no,
                    'price' => $ProfitPrice, 'source_id' => $twitter_id, 'order_detail_id' => $orderdtl_id, 'subsidy_type' => 1];
                if ($type == 5) {
                    $PrdInfo = PayRecordDetailStay::where([
                        'ordernum' => $out_trade_no,
                        'user_id' => $map['user_id'],
                        'order_detail_id' => $orderdtl_id,
                        'type' => 5,
                    ])->first();
                    if (empty($PrdInfo)) {
                        return PayRecordDetailStay::create($map);
                    }
                } else {
                    $PrdInfo = PayRecordDetail::where([
                        'ordernum' => $map['ordernum'],
                        'user_id' => $map['user_id'],
                        'type' => $map['type'],
                    ])->first();
                    if (empty($PrdInfo)) {
                        return PayRecordDetail::create($map);
                    }
                }
            }
        }

        return $rst;

    }

}
