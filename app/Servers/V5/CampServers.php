<?php

namespace App\Servers\V5;

use App\Models\Column;
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

        $size = $params['size'] ?? 10;
        $page = $params['page'] ?? 1;

        $offset = max(($page - 1) * $size,0);



        $sql = " from (
SELECT
sub.id as sub_id,his.id as history_id,sub.user_id,u.nickname,u.phone,
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

        if ($is_end === 0){
            $sql .= ' where is_end = 0';
        }

        $count_sql = 'select count(*) as counts '.$sql;
        $query_sql = 'select *  '.$sql.' limit '.$size.' offset '.$offset;

        $total = DB::select($count_sql);
        $data = DB::select($query_sql);



//current_page: 1
//data: [{id: 12808957, user_id: 7221831, is_end: 1, end_time: "2022-01-18 21:57:36",…},…]
//first_page_url: "http://app.v4.api.nlsgapp.com/api/admin_v4/class/camp_clock_in_info?page=1"
//from: 1
//last_page: 30
//last_page_url: "http://app.v4.api.nlsgapp.com/api/admin_v4/class/camp_clock_in_info?page=30"
//next_page_url: "http://app.v4.api.nlsgapp.com/api/admin_v4/class/camp_clock_in_info?page=2"
//path: "http://app.v4.api.nlsgapp.com/api/admin_v4/class/camp_clock_in_info"
//per_page: "10"
//prev_page_url: null
//to: 10
//total: 298

        foreach ($data as &$v){
            $v->userInfo = [
                'id'=>$v->user_id,
                'nickname'=>$v->nickname,
                'phone'=>$v->phone,
            ];
        }

        $res['current_page'] = $page;
        $res['data'] = $data;
        $res['total'] = $total[0]->counts ?? 0;

        return $res;


//        $query = History::query()
//            ->where('relation_type', '=', 5)
//            ->where('relation_id', '=', $works_id)
//            ->where('info_id', '=', $works_info_id)
//            ->select([
//                'id', 'user_id', 'is_end',
//                DB::raw("IF(is_end = 0,'-',(IF(end_time is NULL,updated_at,end_time))) as end_time")
//            ])
//            ->with(['userInfo:id,phone,nickname']);
//
//        if ($is_end !== -1) {
//            $query->where('is_end', '=', $is_end);
//        }
//
//        return $query->paginate($params['size'] ?? 10);

//        $query = Subscribe::query()->from('nlsg_subscribe as sub')
//            ->leftJoin('nlsg_history as his', function ($q) use ($works_id, $works_info_id) {
//                $q->on('sub.user_id', '=', 'his.user_id')
//                    ->where('his.relation_type', '=', 5)
//                    ->where('his.relation_id', '=', $works_id)
//                    ->where('his.info_id', '=', $works_info_id);
//            })
//            ->where('sub.relation_id', '=', $works_id)
//            ->where('sub.type', '=', 7)
//            ->where('sub.status', '=', 1)
//            ->where('sub.is_del', '=', 0)
//            ->select([
//                'sub.id', 'sub.user_id',
//                DB::raw("if(his.is_end = 1,1,0) as is_end"),
//                DB::raw("if(his.is_end = 1,(IF(his.end_time is NULL,his.updated_at,his.end_time)),'-') as end_time"),
//            ])
//            ->with(['userInfo:id,phone,nickname']);
//
//        if ($is_end === 1) {
//            $query->where('his.is_end', '=', 1);
//        }
//
//        if ($is_end === 0){
////            $query->whereRaw('his.is_end =0 or his.is_end is NULL');
//        }
//
//        return $query->paginate($params['size'] ?? 10);

//        $query = Subscribe::query()
//            ->with([
//                'userInfo:id,phone,nickname',
//                'historyInfo' => function ($q) use ($works_id, $works_info_id) {
//                    $q->where('relation_type', '=', 5)
//                        ->where('relation_id', '=', $works_id)
//                        ->where('info_id', '=', $works_info_id)
//                        ->select(['id', 'user_id', 'is_end', 'updated_at', 'end_time']);
//                }
//            ])
//            ->where('relation_id', '=', $works_id)
//            ->where('type', '=', 7)
//            ->where('status', '=', 1)
//            ->where('is_del', '=', 0)
//            ->select(['id', 'user_id']);
//
//
//        if ($is_end === 1) {
//            $query->whereHas('historyInfo', function ($q) use ($works_id, $works_info_id) {
//                $q->where('relation_type', '=', 5)
//                    ->where('relation_id', '=', $works_id)
//                    ->where('info_id', '=', $works_info_id)
//                    ->where('is_end', '=', 1);
//            });
//        }

//        if ($is_end === 0){
//        $query->whereHas('historyInfo', function ($q) use ($works_id, $works_info_id) {
//            $q->where('relation_type', '=', 5)
//                ->where('relation_id', '=', $works_id)
//                ->where('info_id', '=', $works_info_id)
//                ->where('is_end', '!=', 1);
//        });

//            $query->where(function($q)use($works_id,$works_info_id){
//                $q->doesntExist('historyInfo');
//                    ->orWhereHas('historyInfo',function($q)use($works_id,$works_info_id){
//                        $q->where('relation_type','=',5)
//                            ->where('relation_id','=',$works_id)
//                            ->where('info_id','=',$works_info_id)
//                            ->where('is_end','!=',1);
//                    });
//            });
//        }

//        DB::connection()->enableQueryLog();
//        $query->limit(10)->get();
//        dd(DB::getQueryLog());

//        return $query->paginate($params['size'] ?? 10);


    }


}
