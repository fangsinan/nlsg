<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use App\Models\Column;
use App\Models\CommentReply;
use App\Models\Notify;
use App\Models\ShortVideoModel;
use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\Attach;
use App\Models\Like;
use App\Models\Works;
use App\Models\Wiki;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    /**
     * @api {get} api/v5/comment/list  想法的列表
     * @apiVersion 5.0.0
     * @apiName  v5 index
     * @apiGroup Comment
     *
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/comment/list?id=1&type=1
     *
     * @apiParam {number} type  类型 1.专栏 2.讲座 3.听书 4.精品课 5.百科 6.训练营 7短视频
     * @apiParam {number} id    模块id
     * @apiParam {number} info_id    次级id
     * @apiParam {number} order  默认1  最新是2
     * @apiParam {number} self   只看作者 1  默认0
     *
     * @apiSuccess {string} content  发表的内容
     * @apiSuccess {number} forward_num  转发数
     * @apiSuccess {number} share_num    分享数
     * @apiSuccess {number} like_num     喜欢数
     * @apiSuccess {number} reply_num    评论数
     * @apiSuccess {number} is_like      是否点赞 1 是 0 否
     * @apiSuccess {number} is_quality    是否精选
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
    public function index(Request $request)
    {
        $input = $request->all();
        $order = $input['order'] ?? 1;
        $self = $input['self'] ?? 0;
        $id = $input['id'] ??0;
        $uid = $this->user['id'] ?? 0;
        $type = $input['type']??0;

        $model = new Comment();
        $lists = $model->getIndexComment($id, $type, $uid, $order, $self,$input['info_id']??0);

        return success($lists['data'] ??[]);
    }


    /**
     * @api {get} api/v5/comment/store  发表想法
     * @apiVersion 5.0.0
     * @apiName  store
     * @apiGroup Comment
     *
     * @apiParam {number} id  模块id
     * @apiParam {number} info_id  次级id
     * @apiParam {number} pid 转发评论id
     * @apiParam {string} content 发布的内容
     * @apiParam {string} img  多个图片  格式 a.png,b.png,c.png
     * @apiParam {string} type 模块类型  类型 1.专栏 2.讲座 3.听书 4.精品课 5.百科 6.训练营  7短视频
     *
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

        $input = $request->all();
        $img = $input['img'] ?? '';

        if ( ! $input['id']) {
            return error(1000, '参数有误');
        }
        $result = Comment::create([
            'user_id'     => $this->user['id'],
            'relation_id' => $input['id'],
            'pid'         => $input['pid'] ?? 0,
            'content'     => $input['content'] ?? '',
            'type'        => $input['type'] ?? 1,
            'info_id'     => $input['info_id']??0,
        ]);

        if ($result->id) {
            switch ($input['type']){
                case  5:
                    Wiki::where('id', $input['id'])->increment('comment_num');
                    break;
                case  7:
                    ShortVideoModel::where('id', $input['id'])->increment('comment_num');
                    break;
            }
            if ( ! empty($img)) {
                $imgArr = explode(',', $img);
                $data = [];
                foreach ($imgArr as $v) {
                    $data[] = [
                        'relation_id' => $result->id,
                        'img'         => $v,
                        'type'        => 1
                    ];
                }
                Attach::insert($data);
            }

            return success();
        }

    }

    /**
     * @api {post} api/v5/comment/destroy  删除想法
     * @apiVersion 5.0.0
     * @apiName  v5 destroy
     * @apiGroup Comment
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/comment/destroy
     * @apiParam {int} id 评论id
     * @apiParam {number} comment_type  1主评论  2 次级评论
     *
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
    public function destroy(Request $request)
    {
        $id = $request->input('id');
        $input = $request->input();


        if(!empty($input['comment_type']) && $input['comment_type'] == 1){
            $comment =  Comment::where('id', $id)->first();
            $c_type = $comment['type'];
        }else{
            $comment = CommentReply::where('id', $input['comment_id'])->first();

            $main_comment = Comment::where('id', $comment['comment_id'])->first();
            $c_type = $main_comment['type'];
        }



        if (!$comment){
            return error(1000,'评论不存在');
        }
        if( $this->user['is_community_admin']==0 ){
            if ($comment->user_id !== $this->user['id']){
                return error(1000,'没有权限删除');
            }
        }


        //主评论
        if(!empty($input['comment_type']) && $input['comment_type'] == 1){
            $res = Comment::where('id', $id)
                ->update(['status' => 0]);
            if ($res) {
                //子评论数量
                $count = CommentReply::where('comment_id', $id)->count();
                CommentReply::where('comment_id', $id)->update(['status' => 0]);
                switch ($comment->type){
                    case 5:
                        Wiki::where('id', $comment->relation_id)->decrement('comment_num',$count+1);
                        break;
                    case 7:
                        ShortVideoModel::where('id', $comment->relation_id)->decrement('comment_num',$count+1);
                        break;
                }
            }

        }else{
            CommentReply::where('id', $id)->update(['status' => 0]);
            
            if($c_type == 7){
                ShortVideoModel::where('id', $comment->relation_id)->decrement('comment_num');
            }
        }



        return $this->success();


    }
    // 评论置顶
    public function editTop(Request $request)
    {
        $id = $request->input('id');
        $is_top = $request->input('is_top');

        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
            'is_top' => 'required|boolean',
        ]);
        if ($validator->fails()) {
            return $this->error(0,$validator->messages()->first());
        }


        if( empty($this->user['backend_user']) ){
            return $this->error(1000,'没有权限操作');
        }

        $comment =  Comment::where('id', $id)->first();
        if (!$comment){
            return $this->error(1000,'评论不存在');
        }
       
        $res = Comment::where('id', $id)->update(['is_top' => $is_top]);
        if(empty($res)){
            return $this->error(0,'操作失败');
        }
        return $this->success();


    }
}
