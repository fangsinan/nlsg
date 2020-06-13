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

        $expire_num = CacheTools::getExpire('get_list');
        $list = Cache::tags($cache_key_name)->get($cache_name);
        if (empty($list)) {
            $list = $this->getListDataFromDb($params);
            Cache::tags($cache_key_name)->put($cache_name, $list, $expire_num);
        }
        return $list;
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

    //***************************ORM重写***************************************

    public function getListDataFromDb($params) {

//        DB::connection()->enableQueryLog();

        $query = MallGoods::where('status', '=', 2);
        if (!empty($params['ids_str'] ?? '')) {
            if (!is_array($params['ids_str'])) {
                $params['ids_str'] = explode(',', $params['ids_str']);
            }
            $query->whereIn('id', $params['ids_str']);
        }
        if (!empty($params['cid'] ?? '')) {
            if (!is_array($params['cid'])) {
                $params['cid'] = explode(',', $params['cid']);
            }
            $query->whereIn('category_id', $params['cid']);
        }
        switch ($params['ob'] ?? 'default') {
            case 'new_asc':
                $query->orderBy('ctime', 'asc');
                break;
            case 'new_desc':
                $query->orderBy('ctime', 'desc');
                break;
            case 'sales_asc':
                $query->orderBy('sales_num', 'asc');
                break;
            case 'sales_desc':
                $query->orderBy('sales_num', 'desc');
                break;
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'ids_str':
                //指定id就是指定排序
                if (!empty($params['ids_str'])) {
                    $query->orderByRaw('FIELD(id,' . implode(',', $params['ids_str']) . ') asc');
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
                    $query->orderByRaw('FIELD(id,' . $recommend_goods_id . ') asc');
                }
                $query->orderBy(DB::raw('view_num+collection_num'), 'desc');
        }
        $query->orderBy('id', 'desc');
        //当指定id情况下可能不需要分页  get_all = 1则返回所有
        if (($params['get_all'] ?? 0) !== 1) {
            $query->limit($params['size'])->offset(($params['page'] - 1) * $params['size']);
        }

        $select_field = ['id', 'name', 'subtitle', 'picture', 'original_price', 'price', 'category_id'];

        //是否需要返回商品详情(包括详情)
        if (($params['get_details'] ?? 0) == 1) {
            $select_field[] = 'content';
        }

        $query->select($select_field);

        //预查询拼接
        $with_query = [];
        if (($params['get_details'] ?? 0) == 1) {
            $with_query[] = 'tos_bind_list';
            $with_query[] = 'tos_bind_list.tos';
            $with_query[] = 'picture_list';
        }
        if (($params['get_sku'] ?? 0) == 1) {
            $with_query[] = 'sku_list';
            $with_query[] = 'sku_list.sku_vavlue_list';
        }

        $with_query[] = 'category_list';

        if (!empty($with_query)) {
            $query->with($with_query);
        }

        $res = $query->get();

//        dd(DB::getQueryLog());
        return $res;
    }

    public function sku_list() {
        return $this->hasMany('App\Models\MallSku', 'goods_id', 'id')
                        ->where('status', '=', 1)
                        ->select(['id', 'goods_id', 'sku_number', 'picture',
                            'original_price', 'price', 'stock']);
    }

    public function tos_bind_list() {
        return $this->hasMany('App\Models\MallTosBind', 'goods_id', 'id')
                        ->select(['goods_id', 'tos_id']);
    }

    public function picture_list() {
        return $this->hasMany('App\Models\MallPicture', 'goods_id', 'id')
                        ->select(['url', 'is_main', 'is_video', 'duration', 'goods_id'])
                        ->where('status', '=', 1)
                        ->orderBy('is_video', 'desc')
                        ->orderBy('is_main', 'desc')
                        ->orderBy('rank', 'asc')
                        ->orderBy('id', 'asc');
    }

    public function category_list() {
        return $this->hasOne('App\Models\MallCategory', 'id', 'category_id')
                        ->select(['id', 'name'])
                        ->where('status', '=', 1);
    }

    /**
     * 首页好物推荐
     * @param $ids 相关作品id
     * @return bool
     */
    public function getIndexGoods($ids)
    {
        $lists= MallGoods::query()
            ->select('id','name','picture','original_price')
            ->whereIn('id',$ids)
            ->orderBy('created_at','desc')
            ->take(10)
            ->get()
            ->toArray();
        return $lists;
    }

}
