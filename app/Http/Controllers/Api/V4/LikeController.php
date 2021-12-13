<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Task;
use Illuminate\Http\Request;
use App\Models\Like;
use App\Models\Notify;
use App\Models\User;
use Mockery\Matcher\Not;
use JPush;

class LikeController extends Controller
{
    /**
     * @api {post} api/v4/like 点赞
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
        if (!$id || !$type){
            return error(1000, '参数不全');
        }
        $list = Like::where(['relation_id'=> $id, 'user_id'=> $this->user['id'], 'type'=>$type])->first();
        if ($list){
            return error(1000,'不要重复操作');
        }

        $res = Like::create([
            'relation_id' => $id,
            'user_id'     => $this->user['id'],
            'type'        => $type
        ]);
        if ($res){
            if ($type ==1){
                $comment = Comment::where('id', $id)->first();
                $notify = new Notify();
                $notify->from_uid = $this->user['id'];
                $notify->to_uid   = $comment->user_id;
                $notify->source_id= $id;
                $notify->type     = 1;
                $notify->subject  = '喜欢了你的想法';
                $notify->save();
            }
            //增加喜欢
            Comment::where('id', $id)->increment('like_num');
            $from_user = User::where('id', $this->user['id'])->value('nickname');
            //发送通知
            Task::send(13, $comment->user_id, $id, 0, '',false,false, 0, $from_user, $comment->type, $comment->relation_id);

            return success('操作成功');
        }
        return error(1000, '操作失败');

    }

    /**
     * @api {post} api/v4/unlike 取消点赞
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
        $res = Like::where(['relation_id'=> $id, 'user_id'=>$this->user['id'], 'type'=>$type])->delete();
        if($res){
            //减少喜欢
            Comment::where('id', $id)->decrement('like_num');
            return success('操作成功');
        }
        return error(0,'操作失败');

    }
}
