<?php

namespace App\Http\Controllers\Admin\V4;

use App\Http\Controllers\Controller;
use App\Models\Column;
use App\Models\Works;
use Illuminate\Http\Request;
use Carbon\Carbon;

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

    /**
    * @api {get} api/admin_v4/class/works 精品课
    * @apiVersion 4.0.0
    * @apiName  lecture
    * @apiGroup 虚拟课程
    * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/class/works
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

    public  function  works(Request $request)
    {
        $work_id  =  $request->get('work_id');
        $title    =  $request->get('title');
        $status   =  $request->get('status');
        $nickname =  $request->get('author');
        $category_id =  $request->get('category_id');
        $start    =  $request->get('start');
        $end      =  $request->get('end');
        $is_end   =  $request->get('is_end');

        $limit   =  $request->query('limit', 15);
        $offset  =   $request->query('offset', 0);


        $query  = Works::with('user:id,nickname')
                ->when($work_id, function ($query) use ($work_id) {
                    $query->where('id', $work_id);
                })
                ->when($status, function ($query) use ($status) {
                    $query->where('status', $status);
                })
                ->when($is_end, function ($query) use ($title) {
                    $query->where('is_end', $is_end);
                })
                ->when($title, function ($query) use ($title) {
                    $query->where('title', 'like', '%'.$title.'%');
                })
                ->when($nickname, function ($query) use ($nickname) {
                    $query->whereHas('user', function ($query) use ($nickname) {
                        $query->where('nickname', 'like', '%'.$nickname.'%');
                    });
                })
                ->when($category_id, function ($query) use ($category_id) {
                   $query->whereHas('categoryRelation', function ($query) use ($category_id) {
                       $query->where('category_id', $category_id);
                   });
                })
                ->when($start && $end, function ($query) use ($start, $end) {
                   $query->whereBetween('created_at', [
                       Carbon::parse($start)->startOfDay()->toDateTimeString(),
                       Carbon::parse($end)->endOfDay()->toDateTimeString(),
                   ]);
                });

        $lists = $query->select('id','title', 'type','is_end','created_at','user_id','view_num', 'status','price')
                  ->orderBy('id', 'desc')
                  ->limit($limit)
                  ->offset($offset)
                  ->get()
                  ->toArray();

        return success($lists);

    }

}
