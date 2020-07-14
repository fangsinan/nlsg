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
     * @api {get} api/v4/comment/index  想法
     * @apiVersion 4.0.0
     * @apiName  index
     * @apiGroup Comment
     *
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/comment/index
     *
     * @apiParams {number} type  类型 1.专栏 2.讲座 3.听书 4.精品课
     * @apiParams {number} id    模块id
     *
     * @apiSuccess {string} content  发表的内容
     * @apiSuccess {number} forward_num  转发数
     * @apiSuccess {number} share_num    分享数
     * @apiSuccess {number} like_num     喜欢数
     * @apiSuccess {number} reply_num    评论数
     * @apiSuccess {string} user            发布的用户
     * @apiSuccess {string} user.nickname   用户昵称
     * @apiSuccess {string} user.headimg    用户头像
     * @apiSuccess {string} attach          图片
     * @apiSuccess {string} attach.img      图片地址
     * @apiSuccess {string} reply           回复
     * @apiSuccess {string} reply.content   回复的内容
     * @apiSuccess {string} reply.from_user 评论者
     * @apiSuccess {string} reply.to_user   被回复者
     * @apiSuccess {string} quote           引用
     * @apiSuccess {string} quote.content   引用的内容
     *
     * @apiSuccessExample  Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "code": 200,
     *       "msg" : '成功',
     * "data": {
     * "current_page": 1,
     * "data": [
     * {
     * "id": 1,
     * "pid": 0,
     * "user_id": 168934,
     * "relation_id": 1,
     * "content": "测试",
     * "type": 1,
     * "forward_num": 0,
     * "share_num": 0,
     * "like_num": 0,
     * "reply_num": 3,
     * "is_quality": 0,
     * "created_at": "2020-06-10 07:25:04",
     * "updated_at": "2020-07-07 18:55:59",
     * "status": 1,
     * "user": {
     * "id": 168934,
     * "nickname": "chandler"
     * },
     * "quote": {
     * "pid": 1,
     * "content": "说的不错啊"
     * },
     * "attach": [
     * {
     * "id": 1,
     * "relation_id": 1,
     * "img": "/wechat/mall/goods/3476_1533614056.png"
     * },
     * {
     * "id": 2,
     * "relation_id": 1,
     * "img": "/wechat/mall/goods/3476_1533614056.png"
     * }
     * ],
     * "reply": [
     * {
     * "id": 1,
     * "comment_id": 1,
     * "from_uid": 168934,
     * "to_uid": 211172,
     * "content": "修改新内容",
     * "from_user": {
     * "id": 168934,
     * "nickname": "chandler"
     * },
     * "to_user": {
     * "id": 211172,
     * "nickname": "能量时光"
     * }
     * },
     * {
     * "id": 2,
     * "comment_id": 1,
     * "from_uid": 211172,
     * "to_uid": 168934,
     * "content": "你也不错",
     * "from_user": {
     * "id": 211172,
     * "nickname": "能量时光"
     * },
     * "to_user": {
     * "id": 168934,
     * "nickname": "chandler"
     * }
     * },
     * {
     * "id": 5,
     * "comment_id": 1,
     * "from_uid": 1,
     * "to_uid": 168934,
     * "content": "不错",
     * "from_user": {
     * "id": 1,
     * "nickname": "刘先森"
     * },
     * "to_user": {
     * "id": 168934,
     * "nickname": "chandler"
     * }
     * }
     * ]
     * }
     * ],
     * "first_page_url": "http://v4.com/api/v4/comment/index?page=1",
     * "from": 1,
     * "last_page": 7,
     * "last_page_url": "http://v4.com/api/v4/comment/index?page=7",
     * "next_page_url": "http://v4.com/api/v4/comment/index?page=2",
     * "path": "http://v4.com/api/v4/comment/index",
     * "per_page": 1,
     * "prev_page_url": null,
     * "to": 1,
     * "total": 7
     * }
     *
     */
    public function index()
    {
        $model = new Comment();
        $lists = $model->getIndexComment(1, 1);
        return success($lists['data']);
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
        $input = $request->all();
        $result = Comment::create([
            'user_id' => $user_id,
            'relation_id' => $input['id'],
            'pid' => $input['pid'],
            'content' => $input['content'],
            'type' => $input['type']
        ]);

        if ($result->id) {
            if ($input['img']) {
                $imgArr = explode(',', $input['img']);
                $data = [];
                foreach ($imgArr as $v) {
                    $data[] = [
                        'relation_id' => $result->id,
                        'img' => $v,
                        'type' => 1
                    ];
                }
                Attach::insert($data);
            }

            switch ($input['type']) {
                case 1:
                    $list = Column::where('id', $input['id'])->first();
                    $subject = '评论了你的专栏';
                    break;
                case  2:
                    break;
            }

            //发送通知
            $notify = new Notify();
            $notify->from_uid = $user_id;
            $notify->to_uid = $list->user_id;
            $notify->source_id = $input['id'];
            $notify->type = 4;
            $notify->subject = $subject;
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
        $id = $request->input('id');
        $content = $request->input('content');
        $img = $request->input('img');
        $res = Comment::where('id', $id)
            ->update(['content' => $content]);
        if ($res) {
            Attach::where('relation_id', $id)->delete();

            if ($img) {
                $imgArr = explode(',', $img);
                $data = [];
                foreach ($imgArr as $v) {
                    $data[] = [
                        'relation_id' => $id,
                        'img' => $v,
                        'type' => 1
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
        $res = Comment::where('id', $id)
            ->update(['status' => 0]);
        if ($res) {
            CommentReply::where('comment_id', $id)->update(['status' => 0]);
            return $this->success();
        }
    }


}
