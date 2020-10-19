<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use App\Models\Column;
use App\Models\Subscribe;
use Illuminate\Http\Request;
use App\Models\Live;
use App\Models\LiveInfo;
use App\Models\User;
use App\Models\LiveWorks;
use Carbon\Carbon;

class LiveController extends Controller
{
    /**
     * @api {get} api/v4/live/index  直播首页
     * @apiVersion 4.0.0
     * @apiName  index
     * @apiGroup 直播
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/live/index
     *
     * @apiSuccess {array} live_lists 直播列表
     * @apiSuccess {array} live_lists.title 直播标题
     * @apiSuccess {array} live_lists.price 直播价格
     * @apiSuccess {array} live_lists.cover_img 直播封面
     * @apiSuccess {array} live_lists.type 直播类型 1单场 2多场
     * @apiSuccess {array} live_lists.user 直播用户信息
     * @apiSuccess {array} live_lists.live_time 直播时间
     * @apiSuccess {array} live_lists.live_status 直播状态
     * @apiSuccess {array} back_lists 回放列表
     *
     * @apiSuccessExample  Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "code": 200,
     *       "msg" : '成功',
     *       "data":[
     *                "data": {
     * "live_lists": [
     * {
     * "id": 136,
     * "user_id": 161904,
     * "title": "测试57",
     * "describe": "行字节处理知牛哥教学楼哦咯咯娄哦咯加油加油加油",
     * "price": "0.00",
     * "cover_img": "/nlsg/works/20200611095034263657.jpg",
     * "begin_at": "2020-10-01 15:02:00",
     * "type": 1,
     * "user": {
     * "id": 161904,
     * "nickname": "王琨"
     * },
     * "live_time": "2020.10.01 15:02",
     * "live_status": "正在直播"
     * }
     * ],
     * "back_lists": [
     * {
     * "id": 136,
     * "user_id": 161904,
     * "title": "测试57",
     * "describe": "行字节处理知牛哥教学楼哦咯咯娄哦咯加油加油加油",
     * "price": "0.00",
     * "cover_img": "/nlsg/works/20200611095034263657.jpg",
     * "begin_at": "2020-10-01 15:02:00",
     * "type": 1,
     * "user": {
     * "id": 161904,
     * "nickname": "王琨"
     * },
     * "live_time": "2020.10.01 15:02"
     * },
     * {
     * "id": 137,
     * "user_id": 255446,
     * "title": "测试",
     * "describe": "测试",
     * "price": "1.00",
     * "cover_img": "/nlsg/works/20200611172548507266.jpg",
     * "begin_at": "2020-10-01 15:02:00",
     * "type": 1,
     * "user": null,
     * "live_time": "2020.10.01 15:02"
     * }
     * ]
     * }
     *         ]
     *     }
     *
     */
    public function index()
    {
        $liveLists = Live::with('user:id,nickname')
            ->select('id', 'user_id', 'title', 'describe', 'price', 'cover_img', 'begin_at', 'type', 'end_at')
            ->where('status', 4)
            ->orderBy('begin_at', 'desc')
            ->limit(3)
            ->get()
            ->toArray();
        if ( ! empty($liveLists)) {
            foreach ($liveLists as &$v) {
                if (strtotime($v['end_at']) < time()) {
                    $v['live_status'] = '已结束';
                } else {
                    $v['live_status'] = '正在直播';
                }
                $v['live_time'] = date('Y.m.d H:i', strtotime($v['begin_at']));
            }
        }

        $backLists = Live::with('user:id,nickname')
            ->select('id', 'user_id', 'title', 'describe', 'price', 'cover_img', 'begin_at', 'type')
            ->where('end_at', '>', Carbon::now()->toDateTimeString())
            ->where('status', 4)
            ->orderBy('created_at', 'desc')
            ->limit(2)
            ->get()
            ->toArray();
        if ( ! empty($backLists)) {
            foreach ($backLists as &$v) {
                $v['live_time'] = date('Y.m.d H:i', strtotime($v['begin_at']));
            }
        }

        $liveWork = new LiveWorks();
        $recommend = $liveWork->getLiveWorks(0, 1, 3);
        $data = [
            'live_lists' => $liveLists,
            'back_lists' => $backLists,
            'recommend'  => $recommend
        ];
        return success($data);
    }

    /**
     * @api {get} api/v4/live/lists  直播更多列表
     * @apiVersion 4.0.0
     * @apiName  lists
     * @apiGroup 直播
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/live/lists
     * @apiParam  {number}  page  分页
     *
     * @apiSuccess {string}  title 同直播首页返回值
     *
     * @apiSuccessExample  Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "code": 200,
     *       "msg" : '成功',
     *       "data":[
     *                {
     * "id": 136,
     * "user_id": 161904,
     * "title": "测试57",
     * "describe": "行字节处理知牛哥教学楼哦咯咯娄哦咯加油加油加油",
     * "price": "0.00",
     * "cover_img": "/nlsg/works/20200611095034263657.jpg",
     * "begin_at": "2020-10-01 15:02:00",
     * "type": 1,
     * "user": {
     * "id": 161904,
     * "nickname": "王琨"
     * },
     * "live_time": "2020.10.01 15:02",
     * "live_status": "正在直播"
     * }
     *         ]
     *     }
     *
     */
    public function getLiveLists()
    {
        $lists = Live::with('user:id,nickname')
            ->select('id', 'user_id', 'title', 'describe', 'price', 'cover_img', 'begin_at', 'type', 'end_at')
            ->where('status', 4)
            ->orderBy('begin_at', 'desc')
            ->paginate(10)
            ->toArray();
        if ( ! empty($lists['data'])) {
            foreach ($lists['data'] as &$v) {
                if (strtotime($v['begin_at']) > time()) {
                    $v['live_status'] = '未开始';
                } else {
                    if (strtotime($v['end_at']) < time()) {
                        $v['live_status'] = '已结束';
                    } else {
                        $v['live_status'] = '正在直播';
                    }
                }

                $v['live_time'] = date('Y.m.d H:i', strtotime($v['begin_at']));
            }
        }

        return success($lists['data']);
    }

    /**
     * @api {get} api/v4/live/back_lists  回放更多列表
     * @apiVersion 4.0.0
     * @apiName  back_lists
     * @apiGroup 直播
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/live/back_lists
     * @apiParam  {number}  page  分页
     *
     * @apiSuccess {string}  title 同直播首页返回值
     *
     * @apiSuccessExample  Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "code": 200,
     *       "msg" : '成功',
     *       "data":[
     *                {
     * "id": 136,
     * "user_id": 161904,
     * "title": "测试57",
     * "describe": "行字节处理知牛哥教学楼哦咯咯娄哦咯加油加油加油",
     * "price": "0.00",
     * "cover_img": "/nlsg/works/20200611095034263657.jpg",
     * "begin_at": "2020-10-01 15:02:00",
     * "type": 1,
     * "user": {
     * "id": 161904,
     * "nickname": "王琨"
     * },
     * "live_time": "2020.10.01 15:02"
     * }
     *         ]
     *     }
     *
     */
    public function getLiveBackLists()
    {
        $lists = Live::with('user:id,nickname')
            ->select('id', 'user_id', 'title', 'describe', 'price', 'cover_img', 'begin_at', 'type')
            ->where('end_at', '>', Carbon::now()->toDateTimeString())
            ->where('status', 4)
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->toArray();
        if ( ! empty($lists['data'])) {
            foreach ($lists['data'] as &$v) {
                $v['live_time'] = date('Y.m.d H:i', strtotime($v['begin_at']));
            }
        }
        return success($lists['data']);
    }

    /**
     * @api {get} api/v4/live/channels  直播场次列表
     * @apiVersion 4.0.0
     * @apiName  channels
     * @apiGroup 直播
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/live/channels
     * @apiParam {number} id  直播期数id
     *
     * @apiSuccess {string} live_time    直播时间
     * @apiSuccess {string} live_status  直播状态
     * @apiSuccess {string} user         直播用户
     * @apiSuccess {string} live         直播相关
     * @apiSuccess {string} live.title   直播标题
     * @apiSuccess {string} live.price   直播价格
     * @apiSuccess {string} live.cover_img   直播封面
     *
     * @apiSuccessExample  Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "code": 200,
     *       "msg" : '成功',
     *       "data":[
     *               {
     * "id": 11,
     * "user_id": 161904,
     * "live_pid": 1,
     * "begin_at": "2020-10-17 10:00:00",
     * "end_at": null,
     * "user": {
     * "id": 161904,
     * "nickname": "王琨"
     * },
     * "live": {
     * "id": 1,
     * "title": "第85期《经营能量》直播",
     * "price": "0.00",
     * "cover_img": "/live/look_back/live-1-9.jpg"
     * },
     * "live_status": "未开始",
     * "live_time": "2020.10.17 10:00"
     * }
     *         ]
     *     }
     *
     */
    public function getLiveChannel(Request $request)
    {
        $id = $request->get('id');
        $lists = LiveInfo::with(['user:id,nickname', 'live:id,title,price,cover_img'])
            ->select('id', 'user_id', 'live_pid', 'begin_at', 'end_at')
            ->where('status', 1)
            ->where('live_pid', $id)
            ->orderBy('begin_at', 'desc')
            ->paginate(10)
            ->toArray();

        if ( ! empty($lists['data'])) {
            foreach ($lists['data'] as &$v) {
                if (strtotime($v['begin_at']) > time()) {
                    $v['live_status'] = '未开始';
                } else {
                    if (strtotime($v['end_at']) < time()) {
                        $v['live_status'] = '已结束';
                    } else {
                        $v['live_status'] = '正在直播';
                    }
                }
                $v['live_time'] = date('Y.m.d H:i', strtotime($v['begin_at']));
            }
        }
        return success($lists['data']);
    }

    /**
     * @api {get} api/v4/live/show  直播详情
     * @apiVersion 4.0.0
     * @apiName  show
     * @apiGroup 直播
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/live/show
     * @apiParam {number} live_id  直播id
     *
     * @apiSuccess {string} info  直播相关
     * @apiSuccess {string} info.is_sub 是否订阅专栏
     * @apiSuccess {string} info.level  当前用户等级
     * @apiSuccess {string} info.column_id   专栏id
     * @apiSuccess {string} info.user   用户
     * @apiSuccess {string} info.user.nickname  用户昵称
     * @apiSuccess {string} info.user.headimg   用户头像
     * @apiSuccess {string} info.user.intro     用户简介
     * @apiSuccess {string} recommend.list    推荐
     * @apiSuccess {string} recommend.list.title    推荐标题
     * @apiSuccess {string} recommend.list.subtitle 推荐副标题
     * @apiSuccess {number} recommend.list.original_price     原价格
     * @apiSuccess {number} recommend.list.price     推荐价格
     * @apiSuccess {string} recommend.list.cover_pic 推荐封面图
     *
     * @apiSuccessExample  Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "code": 200,
     *       "msg" : '成功',
     *       "data":[
     *               {
     *                   "id": 274,
     *                   "pic": "https://image.nlsgapp.com/nlsg/banner/20191118184425289911.jpg",
     *                   "title": "电商弹窗课程日历套装",
     *                   "url": "/mall/shop-detailsgoods_id=448&time=201911091925"
     *               },
     *               {
     *                   "id": 296,
     *                   "pic": "https://image.nlsgapp.com/nlsg/banner/20191227171346601666.jpg",
     *                   "title": "心里学",
     *                   "url": "/mall/shop-details?goods_id=479"
     *               }
     *         ]
     *     }
     *
     */
    public function show(Request $request)
    {
        $id = $request->get('live_id');
        $list = LiveInfo::with(['user:id,nickname,headimg,intro', 'live:id,title,price,cover_img,content'])
            ->select('id', 'push_live_url', 'live_url', 'live_url_flv', 'live_pid', 'user_id')
            ->where('id', $id)
            ->first();
        if ($list) {
            $column = Column::where('user_id', $list['user_id'])
                ->orderBy('created_at', 'desc')
                ->first();
            $userId = $this->user['id'] ?? 0;
            $user = new User();
            $isSub = Subscribe::isSubscribe($userId, $column->id, 1);
            $list['column_id'] = $column->id;
            $list['is_sub'] = $this->user['id'] ? $isSub : 0;
            $list['level'] = $user->getLevel($userId);

        }

        $liveWork = new LiveWorks();
        $recommend = $liveWork->getLiveWorks($id, 2, 2);
        $data = [
            'info'      => $list,
            'recomment' => $recommend
        ];
        return success($data);

    }

    /**
     * 重置直播类型
     */
    public function reLiveType()
    {
        $liveLists = Live::orderBy('begin_at', 'desc')
            ->get()
            ->toArray();
        if ($liveLists) {
            foreach ($liveLists as $v) {
                $count = LiveInfo::where('live_pid', $v['id'])->count();
                $type = $count > 1 ? 2 : 1;
                Live::where('id', $v['id'])->update([
                    'type' => $type
                ]);
            }
        }
        return;
    }

}
