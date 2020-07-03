<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use App\Models\CommentReply;
use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\Attach;
use App\Models\Like;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $model = new Comment();
        $lists = $model->getIndexComment(1);
        return $this->success($lists);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $content = $request->input('content');
        $img     = $request->input('img');
        $pid     = $request->input('pid');
        $result  = Comment::create([
            'user_id' => 1,
            'pid'     => $pid,
            'content' => $content,
            'type'    => 1
        ]);

        if ($result->id){
            if ($img){
                $imgArr = explode(',', $img);
                $data = [];
                foreach ($imgArr as $v){
                    $data[] = [
                        'relation_id' => $result->id,
                        'img'   => $v,
                        'type'  => 1
                    ];
                }
                Attach::insert($data);
            }
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
        $img     = $request->input('img');
        $res= Comment::where('id', $id)
            ->update(['content'=>$content]);
        if ($res){
            Attach::where('relation_id', $id)->delete();

            if ($img){
                $imgArr = explode(',', $img);
                $data = [];
                foreach ($imgArr as $v){
                    $data[] = [
                        'relation_id' => $id,
                        'img'   => $v,
                        'type'  => 1
                    ];
                }
                Attach::insert($data);
            }
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
        $res = Comment::where('id',$id)
            ->update(['status'=>0]);
        if($res){
            CommentReply::where('comment_id', $id)->update(['status'=>0]);
            return $this->success();
        }
    }


}
