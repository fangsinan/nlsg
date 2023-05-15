<?php

namespace App\Servers\V5;

use App\Models\StudyLogModel;
use App\Models\Column;
use App\Models\Subscribe;
use App\Models\WorksInfo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

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
            ->where('is_del','=',0)
            ->count();

        $query = WorksInfo::query()
            ->where('column_id', '=', $column_id)
            ->where('status', '=', 4)
            ->select(['id', 'title', DB::raw("$id as works_id"), 'id as works_info_id'])
//            ->withCount(['worksInfoHistory' => function ($q) use ($id) {
//                $q->where('relation_type', '=', 5)
//                    ->where('relation_id', '=', $id)
//                    ->where('is_end', '=', 1);
//            }])
            ->orderBy('rank')
            ->orderBy('id');

        $list = $query->paginate($params['size'] ?? 10);

        foreach ($list as $v) {
            $v->sub_counts = $all_sub_counts;
            $v->clock_in_counts = $this->tempClockInQuery($v->works_id,$v->works_info_id);
//            $v->clock_in_counts     = $v->works_info_history_count;
            $v->not_clock_in_counts = max(
                $all_sub_counts - $v->clock_in_counts,
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

    public function tempClockInQuery($works_id,$works_info_id){
        $sql = "SELECT
	count(*) AS counts
FROM
	(
		SELECT sub.id AS sub_id,
		his.id AS history_id,
		sub.user_id,
		u.nickname,
		u.phone,
		if ( his.is_end = 1, 1, 0 ) AS is_end,
		if ( his.is_end = 1,( IF ( his.end_time IS NULL, his.updated_at, his.end_time )), '-' ) AS end_time from nlsg_subscribe AS sub LEFT
		JOIN ( SELECT * FROM nlsg_history WHERE relation_type = 5 AND relation_id = $works_id AND info_id = $works_info_id  ) AS his on sub.user_id = his.user_id left
		JOIN nlsg_user AS u ON sub.user_id = u.id where sub.relation_id = $works_id
		AND sub.type = 7
		AND sub.`status` = 1
		AND sub.is_del = 0
	) AS a
WHERE
	is_end = 1";
        $total = DB::select($sql);
        return $total[0]->counts ?? 0;
    }


    public function CampClockInInfo($params) {
        $works_id      = $params['works_id'] ?? 0;
        $works_info_id = $params['works_info_id'] ?? 0;
        $is_end        = (int)($params['is_end'] ?? -1);

        if (empty($works_id) || empty($works_info_id)) {
            return ['code' => false, 'msg' => '参数错误'];
        }

        $size = $params['size'] ?? 10;
        $page = $params['page'] ?? 1;

        $offset = max(($page - 1) * $size, 0);


        $sql = " from (
SELECT
sub.id as sub_id,his.id as history_id,sub.user_id,u.nickname,u.phone,his.time_leng,
if(his.is_end = 1,1,0) as is_end,
if(his.is_end = 1,(IF(his.end_time is NULL,his.updated_at,his.end_time)),'-') as end_time
from
nlsg_subscribe as sub
LEFT JOIN (
SELECT * from nlsg_history where relation_type = 5 and relation_id = $works_id and info_id = $works_info_id
) as his
on sub.user_id = his.user_id
left join nlsg_user as u on sub.user_id = u.id
where sub.relation_id = $works_id and sub.type = 7 and sub.`status` = 1 and sub.is_del = 0

)as a ";


        if ($is_end === 1) {
            $sql .= ' where is_end = 1';
        }

        if ($is_end === 0) {
            $sql .= ' where is_end = 0';
        }

        $count_sql = 'select count(*) as counts ' . $sql;
        if(empty($params['is_no_page'])){
            $query_sql = 'select *  ' . $sql . ' limit ' . $size . ' offset ' . $offset;
        }else{
            $query_sql = 'select *  ' . $sql;
        }

        $total = DB::select($count_sql);
        $data  = DB::select($query_sql);

        foreach ($data as &$v) {
            if ($v->is_end === 1) {
                $v->time_leng = '100%';
            } else {
                $v->time_leng = ($v->time_leng ?? 0).'%';
            }

            $v->user_info = [
                'id'       => $v->user_id ?? '',
                'nickname' => $v->nickname ?? '',
                'phone'    => $v->phone ?? '',
            ];
        }

        $res['current_page'] = $page;
        $res['data']         = $data;
        $res['total']        = $total[0]->counts ?? 0;

        return $res;
    }

    public function CampClockInInfo_2($params) {
        $works_id      = $params['works_id'] ?? 0;
        $works_info_id = $params['works_info_id'] ?? 0;
        $is_end        = (int)($params['is_end'] ?? -1);

        if (empty($works_id) || empty($works_info_id)) {
            return ['code' => false, 'msg' => '参数错误'];
        }

        $query = Subscribe::query()
            ->with([
                'userInfo:id,phone,nickname',
                'historyInfo' => function ($q) use ($works_id, $works_info_id) {
                    $q->where('relation_type', '=', 5)
                        ->where('relation_id', '=', $works_id)
                        ->where('info_id', '=', $works_info_id)
                        ->select(['id', 'user_id', 'is_end', 'updated_at', 'end_time']);
                }
            ])
            ->where('relation_id', '=', $works_id)
            ->where('type', '=', 7)
            ->where('status', '=', 1)
            ->where('is_del', '=', 0)
            ->select(['id', 'user_id']);


        if ($is_end === 1) {
            $query->whereHas('historyInfo', function ($q) use ($works_id, $works_info_id) {
                $q->where('relation_type', '=', 5)
                    ->where('relation_id', '=', $works_id)
                    ->where('info_id', '=', $works_info_id)
                    ->where('is_end', '=', 1);
            });
        }

        if ($is_end === 0) {
            $query->where(function ($q) use ($works_id, $works_info_id) {
                $q->whereDoesntHave('historyInfo', function ($q) use ($works_id, $works_info_id) {
                    $q->where('relation_type', '=', 5)
                        ->where('relation_id', '=', $works_id)
                        ->where('info_id', '=', $works_info_id)
                        ->where('is_end', '=', 1);
                })->orWhereHas('historyInfo', function ($q) use ($works_id, $works_info_id) {
                    $q->where('relation_type', '=', 5)
                        ->where('relation_id', '=', $works_id)
                        ->where('info_id', '=', $works_info_id)
                        ->where('is_end', '!=', 1);
                });
            });
        }

        return $query->paginate($params['size'] ?? 10);

    }

    /**
     * 添加学习日志
     */
    public static function add_study_log($user_id,$data=[]){

        $validator = Validator::make($data, [
            'relation_id' => 'required|numeric',
            'relation_type' => 'required|numeric',
            'works_info_id' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return $validator->messages()->first();
        }

        //获取昨天连续学习时长
        $yesterday_continuity_days=StudyLogModel::query()->where('user_id',$user_id)->where('date',date("Y-m-d",strtotime("-1 day")))->orderBy('id','desc')->value('continuity_days')??0;


        //判断是否为开始学习 学习记录间隔超过10秒 为开始学习
        $is_start=0;
        $LastStudyLogModel=StudyLogModel::query()->where([
            'user_id'=>$user_id,
            'relation_id'=>$data['relation_id'],
            'relation_type'=>$data['relation_type'],
            'info_id'=>$data['works_info_id']
        ])->orderBy('id','desc')->first();
        if($LastStudyLogModel){
            $time=$LastStudyLogModel->created_at;
            if((time()-strtotime($time))>15){
                $is_start=1;
            }
        }else{
            $is_start=1;
        }

        $StudyLogModel= new StudyLogModel();
        $StudyLogModel->relation_id=$data['relation_id'];
        $StudyLogModel->relation_type=$data['relation_type'];
        $StudyLogModel->info_id=$data['works_info_id']??0;
        $StudyLogModel->user_id=$user_id;
        $StudyLogModel->time_number=10;
        $StudyLogModel->is_start=$is_start;
        $StudyLogModel->continuity_days=$yesterday_continuity_days+1;
        $StudyLogModel->date=date('Y-m-d');
        $StudyLogModel->year=date('Y');
        $StudyLogModel->month=date('m');
        $StudyLogModel->day=date('d');

        $StudyLogModel->save();
        return true;
    }
}
