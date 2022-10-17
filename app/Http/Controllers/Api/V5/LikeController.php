<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\CommentReply;
use App\Models\Message\Message;
use App\Models\Task;
use Illuminate\Http\Request;
use App\Models\Like;
use App\Models\Notify;
use App\Models\User;
use Mockery\Matcher\Not;
use JPush;

class LikeController extends Controller
{


    // 内容章节点赞 （未使用）
    public function Infolike(Request $request)
    {
        $id   = $request->input('id');
        $type = $request->input('type');
        $comment_type = $request->input('comment_type')??1;
        if (empty($id) || empty($type) || empty($comment_type)){
            return error(1000, '参数不全');
        }

        $list = Like::where(['comment_type'=>$comment_type, 'relation_id'=> $id, 'user_id'=> $this->user['id'], 'type'=>$type])->first();
        if (!empty($list)){
            return error(1000,'不要重复操作');
        }

        $res = Like::create([
            'comment_type'=>$comment_type,
            'relation_id' => $id,
            'user_id'     => $this->user['id'],
            'type'        => $type
        ]);
        if ($res){
            //增加喜欢
            Comment::where('id', $id)->increment('like_num');
            return success('操作成功');
        }
        return error(1000, '操作失败');

    }




    /**
     * @api {post} api/v5/like 评论点赞
     * @apiVersion 5.0.0
     * @apiParam  id  评论id
     * @apiParam  type  类型 1.想法 2.百科 3短视频
     * @apiParam  comment_type  类型 1.主评论 2 次级评论
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
        $comment_type = $request->input('comment_type')??1;
        if (empty($id) || empty($type) || empty($comment_type)){
            return error(1000, '参数不全');
        }

        $list = Like::where(['comment_type'=>$comment_type, 'relation_id'=> $id, 'user_id'=> $this->user['id'], 'type'=>$type, 'status' => 1,])->first();
        if (!empty($list)){
            return error(1000,'不要重复操作');
        }

        $res = Like::create([
            'comment_type'=>$comment_type,
            'relation_id' => $id,
            'user_id'     => $this->user['id'],
            'type'        => $type
        ]);
        if ($res){
            //增加喜欢
            Comment::where('id', $id)->increment('like_num');

            Like::LikeMsg($id,$comment_type,$this->user['id'],$res->id);
            return success('操作成功');
        }
        return error(1000, '操作失败');

    }

    /**
     * @api {post} api/v5/unlike 取消点赞
     * @apiVersion 5.0.0
     * @apiParam  id    评论id
     * @apiParam  type  类型 1.想法 2.百科 3短视频
     * @apiParam  comment_type  类型 1.主评论 2 次级评论
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
        $comment_type = $request->input('comment_type')??1;

        if (empty($id) || empty($comment_type)){
            return error(1000, '参数不全');

        }
        // $res = Like::where(['comment_type'=>$comment_type, 'relation_id'=> $id, 'user_id'=>$this->user['id'], 'type'=>$type])->delete();

        $query_res = Like::where([
            'comment_type'=>$comment_type,
            'relation_id'=> $id,
            'user_id'=>$this->user['id'],
            'type'=>$type,
            'status' => 1,
        ])->first();
        $res = $query_res->update([
            "status" => 2,
        ]);

        if($res){
            //减少喜欢
            Comment::where('id', $id)->decrement('like_num');

            // 发送消息
            Like::LikeMsg($id,$comment_type,$this->user['id'],$query_res->id??0,"UNLIKE");
            return success('操作成功');
        }
        return error(0,'操作失败');

    }
}
