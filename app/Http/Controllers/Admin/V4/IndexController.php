<?php

namespace App\Http\Controllers\Admin\V4;

use App\Http\Controllers\Controller;
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
        if (!$ids){
            return error(1000,'还没有推荐');
        }

        $works = Works::select('id', 'column_id', 'type', 'user_id', 'title', 'cover_img', 'subtitle', 'price', 'is_free','status')
            ->whereIn('id', $ids)
            ->where('status', 4)
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
        return success($works);
    }


}
