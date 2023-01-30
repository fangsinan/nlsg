<?php

namespace App\Http\Controllers\Admin\V4;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\CommentReply;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index(Request $request)
    {
        $start = $request->get('start');
        $end = $request->get('end');
        $type = $request->get('type');
        $content = $request->get('content');
        $title = $request->get('title');
        $chapter = $request->get('chapter');
        $size = $request->get('size', 10);

        $query = Comment::with('user:id,nickname')
            ->when($type, function ($query) use ($type) {
                $query->where('type', $type);
            })
            ->when($title && (in_array($type, [1, 2, 6])), function ($query) use ($title) {
                $query->whereHas('column', function ($query) use ($title) {
                    $query->where('name', 'like', '%' . $title . '%');
                });
            })
            ->when($title && (in_array($type, [3, 4])), function ($query) use ($title) {
                $query->whereHas('work', function ($query) use ($title) {
                    $query->where('title', 'like', '%' . $title . '%');
                });
            })
            ->when($chapter, function ($query) use ($chapter) {
                $query->whereHas('info', function ($query) use ($chapter) {
                    $query->where('title', 'like', '%' . $chapter . '%');
                });
            })
            ->when($title && $type == 5, function ($query) use ($title) {
                $query->whereHas('wiki', function ($query) use ($title) {
                    $query->where('name', 'like', '%' . $title . '%');
                });
            })
            ->when($content, function ($query) use ($content) {
                $query->where('content', 'like', '%' . $content . '%');
            })
            ->when($start && $end, function ($query) use ($start, $end) {
                $query->whereBetween('created_at', [
                    Carbon::parse($start)->startOfDay()->toDateTimeString(),
                    Carbon::parse($end)->endOfDay()->toDateTimeString(),
                ]);
            });

        $query->where('app_project_type','=',APP_PROJECT_TYPE);

        $comments = $query->select('id', 'user_id', 'relation_id', 'info_id', 'content', 'type', 'created_at')
            ->orderBy('id', 'desc')
            ->paginate($size)
            ->toArray();

        if ($comments['data']) {
            $comments['data'] = Comment::convert($comments);
            return success($comments);
        } else {
            $data = [
                'data' => []
            ];
            return success($data);
        }
    }

    /**
     * @api {post} api/admin_v4/comment/forbid 删除想法
     * @apiVersion 4.0.0
     * @apiName  comment
     * @apiGroup 后台-评论
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/comment/forbid 删除想法
     * @apiDescription 删除想法
     *
     * @apiParam {number} id  评论id
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
    public function forbid(Request $request)
    {
        $id = $request->get('id');
        $res = Comment::where('id', $id)->update(['status' => 0]);
        if ($res) {
            Comment::where('id', $id)->decrement('reply_num');
            CommentReply::where('comment_id', $id)->update(['status' => 0]);
            return success();
        }
    }

    /**
     * @api {post} api/admin_v4/comment/reply 评论想法
     * @apiVersion 4.0.0
     * @apiName  comment
     * @apiGroup 后台-评论
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/comment/reply
     * @apiDescription 评论想法
     *
     * @apiParam {number} id  评论id
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
    public function reply(Request $request)
    {
        $user_id = $this->user['id'];
        $input = $request->all();

        $comment = Comment::where('id', $input['comment_id'])->first();
        if (!$comment) {
            return error(1000, '评论不存在');
        }
        $result = CommentReply::create([
            'comment_id' => $input['comment_id'],
            'from_uid' => $user_id,
            'to_uid' => $comment->user_id,
            'content' => $input['content']
        ]);
        if ($result) {
            Comment::where('id', $input['comment_id'])->increment('reply_num');
            //发送通知
            return success();
        }
    }
}
