<?php


namespace App\Servers;


use App\Models\FreightTemplate;
use App\Models\MallAddress;
use Illuminate\Support\Facades\DB;

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

        $id = $params['id'] ?? 0;
        $size = $params['size'] ?? 10;
        $with = [];

        $field = ['id', 'type', 'name'];

        if ($type != 1) {
            $field = array_merge($field, ['admin_name', 'admin_phone', 'admin_phone as phone',
                'province', 'city', 'area', 'details',
                'start_time', 'end_time']);
        } else {
            $with[] = 'f_details';
            $with[] = 'f_details.d_list';
        }

        $query = FreightTemplate::from('nlsg_freight_template');
        if (!empty($id)) {
            $query->where('id', '=', $id);
        }
        $query->where('type', '=', $type)->where('status', '<>', 3);

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

    public function addShop($params)
    {
        if (!in_array($params['type'] ?? 0, [2, 3])) {
            return ['code' => false, 'msg' => 'type参数错误'];
        }

        if (!in_array($params['status'] ?? 0, [1, 2])) {
            return ['code' => false, 'msg' => 'status参数错误'];
        }

        if (empty($params['id'] ?? 0)) {
            $data = new FreightTemplate();
        } else {
            $data = FreightTemplate::whereIN('type', [2, 3])->find($params['id']);
            if (!$data) {
                return ['code' => false, 'msg' => 'id错误'];
            }
        }

        $data->type = $params['type'];
        $data->name = $params['name'];
        $data->admin_name = $params['admin_name'];
        $data->admin_phone = $params['admin_phone'];
        $data->status = $params['status'];
        if (empty($params['start_time'] ?? '')) {
            $data->start_time = date('Y-m-d H:i:s');
        } else {
            $data->start_time = $params['start_time'];
        }

        if (empty($params['end_time'] ?? '')) {
            $data->end_time = '2020-12-31 23:59:59';
        } else {
            $data->end_time = $params['end_time'];
        }

        $data->province = $params['province'];
        $data->city = $params['city'];
        $data->area = $params['area'];
        $data->details = $params['details'];

        $res = $data->save();
        if ($res) {
            return ['code' => true, 'msg' => '添加成功'];
        } else {
            return ['code' => false, 'msg' => '添加失败'];
        }
    }

    public function add($params)
    {
        if (empty($params['id'] ?? 0)) {
            $data = new FreightTemplate();
        } else {
            $data = FreightTemplate::where('type', '=', 1)->find($params['id']);
            if (!$data) {
                return ['code' => false, 'msg' => 'id错误'];
            }
        }

        $data->type = 1;
        $data->name = $params['name'];
        $data->status = $params['status'];

        DB::beginTransaction();

        $t_res = $data->save();
        if ($t_res === false) {
            DB::rollBack();
            return ['code' => false, 'msg' => '失败,请重试.' . __LINE__];
        }

        $template_id = $data->id;

    }

}
