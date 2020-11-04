<?php


namespace App\Servers;


use App\Models\SpecialPriceModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SpecialPriceServers
{
    public function list($params)
    {
        $size = $params['size'] ?? 10;
        $query = SpecialPriceModel::from('nlsg_special_price');

        $with = ['goodsInfo', 'spSkuList', 'spSkuList.skuInfo', 'spSkuList.skuInfo.sku_value_list'];
        $field = ['id', 'goods_id', 'goods_original_price', 'goods_price', 'status', 'type',
            'begin_time', 'end_time', 'group_name', 'group_num_type',
            'group_num', 'group_price', 'group_life', 'created_at'
        ];

        if (!empty($params['id'])) {
            $query->where('id', '=', intval($params['id']));
            $field = ['*'];
        }

        if (!empty($params['type'])) {
            $query->where('type', '=', intval($params['type']));
        }

        if (!empty($params['begin_time'])) {
            $query->where('begin_time', '>=', $params['begin_time']);
        }

        if (!empty($params['end_time'])) {
            $query->where('end_time', '<=', $params['end_time']);
        }

        if (!empty($params['goods_name'])) {
            $query->whereHas('goodsInfo', function (Builder $query) use ($params) {
                $query->where('name', 'like', '%' . $params['goods_name'] . '%');
            });
        }

        if (!empty($params['status'])) {
            $query->where('status', '=', intval($params['status']));
        }

        $list = $query->where('status', '<>', 3)->groupBy('group_name')->orderBy('id', 'desc');
        foreach ($list as $v) {
            if ($v->goods_original_price > 0) {
                $v->goodsInfo->original_price = $v->goods_original_price;
            }
        }

        return $query->with($with)->select($field)->paginate($size);

    }

    public function statusChange($params)
    {
        if (!in_array($params['flag'] ?? '', ['on', 'off', 'del'])) {
            return ['code' => false, 'msg' => 'flag参数错误'];
        }
        $model = SpecialPriceModel::find($params['id'] ?? 0);
        if (empty($model)) {
            return ['code' => false, 'msg' => 'id错误'];
        }

        switch ($params['flag']) {
            case 'on':
                $model->status = 1;
                break;
            case 'off':
                $model->status = 2;
                break;
            default:
                $model->status = 3;
        }

        $res = $model->save();
        if ($res) {
            CacheServers::clear(1);
            return ['code' => true, 'msg' => '成功'];
        } else {
            return ['code' => false, 'msg' => '添加失败,请重试'];
        }
    }

    public function add($params)
    {
        if (empty($params['goods_id'])) {
            return ['code' => false, 'msg' => 'goods_id错误'];
        }
        if (empty($params['status'])) {
            return ['code' => false, 'msg' => 'status参数错误'];
        }
        if (!in_array($params['status'], [1, 2])) {
            return ['code' => false, 'msg' => 'status参数错误'];
        }
        if (empty($params['list'])) {
            return ['code' => false, 'msg' => 'list错误'];
        }
        if (empty($params['goods_price'])) {
            return ['code' => false, 'msg' => 'goods_price错误'];
        }
        if ($params['type'] != 2) {
            if (empty($params['begin_time'])) {
                return ['code' => false, 'msg' => 'begin_time错误'];
            }
            if (empty($params['end_time'])) {
                return ['code' => false, 'msg' => 'end_time错误'];
            }
        }

        foreach ($params['list'] as $v) {
            if (empty($v['sku_number'])) {
                return ['code' => false, 'msg' => 'sku_number错误'];
            }
            if ($params['type'] == 4) {
                //拼团需要校验拼团price
                if (empty($v['group_num'])) {
                    return ['code' => false, 'msg' => 'group_num错误'];
                }
                if (empty($v['group_price'])) {
                    return ['code' => false, 'msg' => 'group_price错误'];
                }
            } elseif ($params['type'] == 2) {
                if (empty($v['sku_price'])) {
                    return ['code' => false, 'msg' => 'sku_price错误'];
                }
            } else {
                //降价和秒杀需要校验sku_price
                if (empty($v['sku_price'])) {
                    return ['code' => false, 'msg' => 'sku_price错误'];
                }
                if (empty($v['sku_price_black'])) {
                    return ['code' => false, 'msg' => 'sku_price_black错误'];
                }
                if (empty($v['sku_price_yellow'])) {
                    return ['code' => false, 'msg' => 'sku_price_yellow错误'];
                }
                if (empty($v['sku_price_dealer'])) {
                    return ['code' => false, 'msg' => 'sku_price_dealer错误'];
                }
                if (($v['is_set_t_money'] ?? 0) == 1) {
                    if (empty($v['t_money'])) {
                        return ['code' => false, 'msg' => 't_money错误'];
                    }
                    if (empty($v['t_money_black'])) {
                        return ['code' => false, 'msg' => 't_money_black错误'];
                    }
                    if (empty($v['t_money_yellow'])) {
                        return ['code' => false, 'msg' => 't_money_yellow错误'];
                    }
                    if (empty($v['t_money_dealer'])) {
                        return ['code' => false, 'msg' => 't_money_dealer错误'];
                    }
                }
            }
        }

        switch ($params['type'] ?? 0) {
            case 1:
                //降价
                $res = $this->addType_1($params);
                break;
            case 2:
                //秒杀
                $res = $this->addType_2($params);
                break;
            case 4:
                //拼团
                $res = $this->addType_4($params);
                break;
            default:
                return ['code' => false, 'msg' => 'type错误'];
        }
        CacheServers::clear(1);
        return $res;
    }

    public function addType_1($params)
    {
        DB::beginTransaction();
        if (!empty($params['group_name'])) {
            $del_res = SpecialPriceModel::where('group_name', '=', $params['group_name'])
                ->where('type', '=', 1)
                ->update(['status' => 3]);
            if ($del_res === false) {
                DB::rollBack();
                return ['code' => false, 'msg' => 'group_name错误'];
            }
        }

        $now = time();
        $now_date = date('Y-m-d H:i:s', $now);
        $group_name = Str::random(5) . $now;

        //添加
        $add_data = [];
        foreach ($params['list'] as $v) {
            $temp = [];
            $temp['goods_type'] = 1;
            $temp['goods_id'] = $params['goods_id'];
            $temp['goods_original_price'] = $params['goods_original_price'] ?? 0;
            $temp['goods_price'] = $params['goods_price'];
            $temp['sku_number'] = $v['sku_number'];
            $temp['sku_price'] = $v['sku_price'];
            $temp['sku_price_black'] = $v['sku_price_black'];
            $temp['sku_price_yellow'] = $v['sku_price_yellow'];
            $temp['sku_price_dealer'] = $v['sku_price_dealer'];
            $temp['stock'] = $v['stock'] ?? 0;

            if ($v['is_set_t_money'] == 1) {
                $temp['t_money'] = $v['t_money'];
                $temp['t_money_black'] = $v['t_money_black'];
                $temp['t_money_yellow'] = $v['t_money_yellow'];
                $temp['t_money_dealer'] = $v['t_money_dealer'];
            } else {
                $temp['t_money'] = $temp['t_money_black'] =
                $temp['t_money_yellow'] = $temp['t_money_dealer'] = 0;
            }

            $temp['begin_time'] = $params['begin_time'];
            $temp['end_time'] = $params['end_time'];
            $temp['created_at'] = $temp['updated_at'] = $now_date;
            $temp['type'] = 1;
            $temp['use_coupon'] = 2;
            $temp['freight_free'] = $temp['freight_free_line'] = 0;
            $temp['group_name'] = $group_name;

            $add_data[] = $temp;
        }

        $add_res = DB::table('nlsg_special_price')->insert($add_data);

        if (!$add_res) {
            DB::rollBack();
            return ['code' => false, 'msg' => 'sku写入错误'];
        }

        DB::commit();
        return ['code' => true, 'msg' => '成功'];
    }

    public function addType_2($params)
    {
        DB::beginTransaction();
        if (!empty($params['group_name'])) {
            $del_res = SpecialPriceModel::where('group_name', '=', $params['group_name'])
                ->where('type', '=', 2)
                ->update(['status' => 3]);
            if ($del_res === false) {
                DB::rollBack();
                return ['code' => false, 'msg' => 'group_name错误'];
            }
        }

        $now = time();
        $now_date = date('Y-m-d H:i:s', $now);
        $group_name = Str::random(5) . $now;
        if (empty($params['time_list'])) {
            return ['code' => false, 'msg' => 'time_list 数据错误'];
        }
        foreach ($params['time_list'] as $tk => &$tv) {
            if (!is_string($tv)) {
                return ['code' => false, 'msg' => 'time_list格式错误'];
            }
            $tv = explode(',', $tv);
            if (count($tv) !== 2) {
                return ['code' => false, 'msg' => 'time_list 时间格式错误'];
            }
        }

        //添加
        $add_data = [];
        foreach ($params['list'] as $v) {
            foreach ($params['time_list'] as $tlv) {
                $temp = [];
                $temp['status'] = $params['status'];
                $temp['goods_type'] = 1;
                $temp['goods_id'] = $params['goods_id'];
                $temp['goods_original_price'] = $params['goods_original_price'] ?? 0;
                $temp['goods_price'] = $params['goods_price'];
                $temp['sku_number'] = $v['sku_number'];
                $temp['stock'] = $v['stock'] ?? 0;
                $temp['sku_price'] = $v['sku_price'];
                $temp['sku_price_black'] = $v['sku_price'];
                $temp['sku_price_yellow'] = $v['sku_price'];
                $temp['sku_price_dealer'] = $v['sku_price'];
                $temp['t_money'] = $temp['t_money_black'] =
                $temp['t_money_yellow'] = $temp['t_money_dealer'] = 0;
                $temp['created_at'] = $temp['updated_at'] = $now_date;
                $temp['type'] = 2;
                $temp['use_coupon'] = 2;
                $temp['freight_free'] = $temp['freight_free_line'] = 0;
                $temp['group_name'] = $group_name;
                $temp['begin_time'] = $tlv[0];
                $temp['end_time'] = $tlv[1];
                $add_data[] = $temp;
            }
        }

        $add_res = DB::table('nlsg_special_price')->insert($add_data);

        if (!$add_res) {
            DB::rollBack();
            return ['code' => false, 'msg' => 'sku写入错误'];
        }

        DB::commit();
        return ['code' => true, 'msg' => '成功'];
    }

    public function addType_4($params)
    {
        DB::beginTransaction();
        if (!empty($params['group_name'])) {
            $del_res = SpecialPriceModel::where('group_name', '=', $params['group_name'])
                ->where('type', '=', 4)
                ->update(['status' => 3]);
            if ($del_res === false) {
                DB::rollBack();
                return ['code' => false, 'msg' => 'group_name错误'];
            }
        }

        $now = time();
        $now_date = date('Y-m-d H:i:s', $now);
        $group_name = Str::random(5) . $now;

        //添加
        $add_data = [];
        foreach ($params['list'] as $v) {
            $temp = [];
            $temp['status'] = $params['status'];
            $temp['goods_type'] = 1;
            $temp['goods_id'] = $params['goods_id'];
            $temp['goods_original_price'] = $params['goods_original_price'] ?? 0;
            $temp['goods_price'] = $params['goods_price'];
            $temp['sku_number'] = $v['sku_number'];
            $temp['stock'] = $v['stock'] ?? 0;
            $temp['group_price'] = $v['group_price'];
            $temp['group_num'] = $v['group_num'];

            $temp['sku_price'] = $temp['sku_price_black'] = $temp['sku_price_yellow'] =
            $temp['sku_price_dealer'] = $temp['t_money'] = $temp['t_money_black'] =
            $temp['t_money_yellow'] = $temp['t_money_dealer'] = 0;

            $temp['created_at'] = $temp['updated_at'] = $now_date;
            $temp['type'] = 4;
            $temp['use_coupon'] = 2;
            $temp['freight_free'] = $temp['freight_free_line'] = 0;
            $temp['group_name'] = $group_name;
            $temp['begin_time'] = $params['begin_time'];
            $temp['end_time'] = $params['end_time'];
            $add_data[] = $temp;
        }

        $add_res = DB::table('nlsg_special_price')->insert($add_data);

        if (!$add_res) {
            DB::rollBack();
            return ['code' => false, 'msg' => 'sku写入错误'];
        }

        DB::commit();
        return ['code' => true, 'msg' => '成功'];
    }

    public function addFlashSaleNew($params)
    {
        $team_id = $params['team_id'] ?? 0;
        if (!in_array($team_id, [1, 2, 3, 4])) {
            return ['code' => false, 'msg' => 'team_id错误'];
        }
        $date = $params['date'] ?? '';
        if (empty($date)) {
            return ['code' => false, 'msg' => 'date错误'];
        }
        if (!in_array($params['status'] ?? 0, [1, 2])) {
            return ['code' => false, 'msg' => 'status参数错误'];
        }
        if (!is_array($params['list'] ?? '')) {
            return ['code' => false, 'msg' => 'list格式错误'];
        }

        switch (intval($team_id)) {
            case 1:
                $begin = $date . ' ' . '09:00:00';
                $end = $date . ' ' . '12:59:59';
                break;
            case 2:
                $begin = $date . ' ' . '13:00:00';
                $end = $date . ' ' . '18:59:59';
                break;
            case 3:
                $begin = $date . ' ' . '19:00:00';
                $end = $date . ' ' . '20:59:59';
                break;
            case 4:
                $begin = $date . ' ' . '21:00:00';
                $end = date('Y-m-d', strtotime("$date +1 days")) . ' ' . '08:59:59';
                break;
        }

        $now = time();
        $now_date = date('Y-m-d H:i:s', $now);
        $group_name = Str::random(5) . $now;

        $add_data = [];

        foreach ($params['list'] as $v) {
            foreach ($v['list'] as $vv) {
                $temp = [];
                $temp['goods_type'] = 1;
                $temp['type'] = 2;
                $temp['use_coupon'] = 2;
                $temp['status'] = $params['status'];
                $temp['goods_id'] = $v['goods_id'];
                $temp['goods_original_price'] = $v['goods_original_price'] ?? 0;
                $temp['goods_price'] = $v['goods_price'];

                $temp['sku_number'] = $vv['sku_number'];
                $temp['stock'] = $vv['stock'] ?? 0;
                $temp['sku_price'] = $vv['sku_price'];
                $temp['sku_price_black'] = $vv['sku_price'];
                $temp['sku_price_yellow'] = $vv['sku_price'];
                $temp['sku_price_dealer'] = $vv['sku_price'];

                $temp['t_money'] = $temp['t_money_black'] = 0;
                $temp['t_money_yellow'] = $temp['t_money_dealer'] = 0;
                $temp['created_at'] = $temp['updated_at'] = $now_date;

                $temp['freight_free'] = $temp['freight_free_line'] = 0;
                $temp['group_name'] = $group_name;
                $temp['begin_time'] = $begin;
                $temp['end_time'] = $end;
                $temp['team_id'] = $team_id;

                $add_data[] = $temp;
            }
        }

        return $add_data;


    }

}
