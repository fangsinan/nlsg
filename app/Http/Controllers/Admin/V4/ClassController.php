<?php

namespace App\Http\Controllers\Admin\V4;

use App\Http\Controllers\Controller;
use App\Models\Column;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    public function index()
    {

    }

    /**
     * @api {get} api/admin_v4/class/column 专栏列表
     * @apiVersion 4.0.0
     * @apiName  column
     * @apiGroup 虚拟课程
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/class/column
     *
     * @apiParam {number} page 分页
     *
     * @apiSuccessExample  Success-Response:
     * HTTP/1.1 200 OK
     * {
     *   "code": 200,
     *   "msg" : '成功',
     *   "data": {
     *
     *    }
     * }
     */
    public   function  column()
    {
        $lists = Column::with('user:id,nickname,phone')
                ->select('id','user_id','sort','name','title','subtitle','message','status','original_price','price','twitter_price','cover_pic','details_pic')
                ->where('type', 1)
                ->orderBy('created_at','desc')
                ->paginate(10)
                ->toArray();
        return success($lists['data']);
    }

    /**
     * @api {get} api/admin_v4/class/lecture 讲座列表
     * @apiVersion 4.0.0
     * @apiName  lecture
     * @apiGroup 虚拟课程
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/class/lecture
     *
     * @apiParam {number} page 分页
     *
     * @apiSuccessExample  Success-Response:
     * HTTP/1.1 200 OK
     * {
     *   "code": 200,
     *   "msg" : '成功',
     *   "data": {
     *
     *    }
     * }
     */
    public  function  lecture()
    {
        $lists = Column::with('user:id,nickname,phone')
                       ->select('id','user_id','sort','name','title','subtitle','message','status','original_price','price','twitter_price','cover_pic','details_pic')
                       ->where('type', 2)
                       ->orderBy('created_at','desc')
                       ->paginate(10)
                       ->toArray();
               return success($lists['data']);
    }

}
