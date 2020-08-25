<?php


namespace App\Servers;


use App\Models\FreightTemplate;
use App\Models\MallAddress;

class FreightServers
{
    public function list($params, $type = 0)
    {
        if ($type != 1) {
            $type = $params['type'];
        }
        if (!in_array($type ?? 0, [1, 2, 3])) {
            return ['code' => false, 'msg' => 'type参数错误'];
        }

        $size = $params['size'] ?? 10;
        $with = [];

        $field = ['id', 'type', 'name',
        ];

        if ($type != 1) {
            $field = array_merge($field, ['admin_name', 'admin_phone', 'admin_phone as phone',
                'province', 'city', 'area', 'details',
                'start_time', 'end_time']);
        } else {
            $with[] = 'f_details';
            $with[] = 'f_details.d_list';
        }

        $query = FreightTemplate::from('nlsg_freight_template')
            ->where('type', '=', $type)
            ->where('status', '<>', 3);

        $query->orderBy('id', 'desc');

        $list = $query->with($with)->select($field)->paginate($size);

        if ($type != 1) {
            foreach ($list as $v) {
                $v->province_name = MallAddress::getNameById($v->province);
                $v->city_name = MallAddress::getNameById($v->city);
                $v->area_name = MallAddress::getNameById($v->area);
            }
        }

        return $list;

    }
}
