<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use App\Models\Column;
use App\Models\Comment;
use App\Models\CommentReply;
use App\Models\ShortVideoModel;
use Illuminate\Http\Request;

class ReplyController extends Controller
{


    /**
     * @api {get} api/v5/reply/store  回复
     * @apiVersion 5.0.0
     * @apiName  v5 store
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
        //兼容老版本comment_type   v5所有次级评论相关以comment_type为主
        if(isset($input['comment_type'])){
            $comment_type = $input['comment_type']??1;
        }else{
            $comment_type = $input['type']??1;
        }
        if(!empty($comment_type) && $comment_type == 1){
            $comment = Comment::where('id', $input['comment_id'])->first();
            if(empty($comment)){
                return error(1000,'评论不存在');
            }
            $add_data = [
                'comment_id'=> $input['comment_id'],
                'from_uid'  => $user_id,
                'to_uid'    => $comment->user_id,
                'content'   => $input['content']
            ];
            $c_type = $comment['type'];
            $Video_id = $comment['relation_id'];


            $replay_num_id = $input['comment_id'];

        }else{
            $comment = CommentReply::where('id', $input['comment_id'])->first();
            if(empty($comment)){
                return error(1000,'评论不存在');
            }
            $add_data = [
                'comment_id'    => $comment['comment_id'],
                'reply_pid'     => $input['comment_id'],
                'from_uid'      => $user_id,
                'to_uid'        => $comment->from_uid,
                'content'       => $input['content']
            ];
            $main_comment = Comment::where('id', $comment['comment_id'])->first();
            $c_type = $main_comment['type'];
            $Video_id = $main_comment['relation_id'];
            $replay_num_id = $comment['comment_id'];

        }
        $result  = CommentReply::create($add_data);

        if ($result){
            Comment::where('id', $replay_num_id)->increment('reply_num');
            if($c_type == 7){
                ShortVideoModel::where('id', $Video_id)->increment('comment_num');
            }
            if($c_type == 6){
                Column::where('id', $Video_id)->increment('comment_num');
            }
            return success();
        }
    }
}
