<?php

    /**
     * 成功输出
     * @param  array  $data
     * @return \Illuminate\Http\JsonResponse
     */
    function success($data ='') {
        $result = [
            'code' => 200,
            'msg'  => '成功',
            'data' => $data
        ];
        return  response()->json($result);
    }

    /**
     * 错误输出
     * @param $code
     * @param  string  $msg
     * @return \Illuminate\Http\JsonResponsew
     */
    function error($code, $msg='',$data='') {
        $result = [
            'code' => $code,
            'msg'  => $msg,
            'data' => $data
        ];
        return  response()->json($result);
    }

    function  covert_img($url, $img_ulr = ''){
        $config_img = $img_ulr == '' ? config('env.IMAGES_URL') : '';
        if (strpos($url, 'http') !== false || strpos($url, 'https') !== false) {
            $url = str_replace($config_img, '', $url);
        }
        return $url;
    }

    function covert_time($seconds)
    {
        if ($seconds > 3600) {
            $hours = intval($seconds / 3600);
            $time = $hours . ":" . gmstrftime('%M:%S', $seconds);
        } else {
            $time = gmstrftime('%H:%M:%S', $seconds);
        }
        return $time;
    }

    function float_number($number){
        $str = round($number * 0.001 * 0.1, 4);
        return $str;
    }

    /**
     * 转换成 年 天 时 分 秒
     */
    function SecToTime($time)
    {
        if (is_numeric($time)) {
            if($time <=0 ){
                return '1秒';
            }
            $value = array(
                "years" => 0, "days" => 0, "hours" => 0,
                "minutes" => 0, "seconds" => 0,
            );
            $t = '';
            if ($time >= 31556926) {
                $value["years"] = floor($time / 31556926);
                $time = ($time % 31556926);
                $t .= $value["years"] . "年";
            }
            if ($time >= 86400) {
                $value["days"] = floor($time / 86400);
                $time = ($time % 86400);
                $t .= $value["days"] . "天";
            }
            if ($time >= 3600) {
                $value["hours"] = floor($time / 3600);
                $time = ($time % 3600);
                $t .= $value["hours"] . "小时";
            }
            if ($time >= 60) {
                $value["minutes"] = floor($time / 60);
                $time = ($time % 60);
                $t .= $value["minutes"] . "分";
            }
            //分钟数如果都为空 则单独显示秒
            if($t == ''){
                $value["seconds"] = floor($time);
//            return (array) $value;
                $t .= $value["seconds"] . "秒";
            }

            return $t;

        } else {
            return '1秒';
        }
    }


    /*
     * $type = 1  本周周一   2 上周周一
     * */
    function getWeekDay(){
        $timestr = time();         //当前时间戳
        $now_day = date('w',$timestr);      //当前是周几
        $now_day = $now_day?$now_day:7;         //周日为0

        //获取周一
        $monday_str = $timestr - ($now_day-1)*86400;
        $monday = date('Y-m-d', $monday_str);

        //获取周日
//        $sunday_str = $timestr + (7-$now_day)*86400;
//        $sunday = date('Y-m-d', $sunday_str);
//        for($i=0;$i<7;$i++)
//        {
//            $arr[$i]=date('Y-m-d',strtotime($monday.'+'.$i.'day'));
//        }
        $top_monday = date('Y-m-d', $monday_str-86400*7);

        return [
            'monday'    =>$monday,
            'top_monday'=>$top_monday,
        ];
    }
	
	/**
	 * @param $res
	 * @return bool
	 * 检查返回结果
	 */
	function checkRes($res){
	
	    if($res === true ||  is_array($res) ||is_object($res)){
	        return true;
	    }
	    if(is_numeric($res)){
	        return true;
	    }
	
	    return false;
	
	}
	
	/**
	 * 秒数 转换成 1:01
	 */
	function TimeToMinSec($time)
	{
	    if (is_numeric($time)) {
	        if($time <=0 ){
	            return '00:01';
	        }
	        $value = array(
	            "years" => 0, "days" => 0, "hours" => 0,
	            "minutes" => 0, "seconds" => 0,
	        );
	        $t = '';
	
	        if ($time >= 60) {
	            $value["minutes"] = floor($time / 60);
	            $time = ($time % 60);
	            $t .= $value["minutes"] . ":";
	        }else{
	            $t .= "00:";
	        }
	        //分钟数如果都为空 则单独显示秒
	        if($t == ''){
	            $value["seconds"] = floor($time);
	
	            $t .= $value["seconds"] . ":";
	        }else{
	            $t .= "01";
	        }
	
	        return $t;
	
	
	    } else {
	        return '00:01';
	    }
	}
	
	function add_log($url,$message='',$parameter=''){
	    Illuminate\Support\Facades\DB::table('nlsg_log')->insert([
	        'url'=>$url,
	        'code'=>'info',
	        'type'=>2,
	        'message'=>$message,
	        'parameter'=>$parameter,
	        'created_at'=>date('Y-m-d H:i:s'),
	    ]);
	}
	
	
	function get_page_size($data){
	    if(empty($data['size']) || !is_numeric($data['size']) || $data['size']<=0){
	        return 10;
	    }
	    return  $data['size'];
	}

    // 统一全局 种类 类型 [ 配置对各个表进行类型对应 ]
    // 110专栏  120课程  130讲座  140训练营  150 商品  160集合（161 大咖讲书） 170 直播

    // 收藏表类型  nlsg_collection => 1专栏  2课程  3商品  4书单 5百科 6听书 7讲座  8训练营',
    // 历史记录表类型 nlsg_history => 1专栏   2讲座  3听书  4精品课程   5训练营
    // 订阅表类型 nlsg_subscribe  => 1 专栏  2作品  3 直播  4会员 5线下产品  6讲座  7训练营  8专题
    function FuncType($type=0)
    {
        
        switch($type){
            case 110:    // 110专栏 
                $res = [
                    'col_type' => 1,    // 收藏表类型  nlsg_collection
                    'his_type' => 1,    // 历史记录表类型 nlsg_history
                    'sub_type' => 1,    // 订阅表类型
                ];
                break;
            case 120: // 120课程
                $res = [
                    'col_type' => 2,    // 收藏表类型  nlsg_collection
                    'his_type' => 4,    // 历史记录表类型 nlsg_history
                    'sub_type' => 2,    // 订阅表类型
                ];
                break;
            case 130:  // 130讲座 
                $res = [
                    'col_type' => 7,    // 收藏表类型  nlsg_collection
                    'his_type' => 2,    // 历史记录表类型 nlsg_history
                    'sub_type' => 6,    // 订阅表类型
                ];
                break;;break;
            case 140:  // 140训练营
                $res = [
                    'col_type' => 8,    // 收藏表类型  nlsg_collection
                    'his_type' => 5,    // 历史记录表类型 nlsg_history
                    'sub_type' => 7,    // 订阅表类型
                ];
                break;
            case 150: // 150 商品
                $res = [
                    'col_type' => 3,    // 收藏表类型  nlsg_collection
                    'his_type' => 0,    // 历史记录表类型 nlsg_history
                    'sub_type' => 0,    // 订阅表类型
                ];
                break;
            case 160: // 160 集合
            case 161: //大咖讲书
                $res = [
                    'col_type' => 4,    // 收藏表类型  nlsg_collection
                    'his_type' => 0,    // 历史记录表类型 nlsg_history
                    'sub_type' => 8,    // 订阅表类型
                ];
                break;
            
            case 170:  // 170 直播
                $res = [
                    'col_type' => 0,    // 收藏表类型  nlsg_collection
                    'his_type' => 0,    // 历史记录表类型 nlsg_history
                    'sub_type' => 3,    // 订阅表类型
                ];
                break;
            default:
                $res = [
                    'col_type' => 0,    // 收藏表类型  nlsg_collection
                    'his_type' => 0,    // 历史记录表类型 nlsg_history
                    'sub_type' => 0,    // 订阅表类型
                ];
                break;
        }

        return $res;
    }
