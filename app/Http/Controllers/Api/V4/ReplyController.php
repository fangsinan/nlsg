<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\CommentReply;
use App\Models\Notify;
use App\Models\ShortVideoModel;
use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\User;

class ReplyController extends Controller
{


    /**
     * @api {get} api/v4/reply/store  回复
     * @apiVersion 4.0.0
     * @apiName  store
     * @apiGroup Reply
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/reply/store
     *
     * @apiParam {number} type 1主评论  2 次级评论
     * @apiParam {number} comment_id 评论id
     * @apiParam {string} content    回复内容
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
    public function store(Request $request)
    {
        $user_id  = $this->user['id'];
        $input    = $request->all();
        if(!empty($input['type']) && $input['type'] == 1){
            $comment = Comment::where('id', $input['comment_id'])->first();
            $add_data = [
                'comment_id'=> $input['comment_id'],
                'from_uid'  => $user_id,
                'to_uid'    => $comment->user_id,
                'content'   => $input['content']
            ];
            $c_type = $comment['type'];
        }else{
            $comment = CommentReply::where('id', $input['comment_id'])->first();
            $add_data = [
                'comment_id'    => $comment['comment_id'],
                'reply_pid'     => $input['comment_id'],
                'from_uid'      => $user_id,
                'to_uid'        => $comment->from_uid,
                'content'       => $input['content']
            ];


            $main_comment = Comment::where('id', $comment['comment_id'])->first();
            $c_type = $main_comment['type'];

        }
        if (!$comment){
            return error(1000,'评论不存在');
        }
        $result  = CommentReply::create($add_data);
        if ($result){
            Comment::where('id', $input['comment_id'])->increment('reply_num') ;
            if($c_type == 7){
                ShortVideoModel::where('id', $input['id'])->increment('comment_num');
            }
//            //发送通知
//            $notify = new Notify();
//            $notify->from_uid = $user_id;
//            $notify->to_uid   = $comment->user_id;
//            $notify->source_id= $result->id;
//            $notify->type     = 2;
//            $notify->subject  = '回复了你的评论';
//            $content = [
//                'summary'   => $input['content'],
//            ];
//            $notify->content = $input['content'] ? serialize($content) : '';
//            $notify->save();
//
//            $from_user = User::where('id', $user_id)->value('nickname');
//            //发送通知
//            Task::send(12, $comment->user_id, $result->id, 0, '',false,false, 0, $from_user, $comment->type, $comment->relation_id);

            return success();
        }
    }


    /**
     * @api {get} api/v4/reply/update  回复更新内容
     * @apiVersion 4.0.0
     * @apiName  update
     * @apiGroup Reply
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/reply/update
     *
     * @apiParam {number} id       回复id
     * @apiParam {string} content  回复内容
     *
     * @apiSuccessExample  Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "code": 200,
     *       "msg" : '成功',
     *       "data":[
     *
     *         ]
     *     }
     *
     */

    public function update(Request $request)
    {
        $id      = $request->input('id');
        $content = $request->input('content');
        $res= CommentReply::where('id', $id)
            ->update(['content'=>$content]);

        if ($res){
            return success();
        }
    }

    /**
     * @api {get} api/v4/reply/destroy 回复删除
     * @apiVersion 4.0.0
     * @apiName  destroy
     * @apiGroup Reply
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/reply/destroy
     *
     * @apiParam {int} id 回复id
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
    public function destroy(Request $request)
    {
        $id = $request->input('id');
        $res = CommentReply::where('id',$id)
            ->update(['status'=>0]);
        if($res){
            return success();
        }
    }
}
