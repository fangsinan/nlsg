<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function success($data =[])
    {
        $result = [
            'code' =>200,
            'msg'  =>'成功',
            'data' => $data
        ];
        return  response()->json($result);
    }

    protected function error($code, $msg='')
    {
        $result = [
            'code' => $code,
            'msg'  => $msg,
            'data' => ''
        ];
        return  response()->json($result);
    }

}
