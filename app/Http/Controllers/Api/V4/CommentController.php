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
     * @api {get} api/v4/comment/list  列表
     * @apiVersion 4.0.0
     * @apiName  list
     * @apiGroup Comment
     *
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/comment/list?id=1&type=1
     *
     * @apiParam {number} type  类型 1.专栏 2.讲座 3.听书 4.精品课 5.百科
     * @apiParam {number} id    模块id
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
    public function index(Request $request)
    {
        $input = $request->all();
        $model = new Comment();
        $lists = $model->getIndexComment($input['id'], $input['type']);
        return success($lists['data']);
    }


    /**
     * @api {get} api/v4/comment/store  发表想法
     * @apiVersion 4.0.0
     * @apiName  index
     * @apiGroup Comment
     *
     * @apiParam {number} id  模块id
     * @apiParam {number} pid 转发评论id
     * @apiParam {string} content 发布的内容
     * @apiParam {string} type 模块类型  类型 1.专栏 2.讲座 3.听书 4.精品课
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
        $user_id = 1;
        $input = $request->all();

        if ( ! $input['id']) {
            return error(1000, '参数有误');
        }
        $result = Comment::create([
            'user_id'     => $user_id,
            'relation_id' => $input['id'],
            'pid'         => $input['pid'] ?? 0,
            'content'     => $input['content'] ?? '',
            'type'        => $input['type'] ?? 1
        ]);

        if ($result->id) {
            if ($input['img']) {
                $imgArr = explode(',', $input['img']);
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

            switch ($input['type']) {
                case 1:
                    $list = Column::where('id', $input['id'])
                        ->where('type', 1)
                        ->where('status', 1)
                        ->first();
                    $subject = '评论了你的专栏';
                    break;
                case  2:
                    $list = Column::where('id', $input['id'])
                        ->where('type', 2)
                        ->where('status', 1)
                        ->first();
                    $subject = '评论了你的讲座';
                    break;
                case  3:
                    $list = Works::where('id', $input['id'])
                        ->where('is_audio_book', 1)
                        ->where('status', 4)
                        ->first();
                    $subject = '评论了你的听书';
                    break;
                case  4:
                    $list = Works::where('id', $input['id'])
                        ->where('is_audio_book', 0)
                        ->where('status', 4)
                        ->first();
                    $subject = '评论了你的精品课';
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

            return success();
        }

    }


    /**
     * @api {get} api/v4/comment/update 更新想法
     * @apiVersion 4.0.0
     * @apiName  update
     * @apiGroup Comment
     *
     * @apiParam {number} id 模块id
     * @apiParam {string} content 发表的内容
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
     * @api {get} api/v4/comment/show  评论详情
     * @apiVersion 4.0.0
     * @apiName  show
     * @apiGroup Comment
     *
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/comment/show?id=1&page=1
     *
     * @apiParam {number} id   评论id
     * @apiParam {number} page 回复分页
     *
     * @apiSuccess {string} content  评论的内容
     * @apiSuccess {string} user
     * @apiSuccess {string} user.nickname 评论的用户昵称
     * @apiSuccess {string} user.headimg  评论的用户头像
     * @apiSuccess {number} forward_num   转发数
     * @apiSuccess {number} share_num     分享数
     * @apiSuccess {number} like_num      喜欢数
     * @apiSuccess {number} reply_num     回复数
     * @apiSuccess {number} is_quality    是否精选
     * @apiSuccess {string} attach        图片
     * @apiSuccess {string} attach.img    图片地址
     * @apiSuccess {string} column|works|wiki   type =1、2 专栏和讲座 返回 title、subtitle、cover_pic， type =3、4 听书和精品课 works 返回title subtitle、cover_img ， type=5 百科 wiki 返回name、cover
     * @apiSuccess {string} reply         回复
     * @apiSuccess {string} reply.content   回复内容
     * @apiSuccess {string} reply.from_user 回复者     【 张三 form_user 回复 李四 to_user 】
     * @apiSuccess {string} reply.from_user.nickname   回复者昵称
     * @apiSuccess {string} reply.from_user.headimg    回复者头像
     * @apiSuccess {string} reply.to_user   被回复者
     * @apiSuccess {string} reply.to_user.nickname   被回复者昵称
     * @apiSuccess {string} reply.to_user.headimg    被回复者头像
     *
     *
     * @apiSuccessExample  Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "code": 200,
     *       "msg" : '成功',
     * data": {
     * "id": 1,
     * "pid": 0,
     * "user_id": 168934,
     * "relation_id": 1,
     * "content": "测试",
     * "forward_num": 0,
     * "share_num": 0,
     * "like_num": 0,
     * "reply_num": 3,
     * "reward_num": 3,
     * "reply": [
     * {
     * "id": 1,
     * "from_uid": 168934,
     * "to_uid": 211172,
     * "from_user": {
     * "id": 168934,
     * "nickname": "chandler",
     * "headimg": "/wechat/works/headimg/3833/2017110823004219451.png"
     * },
     * "to_user": {
     * "id": 211172,
     * "nickname": "能量时光",
     * "headimg": "/wechat/works/headimg/3833/2017110823004219451.png"
     * }
     * },
     * {
     * "id": 2,
     * "from_uid": 211172,
     * "to_uid": 168934,
     * "from_user": {
     * "id": 211172,
     * "nickname": "能量时光",
     * "headimg": "/wechat/works/headimg/3833/2017110823004219451.png"
     * },
     * "to_user": {
     * "id": 168934,
     * "nickname": "chandler",
     * "headimg": "/wechat/works/headimg/3833/2017110823004219451.png"
     * }
     * }
     * ],
     * "user": {
     * "id": 168934,
     * "nickname": "chandler",
     * "headimg": "/wechat/works/headimg/3833/2017110823004219451.png"
     * },
     * "quote": null,
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
     * "reward": [
     * {
     * "id": 59,
     * "user_id": 1,
     * "relation_id": 1,
     * "user": {
     * "id": 1,
     * "nickname": "刘先森",
     * "headimg": "https://nlsg-saas.oss-cn-beijing.aliyuncs.com/static/class/157291903507887.png"
     * }
     * },
     * {
     * "id": 60,
     * "user_id": 211172,
     * "relation_id": 1,
     * "user": {
     * "id": 211172,
     * "nickname": "能量时光",
     * "headimg": "/wechat/works/headimg/3833/2017110823004219451.png"
     * }
     * }
     * ]
     * }
     *     }
     *
     */

    public function show(Request $request)
    {
        $id = $request->get('id');
        $comment = new Comment();
        $lists = $comment->getCommentList($id);
        if ( ! $lists) {
            return error(1000, '评论不存在');
        }


        return success($lists);
    }

    /**
     * @api {get} api/v4/comment/destroy  删除想法
     * @apiVersion 4.0.0
     * @apiName  destroy
     * @apiGroup Comment
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/comment/destroy
     * @apiParam {int} id 评论id
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
        $res = Comment::where('id', $id)
            ->update(['status' => 0]);
        if ($res) {
            CommentReply::where('comment_id', $id)->update(['status' => 0]);
            return $this->success();
        }
    }

    /**
     * @api {get} api/v4/comment/forward/user 想法-转发列表
     * @apiVersion 4.0.0
     * @apiName  forward_user
     * @apiGroup Comment
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/comment/forward/user
     * @apiParam {number} id 评论id
     *
     * @apiSuccess {string} user 用户
     * @apiSuccess {string} user.nickname 用户昵称
     * @apiSuccess {string} user.headimg 用户头像
     *
     * @apiSuccessExample  Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "code": 200,
     *       "msg" : '成功',
     * "data": [
     * {
     * "id": 7,
     * "user_id": 168934,
     * "user": {
     * "id": 168934,
     * "nickname": "chandler",
     * "headimg": "/wechat/works/headimg/3833/2017110823004219451.png"
     * }
     * }
     * ]
     *     }
     *
     */
    public function getForwardUser(Request $request)
    {
        $id = $request->input('id');
        $forwardUser = Comment::with('user:id,nickname,headimg,intro')
            ->select(['id', 'user_id'])
            ->where('pid', $id)
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->toArray();
        if($forwardUser['data']){
            foreach($forwardUser['data'] as &$v){
                $v['is_follow'] = 1;
            }
        }
        return success($forwardUser['data']);
    }

    /**
     * @api {get} api/v4/comment/like/user 想法-喜欢列表
     * @apiVersion 4.0.0
     * @apiName  like_user
     * @apiGroup Comment
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/comment/like/user
     * @apiParam {number} id 评论id
     *
     * @apiSuccess {string} user 用户
     * @apiSuccess {string} user.nickname 用户昵称
     * @apiSuccess {string} user.headimg 用户头像
     *
     * @apiSuccessExample  Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "code": 200,
     *       "msg" : '成功',
     * "data": [
     * {
     * "id": 6,
     * "user_id": 1,
     * "user": {
     * "id": 1,
     * "nickname": "刘先森",
     * "headimg": "https://nlsg-saas.oss-cn-beijing.aliyuncs.com/static/class/157291903507887.png"
     * }
     * }
     * ]
     *     }
     *
     */

    public function getLikeUser(Request $request)
    {
        $id = $request->input('id');
        $likeUser = Like::with('user:id,nickname,headimg,intro')
            ->select(['id', 'user_id'])
            ->where('relation_id', $id)
            ->where('type', 1)
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->toArray();
        if($likeUser['data']){
            foreach($likeUser['data'] as &$v){
                $v['is_follow'] = 1;
            }
        }
        return success($likeUser['data']);
    }


}
