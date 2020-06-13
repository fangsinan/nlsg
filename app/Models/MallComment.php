<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

/**
 * 商品评论表
 */
class MallComment extends Base {

    protected $table = 'nlsg_mall_comment';

    public static function getListFromDb($params) {
        $goods_id = intval($params['goods_id']);
        $pid = intval($params['pid'] ?? 0);

        $list = [];
        self::getDataByGidPid($list, $goods_id, $pid, $params['page'], $params['size']);

        $res['count'] = DB::table('nlsg_mall_comment')
                ->where('goods_id', '=', $goods_id)
                ->where('pid', '=', 0)
                ->where('status', '=', 1)
                ->count();
        $res['list'] = $list;

        return $res;
    }

    public static function getDataByGidPid(&$list, $goods_id, $pid = 0, $page = 1, $size = 10) {

        $query = DB::table('nlsg_mall_comment as nmc')
                ->join('nlsg_user as nu', 'nmc.user_id', '=', 'nu.id')
                ->leftJoin('nlsg_user as na', 'nmc.reply_user_id', '=', 'na.id')
                ->leftJoin('nlsg_mall_sku as sku', 'nmc.sku_number', '=', 'sku.sku_number')
                ->select(['nmc.id', 'nu.id as user_id', 'nu.headimg', 'nu.nick_name',
            'nu.level', 'nu.expire_time', 'nmc.content', 'nmc.ctime', 'nmc.pid',
            'nmc.goods_id', 'nmc.sku_number', 'nmc.star', 'nmc.reply_comment',
            'nmc.reply_time', 'nmc.reply_user_id',
            'na.nick_name as reply_nick_name', 'sku.id as sku_id']);

        $query->where([
            ['nmc.goods_id', '=', $goods_id],
            ['nmc.status', '=', 1],
            ['nmc.pid', '=', $pid]
        ]);

        if ($pid == 0) {
            $query->orderBy('rank', 'asc');
            $query->limit($size)->offset(($page - 1) * $size);
        }

        $query->orderBy('nmc.star', 'desc')->orderBy('nmc.id', 'desc');

        $list = $query->get();

        foreach ($list as $k => $v) {
            $list[$k]->sku_value = MallSkuValue::getListBySkuNum($v->sku_number);
            $temp_data = [];
            self::getDataByGidPid($temp_data, $goods_id, $v->id);
            $list[$k]->list = $temp_data;
        }
    }

    public function getList($params) {

        if (empty($params['goods_id'])) {
            return ['code' => false, 'msg' => '参数错误'];
        }

        $cache_key_name = 'mall_comment_goods_' . $params['goods_id'] . '_'
                . $params['size'] . '_' . $params['page'];

        $expire_num = CacheTools::getExpire('mall_comment_list');
        $res = Cache::get($cache_key_name);
        if (empty($res)) {
            $res = self::getListFromDb($params);
            Cache::add($cache_key_name, $res, $expire_num);
        }
        return $res;
    }

}
