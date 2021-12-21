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
            $value["seconds"] = floor($time);
            //return (array) $value;
            $t .= $value["seconds"] . "秒";
            return $t;

        } else {
            return (bool) false;
        }
    }