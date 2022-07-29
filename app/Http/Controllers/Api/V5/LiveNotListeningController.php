<?php

namespace App\Http\Controllers\Api\V5;
use App\Http\Controllers\Controller;
use App\Servers\LiveConsoleServers;
use Illuminate\Support\Facades\DB;
use App\Models\Live;
use Illuminate\Http\Request;

class LiveNotListeningController extends Controller
{

    //标记未进直播间
    //http://127.0.0.1:8000/api/v5/live/notlisten
    public static function LiveNot()
    {
        //所有开播直播间  58
//        $live_ids = [
//            169,170,168,173,177,182,185,194,199,206,210,214,221,220,230,238,245,254,261,263,269,271,277,283,286,294,297,308,315,318,325,326,332,335,341,343,350,354,366,373,382,392,396,402,411,412,433,448,458,489,495,508,510,519,532,542,551,562
//        ];
        $live_ids = [
            343,350,354,366,373,382,392,396,402,411,412,433,448,458,489,495,508,510,519,532,542,551,562
        ];
        $lives = Live::select("id", "begin_at", "end_at")->whereIn('id', $live_ids)->orderBy('begin_at', 'asc')->get()->toArray();
//        echo '<pre>';
//        var_dump($lives);

        $order_table = "data_analysis_live49_9";
        $online_tabs = [
            "nlsg_live_online_user",
            "nlsg_live_online_user20220131",
        ];

        $while_flag = true;
        while ($while_flag) {

            $order_list = DB::table($order_table)->select("id", "user_id", "order_id")->where("is_scan", 0)->limit(500)->get()->toArray();
            if(empty($order_list)){
                $while_flag = false;
                break;
            }
//        var_dump($order_list);
//        return ;
            //遍历基础数据
            foreach ($order_list as $o_k => $o_v) {
//            echo 'order-------start------'.$o_v->id.PHP_EOL;
                $analysis_id = $o_v->id;
                $user_id = $o_v->user_id;
                $order_id = $o_v->order_id;

                //标记执行
                $time = date("Y-m-d H:i:s", time());
                $data = ['time' => $time, 'is_scan' => 1];
                DB::table($order_table)->where(['id' => $analysis_id])->update($data);

                //遍历每场直播第一天是否收听，收听一场即为收听 58场
                foreach ($lives as $key => $val) {
//                echo 'live-------start------'.$val['id'].PHP_EOL;
                    $start_time = strtotime("-1 day", strtotime($val["begin_at"]));
                    $begin_at = date("Y-m-d H:i:s", $start_time);
                    $end_at = date("Y-m-d H:i:s", $start_time + 90 * 60);

//                echo $begin_at.PHP_EOL;
//                echo $end_at;
//                exit;
                    $flag = 0;
                    //遍历在线记录 9张
                    foreach ($online_tabs as $ov) {
//                    var_dump($ov);
//                    exit;
//                    echo 'online-------start------'.$ov.PHP_EOL;
                        $online_data = DB::table($ov)->select("live_id", "user_id", "online_time")
                            ->where("user_id", $user_id)->where("online_time", ">", $begin_at)
                            ->where("online_time", "<", $end_at)->first();

                        if (!empty($online_data)) {
                            echo 'online-------end------收听' . $ov . PHP_EOL;
                            $flag = 1;
                            //更新收听标记
                            $data = ['is_flag' => 1, 'listen_time' => $online_data->online_time, 'listen_live_id' => $online_data->live_id];
                            DB::table($order_table)->where(['id' => $analysis_id])->update($data);
                            break;
                        }

                    }

                    if ($flag == 1) {
                        break;
                    }
                }
            }
        }
    }

    //批量跑在线数据
    //http://127.0.0.1:8000/api/v5/live/LiveOnlineAdd
    public   function LiveOnline()
    {
        LiveConsoleServers::LiveOnline();
//        LiveConsoleServers::OnlineRedisPush();

    }

}
