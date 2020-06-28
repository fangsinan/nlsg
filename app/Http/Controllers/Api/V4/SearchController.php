<?php


namespace App\Http\Controllers\Api\V4;


use App\Http\Controllers\Controller;
use App\Models\Column;
use App\Models\MallGoods;
use App\Models\Works;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    //全局搜索
    public function index(Request $request)
    {
        $keywords = $request->input('keywords','');
//        $type     = $params['type'];
        $pageSize          = 50;
        $page              = 1;

        //搜索专栏
        $res['column'] = Column::search($keywords,1);
        //课程
        $res['works'] = Works::search($keywords,0);
        //讲座
        $res['lecture'] = Column::search($keywords,2);
        //听书
        $res['listen_book'] = Works::search($keywords,1);
        //用户
        //商品
        MallGoods::search($keywords);

        return $this->success($res);

    }

}