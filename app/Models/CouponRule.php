<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class CouponRule extends Model {

    protected $table = 'nlsg_coupon_rule';

    public static function getListFromDb($params) {
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
            if(($params['goods_only']??0) == 1){
                $sql = $sql_2;
            }else{
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

    public function getList($params) {

        $cache_key_name = 'c_l_' . ($params['goods_id'] ?? 0) . '_' .
                ($params['get_all'] ?? 0) . '_' . ($params['page'] ?? 0) .
                '_' . ($params['size'] ?? 0);

        $expire_num = CacheTools::getExpire('coupon_rule_list');
        $res = Cache::get($cache_key_name);
        if (empty($res)) {
            $res = self::getListFromDb($params);
            Cache::add($cache_key_name, $res, $expire_num);
        }
        return $res;
    }

}
