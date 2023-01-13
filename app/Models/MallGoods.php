<?php

namespace App\Models;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MallGoods extends Base
{

    protected $table = 'nlsg_mall_goods';

    public function getList($params, $user = [], $cache = true, $hide = 0)
    {
        $list = $this->getListData($params, $cache);

        //收藏
        $col_list = Collection::getGoodsColByUid($user['id'] ?? 0);

        $count_list = count($list);

        foreach ($list as $v) {
            if (in_array($v->id, $col_list)) {
                $v->collect = 1;
            } else {
                $v->collect = 0;
            }
            $v->stock = $this->getGoodsAllStock($v->id);
            foreach ($v->sku_list as $vv) {
                $vv->stock = $this->getGoodsAllStock($v->id, $vv->id);
                $temp_id_arr = [];
                foreach ($vv->sku_value_list as $sv) {
                    $temp_id_arr[] = $sv->id;
                }
                $vv->id_arr = $temp_id_arr;
            }
            if (($params['get_details'] ?? 0) == 1) {
                $v->service_description = $this->mallServiceDescription();
                $v->buyer_reading = $this->buyerReading();
            }
            if ($count_list == 1) {
                //详情时候获取一条评论
                $mcModel = new MallComment();
                $v->comment_list = $mcModel->getList(['goods_id' => $v->id, 'page' => 1, 'size' => 1, 'for_goods_info' => 1]);
            } else {
                $v->comment_list = new class {
                };
            }
        }

        //获取商品所处的活动
        $agModel = new ActiveGroupGlModel();
        foreach ($list as $v) {
            $temp_gl = $agModel->getList([
                'goods_type' => 1, 'goods_id' => $v->id, 'simple' => 1
            ]);
            $v->active_group_list = array_values($temp_gl);
        }

        //价格类
        $getPriceTools = new GetPriceTools();
        $getPriceTools->goodsList(
            $list,
            $user['true_level'] ?? 0,
            $user['id'] ?? 0,
            $user['is_staff'] ?? 0,
            $hide
        );

        //临时 秒杀放第一个
        foreach ($list as $k => $v) {
            $temp_sku_list = [];
            foreach ($v->sku_list as $vv) {
                if ($vv['sp_type'] == 2) {
                    array_push($temp_sku_list, $vv);
                }
            }
            foreach ($v->sku_list as $vv) {
                if ($vv['sp_type'] != 2) {
                    array_push($temp_sku_list, $vv);
                }
            }
            unset($list[$k]->sku_list);
            $list[$k]->sku_list = $temp_sku_list;
        }


        return $list;
    }

    protected function getListData($params, $cache = true)
    {
        $cache_key_name = 'goods_list'; //哈希组名
        //缓存放入 goods_list
        $cache_name_arr['page'] = $params['page'] ?? 1;
        $cache_name_arr['size'] = $params['size'] ?? 10;
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
        $cache_name_arr['zone_id'] = $params['zone_id'] ?? 0;
        $cache_name_arr['get_all'] = $params['get_all'] ?? 0;
        $cache_name = implode('_', $cache_name_arr);
        $expire_num = CacheTools::getExpire('get_list');
        $list = Cache::tags($cache_key_name)->get($cache_name);
        if (empty($list)) {
            $list = $this->getListDataFromDb($params);
            if ($cache) {
                Cache::tags($cache_key_name)->put($cache_name, $list, $expire_num);
            }
        }
        return $list;
    }

    //获取商品sku价格
    public function sku_price_list($id)
    {
        return DB::table(MallSku::$table)
            ->where('goods_id', '=', $id)
            ->where('status', '=', 1)
            ->select(['id', 'sku_number', 'price', 'original_price', 'cost', 'promotion_cost'])
            ->get();
    }

    //***************************ORM重写***************************************

    public function getListDataFromDb($params)
    {

        if (($params['invalid'] ?? 0) == 0) {
            $query = MallGoods::where('status', '=', 2);
        } else {
            $query = MallGoods::whereIn('status', [1, 2]);
        }

        if (!empty($params['ids_str'] ?? '')) {
            if (!is_array($params['ids_str'])) {
                $params['ids_str'] = explode(',', $params['ids_str']);
            }
            $query->whereIn('id', $params['ids_str']);
        }

        if ($params['zone_id'] ?? 0) {
            $temp_gl = MallGoodsListDetails::where('list_id', '=', $params['zone_id'])
                ->select(['goods_id'])
                ->get();
            if ($temp_gl->isEmpty()) {
                return [];
            } else {
                $ids_str = array_column($temp_gl->toArray(), 'goods_id');
                $query->whereIn('id', $ids_str);
            }
        }

        if (!empty($params['cid'] ?? '')) {
            if (!is_array($params['cid'])) {
                $params['cid'] = explode(',', $params['cid']);
            }
            $query->whereIn('category_id', $params['cid']);
        }
        switch ($params['ob'] ?? 'default') {
            case 'new_asc':
                $query->orderBy('created_at', 'asc');
                break;
            case 'new_desc':
                $query->orderBy('created_at', 'desc');
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
                    $query->orderByRaw('FIELD(id,' . $recommend_goods_id . ') desc');
                }
                $query->orderBy('created_at', 'desc');
                $query->orderBy(DB::raw('sales_num_virtual+sales_num'), 'desc');
        }
        $query->orderBy('id', 'desc');
        //当指定id情况下可能不需要分页  get_all = 1则返回所有
        if (($params['get_all'] ?? 0) !== 1) {
            $query->limit($params['size'])->offset(($params['page'] - 1) * $params['size']);
        }

        $select_field = ['id', 'name', 'subtitle', 'picture', 'original_price', 'price', 'category_id', 'sales_num'];
        if (($params['invalid'] ?? 0) == 1) {
            $select_field[] = 'status';
        }

        $top_content = '';
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
            $with_query[] = 'sku_list.sku_value_list';
            if (($params['invalid'] ?? 0) == 1) {
                $with_query[] = 'sku_list_all';
                $with_query[] = 'sku_list_all.sku_value_list';
            }
        }

        $with_query[] = 'category_list';

        if (!empty($with_query)) {
            $query->with($with_query);
        }

        $res = $query->get();

        if (($params['get_details'] ?? 0) == 1) {
            $top_content = ConfigModel::getData(11);
            foreach ($res as $v) {
                $v->content = $top_content . $v->content;
            }
        }

        return $res;
    }

    //获取商品全规格总库存
    public function getGoodsAllStock($goods_id, $sku_id = 0, $sku_number = 0)
    {
        if ($sku_number) {
            return MallSku::where('sku_number', '=', $sku_number)
                ->sum('stock');
        } else {
            if ($sku_id) {
                return MallSku::where('id', '=', $sku_id)
                    ->sum('stock');
            } else {
                return MallSku::where('goods_id', '=', $goods_id)
                    ->where('status', '=', 1)
                    ->sum('stock');
            }
        }

    }

    public function sku_list()
    {
        return $this->hasMany('App\Models\MallSku', 'goods_id', 'id')
            ->where('status', '=', 1)
            ->select(['id', 'goods_id', 'sku_number', 'picture', 'original_price', 'price', 'stock', 'status','min_buy_num']);
    }

    public function sku_list_back()
    {
        return $this->hasMany('App\Models\MallSku', 'goods_id', 'id')
            ->where('status', '=', 1)
            ->select();
    }

    public function sku_list_all()
    {
        return $this->hasMany('App\Models\MallSku', 'goods_id', 'id')
            ->select(['id', 'goods_id', 'sku_number', 'picture', 'original_price', 'price', 'stock', 'status','min_buy_num']);
    }

    public function tos_bind_list()
    {
        return $this->hasMany('App\Models\MallTosBind', 'goods_id', 'id')
            ->select(['goods_id', 'tos_id']);
    }

    public function picture_list()
    {
        return $this->hasMany('App\Models\MallPicture', 'goods_id', 'id')
            ->select(['url', 'is_main', 'is_video', 'duration', 'goods_id',
                DB::raw('(case when is_video = 0 then url ELSE cover_img END) as cover_img')])
            ->where('status', '=', 1)
            ->orderBy('is_video', 'desc')
            ->orderBy('is_main', 'desc')
            ->orderBy('rank', 'asc')
            ->orderBy('id', 'asc');
    }

    public function category_list()
    {
        return $this->hasOne('App\Models\MallCategory', 'id', 'category_id')
            ->select(['id', 'name'])
            ->where('status', '=', 1);
    }

    public function categoryStr()
    {
        return $this->hasOne('App\Models\MallCategory', 'id', 'category_id')
            ->select(['id', 'pid']);
    }

    /**
     * 首页好物推荐
     * @param $ids 相关作品id
     * @return bool
     */
    public function getIndexGoods($ids)
    {
        $lists = MallGoods::query()
            ->select('id', 'name', 'picture', 'original_price', 'price')
            ->whereIn('id', $ids)
            ->where('status', 2)
            ->orderByRaw('FIELD(id,' . implode(',', $ids) . ')')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
        if ($lists->isEmpty()) {
            return [];
        } else {
            return $lists->toArray();
        }
    }

    public function collect($goods_id, $user_id)
    {
        if (empty($goods_id)) {
            return ['code' => false, 'msg' => '参数错误'];
        }
        $check = Collection::where('user_id', '=', $user_id)
            ->where('relation_id', '=', $goods_id)
            ->where('type', '=', 3)
            ->first();
        if ($check) {
            $res = $check->forceDelete();
        } else {
            $model = new Collection();
            $model->type = 3;
            $model->user_id = $user_id;
            $model->relation_id = $goods_id;

            $res = $model->save();
        }

        if ($res) {
            return ['code' => true, 'msg' => '成功'];
        } else {
            return ['code' => false, 'msg' => '失败'];
        }
    }

    /**
     * 团购商品详情
     * @param array $params
     * @param array $user
     */
    public function groupByGoodsInfo($params, $user)
    {
        $group_buy_id = $params['group_buy_id'] ?? 0;
        if (empty($group_buy_id)) {
            return ['code' => false, 'msg' => '参数错误'];
        }
        $now = time();
        $now_data = date('Y-m-d H:i:s', $now);
        $check_id = SpecialPriceModel::where('group_name', '=', $group_buy_id)
            ->where('status', '=', 1)
            ->where('type', '=', 4)
            ->where('begin_time', '<=', $now_data)
            ->where('end_time', '>=', $now_data)
            ->select(['id', 'goods_id', 'group_num', 'begin_time',
                'goods_price', 'sku_number', 'group_price'])
            ->get();

        if ($check_id->isEmpty()) {
            return ['code' => false, 'msg' => '参数错误'];
        }
        $check_id = $check_id->toArray();

        $sku_number_list = array_column($check_id, 'sku_number');

        $data = $this->getListData(
            [
                'ids_str' => [$check_id[0]['goods_id']],
                'get_sku' => 1,
                'get_details' => 1,
                'page' => 1,
                'size' => 1
            ],
            true
        );
        $data = $data[0];

        $data['group_buy_id'] = $group_buy_id;

        if (0) {
            $data['order_num'] = MallOrder::where('sp_id', $check_id[0]['id'])
                ->where('status', '>', 1)
                ->count();
        } else {
            $data['order_num'] = DB::table('nlsg_mall_group_buy_list as gbl')
                ->join('nlsg_mall_order_detail as od', 'gbl.order_id', '=', 'od.order_id')
                ->where('gbl.group_buy_id', '=', $check_id[0]['id'])
                ->where('gbl.is_success', '=', 1)
                ->sum('od.num');
        }

        if ($check_id[0]['begin_time'] > $now_data) {
            $data->is_begin = 0;
        } else {
            $data->is_begin = 1;
        }

        if ($check_id[0]['goods_price'] > 0) {
            $data['price'] = $check_id[0]['goods_price'];
        }
        $data['group_num'] = $check_id[0]['group_num'];

        $stock = 0;

        foreach ($data['sku_list'] as $k => $v) {
            if (!in_array($v->sku_number, $sku_number_list)) {
                unset($data['sku_list'][$k]);
            }
            foreach ($check_id as $vv) {
                if ($v->sku_number == $vv['sku_number']) {
                    $stock += $v['stock'];
                    $v->group_num = $vv['group_num'];
                    $v->price = $vv['group_price'];
                    $v->normal_price = MallSku::where('sku_number', '=', $vv['sku_number'])->sum('price');
                    $v->stock = $this->getGoodsAllStock($vv['goods_id'], 0, $vv['sku_number']);
                }
            }
        }

        $temp_sku_list = [];
        foreach ($data['sku_list'] as $v) {
            $temp_sku_list[] = $v;
        }
        unset($data['sku_list']);
        $data['sku_list'] = $temp_sku_list;

        $data['normal_price'] = MallGoods::where('id', '=', $data['id'])->sum('price');
        //$data['stock'] = $this->getGoodsAllStock($data['id']);
        $data['stock'] = $stock;
        $data['service_description'] = $this->mallServiceDescription();
        $data['buyer_reading'] = $this->buyerReading();

        $mcModel = new MallComment();
        $data['comment_list'] = $mcModel->getList(['goods_id' => $data['id'], 'page' => 1, 'size' => 1]);

        return [$data];
    }

    // 全局搜索用 $keywords
    static function search($keywords)
    {
        $res = MallGoods::select('id', 'name', 'subtitle', 'original_price', 'picture', 'price')
            ->where('status', 2)
            //->where('can_sale', 1)
            ->where(function ($query) use ($keywords) {
                $query->orWhere('name', 'LIKE', "%$keywords%");
                $query->orWhere('subtitle', 'LIKE', "%$keywords%");
            })->get();

        return ['res' => $res, 'count' => $res->count()];
    }

    public function forYourReference($num, $user = [])
    {
        if (empty($user['id'] ?? 0)){
            return [];
        }
        $kind = 5;//可能性
        $key = substr(time(), 9);
        $key = $key % $kind;
        if (($user['ad_switch'] ?? 1) == 2) {
            return [];
        }
        $cache_key_name = 'fyr_' . $key;
        $id_list = Cache::get($cache_key_name);

        if (empty($id_list)) {
            $id_list = MallGoods::where('status', '=', 2)
                ->orderByRaw('rand()')
                ->limit($num)
                ->select(['id'])
                ->get()
                ->toArray();

            $id_list = array_column($id_list, 'id');
            $id_list = implode(',', $id_list);

            $expire_num = CacheTools::getExpire('fyr_list');
            Cache::put($cache_key_name, $id_list, $expire_num);
        }

        return $this->getList([
            'ids_str' => $id_list,
            'page' => 1,
            'size' => 1,
            'get_all' => 1
        ], $user, false);
    }

    //商城服务说明
    public function mallServiceDescription()
    {
        $res = ConfigModel::getData(6);
        $freight_line = ConfigModel::getData(1);
        $post_money = ConfigModel::getData(7);
        $res = str_replace('$freight_line', $freight_line, $res);
        $res = str_replace('$post_money', $post_money, $res);
        return json_decode($res);
    }

    //商城购买须知
    public function buyerReading()
    {
        $res = ConfigModel::getData(16);
        return json_decode($res);
    }

}
