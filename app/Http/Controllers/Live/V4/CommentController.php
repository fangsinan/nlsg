<?php

namespace App\Http\Controllers\Live\V4;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ControllerBackend;
use App\Models\Live;
use App\Models\LiveComment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CommentController extends ControllerBackend
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
     * @apiParam {string} nicknake 用户账号
     * @apiParam {string} content  评论内容
     * @apiParam {string} start  开始时间
     * @apiParam {string} end    结束时间
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
        $nickname = $request->get('nickname');
        $content = $request->get('content');
        $start = $request->get('start');
        $end = $request->get('end');
        $query = LiveComment::with(['user:id,nickname', 'live:id,title'])
            ->when($content, function ($query) use ($content) {
                $query->where('content', 'like', '%'.$content.'%');
            })
            ->when($nickname, function ($query) use ($nickname) {
                $query->whereHas('user', function ($query) use ($nickname) {
                    $query->where('nickname', 'like', '%'.$nickname.'%');
                });
            })
            ->when($title, function ($query) use ($title) {
                $query->whereHas('live', function ($query) use ($title) {
                    $query->where('title', 'like', '%'.$title.'%');
                });
            })
            ->when($start && $end, function ($query) use ($start, $end) {
                $query->whereBetween('created_at', [
                    Carbon::parse($start)->startOfDay()->toDateTimeString(),
                    Carbon::parse($end)->endOfDay()->toDateTimeString(),
                ]);
            });
        $lists = $query->select('id', 'live_id', 'user_id', 'content', 'created_at')
            ->where('status', 1)
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->toArray();
        return success($lists);

    }

    /**
     * @api {get} api/live_v4/comment/show 评论查看
     * @apiVersion 4.0.0
     * @apiName  comment/show
     * @apiGroup 直播后台-评论查看
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/live_v4/comment/show
     * @apiDescription  评论查看
     *
     * @apiParam {number} id  评论id
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
    public function show(Request $request)
    {
        $id = $request->get('id');
        $list = LiveComment::with('user:id,nickname')
            ->select('id', 'user_id', 'content', 'created_at')
            ->where('id', $id)
            ->first();
        if ( ! $list) {
            return error(1000, '评论不存在');
        }
        return success($list);
    }

    /**
     * @api {post} api/live_v4/comment/delete 直播评论删除
     * @apiVersion 4.0.0
     * @apiName  comment/delete
     * @apiGroup 直播后台
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/live_v4/comment/delete
     * @apiDescription  直播评论删除
     *
     * @apiParam {number} id 直播评论id
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
    public function delete(Request $request)
    {
        $id   = $request->input('id');
        $list = LiveComment::where('id', $id)->first();
        if ( !$list) {
            return error(1000, '直播评论不存在');
        }
        $res = LiveComment::where('id', $id)->update(['status' => 0]);
        if ($res) {
            return success();
        }
    }
}
