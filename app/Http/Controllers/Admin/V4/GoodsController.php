<?php


namespace App\Http\Controllers\Admin\V4;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\servers\MallOrderServers;

class GoodsController extends Controller
{
    //todo 添加商品
    public function add(Request $request){



        $params = $request->input();

        return $this->success($params);
    }
}
