<?php

namespace App\Servers\V5;

use App\Models\Live;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class TempToolsServers
{
    public function meiKan() {
        $begin_time = date('Y-m-d H:i:s');
        $while_flag = true;
        $size       = 1000;
        $page       = 1;

        if (1) {
            //确定查询用户的直播数据
            while ($while_flag) {
                $temp_query = Order::query()
                    ->where('type', '=', 10)
                    ->where('pay_price', '=', 49.9)
                    ->where('is_shill', '=', 0)
                    ->where('status', '=', 1)
                    ->where('live_id', '>', 177)
                    ->where('live_id', '<=', 300)
                    ->select(['user_id', 'live_id'])
                    ->limit($size)
                    ->offset(($page - 1) * $size)
                    ->get();
                if ($temp_query->isEmpty()) {
                    $while_flag = false;
                } else {
                    $temp_query = $temp_query->toArray();

                    $temp_user_array = [];
                    $temp_live_array = [];

                    foreach ($temp_query as $v) {
                        $temp_user_array[] = ['user_id' => $v['user_id'], 'day_flag' => 1];
                        $temp_user_array[] = ['user_id' => $v['user_id'], 'day_flag' => 2];
                        $temp_live_array[] = ['live_id' => $v['live_id'], 'day_flag' => 1];
                        $temp_live_array[] = ['live_id' => $v['live_id'], 'day_flag' => 2];
                    }

                    DB::table('temp_tool_meikan_user')->insertOrIgnore($temp_user_array);
                    DB::table('temp_tool_meikan_live')->insertOrIgnore($temp_live_array);

                }
                $page++;
            }
        }


        if (1) {
            //匹配直播的首日日期和星期
            $live_check = Live::query()
                ->from('nlsg_live as l')
                ->join('temp_tool_meikan_live as ml', 'l.id', '=', 'ml.live_id')
                ->select(['ml.live_id', 'ml.id', 'l.begin_at', 'ml.day_flag'])
                ->get();

            $week_array = [7, 1, 2, 3, 4, 5, 6];

            foreach ($live_check as $v) {
                $temp = [];

                if ($v->day_flag === 1) {
                    $temp['first_day'] = date('Y-m-d', strtotime("$v->begin_at -1 days"));
                } else {
                    $temp['first_day'] = date('Y-m-d', strtotime("$v->begin_at"));
                }
                $temp['week'] = $week_array[date('w', strtotime($temp['first_day']))];

                DB::table('temp_tool_meikan_live')
                    ->where('id', '=', $v->id)
                    ->update($temp);
            }
        }


        if (1) {
            //匹配观看记录
            $online_user_model = [
                'nlsg_live_online_user',
                'nlsg_live_online_user20211110',
                'nlsg_live_online_user20211126',
                'nlsg_live_online_user20211211',
                'nlsg_live_online_user20211223',
                'nlsg_live_online_user20220105',
                'nlsg_live_online_user20220207',
            ];

            $live_id_array = DB::table('temp_tool_meikan_live')
                ->whereIn('week', [1, 2, 4, 5])
                ->get();

            foreach ($online_user_model as $uv) {
                foreach ($live_id_array as $lv) {
                    $sql = 'update temp_tool_meikan_user as mu
join ' . $uv . ' as ou on mu.user_id = ou.user_id
set mu.meikan = 1
where mu.meikan = 0 and mu.day_flag = 1 and ou.live_id = ' .
                        $lv->live_id . ' and ou.online_time_str like "' . $lv->first_day . '%"';
                    DB::select($sql);

                    if ($lv->day_flag === 1){
                        $sql = 'update temp_tool_meikan_user as mu
join ' . $uv . ' as ou on mu.user_id = ou.user_id
set mu.meikan = 1
where mu.meikan = 0 and mu.day_flag = 2 and ou.live_id = ' .
                            $lv->live_id . ' and ou.online_time_str like "' . $lv->first_day . '%"';
                        DB::select($sql);
                    }

                }
            }
        }


        return ['code' => true, 'msg' => 'ok', 'begin' => $begin_time, 'end' => date('Y-m-d H:i:s')];
    }


}
