<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use App\Models\Column;
use App\Models\CommentReply;
use App\Models\Notify;
use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\Attach;
use App\Models\Like;

class CommentController extends Controller
{

   /**
    * @api {get} api/v4/comment/index 列表
    * @apiVersion 4.0.0
    * @apiName  评论列表
    *
    * @apiParam {int} id    模块id
    * @apiParam {int} type  类型
    *
    * @apiSuccess {String} token
    * @apiSuccess {String} result
    *
    * @apiSuccessExample  成功响应:
    *  HTTP/1.1 200 OK
    *   {
    *      "code": 200,
    *      "msg" : '成功',
    *      "data": {
    *
    *       }
    *   }
    *
   */
    public function index()
    {
        $model = new Comment();
        $lists = $model->getIndexComment(1);
        return $this->success($lists);
    }


    /**
     * @api {get} api/v4/comment/store 登录
     * @apiVersion 4.0.0
     * @apiName   登录
     *
     * @apiParam  id     模块id
     * @apiParam  pid     父id
     * @apiParam  content 内容
     * @apiParam  type    类型
     *
     * @apiSuccess {String} token   token
     *
     * @apiSuccessExample {json} 成功响应:
     *   {
     *      "code": 200,
     *      "msg" : '成功',
     *      "data": {
     *
     *       }
     *   }
     *
    */
    public function store(Request $request)
    {
        $user_id = 1;
        $input  = $request->all();
        $result  = Comment::create([
            'user_id'     => $user_id,
            'relation_id' => $input['id'],
            'pid'     => $input['pid'],
            'content' => $input['content'],
            'type'    => $input['type']
        ]);

        if ($result->id){
            if ($input['img']){
                $imgArr = explode(',', $input['img']);
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

            switch ($input['type']){
                case 1:
                    $list    = Column::where('id', $input['id'])->first();
                    $subject = '评论了你的专栏';
                    break;
                case  2:
                    break;
            }

            //发送通知
            $notify = new Notify();
            $notify->from_uid = $user_id;
            $notify->to_uid   = $list->user_id;
            $notify->source_id= $input['id'];
            $notify->type     = 4;
            $notify->subject  = $subject;
            $notify->save();

            return $this->success();
        }

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
