<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;
use App\Models\Like;
use App\Models\Notify;
use Mockery\Matcher\Not;

class LikeController extends Controller
{

    /**
     * @api {get} api/v4/like 点赞
     * @apiVersion 4.0.0
     * @apiParam  id  评论id
     * @apiParam  type  类型 1.想法 2.百科
     * @apiGroup Api
     *
     * @apiSuccess {String} token   token
     *
     * @apiSuccessExample 成功响应:
     *   {
     *      "code": 200,
     *      "msg" : '成功',
     *      "data": {
     *
     *       }
     *   }
     *
     */
    public function like(Request $request)
    {
        $id   = $request->input('id');
        $type = $request->input('type');
        if (!$id){
            return false;
        }
        $list = Like::where(['relation_id'=> $id, 'user_id'=> 1, 'type'=>$type])->first();
        if ($list){
            return error(1000,'不要重复操作');
        }

        $res = Like::create([
            'relation_id' => $id,
            'user_id'     => 1,
            'type'        => 1
        ]);
        if ($res){
            if ($type ==1){
                $comment = Comment::where('id', $id)->first();
                $notify = new Notify();
                $notify->from_uid = 1;
                $notify->to_uid   = $comment->user_id;
                $notify->source_id= 1;
                $notify->type     = 1;
                $notify->subject  = '喜欢了你的想法';
                $notify->save();
            }
            return success('操作成功');
        }
        return error(1000, '操作失败');

    }

    /**
     * @api {get} api/unlike 点赞
     * @apiVersion 4.0.0
     * @apiParam  id    评论id
     * @apiParam  type  类型 1.想法 2.百科
     * @apiGroup Api
     *
     * @apiSuccess {String} token   token
     *
     * @apiSuccessExample 成功响应:
     *   {
     *      "code": 200,
     *      "msg" : '成功',
     *      "data": {
     *
     *       }
     *   }
     *
     */
    public function unlike(Request $request)
    {
        $id   = $request->input('id');
        $type = $request->input('type');
        if (!$id){
            return false;
        }
        $res = Like::where(['relation_id'=> $id, 'user_id'=>1, 'type'=>$type])->delete();
        if($res){
            return success('操作成功');
        }
        return error('操作失败');

    }
}
