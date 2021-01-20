<?php

namespace App\Http\Controllers\Admin\V4;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Works;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index(Request $request)
    {
        $start = $request->get('start');
        $end   = $request->get('end');

        $query = Comment::with('user:id,nickname')
            ->when($start && $end, function ($query) use ($start, $end) {
                $query->whereBetween('created_at', [
                    Carbon::parse($start)->startOfDay()->toDateTimeString(),
                    Carbon::parse($end)->endOfDay()->toDateTimeString(),
                ]);
            });

        $lists = $query->select('id','user_id', 'relation_id', 'info_id', 'content', 'type', 'created_at')
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->toArray();
        if ($lists){
            $lists =  Comment::convert($lists['data']);
            return success($lists);
        }

    }
}
