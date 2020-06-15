<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

/**
 * Description of FreightTemplate
 *
 * @author wangxh
 */
class FreightTemplate extends Base {

    protected $table = 'nlsg_freight_template';

    public function listOfShop($flag) {
        $cache_key_name = 'freight_template_list';
        $expire_num = CacheTools::getExpire('freight_template_list');
        $now = date('Y-m-d H:i:s');

        $res = Cache::get($cache_key_name);
        if (empty($res)) {
            $res = self::whereIn('type', [2, 3])
                    ->where('status', '=', 1)
                    ->select(['id', 'type', 'name',
                        'admin_name', 'admin_phone',
                        'province', 'city', 'area', 'details',
                        'start_time', 'end_time'])
                    ->get();
            Cache::add($cache_key_name, $res, $expire_num);
        }

        $list = [];

        foreach ($res as $v) {
            if ($v->type == $flag && $v->start_time <= $now && $v->end_time >= $now) {
                $v->province_name = MallAddress::getNameById($v->province);
                $v->city_name = MallAddress::getNameById($v->city);
                $v->area_name = MallAddress::getNameById($v->area);
                unset($v->type, $v->start_time, $v->end_time);
                $list[] = $v;
            }
        }

        return $list;
    }

}
