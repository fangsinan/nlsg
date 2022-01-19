<?php

namespace App\Servers\V5;

use App\Models\Column;
use App\Models\History;
use App\Models\Subscribe;
use App\Models\WorksInfo;
use Illuminate\Support\Facades\DB;

class CampServers
{
    public function CampClockIn($params) {
        $id = $params['id'] ?? 0;
        if (empty($id)) {
            return ['code' => false, 'msg' => '参数错误'];
        }

        $check_id = Column::query()->where('id', '=', $id)
            ->where('type', '=', 3)
            ->select(['id', 'name', 'real_subscribe_num', 'info_column_id'])
            ->first();

        if (empty($check_id)) {
            return ['code' => false, 'msg' => '参数错误'];
        }

        $column_id = $check_id->info_column_id === 0 ? $check_id->id : $check_id->info_column_id;

        $all_sub_counts = Subscribe::query()
            ->where('type', '=', 7)
            ->where('relation_id', '=', $id)
            ->where('status', '=', 1)
            ->count();

        $query = WorksInfo::query()
            ->where('column_id', '=', $column_id)
            ->where('status', '=', 4)
            ->select(['id', 'title', DB::raw("$id as works_id"), 'id as works_info_id'])
            ->withCount(['worksInfoHistory' => function ($q) use ($id) {
                $q->where('relation_type', '=', 5)
                    ->where('relation_id', '=', $id)
                    ->where('is_end', '=', 1);
            }])
            ->orderBy('rank')
            ->orderBy('id');

        $list = $query->paginate($params['size'] ?? 10);

        foreach ($list as $v) {
            $v->clock_in_counts     = $v->works_info_history_count;
            $v->not_clock_in_counts = max(
                $all_sub_counts - $v->works_info_history_count,
                0
            );

            if ($all_sub_counts === 0) {
                $v->p = 0;
            } else {
                $v->p = $v->clock_in_counts / ($v->clock_in_counts + $v->not_clock_in_counts);
            }
            if ($v->p >= 1) {
                $v->p = '100%';
            } else {
                $v->p = sprintf("%01.2f", $v->p * 100) . '%';
            }
        }

        return $list;
    }


    public function CampClockInInfo($params) {
        $works_id      = $params['works_id'] ?? 0;
        $works_info_id = $params['works_info_id'] ?? 0;
        $is_end        = (int)($params['is_end'] ?? -1);

        if (empty($works_id) || empty($works_info_id)) {
            return ['code' => false, 'msg' => '参数错误'];
        }

        $query = History::query()
            ->where('relation_type', '=', 5)
            ->where('relation_id', '=', $works_id)
            ->where('info_id', '=', $works_info_id)
            ->select([
                'id', 'user_id', 'is_end',
                DB::raw("IF(is_end = 0,'-',(IF(end_time is NULL,updated_at,end_time))) as end_time")
            ])
            ->with(['userInfo:id,phone']);

        if ($is_end !== -1) {
            $query->where('is_end', '=', $is_end);
        }

        return $query->paginate($params['size'] ?? 10);
    }


}
