<?php

namespace App\Http\Controllers\Admin\V4;

use App\Http\Controllers\Controller;
use App\Models\Lists;
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
        $list = Recommend::where('position', 1)
            ->where('type', 2)
            ->value('relation_id');
        $ids = explode(',', $list);
        if (!$ids) {
            return error(1000, '还没有推荐');
        }

        $works = Works::select('id', 'column_id', 'type', 'user_id', 'title', 'cover_img', 'subtitle', 'price', 'is_free', 'status')
            ->whereIn('id', $ids)
            ->where('status', 4)
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
        return success($works);
    }

    /**
     * @api {get} api/v4/index/course  首页-课程集合
     * @apiVersion 4.0.0
     * @apiName  course
     * @apiGroup Index
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/index/course
     *
     * @apiSuccess {string} title 标题
     * @apiSuccess {string} subtitle 副标题
     * @apiSuccess {string} cover 封面
     * @apiSuccess {number}  num  数量
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
    public function course()
    {
        $list = Recommend::where('position', 1)
            ->where('type', 10)
            ->value('relation_id');
        $ids = explode(',', $list);
        if (!$ids) {
            return error(1000, '还没有推荐');
        }

        $lists = Lists::select('id', 'title', 'subtitle', 'cover', 'num')
            ->with(['works' => function ($query) {
                $query->select('works_id', 'user_id', 'title', 'cover_img')
                    ->where('status', 4)
                    ->limit(3)
                    ->inRandomOrder();
            }, 'works.user' => function ($query) {
                $query->select('id', 'nickname', 'headimg');
            }])->whereIn('id', $ids)
            ->where('type', $type)
            ->where('status', 1)
            ->first();
        return success($lists);
    }


}
