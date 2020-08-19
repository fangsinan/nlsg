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


    public function add($params)
    {

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
            return ['code' => true, 'msg' => '成功'];
        } else {
            return ['code' => false, 'msg' => '添加失败,请重试'];
        }
    }
}
