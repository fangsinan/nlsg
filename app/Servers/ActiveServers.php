<?php


namespace App\Servers;


use App\Models\ActiveGroupGlModel;

class ActiveServers
{
    public function list($params)
    {
        $size = $params['size'] ?? 10;
        $query = ActiveGroupGlModel::from('nlsg_active_group_list');

        if (!empty($params['id'])) {
            $query->where('id', '=', intval($params['id']));
        }

        if (!empty($params['title'])) {
            $query->where('title', 'like', '%' . intval($params['title'] . '%'));
        }

        if (!empty($params['begin_time'])) {
            $query->where('begin_time', '>=', $params['begin_time']);
        }

        if (!empty($params['end_time'])) {
            $query->where('end_time', '<=', $params['end_time']);
        }


        $with = [];
        $field = ['id','title','begin_time','end_time','ad_begin_time','status'];

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

}
