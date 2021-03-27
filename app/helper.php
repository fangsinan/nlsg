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
        $str = round($number * 0.001 * 0.1, 2)."万";
        return $str;
    }
