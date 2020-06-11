<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class MallOrder extends Model {

    protected $table = 'nlsg_mall_order';

    //检擦用户在规定时间内参加过秒杀活动
    public function getUserSecKillOrder($params) {
        if (empty($params['user_id'])) {
            return [];
        }

//        DB::connection()->enableQueryLog();

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

        $list = $query->whereRaw('FIND_IN_SET(2,nmod.special_price_type)')
                ->where('nmo.status', '>', 1)
                ->where('nmo.is_stop', '=', 0)
                ->select(['nmod.sku_number'])
                ->get();


        $sku_list = [];
        foreach ($list as $v) {
            $sku_list[] = $v->sku_number;
        }
        return array_unique($sku_list);
    }

}
