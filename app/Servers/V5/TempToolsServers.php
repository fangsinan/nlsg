<?php

namespace App\Servers\V5;

use App\Models\Live;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Predis\Client;

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

                    if ($lv->day_flag === 1) {
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

    //临时 用于筛选9.9
    public function meikan2() {
        $begin_time = date('Y-m-d H:i:s');
        $while_flag = true;
        $size       = 1000;
        $page       = 1;

        if (0) {
            //获取9.9的用户id
            while ($while_flag) {
                $temp_query = Order::query()
                    ->where('type', '=', 10)
                    ->where('pay_price', '=', 9.9)
                    ->where('is_shill', '=', 0)
                    ->where('status', '=', 1)
                    ->where('live_id', '>', 89)
                    ->where('live_id', '<=', 300)
                    ->select(['user_id', 'live_id'])
                    ->limit($size)
                    ->offset(($page - 1) * $size)
                    ->get();
                if ($temp_query->isEmpty()) {
                    $while_flag = false;
                } else {
                    $temp_query      = $temp_query->toArray();
                    $temp_user_array = [];
                    foreach ($temp_query as $v) {
                        $temp_user_array[] = ['user_id' => $v['user_id'], 'day_flag' => 1];
                        $temp_user_array[] = ['user_id' => $v['user_id'], 'day_flag' => 2];
                    }
                    DB::table('temp_tool_meikan_user')->insertOrIgnore($temp_user_array);
                }
                $page++;
            }
        }

        if (0) {
            //获取直播
            $while_flag = true;
            $page       = 1;
            while ($while_flag) {
                $temp_query = Order::query()
                    ->where('type', '=', 10)
                    ->whereIn('pay_price', [49.9, 9.9])
                    ->where('is_shill', '=', 0)
                    ->where('status', '=', 1)
                    ->where('live_id', '>', 89)
                    ->where('live_id', '<=', 300)
                    ->select(['user_id', 'live_id','pay_price'])
                    ->limit($size)
                    ->offset(($page - 1) * $size)
                    ->get();
                if ($temp_query->isEmpty()) {
                    $while_flag = false;
                } else {
                    $temp_query      = $temp_query->toArray();
                    $temp_live_array = [];
                    foreach ($temp_query as $v) {
                        $temp_live_array[] = ['live_id' => $v['live_id'], 'day_flag' => 1,'pay_price'=>$v['pay_price']];
                        $temp_live_array[] = ['live_id' => $v['live_id'], 'day_flag' => 2,'pay_price'=>$v['pay_price']];
                    }
                    DB::table('temp_tool_meikan_live')->insertOrIgnore($temp_live_array);
                }
                $page++;
            }
        }

        if (0) {
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
                ->where(function($q){
                    $q->where('pay_price','=',49.9)->whereIn('week', [1, 2, 4, 5]);
                })
                ->orWhere('pay_price','=',9.9)
                ->get();


            foreach ($online_user_model as $uv) {
                foreach ($live_id_array as $lv) {
                    $sql = 'update temp_tool_meikan_user as mu
join ' . $uv . ' as ou on mu.user_id = ou.user_id
set mu.meikan = 1
where mu.meikan = 0 and mu.day_flag = 1 and ou.live_id = ' .
                        $lv->live_id . ' and ou.online_time_str like "' . $lv->first_day . '%"';
                    DB::select($sql);

                    if ($lv->day_flag === 1) {
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

    public function meikan3(){
        $while_flag = true;
        $size       = 1000;
        $page       = 1;

        if (0) {
            //确定查询用户的直播数据
            while ($while_flag) {
                $temp_query = Order::query()
                    ->where('type', '=', 10)
                    ->where('is_shill', '=', 0)
                    ->where('status', '=', 1)
                    ->whereIn('pay_price',[9.9,49.9])
                    ->where('live_id', '>', 100)
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
                        $temp_live_array[] = ['live_id' => $v['live_id'], 'day_flag' => 1];
                    }

                    DB::table('temp_tool_meikan_user')->insertOrIgnore($temp_user_array);
                    DB::table('temp_tool_meikan_live')->insertOrIgnore($temp_live_array);

                }
                $page++;
            }
        }

        if (0) {
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

            $live_id_array = DB::table('temp_tool_meikan_live')->get();


            foreach ($online_user_model as $uv) {
                foreach ($live_id_array as $lv) {
                    $sql = 'update temp_tool_meikan_user as mu
join ' . $uv . ' as ou on mu.user_id = ou.user_id
set mu.meikan = 1
where mu.meikan = 0 and mu.day_flag = 1 and ou.live_id = ' .
                        $lv->live_id;
                    DB::select($sql);

                }
            }
        }


    }

    public function insertOnlineUserTest(){
        $this->createTestRedisData();


        $begin_time = time();

        $redisConfig = config('database.redis.default');
        $Redis = new Client($redisConfig);
        $Redis->select(0);
        $all_count = $Redis->scard('test_user_id_set_1');


        $while_flag = true;
        $time_str = date('Y-m-d H:i');

        while($while_flag){
            $temp_list = $Redis->srandmember('test_user_id_set_1', 10000);
            if (empty($temp_list)) {
                $while_flag = false;
            }else{

                $add_data = [];
                foreach ($temp_list as $v){
                    $add_data[] = [
                        'live_id'=>999,
                        'user_id' => $v,
                        'live_son_flag' => 168934,
                        'online_time_str' => $time_str,
                    ];
                }

                DB::table('nlsg_live_online_user_innodb')->insert($add_data);
                $Redis->srem('test_user_id_set_1', $temp_list);
            }
        }

        $begin_time_2 = time();
        $all_count_2 = $Redis->scard('test_user_id_set_2');
        $while_flag = true;
        $time_str = date('Y-m-d H:i');

        while($while_flag){
            $temp_list = $Redis->srandmember('test_user_id_set_2', 10000);
            if (empty($temp_list)) {
                $while_flag = false;
            }else{
                $add_data = [];
                foreach ($temp_list as $v){
                    $add_data[] = [
                        'live_id'=>999,
                        'user_id' => $v,
                        'live_son_flag' => 168934,
                        'online_time_str' => $time_str,
                    ];
                }
                DB::table('nlsg_live_online_user_myisam')->insert($add_data);
                $Redis->srem('test_user_id_set_2', $temp_list);
            }
        }


        dd([
            '条数_1'=>$all_count,
            '开始_1'=>$begin_time,
            '结束_1'=>time(),
            '使用_1'=>time() - $begin_time,
            '条数_2'=>$all_count_2,
            '开始_2'=>$begin_time_2,
            '结束_2'=>time(),
            '使用_2'=>time() - $begin_time_2,
        ]);





    }

    public function createTestRedisData(){
        $redisConfig = config('database.redis.default');
        $Redis = new Client($redisConfig);
        $Redis->select(0);

        //生成100个随机数的数组

        $user_id_array = [];
        for ($i = 1; $i <= 100000; $i++) {
            $user_id_array[] = $i;
        }
        $Redis->sadd('test_user_id_set_1', $user_id_array);
        $Redis->sadd('test_user_id_set_2', $user_id_array);

    }

}
