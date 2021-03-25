<?php

namespace App\Http\Controllers\Live\V4;

use App\Http\Controllers\Controller;
use App\Models\Live;
use App\Models\LiveComment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * @api {get} api/live_v4/comment/index 评论列表
     * @apiVersion 4.0.0
     * @apiName  comment/index
     * @apiGroup 直播后台-评论列表
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/live_v4/comment/index
     * @apiDescription  评论列表
     *
     * @apiParam {number} page 分页
     * @apiParam {string} title 名称
     *
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
    public function index(Request $request)
    {
        $title = $request->get('title');
        $query = LiveComment::with(['user:id,nickname', 'live:id,title'])
            ->when($title, function ($query) use ($title) {
                $query->where('title', 'like', '%'.$title.'%');
            });
        $lists = $query->select('id', 'live_id', 'user_id', 'content', 'created_at')
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->toArray();
        return success($lists);

    }
}
