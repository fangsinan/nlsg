<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\CommentReply;
use App\Models\Notify;
use Illuminate\Http\Request;

class ReplyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user_id = 1;
        $input    = $request->all();

        $comment = Comment::where('id', $input['comment_id'])->first();
        if (!$comment){
            return error(1000,'评论不存在');
        }
        $result  = CommentReply::create([
            'comment_id'=> $input['comment_id'],
            'from_uid'  => $user_id,
            'to_uid'    => $comment->user_id,
            'content'   => $input['content']
        ]);
        if ($result){

            Comment::where('id', $input['comment_id'])->increment('reply_num');

            //发送通知
            $notify = new Notify();
            $notify->from_uid = $user_id;
            $notify->to_uid   = $comment->user_id;
            $notify->source_id= $result->id;
            $notify->type     = 5;
            $notify->subject  = '回复了你的评论';
            $content = [
                'summary'   => $input['content'],
            ];
            $notify->content = $input['content'] ? serialize($content) : '';
            $notify->save();

            //发送通知
            return $this->success();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }


    /**
     * 更新回复内容
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $id      = $request->input('id');
        $content = $request->input('content');
        $res= CommentReply::where('id', $id)
            ->update(['content'=>$content]);

        if ($res){
            return $this->success();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $id = $request->input('id');
        $res = CommentReply::where('id',$id)
            ->update(['status'=>0]);
        if($res){
            return $this->success();
        }
    }
}
