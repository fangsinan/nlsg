<?php

    /**
     * 成功输出
     * @param  array  $data
     * @return \Illuminate\Http\JsonResponse
     */
    function success($data =[]) {
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
    function error($code, $msg='') {
        $result = [
            'code' => $code,
            'msg'  => $msg,
            'data' => ''
        ];
        return  response()->json($result);
    }
