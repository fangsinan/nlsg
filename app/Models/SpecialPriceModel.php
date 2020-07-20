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
            if ($v->stock > 0 && ($v->use_stock >= $v->stock )) {
                unset($res[$k]);
            }
        }

        //配置
        //1:一次活动参加一次
        //2:一次活动一天参加一次
        //3:一次活动指定时间内参加一次
        $sec_kill_count_flag = ConfigModel::getData(9);
        if ($sec_kill_count_flag == 1) {
            $today_begin_time = $today_end_time = null;
        } elseif ($sec_kill_count_flag == 3) {
            $get_begin_end_time = ConfigModel::getData(10);
            if (!empty($get_begin_end_time)) {
                $get_begin_end_time = explode(',', $get_begin_end_time);
                $today_begin_time = $get_begin_end_time[0];
                $today_end_time = $get_begin_end_time[1];
            }
        }
        $temp_sec_flag = 0;

        //修改秒杀记录查询
        //获取用户今天的秒杀订单数据(秒杀一天一次)
        if ($user_id) {
            $oModel = new MallOrder();
            $sec_kill_list = $oModel->getUserSecKillOrderNew([
                'user_id' => $user_id,
                'begin_time' => $today_begin_time,
                'end_time' => $today_end_time,
            ]);
        } else {
            $sec_kill_list = [];
        }

        if (!empty($sec_kill_list)) {

            foreach ($res as $k => $v) {
                if ($v->type == 2 && in_array($v->id, $sec_kill_list)) {
                    $temp_sec_flag = 1;
                    unset($res[$k]);
                }
            }

            //如果用户今天有秒杀订单,则过滤已经秒杀过的特价信息
//            foreach ($res as $k => $v) {
//                if ($v->type == 2 && in_array($v->sku_number, $sec_kill_list)) {
//                    $temp_sec_flag = 1;
//                    unset($res[$k]);
//                }
//            }
            //如果是所有商品一天一次  则过滤掉所有秒杀信息
//            if ($sec_kill_count_flag == 2 && $temp_sec_flag == 1) {
//                foreach ($res as $k => $v) {
//                    if ($v->type == 2) {
//                        unset($res[$k]);
//                    }
//                }
//            }
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
                ->whereIn('type', [1, 2, 4])
                ->groupBy('type')
                ->orderByRaw('FIELD(type,' . $sp_type_order . ') asc')
                ->orderBy('id', 'desc')
                ->select([
                    'id', 'goods_type', 'goods_id', 'goods_original_price',
                    'goods_price', 'sku_number', 'stock', 'use_stock',
                    'sku_original_price', 'sku_price',
                    'sku_price_black', 'sku_price_yellow',
                    'group_price', 'sku_price_dealer',
                    'is_set_t_money', 't_money', 't_money_black',
                    't_money_yellow', 't_money_dealer',
                    'begin_time', 'end_time', 'type',
                    'use_coupon', 'group_name', 'group_num_type', 'group_num',
                    'freight_free', 'freight_free_line'
                ])
                ->get();

        return $list;
    }

    public function getSecList($flag = 1) {
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
                        'nsp.goods_price', 'nsp.begin_time', 'nsp.end_time',
                        DB::raw('unix_timestamp(begin_time) as begin_timestamp'),
                        DB::raw('unix_timestamp(end_time) as end_timestamp')])
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
        if ($flag == 1) {
            return $sec_date_list;
        } else {
            $temp_res = [];
            foreach ($sec_date_list as $k => $v) {
                $t = [];
                $t['time'] = $k;

                $t['data'] = $v;
                $temp_res[] = $t;
            }
            return $temp_res;
        }
    }

    //首页秒杀推荐
    public function homeSecList() {
        $list = $this->getSecList();

        $now = date('Y-m-d H:i:00');
        $res = [];
        foreach ($list as $k => $v) {
            if ($k >= $now) {
                //$temp['time'] = $k;
                //$temp['list'] = $v;
                //$res = $temp;
                //$res[] = $v;
                //break;
                $res = array_merge($res,$v);
            }
        }
        return array_slice($res,0,2);
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
        $group_buy_id_list = [];
        foreach ($res as $v) {
            $group_buy_id_list[] = $v->group_name;
            $v->user_count = 0;
            $v->order_user = [];
        }

        $order_list = $this->groupBuyListCounts($group_buy_id_list);

        foreach ($res as $v) {
            foreach ($order_list as $vv) {
                if ($v->group_buy_id == $vv->group_buy_id) {
                    $v->user_count = $vv->order_count;
                    $v->order_user = $vv->headimg_list;
                }
            }
        }

        return $res;
    }

    public function groupBuyListCounts($id, $limit = 5) {

        $query = DB::table('nlsg_mall_group_buy_list as gbl')
                ->join('nlsg_mall_order as nmo', 'gbl.order_id', '=', 'nmo.id')
                ->join('nlsg_user as nuser', 'gbl.user_id', '=', 'nuser.id')
                ->whereIn('gbl.group_name', $id)
                ->where('nmo.is_stop', '=', 0)
                ->where('nmo.is_del', '=', 0);

        $query_temp = clone $query;

        $user_list = $query_temp->select(['gbl.group_name as group_buy_id', 'gbl.user_id', 'nuser.headimg'])
                ->limit($limit)
                ->get();

        $order_list = $query->select([
                    'gbl.group_name as group_buy_id', DB::raw('count(gbl.id) as order_count')
                ])
                ->groupBy('gbl.group_buy_id')
                ->get();

        foreach ($order_list as $v) {
            $v->user_list = [];
            $v->headimg_list = [];
            foreach ($user_list as $vv) {
                if ($v->group_buy_id == $vv->group_buy_id) {
                    $v->headimg_list[] = $vv->headimg;
                    unset($vv->group_buy_id);
                    $v->user_list[] = $vv;
                }
            }
        }
        return $order_list;
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
                    ->select(['nsp.group_name as group_buy_id', 'nsp.goods_id', 'nmg.name',
                        'nmg.subtitle', 'nmg.original_price', 'group_num', 'group_price',
                        'nsp.begin_time', 'nsp.end_time', 'group_name'])
                    ->groupBy('nsp.group_name')
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
        //return array_values($res->toArray());
        $temp_res = [];
        foreach ($res as $v) {
            $temp_res[] = $v;
        }
        return $temp_res;
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
