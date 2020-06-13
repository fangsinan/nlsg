<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class SpecialPriceModel extends Base {

    protected $table = 'nlsg_special_price';

    public function getPriceByGoodsId($id, $goods_type, $user_id) {
        $now = time();
        $now_date = date('Y-m-d H:i:s', $now);
        $today_begin_time = date('Y-m-d', $now);
        $today_end_time = date('Y-m-d 23:59:59', $now);

        //获取所谓未结束活动信息
        $res = $this->getSpData($id, $goods_type);

        //筛选时间和库存
        foreach ($res as $k => $v) {
            if ($v->begin_time > $now_date || $v->end_time < $now_date) {
                unset($res[$k]);
            }

            //stock=0表示无库存限制    use_stock已经使用的库存
            //todo redis
            if ($v->stock > 0 && ($v->use_stock >= $v->stock )) {
                unset($res[$k]);
            }
        }

        //配置
        $sec_kill_count_flag = 2; //1 一個商品一天一次  2所有商品一天一次
        $temp_sec_flag = 0;

        //获取用户今天的秒杀订单数据(秒杀一天一次)
        if ($user_id) {
            $oModel = new MallOrder();
            $sec_kill_list = $oModel->getUserSecKillOrder([
                'user_id' => $user_id,
                'begin_time' => $today_begin_time,
                'end_time' => $today_end_time,
            ]);
        } else {
            $sec_kill_list = [];
        }

        if (!empty($sec_kill_list)) {
            //如果用户今天有秒杀订单,则过滤已经秒杀过的特价信息
            foreach ($res as $k => $v) {
                if ($v->type == 2 && in_array($v->sku_number, $sec_kill_list)) {
                    $temp_sec_flag = 1;
                    unset($res[$k]);
                }
            }

            //如果是所有商品一天一次  则过滤掉所有秒杀信息
            if ($sec_kill_count_flag == 2 && $temp_sec_flag == 1) {
                foreach ($res as $k => $v) {
                    if ($v->type == 2) {
                        unset($res[$k]);
                    }
                }
            }
        }

        return $res->toArray();
    }

    public function getSpData($id, $goods_type) {
        $expire_num = CacheTools::getExpire('goods_sp_list_exprie');
        $cache_key_name = 'goods_sp_list_' . $goods_type; //哈希组名
        //缓存放入 goods_list
        //名称购成  page_size_(get_sku)_ob_(ids_str)
        $cache_name = 'goods_' . $id;

        $list = Cache::tags($cache_key_name)->get($cache_name);

        if (empty($list)) {
            $list = $this->getSpDataFromDb($id, $goods_type);
            Cache::tags($cache_key_name)->put($cache_name, $list, $expire_num);
        }
        return $list;
    }

    public function getSpDataFromDb($id, $goods_type) {
        //config  活动优先顺序
        $sp_type_order = ConfigModel::getData(2);

        $list = DB::table('nlsg_special_price')
                ->where('goods_id', '=', $id)
                ->where('goods_type', '=', $goods_type)
                ->where('status', '=', 1)
                ->where('end_time', '>', date('Y-m-d H:i:s'))
                ->whereIn('type', [1, 2, 3, 4])
                ->groupBy('type')
                ->orderByRaw('FIELD(type,' . $sp_type_order . ') asc')
                ->orderBy('id', 'desc')
                ->select([
                    'id', 'goods_type', 'goods_id', 'goods_original_price',
                    'goods_price', 'sku_number', 'stock', 'sku_original_price',
                    'sku_price', 'sku_price_black', 'sku_price_yellow', 'group_price',
                    'sku_price_dealer', 'is_set_t_money', 't_money', 't_money_black',
                    't_money_yellow', 't_money_dealer', 'begin_time', 'end_time',
                    'type', 'use_coupon', 'group_name', 'group_num_type', 'group_num'
                ])
                ->get();

        return $list;
    }

    public function getSecList() {
        $cache_key_name = 'set_kill_list';
        $expire_num = CacheTools::getExpire('set_kill_list');

        $sec_date_list = Cache::get($cache_key_name);
        if (empty($sec_date_list)) {
            $sec_date_list = $this->getSecDateList();
            $res = self::from('nlsg_special_price as nsp')
                    ->where('nsp.type', '=', 2)
                    ->where('nsp.status', '=', 1)
                    ->whereIn('begin_time', $sec_date_list)
                    ->join('nlsg_mall_goods as nmg', function($join) {
                        $join->on('nsp.goods_id', '=', 'nmg.id')
                        ->where('nmg.status', '=', 2);
                    })
                    ->select(['nsp.goods_id', 'nmg.name', 'nmg.subtitle',
                        'nsp.goods_original_price',
                        'nmg.original_price',
                        'nsp.goods_price',
                        'nsp.begin_time', 'nsp.end_time'])
                    ->get();

            $sec_date_list = array_fill_keys($sec_date_list, []);
            foreach ($sec_date_list as $k => &$v) {
                foreach ($res as $vv) {
                    if ($k == $vv->begin_time) {
                        $v[] = $vv;
                    }
                }
            }
            Cache::add($cache_key_name, $sec_date_list, $expire_num);
        }
        return $sec_date_list;
    }

    //todo 秒杀列表
    public function secList() {
        
    }

    //todo 拼团列表
    public function groupList() {
        
    }

    //首页秒杀推荐
    public function homeSecList() {

        $list = $this->getSecList();
        $now = date('Y-m-d H:i:00');
        $res = [];
        foreach ($list as $k => $v) {
            if ($k >= $now) {
                $temp['time'] = $k;
                $temp['list'] = $v;
                $res = $temp;
                break;
            }
        }
        return $res;
    }

    //获取秒杀时间分组
    public function getSecDateList() {
        $now = Date('Y-m-d H:i:00');
        $list_pass = self::where('begin_time', '<', $now)
                        ->where('type', '=', 2)
                        ->orderBy('begin_time', 'desc')
                        ->select(DB::raw('FROM_UNIXTIME(UNIX_TIMESTAMP(begin_time),\'%Y-%m-%d %H:%i:00\') as time'))
                        ->limit(2)
                        ->get()->toArray();
        $list = self::where('begin_time', '>=', $now)
                        ->where('type', '=', 2)
                        ->orderBy('begin_time', 'asc')
                        ->select(DB::raw('FROM_UNIXTIME(UNIX_TIMESTAMP(begin_time),\'%Y-%m-%d %H:%i:00\') as time'))
                        ->get()->toArray();

        $res = array_unique(array_merge(array_column($list_pass, 'time'), array_column($list, 'time')));
        sort($res);
        return $res;
    }

    //首页拼团推荐
    public function homeGroupList() {
        $res = $this->homeGroupListFromDb(2);
        return $res;
    }

    //拼团首页
    public function groupBuyList() {
        $res = $this->homeGroupListFromDb(0);
        foreach($res as $v){
            $v->order_count = 1;
            $v->user_count = 4;
            $v->order_user = [
                '1.jpg',
                '1.jpg',
                '1.jpg',
                '1.jpg',
            ];
        }
        return $res;
    }

    public function homeGroupListFromDb($limit = 0) {
        $cache_key_name = 'home_group_list';
        $expire_num = CacheTools::getExpire('home_group_list');
        $now = date('Y-m-d H:i:s');

        $res = Cache::get($cache_key_name);
        if (empty($res)) {
            $res = self::from('nlsg_special_price as nsp')
                    ->where('nsp.type', '=', 4)
                    ->where('nsp.status', '=', 1)
                    ->where('end_time', '>=', $now)
                    ->join('nlsg_mall_goods as nmg', function($join) {
                        $join->on('nsp.goods_id', '=', 'nmg.id')
                        ->where('nmg.status', '=', 2);
                    })
                    ->select(['nsp.goods_id', 'nmg.name', 'nmg.subtitle',
                        'nmg.original_price', 'group_num', 'group_price',
                        'nsp.begin_time', 'nsp.end_time', 'group_name'])
                    ->get();
            Cache::add($cache_key_name, $res, $expire_num);
        }

        $key_array = [];
        foreach ($res as $k => $v) {
            if ($v->begin_time >= $now || $v->end_time <= $now) {
                unset($res[$k]);
            } else {
                array_push($key_array, $k);
            }
        }

        if ($limit) {
            $key_array = array_rand($key_array, 2);
            foreach ($res as $k => $v) {
                if (!in_array($k, $key_array)) {
                    unset($res[$k]);
                }
            }
        }
        return $res;
    }

    public function tempSetSecTime() {
        $b_date = '2020-06-11 09:00:00';
        $list = self::where('type', '=', 2)
                ->select(['id', 'begin_time', 'end_time'])
                ->get();
        foreach ($list as $v) {
            $long = rand(1, 20);
            $begin_time = $b_date;
            $end_time = date('Y-m-d H:i:s', strtotime("$b_date + $long minute") - 1);
            $temp = self::find($v->id);
            $temp->begin_time = $begin_time;
            $temp->end_time = $end_time;
            $temp->save();
            $b_date = date('Y-m-d H:i:s', strtotime("$end_time + $long minute +1 second"));
        }
    }

}
