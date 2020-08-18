<?php


namespace App\Servers;


use App\Models\ActiveGroupGglModel;
use App\Models\ActiveGroupGlModel;
use App\Models\ActiveGroupGmlModel;
use Illuminate\Support\Facades\DB;

class ActiveServers
{
    public function list($params)
    {
        $size = $params['size'] ?? 10;
        $query = ActiveGroupGlModel::from('nlsg_active_group_list')
            ->where('status', '<>', 3);

        $with = [];

        if (!empty($params['id'])) {
            $query->where('id', '=', intval($params['id']));

            $with[] = 'moduleList';
            $with[] = 'moduleList.goodsList';
            $with[] = 'moduleList.goodsList.bindingGoodsInfo';
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
        if ($params['id'] ?? 0) {
            $model = ActiveGroupGlModel::find($params['id']);
            if (!$model) {
                return ['code' => false, 'msg' => 'id错误'];
            }
        } else {
            $model = new ActiveGroupGlModel();
        }

        if (empty($params['title'])) {
            return ['code' => false, 'msg' => 'title参数错误'];
        }
        if (empty($params['begin_time'])) {
            return ['code' => false, 'msg' => 'begin_time参数错误'];
        }
        if (empty($params['end_time'])) {
            return ['code' => false, 'msg' => 'end_time参数错误'];
        }
        if (empty($params['ad_begin_time'])) {
            return ['code' => false, 'msg' => 'ad_begin_time参数错误'];
        }
        if (empty($params['pre_begin_time'])) {
            return ['code' => false, 'msg' => 'pre_begin_time参数错误'];
        }
        if (empty($params['lace_img'])) {
            return ['code' => false, 'msg' => 'lace_img参数错误'];
        }
        if (empty($params['status'] || !in_array($params['status'], [1, 2]))) {
            return ['code' => false, 'msg' => 'status参数错误'];
        }

        $model->title = $params['title'];
        $model->begin_time = $params['begin_time'];
        $model->end_time = $params['end_time'];
        $model->ad_begin_time = $params['ad_begin_time'];
        $model->pre_begin_time = $params['pre_begin_time'];
        $model->lace_img = $params['lace_img'];
        $model->status = $params['status'];

        $model->wx_share_title = $params['wx_share_title'] ?? '';
        $model->wx_share_img = $params['wx_share_img'] ?? '';
        $model->wx_share_desc = $params['wx_share_desc'] ?? '';

        $res = $model->save();
        if ($res) {
            return ['code' => true, 'msg' => '成功'];
        } else {
            return ['code' => false, 'msg' => '添加失败,请重试'];
        }
    }

    public function binding($params)
    {
        if (empty($params)) {
            return ['code' => false, 'msg' => '参数错误'];
        }

        $check_active = ActiveGroupGlModel::find($params['active_id'] ?? 0);
        if (!$check_active) {
            return ['code' => false, 'msg' => '活动id错误'];
        }

        DB::beginTransaction();

        //删除原有module和goods数据
        $del_m_res = ActiveGroupGmlModel::where('aid', '=', $params['active_id'])->delete();
        if ($del_m_res === false) {
            DB::rollBack();
            return ['code' => false, 'msg' => 'del module error'];
        }
        $del_g_res = ActiveGroupGglModel::where('aid', '=', $params['active_id'])->delete();
        if ($del_g_res === false) {
            DB::rollBack();
            return ['code' => false, 'msg' => 'del goods error'];
        }

        foreach ($params['module_list'] as $v) {
            if (empty($v['title']) || empty($v['goods_list'])) {
                DB::rollBack();
                return ['code' => false, 'msg' => 'module data error'];
            }

            $m_model = new ActiveGroupGmlModel();
            $m_model->aid = $params['active_id'];
            $m_model->title = $v['title'];
            $m_model->status = 1;
            $m_model->rank = $params['rank'] ?? 1;
            $m_res = $m_model->save();
            if (!$m_res) {
                DB::rollBack();
                return ['code' => false, 'msg' => 'add module error'];
            }

            $g_data = [];
            $now_date = date('Y-m-d H:i:s');
            foreach ($v['goods_list'] as $gv) {
                $temp = [];
                $temp['aid'] = $params['active_id'];
                $temp['mid'] = $m_model->id;
                $temp['goods_type'] = 1;
                $temp['goods_id'] = $gv;
                $temp['created_at'] = $temp['updated_at'] = $now_date;
                $g_data[] = $temp;
            }

            $g_res = DB::table('nlsg_active_group_goods_lit')
                ->insert($g_data);
            if (!$g_res) {
                DB::rollBack();
                return ['code' => false, 'msg' => 'add goods error'];
            }
        }

        DB::commit();
        return ['code' => true, 'msg' => '成功'];
    }

    public function statusChange($params)
    {
        if (!in_array($params['flag'] ?? '', ['on', 'off', 'del'])) {
            return ['code' => false, 'msg' => 'flag参数错误'];
        }
        $model = ActiveGroupGlModel::find($params['id'] ?? 0);
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
