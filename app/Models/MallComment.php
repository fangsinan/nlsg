<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

/**
 * 商品评论表
 */
class MallComment extends Base
{

    protected $table = 'nlsg_mall_comment';

    public static function getListFromDb($params)
    {
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

    public static function getDataByGidPid(&$list, $goods_id, $pid = 0, $page = 1, $size = 10)
    {

        $now_date = date('Y-m-d H:i:s');
        $query = DB::table('nlsg_mall_comment as nmc')
            ->join('nlsg_user as nu', 'nmc.user_id', '=', 'nu.id')
            ->leftJoin('nlsg_user as na', 'nmc.reply_user_id', '=', 'na.id')
            ->leftJoin('nlsg_mall_sku as sku', 'nmc.sku_number', '=', 'sku.sku_number')
            ->leftJoin('nlsg_vip_user as vu', function ($query) use ($now_date) {
                $query->on('vu.user_id', '=', 'nu.id')
                    ->where('vu.status', '=', 1)
                    ->where('vu.is_default', '=', 1)
                    ->where('vu.start_time', '<', $now_date)
                    ->where('vu.expire_time', '>', $now_date);
            })
            ->select(['nmc.id', 'nu.id as user_id', 'nu.headimg', 'nu.nickname',
                'nu.level', 'nu.expire_time', 'nmc.content', 'nmc.pid', 'vu.level as vu_level', DB::raw('"" as level_name'),
                DB::raw('FROM_UNIXTIME(UNIX_TIMESTAMP(nmc.created_at),\'%Y-%m-%d\') as created_at'),
                'nmc.goods_id', 'nmc.sku_number', 'nmc.star', 'nmc.reply_comment',
                'nmc.replied_at', 'nmc.reply_user_id', 'nmc.picture',
                'na.nickname as reply_nickname', 'sku.id as sku_id']);

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
            switch ($v->vu_level) {
                case 1:
                    $v->level = 101;
                    break;
                case 2:
                    $v->level = 102;
            }

            switch ($v->level) {
                case 1:
                    $v->level_name = '会员';
                    break;
                case 2:
                    $v->level_name = '推客';
                    break;
                case 3:
                    $v->level_name = '黑钻';
                    break;
                case 4:
                    $v->level_name = '皇钻';
                    break;
                case 5:
                    $v->level_name = '代理商';
                    break;
                case 101:
                    $v->level_name = '幸福大使';
                    break;
                case 102:
                    $v->level_name = '钻石';
                    break;
                default:
                    $v->level_name = '';
            }
            $list[$k]->sku_value = MallSkuValue::getListBySkuNum($v->sku_number);
            if ($v->picture) {
                $list[$k]->picture = explode(',', $v->picture);
            } else {
                $list[$k]->picture = [];
            }
            $temp_data = [];
            self::getDataByGidPid($temp_data, $goods_id, $v->id);
            $list[$k]->list = $temp_data;
        }
    }

    public function getList($params)
    {
        if (empty($params['goods_id'])) {
            return ['code' => false, 'msg' => '参数错误'];
        }
        $params['size'] = $params['size'] ?? 4;
        $params['page'] = $params['page'] ?? 1;

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

    public function getComment($comment_id, $user)
    {
        $data = MallComment::where('user_id', '=', $user['id'])
            ->select(['id', 'content', 'picture', 'star', 'status', 'issue_type'])
            ->find($comment_id);

        if (empty($data)) {
            return ['code' => false, 'msg' => '参数错误'];
        }

        $order_data = MallOrderDetails::where('comment_id', '=', $comment_id)
            ->with(['goodsInfo', 'skuInfo', 'skuInfo.sku_value_list'])
            ->select(['goods_id', 'sku_number'])
            ->first();

        $data['info'] = $order_data;

        if (empty($data->picture)) {
            $data->picture = [];
        } else {
            $data->picture = explode(',', $data->picture);
        }

        if (empty($data->issue_type)) {
            $data->issue_type = [];
        } else {
            $data->issue_type = explode(',', $data->issue_type);
        }

        return $data;
    }

}
