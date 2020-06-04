<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;

/**
 * Description of MallGoods
 *
 * @author wangxh
 */
class MallGoods extends Model {

    protected $table = 'nlsg_mall_goods';

    public function getList($params, $get_sku = 0, $user = []) {

        $list = $this->getListData($params, $get_sku);

        //获取商品所处的活动
//        $agModel = new ActiveGroupGlModel();
//        foreach ($list as &$v) {
//            $v['active_group_list'] = $agModel->getList([
//                'goods_type' => 1, 'goods_id' => $v['id']
//            ]);
//        }
        //价格类
//        $getPriceTools = new GetPriceTools();
//        $getPriceTools->goodsList($list, $user['level'], $user['id'], $user['is_staff']);

        return $list;
    }

    protected function getListData($params, $get_sku = 0) {
        $now = time();

        if (0) {
            $cache_key_name = 'goods_list'; //哈希组名
            //缓存放入 goods_list
            //名称购成  page_size_(get_sku)_ob_(ids_str)
            $cache_name_arr['offset'] = $params['offset'];
            $cache_name_arr['limit'] = $params['limit'];
            $cache_name_arr['get_sku'] = $get_sku;
            $cache_name_arr['ob'] = $params['ob'];
            $params['cid'] = $params['cid'] ?? '';
            $cache_name_arr['cid'] = is_array($params['cid']) ?
                    implode(',', $params['cid']) : $params['cid'];

            $cache_name_arr['ids'] = is_array($params['ids_str']) ?
                    implode(',', $params['ids_str']) : $params['ids_str'];

            $cache_name = implode('_', $cache_name_arr);
            $expire_num = Cache::$goods_list_exprie;
            $cache_data = Cache::typeHash([
                        'flag' => 'get',
                        'key' => $cache_key_name,
                        'field' => $cache_name,
            ]);
            $cache_data = json_decode($cache_data, true);
        } else {
            $cache_data = [];
        }


        if (empty($cache_data) || $cache_data['expire'] <= $now) {
            $list = $this->getListDataFromDb($params, $get_sku);

            if (0) {
                $cache_data[$cache_name] = [
                    'expire' => $now + $expire_num,
                    'list' => $list
                ];

                Cache::typeHash([
                    'flag' => 'set',
                    'data' => $cache_data,
                    'key' => $cache_key_name,
                    'add' => 1
                ]);
            }
        } else {
            $list = $cache_data['list'];
        }

        return $list;
    }

    public function sku_list() {
//        return $this->hasMany('App\Models\MallSku', 'id', 'goods_id');
//        return $this->hasOne('App\Models\MallSku', 'goods_id', 'id');
        return $this->belongsToMany('App\Models\MallSku', 'id', 'goods_id');
    }

    protected function getListDataFromDb($params, $get_sku = 0) {

        if (0) {
            $goodsModel = new self();
            $skuModel = new MallSku();
            $categoryModel = new MallCategory();
//            $goods = DB::table(self::$table.' g')
//                    ->join(MallSku::$table.' s','g.id','=','s.goods_id')
//                    ->where('g.status',2)
//                    ->where('s.status',1)
//                    ->get();
//            $goods = $goodsModel->limit(2)
//                            ->offset(0)
//                            ->where('status', 2)
//                            ->whereIn('id', [231, 91, 98])
//                            ->get()->toArray();
//            $goods = MallGoods::find(98);
//            $goods = MallGoods::limit(2)->where('id', '>', 58)->get();
//            foreach($goods as &$v){
//                $v->sku_list = $v->sku_list();
//            }
//            $goods = $goods->toArray();
//            $res['goods'] = $goods;
//            $res['type'] = gettype($goods);
//            dd($res);
        }

        if (0) {
            //DB样例
            $query = DB::table('nlsg_mall_goods as g')
                    ->join('nlsg_mall_sku as s', 's.goods_id', '=', 'g.id')
                    ->leftJoin('nlsg_mall_category as c', 'g.category_id', '=', 'c.id')
                    ->where('g.id', '=', 98)
                    ->groupBy('g.id');
            $query->select('g.id', 'g.name', 'g.subtitle', 'g.picture', 'g.original_price',
                    'g.price', 'c.name as category', DB::raw('sum(s.stock) as stock'));
            $goods = $query->get();
        }


        if (1) {
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
                    $query->orderBy('FIELD(g.id,' . implode(',', $params['ids_str']) . ')', 'asc');
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

            $goods_list = $query
                            ->groupBy('g.id')
                            ->select(['g.id', 'g.name', 'g.subtitle', 'g.picture',
                                    'g.original_price', 'g.price', 'c.name as category',
                                    DB::raw('sum(s.stock) as stock')])
                            ->get()->toArray();
            dd($goods_list);
            exit();
        } else {

            $goodsModel = new self();
            $skuModel = new MallSkuModel();
            $categoryModel = new MallCategoryModel();

            $query = $goodsModel->alias('mg')
                            ->join($skuModel->getTableName . ' ms',
                                    'mg.id = ms.goods_id and ms.status = 1')
                            ->join($categoryModel->getTableName() . ' mc',
                                    'mg.category_id = mc.id', 'left')
                            ->where('mg.status', 2)->where('mg.can_sale', [1, 3], 'in');

            if (!empty($params['ids_str'])) {
                if (!is_array($params['ids_str'])) {
                    $params['ids_str'] = explode(',', $params['ids_str']);
                }
                $query->where('mg.id', $params['ids_str'], 'in');
            }

            if (!empty(params['cid'])) {
                if (!is_array($params['cid'])) {
                    $params['cid'] = explode(',', $params['cid']);
                }
                $query->where('mg.category_id', $params['cid'], 'in');
            }

            switch ($params['ob']) {
                case 'new':
                    $query->order('mg.ctime', 'desc')->order('mg.id', 'desc');
                    break;
                case 'sales':
                    $query->order('mg.sales_num', 'desc')->order('mg.id', 'desc');
                    break;
                case 'price_asc':
                    $query->order('mg.price', 'asc')->order('mg.id', 'desc');
                    break;
                case 'price_desc':
                    $query->order('mg.price', 'desc')->order('mg.id', 'desc');
                    break;
                case 'ids_str':
                    //指定id就是指定排序
                    $query->order('FIELD(mg.id,' . implode(',', $params['ids_str']) . ')', 'asc');
                    break;
                default:
                    //综合 浏览量和收藏量排序
                    //获取后台设置的推荐商品
                    $recommend_goods_id = ConfigModel::getData(3);

                    if (!empty($recommend_goods_id)) {
                        $recommend_goods_id = explode(',', $recommend_goods_id);
                        $recommend_goods_id = array_reverse($recommend_goods_id);
                        $recommend_goods_id = implode(',', $recommend_goods_id);
                        $query->order('FIELD(mg.id,' . $recommend_goods_id . ')', 'desc');
                    }

                    $query->order('(view_num+collection_num)', 'desc')->order('mg.id', 'desc');
            }

            $goods_field = 'mg.id,mg.name,mg.subtitle,mg.picture,'
                    . 'mg.original_price,mg.price,'
                    . 'sum(ms.stock) stock,mc.name category';

            //如果是详情,就获取商品图文详情
            if ($get_sku) {
                $goods_field .= ',mg.content';
            }

            if ($params['get_all'] != 1) {
                $query->limit(intval($params['offset']), intval($params['limit']));
            }

            $res = $query->group('mg.id')
                    ->field($goods_field)
                    ->all();

            if ($get_sku) {
                foreach ($res as $k => $v) {
                    $v->toArray();
                    $res[$k]['tos_list'] = $this->tos_bind_list($v['id']);
                    $res[$k]['picture_list'] = $this->picture_list($v['id']);
                    $res[$k]['sku_list'] = $this->sku_list($v['id']);
                }
            }

            return $res;
        }
    }

}
