<?php


namespace App\Servers;


use App\Models\SpecialPriceModel;

class SpecialPriceServers
{
    public function list($params)
    {
        $size = $params['size'] ?? 10;
        $query = SpecialPriceModel::from('nlsg_special_price')->where('status','<>',3);

        $with = [];

        if (!empty($params['id'])) {
            $query->where('id', '=', intval($params['id']));
        }

        if (!empty($params['begin_time'])) {
            $query->where('begin_time', '>=', $params['begin_time']);
        }

        if (!empty($params['end_time'])) {
            $query->where('end_time', '<=', $params['end_time']);
        }

        $field = ['id', 'title', 'begin_time', 'end_time', 'ad_begin_time', 'status'];
        $field[] = 'wx_share_title';
        $field[] = 'wx_share_img';
        $field[] = 'wx_share_desc';
        $field[] = 'lace_img';

        $query->orderBy('id', 'desc');

        return $query->with($with)->select($field)->paginate($size);

    }

    public function add($params)
    {

    }

    public function statusChange($params)
    {

    }
}
