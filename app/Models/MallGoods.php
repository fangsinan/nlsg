<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class MallGoods extends Model {

    protected $table = 'nlsg_mall_goods';

    public function getList($params, $user = []) {

        $list = $this->getListData($params);

        //获取商品所处的活动
        $agModel = new ActiveGroupGlModel();
        foreach ($list as $v) {
            $v->active_group_list = $agModel->getList([
                'goods_type' => 1, 'goods_id' => $v->id, 'simple' => 1
            ]);
        }

        //价格类
        $getPriceTools = new GetPriceTools();
        $getPriceTools->goodsList($list, $user['level'], $user['id'], $user['is_staff']);

        return $list;
    }

    protected function getListData($params) {
        $cache_key_name = 'goods_list'; //哈希组名
        //缓存放入 goods_list
        //名称购成  page_size_(get_sku)_ob_(ids_str)
        $cache_name_arr['page'] = $params['page'];
        $cache_name_arr['size'] = $params['size'];
        $cache_name_arr['get_sku'] = $params['get_sku'] ?? 0;
        $cache_name_arr['get_details'] = $params['get_details'] ?? 0;
        $cache_name_arr['ob'] = $params['ob'] ?? '';
        $params['cid'] = $params['cid'] ?? '';
        $cache_name_arr['cid'] = is_array($params['cid']) ?
                implode(',', $params['cid']) : $params['cid'];

        if ($params['ids_str'] ?? false) {
            $cache_name_arr['ids'] = is_array($params['ids_str']) ?
                    implode(',', $params['ids_str']) : $params['ids_str'];
        } else {
            $cache_name_arr['ids'] = '';
        }

        $cache_name = implode('_', $cache_name_arr);

        $expire_num = 3600;
        $list = Cache::tags($cache_key_name)->get($cache_name);

        if (empty($list)) {
            $list = $this->getListDataFromDb($params);
            Cache::tags($cache_key_name)->put($cache_name, $list, $expire_num);
        }
        return $list;
    }

    protected function getListDataFromDb($params) {

        $query = DB::table('nlsg_mall_goods as g')
                ->join('nlsg_mall_sku as s', 's.goods_id', '=', 'g.id')
                ->leftJoin('nlsg_mall_category as c', 'g.category_id', '=', 'c.id')
                ->where('g.status', '=', 2);

        if (!empty($params['ids_str'] ?? '')) {
            if (!is_array($params['ids_str'])) {
                $params['ids_str'] = explode(',', $params['ids_str']);
            }
            $query->whereIn('g.id', $params['ids_str']);
        }

        if (!empty($params['cid'] ?? '')) {
            if (!is_array($params['cid'])) {
                $params['cid'] = explode(',', $params['cid']);
            }
            $query->whereIn('g.category_id', $params['cid']);
        }

        switch ($params['ob'] ?? 'default') {
            case 'new_asc':
                $query->orderBy('g.ctime', 'asc');
                break;
            case 'new_desc':
                $query->orderBy('g.ctime', 'desc');
                break;
            case 'sales_asc':
                $query->orderBy('g.sales_num', 'asc');
                break;
            case 'sales_desc':
                $query->orderBy('g.sales_num', 'desc');
                break;
            case 'price_asc':
                $query->orderBy('g.price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('g.price', 'desc');
                break;
            case 'ids_str':
                //指定id就是指定排序
                if (!empty($params['ids_str'])) {
                    $query->orderByRaw('FIELD(g.id,' . implode(',', $params['ids_str']) . ') asc');
                }
                break;
            default:
                //综合 浏览量和收藏量排序
                //获取后台设置的推荐商品
                $recommend_goods_id = ConfigModel::getData(3);
                if (!empty($recommend_goods_id ?? '')) {
                    $recommend_goods_id = explode(',', $recommend_goods_id);
                    $recommend_goods_id = array_reverse($recommend_goods_id);
                    $recommend_goods_id = implode(',', $recommend_goods_id);
                    $query->orderByRaw('FIELD(g.id,' . $recommend_goods_id . ') asc');
                }
                $query->orderBy(DB::raw('view_num+collection_num'), 'desc');
        }

        $query->orderBy('g.id', 'desc');

        //当指定id情况下可能不需要分页  get_all = 1则返回所有
        if (($params['get_all'] ?? 0) !== 1) {
            $query->limit($params['size'])->offset(($params['page'] - 1) * $params['size']);
        }

        $field = ['g.id', 'g.name', 'g.subtitle', 'g.picture', 'g.original_price',
            'g.price', 'c.name as category', 'c.id as category_id',
            DB::raw('sum(s.stock) as stock')];

        //是否需要返回商品详情(包括详情)
        if (($params['get_details'] ?? 0) == 1) {
            $field[] = 'g.content';
        }

        $res = $query->groupBy('g.id')->select($field)->get();

        foreach ($res as $v) {
            if (($params['get_sku'] ?? 0) == 1) {
                $v->sku_list = MallSku::getList($v->id);
            }
            if (($params['get_details'] ?? 0) == 1) {
                $v->picture_list = MallPicture::getList($v->id);
                $v->tos_list = MallTosBind::getList($v->id);
            }
        }
        return $res->toArray();
    }

    //获取商品sku价格
    public function sku_price_list($id) {
        $res = DB::table(MallSku::$table)
                ->where('goods_id', '=', $id)
                ->where('status', '=', 1)
                ->select(['id', 'sku_number', 'price', 'original_price',
                    'cost', 'promotion_cost'])
                ->get();
        return $res;
    }

}
