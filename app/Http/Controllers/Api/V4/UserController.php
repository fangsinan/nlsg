<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\Column;
use App\Models\ConfigModel;
use App\Models\Coupon;
use App\Models\History;
use App\Models\LiveUserPrivilege;
use App\Models\MeetingSales;
use App\Models\UserInvite;
use App\Models\Works;
use App\Models\WorksInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use GuzzleHttp\Client;
use JWTAuth;
use App\Models\User;
use App\Models\FeedBack;
use App\Models\UserFollow;
use App\Models\Comment;
use App\Models\Wiki;
use App\Models\VipUser;

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
     * @apiSuccess {number}  is_author   是否是作者 1是 0 否
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
     * @apiSuccess {string}  history        学习记录
     * @apiSuccess {string}  history.relation_type  学习记录类型 1专栏   2课程   3讲座
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
        $user = User::select('id', 'nickname', 'phone', 'is_author', 'sex', 'headimg', 'headcover', 'intro', 'follow_num', 'fan_num', 'is_author')
            ->with([
                'history' => function ($query) {
                    $query->select(['id', 'user_id', 'relation_id', 'relation_type'])
                        ->where('is_del',0)
                        ->limit(10)
                        ->groupBy('relation_type', 'relation_id')
                        ->orderBy('created_at', 'desc');
                },
                'works' => function ($query) {
                    $query->select(['id', 'user_id', 'title', 'subtitle', 'cover_img', 'subscribe_num', 'original_price'])
                        ->where('status', 4)
                        ->where('type', 2)
                        ->where('is_audio_book', 0);
                },
                'listens' => function ($query) {
                    $query->select(['id', 'user_id', 'title', 'subtitle', 'cover_img', 'subscribe_num', 'original_price'])
                        ->where('status', 4)
                        ->where('is_audio_book', 1);
                },
                'lecture' => function ($query) {
                    $query->select('id', 'user_id', 'name', 'title', 'subtitle', 'original_price', 'subscribe_num', 'cover_pic')
                    ->where(['status'=>1, 'type'=>2]);
                },

            ])
            ->find($id)->toArray();
        $user['columns'] = []; //不显示专栏

        if ($user) {
            $isFollow = UserFollow::where(['from_uid' => $this->user['id'], 'to_uid' => $id])->first();
            $user['is_self'] = $id == $this->user['id'] ? 1 : 0;
            $user['is_follow'] = $isFollow ? 1 : 0;

            if ($user['history']) {
                foreach ($user['history'] as $k=>&$v) {
                    if ($v['relation_type'] == 1) {
                        $v['columns'] = [];//不显示专栏
//                        Column::select('id', 'title', 'cover_pic')
//                            ->where('id', $v['relation_id'])
//                            ->where('status', 1)
//                            ->where('type', 1)

                    } elseif ($v['relation_type'] == 2) {
                        $v['lecture'] = Column::select('id', 'name as title','subtitle', 'cover_pic')
                            ->where('id', $v['relation_id'])
                            ->where('status', 1)
                            ->where('type', 2)
                            ->first();
                    } elseif ($v['relation_type'] == 3) {
                        $v['listens'] = Works::select('id', 'title', 'cover_img', 'is_audio_book')
                            ->where('id', $v['relation_id'])
                            ->where('status', 4)
                            ->where('is_audio_book', 1)
                            ->first();
                    } elseif ($v['relation_type'] == 4) {
                        $v['works'] = Works::select('id', 'title', 'cover_img', 'is_audio_book')
                            ->where('id', $v['relation_id'])
                            ->where('status', 4)
                            ->first();
                    } elseif ($v['relation_type'] == 5) {
                        $v['lecture'] = Column::select('id', 'name as title','subtitle', 'cover_pic')
                            ->where('id', $v['relation_id'])
                            ->where('status', 1)
                            ->where('type', 3)
                            ->first();
                    }
                    if(empty($v['columns']) && empty($v['lecture']) && empty( $v['listens']) && empty($v['works'])){
                        unset($user['history'][$k]);

                    }

                }

                $user['history'] = array_values($user['history']);
            }

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
     * @apiSuccess {number}  comments.flower_num  送花数量
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
        $activity_tag = $request->input('activity_tag','');
        if (!$id) {
            return error('参数不能为空');
        }

        $query = Comment::with(['user:id,nickname,headimg', 'attach:id,relation_id,img'])
            ->select('id', 'pid', 'user_id', 'relation_id', 'type', 'content', 'forward_num', 'share_num', 'like_num', 'flower_num',
                'reply_num', 'created_at')
            ->where('user_id', $id)
            ->where('status', 1)
            ->where('type', '!=',7)
            ->orderBy('created_at', 'desc');

        if ($activity_tag === 'cytx'){
            $query->whereIn('type',[2,4]);
        }

        $comments = $query->paginate(10)
            ->toArray();

        if ($comments['data']) {
            foreach ($comments['data'] as &$v) {
                if ($v['type'] == 1 || $v['type'] == 2) {
                    $v['column'] = Column::where('id', $v['relation_id'])
                        ->select('id', 'name as title', 'subtitle', 'price', 'subscribe_num', 'cover_pic', 'type')
                        ->first();
                } elseif ($v['type'] == 3 || $v['type'] == 4) {
                    $v['works'] = Works::where('id', $v['relation_id'])
                        ->select('id', 'title', 'subtitle', 'price', 'subscribe_num', 'cover_img', 'is_audio_book')
                        ->first();
                } elseif ($v['type'] == 5) {
                    $v['wiki'] = Wiki::where('id', $v['relation_id'])->select('id', 'name', 'cover', 'view_num')
                        ->first();
                }
            }
        }
        $data = [
            'data' => $comments['data'],
            'total' => $comments['total']
        ];
        return success($data);
    }

    /**
     * @api {post} api/v4/user/followed 关注
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
        if (!$uid) {
            return error(1000, '参数错误');
        }
        $list = UserFollow::where([
            'from_uid' => $this->user['id'],
            'to_uid' => $uid
        ])->first();

        if ($list) {
            return error(1000, '不要重复关注');
        }

        UserFollow::create([
            'from_uid' => $this->user['id'],
            'to_uid' => $uid
        ]);

        User::where('id', $uid)->increment('fan_num');
        User::where('id', $this->user['id'])->increment('follow_num');
        return $this->getRes(['code' => true, 'msg' => '关注成功']);
        return success();
    }

    /**
     * @api {post} api/v4/user/unfollow 取消关注
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
            'to_uid' => $uid
        ])->first();

        if (!$follow) {
            return error(1000, '还没有关注');
        }

        if (!$follow->delete()) {
            return error(1000, '取消失败');
        }
        User::where('id', $uid)->decrement('fan_num');
        User::where('id', $this->user['id'])->decrement('follow_num');

        return $this->getRes(['code' => true, 'msg' => '取消成功']);

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
     * @apiSuccess {number} like_nun  喜欢精选
     * @apiSuccess {number} income_num 收益动态
     * @apiSuccess {number} reply_num   评论@
     * @apiSuccess {number} fans_num    新增粉丝
     * @apiSuccess {number} systerm_num  系统通知
     * @apiSuccess {number} update_num   更新消息
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
        $user = User::select(['id', 'nickname', 'sex', 'headimg', 'birthday', 'intro', 'like_nun', 'income_num', 'reply_num', 'fans_num', 'systerm_num', 'update_num'])
            ->where('id', $this->user['id'])
            ->first();
        return success($user);
    }


    /**
     * @api {post} api/v4/user/store 个人更新
     * @apiVersion 4.0.0
     * @apiName  store
     * @apiGroup User
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/user/store
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
        if (!$input['nickname']) {
            return $this->error(1000, '昵称不能为空');
        }
        $res = User::where('id', $this->user['id'])->update([
            'nickname' => $input['nickname'],
            'headimg' => $input['headimg'] ?? '',
            'birthday' => $input['birthday'] ?? '',
            'sex' => $input['sex'],
            'intro' => $input['intro'] ?? ''
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
        $user = User::select(['phone', 'is_wx'])->where('id', $this->user['id'])->first();
        if ($user) {
            $user->phone = !empty($user->phone) ? substr_replace($user->phone, '****', 3, 4) : '';
            $user->is_wx = $user->is_wx ? 1 : 0;
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
        if (!$input['content']) {
            return $this->error(1000, '描述不能为空');
        }

//        $pics  = implode(',', $input['pic']);
//        if (count($pics) > 3) {
//            return $this->error(1000,'图片过多');
//        }
        $res = FeedBack::create([
            'type' => $input['type'],
            'user_id' => $this->user['id'],
            'content' => $input['content'],
            'pic' => $input['pic']
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
     * @apiParam  {number} user_id  用户id 【我的 不用传user_id】
     *
     * @apiSuccessExample 成功响应:
     *
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": [
     * {
     * "id": 6,
     * "from_uid": 211172,
     * "to_uid": 1,
     * "to_user": {
     * "id": 211172,
     * "nickname": "能量时光",
     * "headimg": "/wechat/works/headimg/3833/2017110823004219451.png"
     * },
     * "is_follow": 0
     * },
     * {
     * "id": 9,
     * "from_uid": 168934,
     * "to_uid": 1,
     * "to_user": {
     * "id": 168934,
     * "nickname": "chandler",
     * "headimg": "/wechat/works/headimg/3833/2017110823004219451.png"
     * },
     * "is_follow": 0
     * }
     * ]
     * }
     *
     */
    public function fan(Request $request)
    {
        $uid = $request->get('user_id');
        if (!$uid) {
            $uid = $this->user['id'];
        }
        $user = User::findOrFail($uid);
        if ($user) {
            $lists = UserFollow::with('toUser:id,nickname,intro,headimg')
                ->select('id', 'from_uid', 'to_uid')
                ->where('to_uid', $uid)
                ->paginate(10)->toArray();
            if ($lists['data']) {
                foreach ($lists['data'] as &$v) {
                    if ($v['from_uid'] !== $this->user['id']) {
                        $isFollow = UserFollow::where(['from_uid' => $this->user['id'], 'to_uid' => $v['from_uid']])->first();
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
     * @apiParam  {number} user_id  用户id [我的 不用传user_id】
     *
     * @apiSuccessExample 成功响应:
     *
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": [
     * {
     * "id": 4,
     * "from_uid": 1,
     * "to_uid": 211172,
     * "from_user": {
     * "id": 211172,
     * "nickname": "能量时光",
     * "headimg": "/wechat/works/headimg/3833/2017110823004219451.png"
     * },
     * "is_follow": 0
     * },
     * {
     * "id": 10,
     * "from_uid": 1,
     * "to_uid": 2,
     * "from_user": {
     * "id": 2,
     * "nickname": "刘尚",
     * "headimg": "/wechat/works/headimg/70/2017102911145924225.png"
     * },
     * "is_follow": 0
     * },
     * {
     * "id": 12,
     * "from_uid": 1,
     * "to_uid": 168934,
     * "from_user": {
     * "id": 168934,
     * "nickname": "chandler",
     * "headimg": "/wechat/works/headimg/3833/2017110823004219451.png"
     * },
     * "is_follow": 0
     * }
     * ]
     * }
     *
     */
    public function follower(Request $request)
    {
        $uid = $request->get('user_id');
        if (!$uid) {
            $uid = $this->user['id'];
        }
        $user = User::findOrFail($uid);
        $lists = [];
        if ($user) {
            $lists = UserFollow::with('fromUser:id,nickname,intro,headimg')
                ->select('id', 'from_uid', 'to_uid')
                ->where('from_uid', $uid)
                ->where('status', 1)
                ->paginate(10)->toArray();
            if ($lists['data']) {
                foreach ($lists['data'] as &$v) {
                    if ($v['to_uid'] !== $this->user['id']) {
                        $isFollow = UserFollow::where(['from_uid' => $this->user['id'], 'to_uid' => $v['to_uid'],'status'=>1])->first();
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

        $user_id = $this->user['id'] ?? 0;
        $order = $request->input('order', 'desc');

        $lists = History::where(['user_id' => $user_id, 'is_del' => 0,])
            ->groupBy('relation_id', 'relation_type')->orderBy('updated_at', $order)->paginate($this->page_per_page)->toArray();

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


            if ($val['relation_type'] == 1 or $val['relation_type'] == 2 or $val['relation_type'] == 5) {
//                $column = Column::find($val['relation_id']);
                $column = Column::where(['id'=>$val['relation_id'],'status'=>1])->first();
                $val['column_name'] = $column['name'] ?? '';
                $val['column_cover_img'] = $column['cover_pic'] ?? '';
            }
            if ($val['relation_type'] == 3 or $val['relation_type'] == 4) {
//                $works = Works::find($val['relation_id']);
                $works = Works::where(['id'=>$val['relation_id'],'status'=>4])->first();
                $val['works_name'] = $works['title'] ?? '';
                $val['works_cover_img'] = $works['cover_img'] ?? '';
            }
            if ($val['info_id']) {
                $worksInfo = WorksInfo::find($val['info_id']);
                $val['worksInfo_name'] = $worksInfo['title'] ?? '';
                $val['worksInfo_type'] = $worksInfo['type'] ?? "";
            }
//            $new_list[History::DateTime($val['created_at'])][] = $val;

            if($val['column_name'] || $val['works_name'] ){
                $list[History::DateTime($val['created_at'])][] = $val;
                $new_list[History::DateTime($val['created_at'])][] = $val;
            }
        }

        return $this->success($new_list);
    }


    //接口新数据格式
    public function new_history(Request $request)
    {
        $user_id = $request->input('user_id', 0);

        $user_id = $this->user['id'] ?? 0;
        $order = $request->input('order', 'desc');

        $lists = History::where(['user_id' => $user_id, 'is_del' => 0,])
            ->groupBy('relation_id', 'relation_type')->orderBy('updated_at', $order)->paginate($this->page_per_page)->toArray();

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


            if ($val['relation_type'] == 1 or $val['relation_type'] == 2 or $val['relation_type'] == 5) {
//                $column = Column::find($val['relation_id']);
                $column = Column::where(['id'=>$val['relation_id'],'status'=>1])->first();
                $val['column_name'] = $column['name'] ?? '';
                $val['column_cover_img'] = $column['cover_pic'] ?? '';
            }
            if ($val['relation_type'] == 3 or $val['relation_type'] == 4) {
//                $works = Works::find($val['relation_id']);
                $works = Works::where(['id'=>$val['relation_id'],'status'=>4])->first();
                $val['works_name'] = $works['title'] ?? '';
                $val['works_cover_img'] = $works['cover_img'] ?? '';
            }
            if ($val['info_id']) {
                $worksInfo = WorksInfo::find($val['info_id']);
                $val['worksInfo_name'] = $worksInfo['title'] ?? '';
                $val['worksInfo_type'] = $worksInfo['type'] ?? "";
            }
//            $new_list[History::DateTime($val['created_at'])][] = $val;

            if($val['column_name'] || $val['works_name'] ){
                $list[History::DateTime($val['updated_at'])][] = $val;
//                $list['date'] = History::DateTime($val['created_at']);
//                $list['his_arr'] = $val;
            }
        }

        // 处理数组
        //{
        //   [date]     => '日期'
        //   [his_arr]  =>  当前日期对应的数组
        //}

        if(!empty($list)){
            $new_list = [];
            foreach ($list as $k => $v ){
                $min_list['date'] = $k;
                $min_list['his_arr'] = $v;
                //防止有空key  数组变成对象 前端报错
                if( !empty($min_list) ){
                    $new_list[] = $min_list;
                }
            }
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

        $history_num = User::where(['id'=>$user_id])->value('history_num');

        if ($his_id == 'all') {
            $res = History::where('user_id', $user_id)->update(['is_del' => 1]);
            if ($history_num >0){
                User::where(['id' => $user_id])->update(['history_num' => 0]);
            }

        } else {
            $his_id = explode(',', $his_id);
//            $res = History::where('user_id', $user_id)
//                ->whereIn('id', $his_id)->update(['is_del' => 1]);
            //历史记录展示的是按课程 非章节 删除时需按课程id删除
            $relation_id_list = History::where('user_id', '=', $user_id)
                ->whereIn('id', $his_id)
                ->select(['relation_id'])
                ->get();
            if (empty($relation_id_list)) {
                return $this->error(0, 'fail');
            }
            $relation_id_list = $relation_id_list->toArray();
            $relation_id_list = array_column($relation_id_list, 'relation_id');
            $res = History::where('user_id', '=', $user_id)
                ->whereIn('relation_id', $relation_id_list)
                ->update(['is_del' => 1]);
            if ($history_num >0){
                User::where(['id' => $user_id])->decrement('history_num', count($his_id));
            }

        }
        return $this->success();
    }


    /**
     * @api {get} api/v4/user/collection  我的--收藏列表
     * @apiVersion 4.0.0
     * @apiGroup user
     *
     * @apiParam {string} user_id 用户id
     * @apiParam {string} type  默认1  1专栏  2课程  3商品  4书单 5百科 6听书  7讲座  8训练营
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
        if ($user_id == 0) {
            return $this->success();
        }

        //1专栏  2课程  3商品  4书单 5百科 6听书

        $collection = Collection::where([
            'user_id' => $user_id,
            'type' => $type,
        ])->paginate($this->page_per_page)->toArray();
        $relation_id = array_column($collection['data'], 'relation_id');

        if (empty($relation_id)) {
            return $this->success();
        }
        $list = Collection::getCollection($type, $relation_id, $user_id);
        if ($list == false) {
            $list = [];
        }

        foreach ($collection['data'] as &$value) {
            foreach ($list as &$list_value) {
                if ($value['relation_id'] == $list_value['id']) {
                    $list_value['collection_time'] = $value['created_at'];
                    $list_value['info_id'] = $value['info_id'];

                }
            }
        }
        return $this->success($list);
    }

    /**
     * @api {get} api/v4/user/statistics  我的数据统计
     * @apiVersion 4.0.0
     * @apiGroup user
     *
     *
     * @apiSuccess {string}  nickname  昵称
     * @apiSuccess {string}  headimg   头像
     * @apiSuccess {number}  phone     手机号
     * @apiSuccess {number}  level     等级  2推客  3黑钻  4皇钻  5服务商
     * @apiSuccess {number}  is_vip      1幸福大使  2钻石
     * @apiSuccess {number}  is_author   是否是作者 1是 0 否
     * @apiSuccess {string}  notify_num  消息数量 >0 显示
     * @apiSuccess {string}  follow_num  关注数
     * @apiSuccess {string}  fan_num     粉丝数
     * @apiSuccess {string}  history_num  学习记录数
     * @apiSuccessExample 成功响应:
     * {
     * "code": 200,
     * "msg": "成功",
     * "data":
     *  {
     *       "notify_num": 1,
     *      "follow_num": 2,
     *       "fan_num": 2,
     *      "history_num": 4
     *   }
     * }
     */
    public function statistics()
    {
        if (1) {
            $res['id'] = $this->user['id'] ?? 0;
            $res['nickname'] = $this->user['nickname'] ?? '';
            $res['headimg'] = $this->user['headimg'] ?? '';
            if (is_numeric($this->user['phone'])){
                $res['phone'] = substr_replace($this->user['phone'], '****', 3, 4) ?? '';
            } else {
                $res['phone'] = $this->user['phone'];
            }
            $res['level'] = $this->user['true_level'] ?? 0;
            $res['is_author'] = $this->user['is_author'] ?? 0;
            $res['expire_time'] = $this->user['expire_time'] ?? '';
            $res['notify_num'] = $this->user['notify_num'] ?? 0;
            $res['follow_num'] = $this->user['follow_num'] ?? 0;
            $res['fan_num'] = $this->user['fan_num'] ?? 0;
            $res['history_num'] = $this->user['history_num'] ?? 0;
            $res['is_vip'] = $this->user['new_vip']['level'] ?? 0;
            $res['sex'] = $this->user['sex'];
            $res['is_community_admin'] = $this->user['is_community_admin'];  // im管理员

            if ($res['id']) {
                $is_live = LiveUserPrivilege::where('user_id', $this->user['id'])
                    ->where('privilege', 2)
                    ->where('is_del', 0)
                    ->first();
                $res['is_live'] = $is_live ? 1 : 0;
            } else {
                $res['is_live'] = 0;
            }

            //判断是否为会场销售老师
            $res['is_meeting_sales'] = 0;
            $check_meeting_sales = MeetingSales::where('user_id', '=', $this->user['id'])
                ->where('status', '=', 1)
                ->select(['id', 'user_id', 'phone', 'nickname', 'qr_code'])
                ->first();
            if (!empty($check_meeting_sales)) {
                $res['is_meeting_sales'] = 1;
            }



            //15天
            $res['cancel_time'] = $this->user['cancel_time'] ? strtotime($this->user['cancel_time']) :0;
            $res['cancel_end_time'] = strtotime($this->user['cancel_time'])+(86400*15);
            $res['cancel_days'] = 0;
            if( strtotime($this->user['cancel_time'])+(86400*15) > time() ){
                $res['cancel_days'] = ceil((strtotime($this->user['cancel_time'])+(86400*15) - time()) / 86400);
            }

            return success($res);

        } else {
            $uid = $this->user['id'];
            $lists = User::select('id', 'nickname', 'headimg', 'phone', 'level', 'is_author',
                'expire_time', 'notify_num', 'follow_num', 'fan_num', 'history_num')
                ->find($uid);
            if ($lists) {
                if (is_numeric($lists->phone)){
                    $lists->phone = substr_replace($lists->phone, '****', 3, 4);
                }
                $is_live = LiveUserPrivilege::where('user_id', $this->user['id'])
                    ->where('privilege', 2)
                    ->where('is_del', 0)
                    ->first();
                $lists['is_live'] = $is_live ? 1 : 0;
                $vip = VipUser::where('user_id', $uid)
                    ->where('status', 1)
                    ->where('is_default', 1)
                    ->first();
                if ($vip) {
                    $lists['is_vip'] = $vip['level'] == 1 ? 1 : 2;
                } else {
                    $lists['is_vip'] = 0;
                }
                if (!empty($lists['level']) && !empty($lists['expire_time']) &&
                    $lists['expire_time'] > date('Y-m-d H:i:s')) {
                    $lists['level'] = $lists['level'];
                } else {
                    $lists['level'] = 0;
                }
            }

            return success($lists);
        }
    }


    //邀请记录
    public function invitationRecord()
    {
        $model = new User();
        $res = $model->getInvitationRecord($this->user['id'] ?? 0);
        return $this->success($res);
    }

    /**
     * @api {POST} api/v4/change/phone  更换手机号
     * @apiVersion 4.0.0
     * @apiGroup user
     *
     * @apiParam {string} phone 手机号
     * @apiParam {string} code  验证码
     *
     * @apiSuccess {number}  id  用户id
     * @apiSuccess {string}  token   用户授权
     * @apiSuccessExample 成功响应:
     * {
     * "code": 200,
     * "msg": "成功",
     * "data":
     *  {
     *
     *   }
     * }
     */
    public function changePhone(Request $request)
    {
        $phone = $request->input('phone');
        $code = $request->input('code');
        $data = [
            'id' => 0,
            'token' => ''
        ];

        if (!$phone) {
            return error(1000, '手机号不能为空', $data);
        }
        if (!$code) {
            return error(1000, '验证码不能为空', $data);
        }

        $dont_check_phone = ConfigModel::getData(35, 1);
        $dont_check_phone = explode(',', $dont_check_phone);
        if (in_array($phone, $dont_check_phone)) {
            if (intval($code) !== 6666) {
                return error(400, '验证码错误', $data);
            }
        } else {
            $res = Redis::get($phone);
            if (!$res) {
                return error(1000, '验证码已过期', $data);
            }

            if ($code !== $res) {
                return error(1000, '验证码错误', $data);
            }
        }

        $list = User::where('phone', $phone)->first();
        if ($list) {
            return error(1000, '该手机号码已存在', $data);
        }
        $res = User::where('id', $this->user['id'])->update(['phone' => $phone]);

        VipUser::where('user_id', $this->user['id'])->update(['username' => $phone]);

        if ($res) {
            $user = User::where('id', $this->user['id'])->first();

            Redis::del($phone);
            $token = auth('api')->login($user);;
            $data = [
                'id' => $user->id,
                'token' => $token
            ];
            return success($data);
        }

    }

    public function bindWechat(Request $request)
    {
        $input = $request->all();

        $data = [
            'sex' => $input['sex'] == '男' ? 1 : 2,
            'province' => $input['province'] ?? '',
            'city' => $input['city'] ?? '',
            'unionid' => $input['unionid'] ?? '',
            'is_wx' => 1
        ];
        $list = User::where('id', $this->user['id'])->first();
        if (is_null($list['headimg']) || $list['headimg'] =='image/202009/13f952e04c720a550193e5655534be86.jpg'){
            $data['headimg'] =  $input['headimg'] ?? '';
        }
        if ( empty($list['nickname']) ){
            $data['nickname'] = $input['nickname'] ?? '';
        }

        $user = User::where('unionid', $input['unionid'])->first();
        if ($user){
            return error(1000, '该微信已被其他账号绑定');
        }

        $res = User::where('id', $this->user['id'])->update($data);
        if ($res) {
            return success();
        }
        return error(1000, '绑定失败');

    }

    public function removeWechat(Request $request)
    {
        $res = User::where('id', $this->user['id'])->update(
            [
                'unionid' => '',
                'is_wx' => 0
            ]);
        if ($res) {
            return success();
        }
        return error(1000, '解绑失败');
    }

    /**
     * @api {get} api/v4/user/coupon   邀请有礼
     * @apiVersion 4.0.0
     * @apiName  coupon
     * @apiGroup User
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/user/coupon
     *
     * @apiParam {string}   token  当前用户token
     *
     * @apiSuccess {string}  coupon 优惠券
     * @apiSuccess {string}  coupon.name 优惠券名称
     * @apiSuccess {string}  coupon.price 优惠券价格
     * @apiSuccess {string}  coupon.begin_time 优惠券开始时间
     * @apiSuccess {string}  coupon.end_time 优惠券结束时间
     * @apiSuccess {number}  invite_num 邀请数量
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
    public function getUserCoupon()
    {
        $lists = Coupon::select('id', 'user_id', 'name', 'price', 'begin_time', 'end_time')
            ->where('user_id', $this->user['id'])
            ->where('type', 7)
            ->orderBy('created_at')
            ->paginate(10)
            ->toArray();
        if ($lists['data']) {
            foreach ($lists['data'] as &$v) {
                $v['begin_time'] = date('Y-m-d', strtotime($v['begin_time']));
                $v['end_time'] = date('Y-m-d', strtotime($v['end_time']));
            }
        }

        $invite_num = UserInvite::where('from_uid', $this->user['id'])
            ->where('type', 1)
            ->count();
        $invite_num = $invite_num ?? 0;

        $data = [
            'coupon' => $lists['data'],
            'invite_num' => $invite_num
        ];
        return success($data);
    }







    /**
     * @api {get} api/v4/user/edit_user   用户信息采集
     * @apiVersion 4.0.0
     * @apiName  coupon
     * @apiGroup User
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/user/coupon
     *
     * @apiParam {string}   sex   性别  0 未知 1 男 2 女
     * @apiParam {string}   children_age  孩子年龄范围  0:无  1: 0~6岁  2:7~18岁   3: 18岁以上
     *
     * @apiSuccessExample  Success-Response:
     * {
     *    "code": 200,
     *    "msg" : '成功',
     *    "data":[]
     * }
     *
     */
    public function editUserInfo(Request $request){
        $uid = $this->user['id'] ?? 0;

        //sex 性别  0 未知 1 男 2 女
        $sex = $request->input('sex') ?? 0;
        $children_age = $request->input('children_age') ?? 0;

        $res = User::where('id', $uid)->update([
            'sex'           => $sex,
            'children_age'  => $children_age,
        ]);
        if ($res) {
            return $this->getRes(['code'=>0,'msg'=>'提交成功']);
        }else{
            return error(1000, '提交失败');
        }
    }

    /**
    * @api {post} api/v4/user/check_phone   验证手机号是否已经存在
    * @apiVersion 4.0.0
    * @apiName  check-phone
    * @apiGroup User
    * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/user/coupon
    *
    * @apiParam {string}   token  当前用户token
    * @apiParam {string}   phone  手机号
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
    public function checkPhone(Request $request)
    {
        $phone = $request->input('phone');
        if (!$phone){
            return  error(1000, '手机号不能为空');
        }
        $list = User::where('phone', $phone)->first();
        if ($list) {
            return error(1000, '该手机号码已存在');
        }
        return success();
    }





    /**
     * @api {post} api/v4/user/user_his_list   获取学习榜单
     * @apiVersion 4.0.0
     * @apiName  user_his_list
     * @apiGroup User
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/user/user_his_list
     *
     * @apiParam {string}   token  当前用户token
     * @apiParam {string}   phone  手机号
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
    public function userHisList(Request $request){
        $uid = $this->user['id'] ?? 211172;
        $page = $request->input('page');
        //本周一
        $week_one = date("Y-m-d H:i:s",strtotime("last Monday"));
        //上周一
        $top_week_one = date("Y-m-d H:i:s",strtotime("last Monday",strtotime("-1 week")));//上周一

        if($page <= 1){
            $data = User::getUserHisLen(20,$top_week_one);
        }else{
            $data = [];
        }

        //自己的排名
        $u_data = [
            "id" => $uid,
            "nickname" => '',
            "headimg" => '',
            "his_num" => 0,
            "rank"      => 0,
        ];
        if(!empty($uid)){
            $cache_key_name = 'his_len_deteil_'.$uid;
            $u_data = Cache::get($cache_key_name);
            if (empty($u_data)) {
                $user_data = History::select("user_id")->selectRaw('sum(time_number) as num')
                    ->where('user_id',$uid)
                    ->where('created_at','>',$top_week_one)
                    ->where('created_at','<',$week_one)->where('is_del',0)
                    ->first()->toArray();

                $sql = 'select count(*) as count from (select  sum(time_number) as num,user_id from nlsg_history where created_at > ? and created_at < ? and is_del = 0 group by user_id HAVING sum(time_number )>=?) as count_table';
                $his_data = DB::select($sql,[$top_week_one,$week_one,$user_data['num']]);
                
                $u_data['nickname'] = $this->user['nickname']??'';
                $u_data['headimg']  = $this->user['headimg']??'';
                $u_data['his_num']  = SecToTime($user_data['num']);
                $u_data['rank']     = $his_data[0]->count;
                Cache::put($cache_key_name, $u_data, 86400*7);
            }

        }

        return success(['rank_data'=>$data,'user'=>$u_data]);
    }







}
