<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\Column;
use App\Models\History;
use App\Models\Works;
use App\Models\WorksInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use GuzzleHttp\Client;
use JWTAuth;
use App\Models\User;
use App\Models\FeedBack;
use App\Models\UserFollow;
use App\Models\Comment;
use App\Models\Wiki;

class UserController extends Controller
{

    /**
     * @api {get} api/v4/user/homepage  用户主页
     * @apiVersion 4.0.0
     * @apiName  homepage
     * @apiGroup User
     * @apiHeader {string} Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC92NC5jb21cL2FwaVwvdjRcL2F1dGhcL2xvZ2luIiwiaWF0IjoxNTk0OTU0MDQxLCJleHAiOjE1OTYyNTAwNDEsIm5iZiI6MTU5NDk1NDA0MSwianRpIjoiMFVhdmsxT0piNXJSSHFENSIsInN1YiI6MSwicHJ2IjoiMjNiZDVjODk0OWY2MDBhZGIzOWU3MDFjNDAwODcyZGI3YTU5NzZmNyJ9.9qShuy0F5zwn-USMqKeVrDUKUW3JYQYCn46Yy04wbg0
     * @apiParam  {number} user_id  用户id
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/user/homepage
     *
     * @apiSuccess {string}  nickname  用户昵称
     * @apiSuccess {string}  sex       性别   1 男 2 女
     * @apiSuccess {string}  headimg   用户头像
     * @apiSuccess {string}  headcover 背景图
     * @apiSuccess {string}  intro     简介
     * @apiSuccess {string}  follow_num 关注数
     * @apiSuccess {string}  fan_num    粉丝数
     * @apiSuccess {string}  is_teacher 是否为老师
     * @apiSuccess {string}  is_self    是否为当前用户  1 是 0 否
     * @apiSuccess {string}  is_follow  是否关注 1 是 0 否
     * @apiSuccess {string}  works        作品
     * @apiSuccess {string}  works.title  作品标题
     * @apiSuccess {string}  works.subtitle  作品副标题
     * @apiSuccess {string}  works.cover_img     作品封面
     * @apiSuccess {string}  works.subscribe_num 作品订阅数
     * @apiSuccess {string}  works.original_price 作品价格
     *
     * @apiSuccess {string}  column           专栏
     * @apiSuccess {string}  column.name      专栏名称
     * @apiSuccess {string}  column.title     专栏标题
     * @apiSuccess {string}  column.subtitle  专栏副标题
     * @apiSuccess {string}  column.original_price  专栏价格
     *
     * @apiSuccessExample  Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "code": 200,
     *       "msg" : '成功',
     * "data": {
     * "id": 1,
     * "nickname": "刘先森",
     * "headimg": "https://nlsg-saas.oss-cn-beijing.aliyuncs.com/static/class/157291903507887.png",
     * "headcover": null,
     * "intro": "需要思考下了",
     * "follow_num": 10,
     * "fan_num": 0,
     * "is_teacher": 1,
     * "works": {
     * "id": 1,
     * "nickname": "刘先森",
     * "works": [
     * {
     * "user_id": 1,
     * "title": "理解孩子行为背后的原因",
     * "cover_img": "/wechat/works/video/161627/2017061411282192073.jpg",
     * "subscribe_num": 0,
     * "original_price": "0.00"
     * },
     * {
     * "user_id": 1,
     * "title": "帮助孩子树立健康自尊的六个方法",
     * "cover_img": "/wechat/works/video/161627/2017061411462579459.jpg",
     * "subscribe_num": 0,
     * "original_price": "0.00"
     * },
     * {
     * "user_id": 1,
     * "title": "培养责任心是孩子成长的必修课",
     * "cover_img": "/wechat/works/video/161627/2017061411572097640.jpg",
     * "subscribe_num": 0,
     * "original_price": "0.00"
     * }
     * ]
     * },
     * "column": {
     * "id": 1,
     * "nickname": "刘先森",
     * "columns": [
     * {
     * "user_id": 1,
     * "name": "张宝萍专栏",
     * "title": "国家十百千万工程心灵导师",
     * "subtitle": "心灵导师 直击人心",
     * "original_price": "0.00"
     * }
     * ]
     * }
     * }
     *     }
     *
     */
    public function homepage(Request $request)
    {
        $id = $request->get('user_id');
        $user = User::select('id', 'nickname', 'sex', 'headimg', 'headcover', 'intro', 'follow_num', 'fan_num', 'is_teacher')
            ->with([
                'history' => function ($query) {
                    $query->select(['id', 'user_id', 'relation_id','relation_type'])
                        ->limit(4)
                        ->orderBy('created_at', 'desc');
                },
                'history.columns:id,title,cover_pic',
                'history.works:id,title,cover_img,is_audio_book',
                'works'   => function ($query) {
                    $query->select(['id', 'user_id', 'title', 'subtitle','cover_img', 'subscribe_num', 'original_price'])
                        ->where('is_audio_book', 0);
                },
                'listens'   => function ($query) {
                    $query->select(['id', 'user_id', 'title', 'subtitle','cover_img', 'subscribe_num', 'original_price'])
                        ->where('is_audio_book', 1);
                },
                'columns' => function ($query) {
                    $query->select('user_id', 'name', 'title', 'subtitle', 'original_price','subscribe_num','cover_pic');
                },

            ])
            ->findOrFail($id)
            ->toArray();
        if($user){
            $isFollow =  UserFollow::where(['from_uid'=> $this->user['id'], 'to_uid'=>$id])->first();
            $user['is_self'] =  $id == $this->user['id'] ?  1 : 0;
            $user['is_follow'] = $isFollow ? 1: 0;
        }

        return success($user);
    }

    /**
     * @api {get} api/v4/user/feed  用户动态
     * @apiVersion 4.0.0
     * @apiName  feed
     * @apiGroup User
     * @apiParam  {number} user_id  用户id
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/user/feed
     *
     * @apiSuccess {string}  comments             想法
     * @apiSuccess {string}  comments.content     内容
     * @apiSuccess {number}  comments.forward_num 转发数
     * @apiSuccess {number}  comments.share_num   分享数
     * @apiSuccess {number}  comments.like_num    喜欢数
     * @apiSuccess {number}  comments.reply_num   评论数
     * @apiSuccess {number}  comments.created_at  发布时间
     *
     * @apiSuccess {string}  comments.user           评论的用户
     * @apiSuccess {string}  comments.user.nickname  评论的用户昵称
     * @apiSuccess {string}  comments.user.headimg   评论的用户头像
     *
     * @apiSuccess {string}  comments.attach         评论的图片
     * @apiSuccess {string}  comments.attach.img     评论的图片地址
     *
     * @apiSuccess {string}  comments.column           专栏 【讲座】
     * @apiSuccess {string}  comments.column.title     专栏的标题
     * @apiSuccess {string}  comments.column.cover_pic 专栏的封面
     * @apiSuccess {string}  comments.column.price          专栏的价格
     * @apiSuccess {string}  comments.column.subscribe_num  专栏的订阅数
     *
     * @apiSuccess {string}  comments.wiki            百科
     * @apiSuccess {string}  comments.wiki.name       百科标题
     * @apiSuccess {string}  comments.wiki.cover      百科封面
     * @apiSuccess {string}  comments.wiki.view_num   百科浏览数
     *
     *
     * @apiSuccessExample  Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "code": 200,
     *       "msg" : '成功',
     * "data": {
     * "id": 1,
     * "comments": [
     * {
     * "id": 14,
     * "pid": 0,
     * "user_id": 1,
     * "relation_id": 1,
     * "content": "生命 ",
     * "forward_num": 0,
     * "share_num": 0,
     * "like_num": 0,
     * "reply_num": 0,
     * "created_at": "2020-07-14 17:05:45",
     * "user": {
     * "id": 1,
     * "nickname": "刘先森",
     * "headimg": "https://nlsg-saas.oss-cn-beijing.aliyuncs.com/static/class/157291903507887.png"
     * },
     * "attach": [
     * {
     * "id": 16,
     * "relation_id": 14,
     * "img": "/wechat/mall/goods/3476_1533614056.png"
     * },
     * {
     * "id": 17,
     * "relation_id": 14,
     * "img": "/wechat/mall/goods/3476_1533614056.png"
     * },
     * {
     * "id": 18,
     * "relation_id": 14,
     * "img": "/wechat/mall/goods/3476_1533614056.png"
     * }
     * ]
     * }
     * ]
     * }
     *     }
     *
     */
    public function feed(Request $request)
    {
        $id = $request->get('user_id');
        if(!$id){
            return error('参数不能为空');
        }

        $comments = Comment::with(['user:id,nickname,headimg', 'attach:id,relation_id,img'])
            ->select('id', 'pid', 'user_id', 'relation_id', 'type', 'content', 'forward_num', 'share_num', 'like_num',
                'reply_num', 'created_at')
            ->where('user_id', $id)
            ->where('status', 1)
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->toArray();
        if ($comments['data']) {
            foreach ($comments['data'] as &$v) {
                if ($v['type'] == 1 || $v['type'] == 2) {
                    $v['column'] = Column::where('id', $v['relation_id'])
                        ->select('id', 'title', 'subtitle','price', 'subscribe_num', 'cover_pic','type')
                        ->first();
                } elseif ($v['type'] == 3 || $v['type'] == 4) {
                    $v['works'] = Works::where('id', $v['relation_id'])
                        ->select('id', 'title','subtitle', 'price', 'subscribe_num', 'cover_img','is_audio_book')
                        ->first();
                } elseif ($v['type'] == 5) {
                    $v['wiki'] = Wiki::where('id', $v['relation_id'])->select('id', 'name', 'cover', 'view_num')
                        ->first();
                }
            }
        }
        $data = [
            'data' => $comments['data'],
            'total'=> $comments['total']
        ];
        return success($data);
    }

    /**
     * @api {get} api/v4/user/followed 关注
     * @apiVersion 4.0.0
     * @apiName  followed
     * @apiGroup User
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/user/followed
     *
     * @apiParam {number} to_uid 被关注者的uid
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
    public function followed(Request $request)
    {
        $uid = $request->input('to_uid');
        if ( ! $uid) {
            return error(1000, '参数错误');
        }
        $list = UserFollow::where([
            'from_uid' => $this->user['id'],
            'to_uid'   => $uid
        ])->first();

        if ($list) {
            return error(1000, '不要重复关注');
        }

        UserFollow::create([
            'from_uid' => $this->user['id'],
            'to_uid'   => $uid
        ]);

        User::where('id', $uid)->increment('fan_num');

        return success();
    }

    /**
     * @api {get} api/v4/user/unfollow 取消关注
     * @apiVersion 4.0.0
     * @apiName  unfollow
     * @apiGroup User
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/user/unfollow
     *
     * @apiParam {int} to_uid 被关注者uid
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
    public function unfollow(Request $request)
    {
        $uid = $request->input('to_uid');
        $follow = UserFollow::where([
            'from_uid' => $this->user['id'],
            'to_uid'   => $uid
        ])->first();

        if ( ! $follow->delete()) {
            return error(1000, '取消失败');
        }
        return success();
    }

    /**
     * @api {get} api/v4/user/base  基本资料
     * @apiVersion 4.0.0
     * @apiName  base
     * @apiGroup User
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/user/base
     *
     * @apiSuccess {string} nickname 昵称
     * @apiSuccess {string} headimg  头像
     * @apiSuccess {string} birthday 生日
     * @apiSuccess {string} intro    简介
     *
     * @apiSuccessExample  Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "code": 200,
     *       "msg" : '成功',
     *       "data":{
     *          'nickname': '张三',
     *          'headimg' : 'test.png',
     *          'sex': 1,
     *           'birthday': '1990-1-1',
     *           'intro': '简介'
     *        }
     *     }
     *
     */
    public function base()
    {
        $user = User::select(['id', 'nickname', 'sex', 'headimg', 'birthday', 'intro'])
            ->where('id', $this->user['id'])
            ->first();
        return success($user);
    }


    /**
     * @api {get} api/v4/user/store 个人更新
     * @apiVersion 4.0.0
     * @apiName  store
     * @apiGroup User
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/user/base
     *
     * @apiParam {string} nickname 昵称
     * @apiParam {string} headimg   头像
     * @apiParam {number} sex       性别
     * @apiParam {string} birthday  生日
     * @apiParam {string} intro     简介
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
        if ( ! $input['nickname']) {
            return $this->error(1000, '昵称不能为空');
        }
        $res = User::where('id', $this->user['id'])->update([
            'nickname' => $input['nickname'],
            'headimg'  => $input['headimg'],
            'birthday' => $input['birthday'],
            'sex'      => $input['sex'],
            'intro'    => $input['intro']
        ]);
        if ($res) {
            return success();
        }

    }

    /**
     * @api {get} api/v4/user/account  账户与安全
     * @apiVersion 4.0.0
     * @apiName  account
     * @apiGroup User
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/user/account
     *
     * @apiSuccess {string} phone  手机号
     * @apiSuccess {number} is_wx  是否绑定微信 0 否 1是
     *
     * @apiSuccessExample  Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "code": 200,
     *       "msg" : '成功',
     *       "data": {
     *          "is_wx": 1,
     *          "phone": "186****5324"
     *       }
     *     }
     *
     */
    public function account()
    {
        $user = User::select(['phone'])->where('id', $this->user['id'])->first();
        if ($user) {
            $user->phone = substr_replace($user->phone, '****', 3, 4);
            $user->is_wx = 1;
        }
        return success($user);
    }

    /**
     * @api {get} api/v4/user/feedback 我要吐槽
     * @apiVersion 4.0.0
     * @apiParam {string} type 10:使用建议 11:内容漏缺 12:购物相关 13:物流配送 14:客服体验 15:节约相关
     * @apiParam {string} content 内容  不能大于200字
     * @apiParam {string} pic  图片url(数组格式)
     * @apiGroup Api
     *
     * @apiSuccess {String} token
     *
     * @apiSuccessExample  成功响应:
     *   {
     *      "code": 200,
     *      "msg" : '成功',
     *      "data": []
     *   }
     *
     */
    public function feedback(Request $request)
    {
        $input = $request->all();
        if ( ! $input['content']) {
            return $this->error(1000, '描述不能为空');
        }

//        $pics  = implode(',', $input['pic']);
//        if (count($pics) > 3) {
//            return $this->error(1000,'图片过多');
//        }
        $res = FeedBack::create([
            'type'    => $input['type'],
            'user_id' => 1,
            'content' => $input['content'],
            'pic'     => $input['pic']
        ]);
        if ($res) {
            return $this->success();
        }

    }

    /**
     * @api {get} api/v4/user/fan 关注他的人
     * @apiVersion 4.0.0
     * @apiGroup Api
     *
     * @apiParam  {number} user_id  用户id
     *
     * @apiSuccessExample 成功响应:
     *
        {
        "code": 200,
        "msg": "成功",
        "data": [
            {
                "id": 6,
                "from_uid": 211172,
                "to_uid": 1,
                "to_user": {
                    "id": 211172,
                    "nickname": "能量时光",
                    "headimg": "/wechat/works/headimg/3833/2017110823004219451.png"
                },
                "is_follow": 0
            },
            {
                "id": 9,
                "from_uid": 168934,
                "to_uid": 1,
                "to_user": {
                    "id": 168934,
                    "nickname": "chandler",
                    "headimg": "/wechat/works/headimg/3833/2017110823004219451.png"
                },
                "is_follow": 0
            }
        ]
    }
     *
     */
    public function fan(Request $request)
    {
        $uid = $request->get('user_id');
        $user = User::findOrFail($uid);
        if($user){
            $lists = UserFollow::with('toUser:id,nickname,headimg')
                    ->select('id','from_uid','to_uid')
                    ->where('to_uid', $uid)
                    ->paginate(10)->toArray();
            if($lists['data']){
                foreach ($lists['data'] as &$v) {
                    if($v['from_uid'] !== $this->user['id']){
                        $isFollow = UserFollow::where(['from_uid'=>$this->user['id'],'to_uid'=>$v['from_uid']])->first();
                        $v['is_follow'] = $isFollow ? 1 : 0;
                    }
                }
            }
        }
        return success($lists['data']);
    }

    /**
     * @api {get} api/v4/user/follower 他关注的人
     * @apiVersion 4.0.0
     * @apiGroup Api
     *
     * @apiParam  {number} user_id  用户id
     *
     * @apiSuccessExample 成功响应:
     *
     {
        "code": 200,
        "msg": "成功",
        "data": [
            {
                "id": 4,
                "from_uid": 1,
                "to_uid": 211172,
                "from_user": {
                    "id": 211172,
                    "nickname": "能量时光",
                    "headimg": "/wechat/works/headimg/3833/2017110823004219451.png"
                },
                "is_follow": 0
            },
            {
                "id": 10,
                "from_uid": 1,
                "to_uid": 2,
                "from_user": {
                    "id": 2,
                    "nickname": "刘尚",
                    "headimg": "/wechat/works/headimg/70/2017102911145924225.png"
                },
                "is_follow": 0
            },
            {
                "id": 12,
                "from_uid": 1,
                "to_uid": 168934,
                "from_user": {
                    "id": 168934,
                    "nickname": "chandler",
                    "headimg": "/wechat/works/headimg/3833/2017110823004219451.png"
                },
                "is_follow": 0
            }
        ]
    }
     *
     */
    public function follower(Request $request)
    {
        $uid = $request->get('user_id');
        $user = User::findOrFail($uid);
        if($user){
            $lists = UserFollow::with('fromUser:id,nickname,headimg')
                    ->select('id','from_uid','to_uid')
                    ->where('from_uid', $uid)
                    ->paginate(10)->toArray();
            if($lists['data']){
                foreach ($lists['data'] as &$v) {
                    if($v['to_uid'] !== $this->user['id']){
                        $isFollow = UserFollow::where(['from_uid'=>$this->user['id'], 'to_uid'=>$v['to_uid']])->first();
                        $v['is_follow'] = $isFollow ? 1 : 0;
                    }
                }
            }
        }
        return success($lists['data']);
    }


    /**
     * @api {get} api/v4/user/history  我的--历史记录
     * @apiVersion 4.0.0
     * @apiGroup user
     *
     * @apiParam {string} user_id 用户id
     * @apiParam {string} page 页数
     * @apiParam {string} order  desc|asc
     *
     * @apiSuccess {string} result json
     * @apiSuccess {string} column_name 名称   优先级 专栏 > 课程  >章节
     * @apiSuccess {string} works_name 名称   优先级 专栏 > 课程  >章节
     * @apiSuccess {string} works_info_name 名称   优先级 专栏 > 课程  >章节
     * @apiSuccess {string} column_cover_img  封面 优先级 专栏 > 课程
     * @apiSuccess {string} works_cover_img  封面 优先级 专栏 > 课程
     * @apiSuccess {string} user_id  用户id
     *
     * @apiSuccessExample 成功响应:
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": {
     * "07-05 ": [
     * {
     * "id": 8,
     * "column_id": 0,
     * "works_id": 16,
     * "worksinfo_id": 1,
     * "user_id": 211172,
     * "time_leng": "10",
     * "time_number": "5",
     * "is_del": 0,
     * "created_at": "2020-07-04T19:47:22.000000Z",
     * "updated_at": "2020-06-04T20:07:36.000000Z",
     * "column_name": "",
     * "column_cover_img": "",
     * "works_name": "如何经营幸福婚姻",
     * "works_cover_img": "/nlsg/works/20190822150244797760.png",
     * "worksInfo_name": "01何为坚毅"
     * },
     * {
     * "id": 9,
     * "column_id": 1,
     * "works_id": 16,
     * "worksinfo_id": 2,
     * "user_id": 211172,
     * "time_leng": "0",
     * "time_number": "",
     * "is_del": 0,
     * "created_at": "2020-07-04T19:47:22.000000Z",
     * "updated_at": null,
     * "column_name": "王琨专栏",
     * "column_cover_img": null,
     * "works_name": "如何经营幸福婚姻",
     * "works_cover_img": "/nlsg/works/20190822150244797760.png",
     * "worksInfo_name": "02坚毅品格的重要性"
     * }
     * ]
     * }
     * }
     */
    public function history(Request $request)
    {
        $user_id = $request->input('user_id', 0);
        $order = $request->input('order', 'desc');

        $lists = History::where(['user_id' => $user_id, 'is_del' => 0,])
            ->orderBy('created_at', $order)->paginate($this->page_per_page)->toArray();

        if (empty($lists['data'])) {
            return $this->success();
        }
        $new_list = [];
        foreach ($lists['data'] as $key => $val) {
            //查询所属专栏 课程 以及章节
            $val['column_name'] = '';
            $val['column_cover_img'] = '';
            $val['works_name'] = '';
            $val['works_cover_img'] = '';
            $val['worksInfo_name'] = '';


            if ($val['column_id']) {
                $column = Column::find($val['column_id']);
                $val['column_name'] = $column['name'];
                $val['column_cover_img'] = $column['cover_img'];
            }
            if ($val['works_id']) {
                $works = Works::find($val['works_id']);
                $val['works_name'] = $works['title'];
                $val['works_cover_img'] = $works['cover_img'];
            }
            if ($val['worksinfo_id']) {
                $worksInfo = WorksInfo::find($val['worksinfo_id']);
                $val['worksInfo_name'] = $worksInfo['title'];
            }


            $new_list[History::DateTime($val['created_at'])][] = $val;
        }


        return $this->success($new_list);
    }


    /**
     * @api {get} api/v4/user/clear_history  我的--清空学习记录
     * @apiVersion 4.0.0
     * @apiGroup user
     *
     * @apiParam {string} user_id 用户id
     * @apiParam {string} his_id  清空全部值为all  否则传id 多个用英文逗号拼接 如1,2,3
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample 成功响应:
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": []
     * }
     */
    public function clearHistory(Request $request)
    {
        $user_id = $request->input('user_id', 0);
        $his_id = $request->input('his_id', 0);
        if (empty($his_id)) {
            return $this->error(0, 'fail:his_id参数有误');
        }

        if ($his_id == 'all') {
            $res = History::where('user_id', $user_id)->update(['is_del' => 1]);
        } else {
            $his_id = explode(',', $his_id);
            $res = History::where('user_id', $user_id)
                ->whereIn('id', $his_id)->update(['is_del' => 1]);
        }
        if ($res) {
            return $this->success();
        } else {
            return $this->error(0, 'fail');
        }
    }


    /**
     * @api {get} api/v4/user/collection  我的--收藏列表
     * @apiVersion 4.0.0
     * @apiGroup user
     *
     * @apiParam {string} user_id 用户id
     * @apiParam {string} type  默认1  1专栏  2课程  3商品  4书单 5百科 6听书
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample 成功响应:
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": [
     * {
     * "id": 91,
     * "name": "AR立体浮雕星座地球仪",   //商品名称  类型不同返回字段不同
     * "picture": "/nlsg/goods/20191026172620981048.jpg",
     * "original_price": "379.00",
     * "price": "333.52"
     * }
     * ]
     * }
     *
     *
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": [
     * {
     * "id": 1,
     * "name": "王琨专栏",     //专栏名
     * "title": "顶尖导师 经营能量",       //头衔
     * "subtitle": "顶尖导师 经营能量",    //副标题
     * "message": "",
     * "price": "99.00",
     * "cover_pic": "/wechat/works/video/161627/2017121117503851065.jpg",
     * "is_new": 1
     * }
     * ]
     * }
     */


    public function collection(Request $request)
    {

        $user_id = $this->user['id'] ?? 0;
        $type = $request->input('type', 1);
        //1专栏  2课程  3商品  4书单 5百科 6听书

        $collection = Collection::where([
            'user_id' => $user_id,
            'type'    => $type,
        ])->paginate($this->page_per_page)->toArray();
        $relation_id = array_column($collection['data'], 'relation_id');

        $list = Collection::getCollection($type, $relation_id);
        if ($list == false) {
            $list = [];
        }

        return $this->success($list);
    }


}
