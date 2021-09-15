<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class LiveSonFlagPoster extends Model
{
    protected $table = 'nlsg_live_son_flag_poster';

    public function getList($params)
    {
        $live_id = $params['live_id'];
        $size = $params['size'];
        $check_live_id = Live::where('id', '=', $live_id)->first();


        $query = DB::table('nlsg_live_son_flag_poster as p')
            ->join('nlsg_backend_live_role as lr', 'p.son_id', '=', 'lr.son_id')
            ->join('nlsg_live as l', 'p.live_id', '=', 'l.id')
            ->where('p.live_id', '=', $live_id)
            ->where('p.is_del', '=', 0)
            ->where('lr.parent_id', '=', $check_live_id->user_id)
            ->select(['p.id', 'p.live_id', 'p.son_id', 'p.status', 'lr.son', 'lr.son_flag', 'l.title', 'l.begin_at']);

        if (!empty($params['status'] ?? 0)) {
            $query->where('p.status', '=', $params['status']);
        }
        $query->orderBy('lr.sort','asc'); //海报排序

//        $bg_img = LivePoster::where('live_id', '=', $live_id)->where('status', '=', 1)
//            ->select(['image'])
//            ->get();

        $temp_bg_img = ConfigModel::getData(57);
        $temp_bg_img = explode(',', $temp_bg_img);
        $bg_img = [];
        foreach ($temp_bg_img as $v) {
            $temp_data['image'] = $v;
            $bg_img[] = $temp_data;
        }

        $res = $query->paginate($size);
        $custom = collect(['bg_img' => $bg_img]);
        return $custom->merge($res);
    }

    public function createPosterByLiveId($live_id = 0)
    {
        $check_live_id = Live::where('id', '=', $live_id)->first();
        if (empty($check_live_id)) {
            return ['code' => false, 'msg' => 'live_id错误'];
        }

        $son_flag = BackendLiveRole::where('parent_id', '=', $check_live_id->user_id)
            ->where('status','=',1)
            ->select(['son', 'son_id', 'son_flag'])
            ->get();


        if ($son_flag->isEmpty()) {
            return ['code' => true, 'msg' => '没有渠道'];
        }

        $son_flag = $son_flag->toArray();
        //已经添加的
        $old = LiveSonFlagPoster::where('live_id', '=', $live_id)
            ->where('is_del', '=', 0)
            ->pluck('son_id')
            ->toArray();

        $son_id_list = array_column($son_flag, 'son_id');
        //返回交集
        $intersect = array_intersect($son_id_list, $old);
        //返回差集
        $add_son_list = array_diff($son_id_list, $intersect);
        $del_son_list = array_diff($old, $intersect);

        DB::beginTransaction();

        if (!empty($del_son_list)) {
            $del_res = LiveSonFlagPoster::where('live_id', '=', $live_id)
                ->where('is_del', '=', 0)
                ->whereIn('son_id', $del_son_list)
                ->update([
                    'is_del' => 1
                ]);

            if ($del_res == false) {
                DB::rollBack();
                return ['code' => false, 'msg' => '失败' . __LINE__];
            }
        }

        if (!empty($add_son_list)) {
            $add_data = [];
            foreach ($add_son_list as &$v) {
                $temp_add_data = [];
                $temp_add_data['live_id'] = $live_id;
                $temp_add_data['son_id'] = $v;
                $temp_add_data['status'] = 1;
                $add_data[] = $temp_add_data;
            }

            $add_res = DB::table('nlsg_live_son_flag_poster')->insert($add_data);
            if ($add_res == false) {
                DB::rollBack();
                return ['code' => false, 'msg' => '失败' . __LINE__];
            }
        }

        DB::commit();
        return ['code' => true, 'msg' => '成功'];

    }


}
