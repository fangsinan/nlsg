<?php
namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use App\Models\Announce;
use App\Models\Banner;

class IndexController extends Controller
{

    public function index()
    {
        return  'hello world';
    }

    /**
     * @api {post} api/v4/index/announce  获取首页公告
     * @apiVersion 1.0.0
     * @apiName  announce
     * @apiGroup Index
     *
     */
    public function announce()
    {
        $list = Announce::select('id','content')
            ->latest()->first()->toArray();
        return $this->success($list);
    }
    /**
     * @api {post} api/v4/index/banner  获取banner
     * @apiVersion 1.0.0
     * @apiName  banner
     * @apiGroup Index
     *
     */
    public function banner()
    {

    }




}
