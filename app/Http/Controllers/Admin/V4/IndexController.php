<?php

namespace App\Http\Controllers\Admin\V4;

use App\Http\Controllers\Controller;
use App\Models\Lists;
use App\Models\ListsWork;
use App\Models\Recommend;
use App\Models\Works;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    /**
     * @api {get} api/admin_v4/index/works 精选课程
     * @apiVersion 4.0.0
     * @apiName  index/works
     * @apiGroup 后台-虚拟课程
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/index/works
     * @apiDescription 精选课程
     *
     * @apiSuccess {string} title  标题
     * @apiSuccess {string} subtitle  副标题
     * @apiSuccess {string} cover_img 封面图
     * @apiSuccess {string} price    价格
     * @apiSuccess {string}  price  价格
     * @apiSuccess {number}  status  状态
     * @apiSuccess {string}  created_at  创建时间
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
    public function works()
    {
        $lists = Recommend::with('works:id,title,cover_img,price')
            ->select('id', 'relation_id', 'sort', 'created_at')
            ->where('position', 1)
            ->where('type', 2)
            ->orderBy('sort', 'desc')
            ->get();
        return success($lists);
    }

    /**
     * @api {get} api/v4/index/course  首页-课程集合
     * @apiVersion 4.0.0
     * @apiName  course
     * @apiGroup Index
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/index/course
     *
     *
     * @apiSuccess {string}  state 状态 1上架 下架
     * @apiSuccess {string}  works 听书作品
     * @apiSuccess {string}  works.works_id  作品id
     * @apiSuccess {string}  works.title  作品标题
     * @apiSuccess {string}  works.cover_img  作品封面
     *
     * @apiSuccessExample  Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "code": 200,
     *       "msg" : '成功',
     *       "data":[
     *         ]
     *     }
     *
     */
    public function course(Request $request)
    {
        $type = $request->get('type') ??  4;
        if ($type == 4) {
            $lists = ListsWork::with('works:id,title,cover_img,price')
                ->select('id', 'lists_id', 'works_id', 'state')
                ->where('lists_id', 4)
                ->get()
                ->toArray();
        } elseif ($type == 9) {
            $lists = ListsWork::with('wiki:id,name,cover')
                ->select('id', 'lists_id', 'works_id', 'state')
                ->where('lists_id', 9)
                ->get()
                ->toArray();
        } elseif ($type == 10) {
            $lists = ListsWork::with('goods:id,name,picture,price')
                ->select('id', 'lists_id', 'works_id', 'state')
                ->where('lists_id', 10)
                ->get()
                ->toArray();
        }

        return success($lists);
    }


}
