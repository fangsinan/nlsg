<?php


namespace App\Servers;


use App\Models\SpecialPriceModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class SpecialPriceServers
{
    public function list($params)
    {
        $size = $params['size'] ?? 10;
        $query = SpecialPriceModel::from('nlsg_special_price')->where('status', '<>', 3);

        $with = ['goodsInfo', 'spSkuList', 'spSkuList.skuInfo', 'spSkuList.skuInfo.sku_value_list'];
        $field = ['id', 'goods_id', 'goods_original_price', 'goods_price', 'status', 'type',
            'begin_time', 'end_time', 'group_name', 'group_num_type', 'group_num', 'group_price', 'group_life',
        ];

        if (!empty($params['id'])) {
            $query->where('id', '=', intval($params['id']));
            $field = ['*'];
        }

//        $query->where('type', '=', intval($params['type'] ?? 1));

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

        $query->groupBy('group_name')->orderBy('id', 'desc');

        return $query->with($with)->select($field)->paginate($size);

    }

    public function statusChange($params)
    {
        if (!in_array($params['flag'] ?? '', ['on', 'off', 'del'])) {
            return ['code' => false, 'msg' => 'flag参数错误'];
        }
        $model = SpecialPriceModel::find($params['id'] ?? 0);
        if (!$model) {
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
        if (empty($params['list'])) {
            return ['code' => false, 'msg' => 'list错误'];
        }
        if (empty($params['goods_price'])) {
            return ['code' => false, 'msg' => 'goods_price错误'];
        }
        foreach ($params['list'] as $v) {
            if (empty($v['sku_number'])) {
                return ['code' => false, 'msg' => 'sku_number错误'];
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

        //todo 添加
        $add_data = [];
        foreach ($params['list'] as $v) {
            $temp['goods_type'] = 1;
            $temp['goods_id'] = $params['goods_id'];
            $temp['goods_original_price'] = $params['goods_original_price'] ?? 0;
            $temp['goods_price'] = $params['goods_price'];
            $temp['sku_number'] = $v['sku_number'];
            if (empty($v['sku_price'])) {
                return ['code' => false, 'msg' => 'sku_price错误'];
            }
            $temp['sku_price'] = $v['sku_price'];
            $temp['t_money'] = $v['t_money'] ?? 0;
            $temp['t_money_black'] = $v['t_money_black'] ?? 0;
        }


        return '';
    }

    public function addType_2($params)
    {
        return '';
    }

    public function addType_4($params)
    {
        return '';
    }

}
