<?php

namespace App\Http\Controllers\Api\V5;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Live;
use App\Models\Subscribe;
use App\Models\User;
use Illuminate\Http\Request;

class LiveNoEnterController extends Controller
{
    
    public function index(Request $request)
    {
        
        set_time_limit(0);
        ini_set("memory_limit","250M");
        // // 导数据到数据表
        // $this->UserToLiveEx();
        // dd(1);

        // 写入未在线用户
        // $this->notUserToTable();

        // 导出excel
        $this->toExcel();
    }

    function notUserToTable(){
        // 获取差异uid
        $live_ids = [271,277,283,286,294,297,300,308,315,318,325,326,332,335,341,343,350,354,366,373,382,391,392,396,402,411,412,433,448,458,489,490,495,508,510,519,534,543,551,552];
        // $live_ids = [335,341,343,350,354,366,373,382,391,392,396,402,411,412,433,448,458,489,490,495,508,510,519,534,543,551,552];
        $ex = DB::table("nlsg_live_online_user_ex")->select("user_id")->pluck("user_id")->toArray();
        $count = 0;
        $new_user_diff = [];
        
        foreach($live_ids as $live_id){
            $sub_user = Subscribe::select("user_id")->whereIn("relation_id",[$live_id])->where([
                "type" => 3,
                "status"=>1,
            ])->GroupBy("user_id")->pluck("user_id")->toArray();

            $new_nv_add = [];
            $diff_user_id = array_diff($sub_user,$ex);
            $new_diff = array_chunk($diff_user_id,10000);
            foreach($new_diff as $val ){
                foreach ($val as $addv){
                    $new_nv_add[] = [
                        "live_id"       => $live_id,
                        "user_id"       => $addv,
                    ];
                }
                DB::table("nlsg_live_online_user_ex_not")->insertOrIgnore($new_nv_add);
                $new_nv_add = [];
            }
            $new_diff = [];
            $diff_user_id=[];
            $sub_user=[];
            
            // // $new_diff = array_chunk($diff_user_id,10000);
            // foreach($new_diff as $nv){
            //     $is_off_user =Subscribe::whereIn("relation_id",[1,2,3,4,5,6,7,8,9,10,11,12,13,14,15])->where([
            //         "type" => 5,
            //         "status"=>1,
            //         ])->whereIn("user_id",$nv)->pluck("user_id")->toArray();

            //         $nv_user_diff = array_diff($nv,$is_off_user);
            //         $new_nv_add = [];
            //         foreach($nv_user_diff as $val ){
            //             $new_nv_add[] = [
            //                 "live_id"       => $live_id,
            //                 "user_id"       => $val,
            //             ];
            //         }
            //         dd($nv_user_diff);
                    
            // }
            // // 是否购买线下课
        //     foreach($diff_user_id as $add){
        //         // 过滤线下课
        //         $is_off_user =Subscribe::select("user_id","relation_id","pay_time")->whereIn("relation_id",[1,2,3,4,5,6,7,8,9,10,11,12,13,14,15])->where([
        //             "type" => 5,
        //             "status"=>1,
        //             "user_id" =>$add,
        //             ])->value("user_id");
        //         if( empty($is_off_user)){
        //             // 处理新增数据  添加唯一id
        //             // 插入忽略uid唯一错误
                   
        //         }
                
        //    }
        }
    }

    // public function LiveExDelSub()
    // {
    //     # 去除线下课
    //     $ex = DB::table("nlsg_live_online_user_ex")->select("user_id")->pluck("user_id")->toArray();
    //     // 为了走索引  加的relation_id
    //     $sub_user = Subscribe::select("user_id")->whereIn("relation_id",[1,2,3,4,5,6,7,8,9,10,11,12,13,14,15])->where([
    //         "type" => 3,
    //         "status"=>1,
    //     ])->whereIn("user_id",$ex)->GroupBy("user_id")->pluck("user_id")->toArray();
        
    //     $add = DB::table("nlsg_live_online_user_ex")->whereIn("user_id",$user_id)->delete();
    // }

    public function toExcel()
    {


        $columns = ['用户id', '用户手机号', '用户昵称', '直播名称',];

        $fileName = date('Y-m-d H:i') . '-' . random_int(10, 99) . '.csv';
        header('Content-Description: File Transfer');
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header("Access-Control-Allow-Origin: *");
        $fp = fopen('php://output', 'a');//打开output流
        mb_convert_variables('GBK', 'UTF-8', $columns);
        fputcsv($fp, $columns);     //将数据格式化为CSV格式并写入到output流中
        
        $lives = Live::select("id","begin_at","end_at","title")->where([
            "classify" => 1,"is_test"=>0,
        ])->get()->toArray();
        
        $new_live = [];
        foreach($lives as $key=>$val){
            $new_live[$val['id']] = $val['title']."--交付课";
        }

        $of_user_ids = Subscribe::select("user_id")->whereIn("relation_id",[4,5,6,7])->where([
            "type" => 5,
            "status"=>1,
        ])->GroupBy("user_id")->pluck("user_id")->toArray();

        $flag = true;
        $page = 1;
        $new_user = [];
        while($flag){
            $not_uid_datas = DB::table("nlsg_live_online_user_ex_not")->select("user_id","live_id")
            // ->where("id",">=",1087327)
            // ->where("id","<",)
            ->paginate(3000,"",'',$page)->toArray();
            if(empty($not_uid_datas['data'])){
                $flag = false;
                break;
            }

            $not_uids = json_decode(json_encode($not_uid_datas['data']),true);
            $page++;
            $uids = array_column($not_uids,"user_id");
            $user = User::select("id","phone","nickname")->whereIn("id",$uids)->get()->toArray();
            $new_user_e = [];
            foreach($user as $key=>$val){
                $new_user_e[$val['id']]['phone'] = $val['phone'];
                $new_user_e[$val['id']]['nickname'] = $val['nickname'];
            }
            foreach($not_uids as $key=>$nval){
                // 过滤线下课
                if(!in_array($val,$of_user_ids)){
                    $ex_user = [
                        "user_id" => $nval['user_id'],
                        "phone" => $new_user_e[$nval['user_id']]['phone']??"",
                        "nickname" => $new_user_e[$nval['user_id']]['nickname']??'',
                        "live_title" => $new_live[$nval['live_id']]??'',
                    ];
                    $temp_v = [
                        '`'.$ex_user['user_id'],'`'.$ex_user['phone'],$ex_user['nickname'],
                        $ex_user['live_title'],
                    ];
    
                    mb_convert_variables('GBK', 'UTF-8', $temp_v);
                    fputcsv($fp, $temp_v);
                    ob_flush();     //刷新输出缓冲到浏览器
                    flush();        //必须同时使用 ob_flush() 和flush() 函数来刷新输出缓冲。
                }
                
            }

        }
        // 获取差异uid
        // $live_ids = [271,277,283,286,294,297,300,308,315,318,325,326,332,335,341,343,350,354,366,373,382,391,392,396,402,411,412,433,448,458,489,490,495,508,510,519,534,543,551,552];
        fclose($fp);
        exit();
    }


    public function UserToLiveEx(){
        $lives = Live::select("id","begin_at","end_at")->where([
            "classify" => 1,"is_test"=>0,
        ])->where('id',"<",382)
        ->get()->toArray();

        $new_add_table = "nlsg_live_online_user_ex";
        $online_tabs = [
            "nlsg_live_online_user20220207",
            "nlsg_live_online_user20220306",
            "nlsg_live_online_user20220320",
            "nlsg_live_online_user20220601",
            "nlsg_live_online_user",

        ];
        
        foreach ($lives as $key=>$val){
            $begin_at = date("Y-m-d H:i:s",strtotime("-1 day",strtotime($val["begin_at"])));
            $end_at = date("Y-m-d H:i:s",strtotime("-1 day",strtotime($val["end_at"])));
            foreach ($online_tabs as $ov){
                $flag = true; 
                $page = 1; 
                while ($flag){
                    $online_data = DB::table($ov)->select("live_id","user_id","online_time")
                        ->where("live_id",$val['id'])
                        ->where("online_time",">",$begin_at)
                        ->where("online_time","<",$end_at)
                        // ->orderBy('id')
                        ->groupBy('user_id')->paginate(3000,"",'',$page)->toArray();
                    dump($online_data);
                    if(empty($online_data['data'])){
                        $flag = false;
                        break;
                    }
                    // 去除线下课未写

                    foreach($online_data['data'] as $add){
                         // 处理新增数据  添加唯一id
                        // 插入忽略uid唯一错误
                        DB::table($new_add_table)->insertOrIgnore([
                            "live_id"       => $add->live_id,
                            "user_id"       => $add->user_id,
                            "online_time"   => $add->online_time,
                        ]);
                    }
                    $page++;
                }
            }
        }
    }
}
