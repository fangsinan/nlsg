<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\CommentReply;
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
        $data       = $request->all();
        $comment_id = $request->input('comment_id');
        $from_uid   = $request->input('from_uid');
        $to_uid     = $request->input('to_uid');
        $content    = $request->input('content');
        $result  = CommentReply::create([
            'comment_id'=> $comment_id,
            'from_uid'  => $from_uid,
            'to_uid'    => $to_uid,
            'content'   => $content
        ]);
        if ($result){
            Comment::where('id', $comment_id)->increment('reply_num');
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
