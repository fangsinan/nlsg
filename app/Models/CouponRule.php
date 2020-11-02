<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class CouponRule extends Base
{

    protected $table = 'nlsg_coupon_rule';

    public function getList($params, $user_id)
    {
        $cache_key_name = 'coupon_rule_list';

        $expire_num = CacheTools::getExpire('coupon_rule_list');
        $res = Cache::get($cache_key_name);
        if (empty($res)) {
            $res = self::getListFromDbNew();
            Cache::put($cache_key_name, $res, $expire_num);
        }

        $now = time();
        $now_date = date('Y-m-d H:i:s', $now);

        //过滤掉已经结束的
        foreach ($res as $k => $v) {
            $v->can_use = 1;
            if ($v->get_end_time <= $now_date) {
                unset($res[$k]);
            }
        }

        if ($params['id_list'] ?? false) {
            foreach ($res as $k => $v) {
                if (!in_array($v->id, $params['id_list'])) {
                    unset($res[$k]);
                }
            }
        }

        //只返回可以开始领取的
        if (($params['only_begin'] ?? 0) == 1) {
            foreach ($res as $k => $v) {
                if ($v->get_begin_time >= $now_date) {
                    unset($res[$k]);
                }
            }
        }

        //只返回有库存的
        foreach ($res as $k => $v) {
            //infinite库存无限  1无限  0有限
            if (($params['only_stock'] ?? 0) == 1) {
                if ($v->infinite == 0 && $v->sotck <= $v->used_stock) {
                    unset($res[$k]);
                }
            }
        }

        //指定商品id
        if (($params['goods_id'] ?? false)) {
            $get_type = intval($params['get_type'] ?? 0);
            $goods_id = intval($params['goods_id']);
            //1 返回不限制商品 以及 商品可用的
            //2 返回商品可用的
            switch ($get_type) {
                case 1:
                    //过滤掉可用列表不包含商品的
                    foreach ($res as $k => $v) {
                        if (!empty($v->goods_list) && !empty($v->goods_list['can_use'])) {
                            $temp_goods_id_list = array_column($v->goods_list['can_use'], 'id');
                            if (array_search($goods_id, $temp_goods_id_list) === null) {
                                unset($res[$k]);
                            }
                        }
                    }
                    break;
                case 2:
                    //过滤掉不限制的
                    foreach ($res as $k => $v) {
                        if (empty($v->goods_list['can_use'])) {
                            unset($res[$k]);
                        }
                    }
                    break;
                default:
                    //排除掉不能用于该商品的
                    foreach ($res as $k => $v) {
                        if (!empty($v->goods_list) && !empty($v->goods_list['cant_use'])) {
                            $temp_goods_id_list = array_column($v->goods_list['cant_use'], 'id');
                            if (array_search($goods_id, $temp_goods_id_list) !== null) {
                                unset($res[$k]);
                            }
                        }
                    }
            }
        }

        //是否需要判断用户是否还能领取该优惠券
        if ($user_id == 0) {
            $get_list = [];
        } else {
            $cr_id = [];
            foreach ($res as $v) {
                $cr_id[] = $v->id;
            }

            $get_list = Coupon::where('user_id', '=', $user_id)
                ->whereIn('cr_id', $cr_id)
                ->select([
                    'cr_id',
                    DB::raw('max(created_at) as created_at'),
                    DB::raw('count(id) as counts')
                ])->groupBy('cr_id')->get();

            if ($get_list->isEmpty()) {
                $get_list = [];
            } else {
                $get_list = $get_list->toArray();
            }

        }

        //根据优惠券的restrict判断
        foreach ($res as $k => $v) {
            switch (intval($v->restrict)) {
                case 1:
                    foreach ($get_list as $glv) {
                        if ($v->id == $glv['cr_id']) {
                            $v->can_use = 0;
                        }
                    }
                    break;
                case 2:
                    foreach ($get_list as $glv) {
                        if ($v->id == $glv['cr_id'] && $glv['created_at'] >= date('Y-m-d')) {
                            $v->can_use = 0;
                        }
                    }
                    break;
                case 3:
                    foreach ($get_list as $glv) {
                        if ($v->id == $glv['cr_id'] && $v->hold_max_num >= $glv['counts']) {
                            $v->can_use = 0;
                        }
                    }
                    break;
            }
        }

        $temp_res = [];
        foreach ($res as $v) {
            $temp_res[] = $v;
        }
        return $temp_res;
    }

    //ORM重写
    public static function getListFromDbNew()
    {
        //查询所有当前时间没下线的规则
        $res = self::where('status', '=', 1)
            ->where('buffet', '=', 1)
            ->where('get_end_time', '>', date('Y-m-d H:i:s'))
            ->whereIn('use_type', [3])
            ->with(['sub_list', 'sub_list.goods_list'])
            ->select(['id', 'name', 'infinite', 'stock', 'used_stock',
                'price', 'restrict', 'full_cut',
                'get_begin_time', 'get_end_time',
                DB::raw('UNIX_TIMESTAMP(get_begin_time) as get_begin_timestamp'),
                DB::raw('UNIX_TIMESTAMP(get_end_time) as get_end_timestamp'),
                'past', 'use_type', 'remarks',
                'use_time_begin', 'use_time_end', 'created_at',
                'updated_at', 'hold_max_num'])
            ->get();

        foreach ($res as $k => $v) {
            $goods_list['can_use'] = [];
            $goods_list['cant_use'] = [];

            foreach ($v->sub_list as $vv) {
                //1可用  2不可用
                switch ($vv->use_type) {
                    case 1:
                        $goods_list['can_use'][] = $vv->goods_list;
                        break;
                    case 2:
                        $goods_list['cant_use'][] = $vv->goods_list;
                        break;
                }
            }
            $v->goods_list = $goods_list;
            unset($res[$k]->sub_list);
        }

        return $res;
    }

    //优惠券规则补充表
    public function sub_list()
    {
        return $this->hasMany('App\Models\CouponRuleSub', 'rule_id', 'id');
    }

    //**************************DB废弃**************************
    public static function getListFromDb($params)
    {
        $now = time();

        $field = 'r.id,r.`name`,r.infinite,r.stock,r.price,r.`restrict`,r.full_cut,'
            . 'r.get_begin_time,r.get_end_time,r.past,r.use_type,r.remarks,'
            . 'r.use_time_begin,r.use_time_end,r.have_sub,'
            . '(case when infinite = 1 then 1 when '
            . 'infinite = 0 and stock > 0 then 1 ELSE 0 END) can_use';

        $sql_1 = 'select ' . $field . ' from nlsg_coupon_rule r  ';
        $sql_2 = 'select ' . $field . ' from nlsg_coupon_rule r  ';

        $temp_where = 'where r.status = 1 and r.use_type = 3 and (r.get_begin_time <= ' .
            $now . ' or r.get_begin_time = 0) and (r.get_end_time >= ' .
            $now . ' or r.get_end_time = 0)';

        if (($params['show_zero_stock'] ?? 0) != 1) {
            //只显示能领取的
            $temp_where .= ' and (infinite = 1 or (infinite = 0 and stock > 0))';
        }

        if ($params['goods_id'] ?? false) {
            $goods_id = intval($params['goods_id']);
            $sql_2 .= ' left join nlsg_coupon_rule_sub rs on rs.rule_id = r.id ';
            $temp_where_2 = ' and ((rs.goods_id = '
                . $goods_id . ' and rs.use_type = 1) '
                . 'or '
                . '(rs.goods_id <> '
                . $goods_id . ' and rs.use_type = 2))';
        }

        $sql_1 .= $temp_where;
        $sql_2 .= $temp_where;
        if (isset($temp_where_2)) {
            $sql_2 .= $temp_where_2;
        }

        $sql_1 .= ' and r.have_sub = 1';
        $sql_2 .= ' and r.have_sub = 2';


        if ($params['goods_id'] ?? false) {
            if (($params['goods_only'] ?? 0) == 1) {
                $sql = $sql_2;
            } else {
                $sql = 'select * from (' . $sql_1 . ' union ' . $sql_2 . ') as a';
            }
        } else {
            $sql = $sql_1;
        }

        switch ($params['ob'] ?? '') {
            case 'id_asc':
                $sql .= ' order by id asc';
                break;
            case 'price_asc':
                $sql .= ' order by price asc,id desc';
                break;
            case 'price_desc':
                $sql .= ' order by price desc,id desc';
                break;
            case 'id_desc':
            default :
                $sql .= ' order by id desc';
        }

        if (($params['get_all'] ?? 0) != 1) {
            $sql .= ' limit ' . $params['size'] . ' offset ' .
                ($params['page'] - 1) * $params['size'];
        }
        $res = DB::select($sql);
        return $res;
    }

}
