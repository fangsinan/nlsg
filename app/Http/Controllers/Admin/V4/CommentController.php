<?php

namespace App\Http\Controllers\Admin\V4;

use App\Http\Controllers\Controller;
use App\Models\Column;
use App\Models\Comment;
use App\Models\Wiki;
use App\Models\Works;
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

        $query = Comment::with('user:id,nickname')
            ->when($type, function ($query) use ($type) {
                $query->where('type', $type);
            })
            ->when($title && (in_array($type, [1, 2])), function ($query) use ($title) {
                $query->whereHas('column', function ($query) use ($title) {
                    $query->where('name', 'like', '%'.$title.'%');
                });
            })
            ->when($title && (in_array($type, [3, 4])), function ($query) use ($title) {
                $query->whereHas('work', function ($query) use ($title) {
                    $query->where('title', 'like', '%'.$title.'%');
                });
            })
            ->when($title && $type == 5, function ($query) use ($title) {
                $query->whereHas('wiki', function ($query) use ($title) {
                    $query->where('name', 'like', '%'.$title.'%');
                });
            })
            ->when($content, function ($query) use ($content) {
                $query->where('content', 'like', '%'.$content.'%');
            })
            ->when($start && $end, function ($query) use ($start, $end) {
                $query->whereBetween('created_at', [
                    Carbon::parse($start)->startOfDay()->toDateTimeString(),
                    Carbon::parse($end)->endOfDay()->toDateTimeString(),
                ]);
            });
        $comments = $query->select('id', 'user_id', 'relation_id', 'info_id', 'content', 'type', 'created_at')
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->toArray();

        if ($comments['data']) {
            $comments['data'] = Comment::convert($comments);
            return success($comments);
        }
    }

    /**
     * 隐藏评论
     */
    public function forbid(Request $request)
    {
        $id = $request->get('id');
        $res= Comment::where('id', $id)->update(['status'=>0]);
        if($res){
            return  success();
        }
    }
}
