<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use App\Models\Column;
use App\Models\OfflineProducts;
use App\Models\Subscribe;
use Illuminate\Http\Request;
use App\Models\Live;
use App\Models\LiveInfo;
use App\Models\User;
use App\Models\LiveWorks;
use App\Models\Order;
use Illuminate\Support\Facades\Hash;
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
     * @apiSuccess {array} live_lists.is_password 是否需要房间密码 1是 0否
     * @apiSuccess {array} live_lists.live_time 直播时间
     * @apiSuccess {array} live_lists.live_status 直播状态 1未开始 2已结束 3正在直播
     * @apiSuccess {array} back_lists 回放列表
     * @apiSuccess {array} offline    线下课程
     * @apiSuccess {array} offline.title   标题
     * @apiSuccess {array} offline.subtitle  副标题
     * @apiSuccess {array} offline.total_price   原价
     * @apiSuccess {array} offline.price   现价
     * @apiSuccess {array} offline.cover_img   封面
     * @apiSuccess {array} recommend  推荐
     * @apiSuccess {array} recommend.type  类型 1专栏 2讲座 3听书 4精品课  5线下课 6商品
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
     * "live_status": "3"
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
            ->select('id', 'user_id', 'title', 'describe', 'price', 'cover_img', 'begin_at', 'type', 'end_at','playback_price','is_free','password')
            ->where('status', 4)
            ->orderBy('begin_at', 'desc')
            ->limit(3)
            ->get()
            ->toArray();
        if (!empty($liveLists)) {
            foreach ($liveLists as &$v) {
                $channel = LiveInfo::where('live_pid', $v['id'])
                            ->where('status', 1)
                            ->orderBy('id','desc')
                            ->first();
                if (strtotime($channel['begin_at']) > time()) {
                    $v['live_status'] = '1';
                } else {
                    if (strtotime($channel['end_at']) < time()) {
                        $v['live_status'] = '2';
                    } else {
                        $v['live_status'] = '3';
                    }
                }
                if ($v['type']==1){
                    $v['id'] = $channel->id;
                }
                $v['is_password'] = $v['password'] ? 1 : 0;
                $v['live_time']   = date('Y.m.d H:i', strtotime($v['begin_at']));
            }
        }

        $backLists = Live::with('user:id,nickname')
            ->select('id', 'user_id', 'title', 'describe', 'price', 'cover_img', 'begin_at', 'type','playback_price','is_free','password')
            ->where('end_at', '>', Carbon::now()->toDateTimeString())
            ->where('status', 4)
            ->orderBy('created_at', 'desc')
            ->limit(2)
            ->get()
            ->toArray();
        if ( ! empty($backLists)) {
            foreach ($backLists as &$v) {
                $v['is_password'] = $v['password'] ? 1 : 0;
                $v['live_time']   = date('Y.m.d H:i', strtotime($v['begin_at']));
            }
        }

        $offline =  OfflineProducts::where('is_del', 0)
                    ->select('id','title','subtitle','total_price', 'price','cover_img')
                    ->orderBy('created_at','desc')
                    ->limit(3)
                    ->get()
                    ->toArray();

        $liveWork = new LiveWorks();
        $recommend = $liveWork->getLiveWorks(0, 1, 3);
        $data = [
            'banner'     => 'nlsg/works/20201021110516843010.png',
            'live_lists' => $liveLists,
            'back_lists' => $backLists,
            'offline'    => $offline,
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
            ->select('id', 'user_id', 'title', 'describe', 'price', 'cover_img', 'begin_at', 'type', 'end_at','playback_price','is_free','password')
            ->where('status', 4)
            ->orderBy('begin_at', 'desc')
            ->paginate(10)
            ->toArray();
        if ( ! empty($lists['data'])) {
            foreach ($lists['data'] as &$v) {
                $channel = LiveInfo::where('live_pid', $v['id'])
                            ->where('status', 1)
                            ->orderBy('id','desc')
                            ->first();
                if (strtotime($channel['begin_at']) > time()) {
                    $v['live_status'] = '1';
                } else {
                    if (strtotime($channel['end_at']) < time()) {
                        $v['live_status'] = '2';
                    } else {
                        $v['live_status'] = '3';
                    }
                }
                if ($v['type']==1){
                    $v['id'] = $channel->id;
                }
                $v['is_password'] = $v['password'] ? 1 : 0;
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
            ->select('id', 'user_id', 'title', 'describe', 'price', 'cover_img', 'begin_at', 'type','is_free','playback_price','is_free','password')
            ->where('end_at', '>', Carbon::now()->toDateTimeString())
            ->where('status', 4)
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->toArray();
        if ( ! empty($lists['data'])) {
            foreach ($lists['data'] as &$v) {
                $v['is_password'] = $v['password'] ? 1 : 0;
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
     * @apiSuccess {string} live_status  直播状态 1 未开始 2已结束 3正在进行
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
                    $v['live_status'] = '1';
                } else {
                    if (strtotime($v['end_at']) < time()) {
                        $v['live_status'] = '2';
                    } else {
                        $v['live_status'] = '3';
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

            $columnId = $column ?  $column->id : 0;

            $isSub = Subscribe::isSubscribe($userId, $columnId, 1);

            $list['column_id'] =  $columnId;
            $list['is_sub']    =  $isSub ?? 0;
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
     * @api {get} api/v4/offline/info  线下课程详情
     * @apiVersion 4.0.0
     * @apiName  info
     * @apiGroup 直播
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/offline/info
     * @apiParam {number} id  课程id
     *
     * @apiSuccess {string} title 标题
     * @apiSuccess {string} subtitle 副标题
     * @apiSuccess {string} describe 内容
     * @apiSuccess {number} total_price 总价
     * @apiSuccess {number} price  现价
     * @apiSuccess {number} image  详情图
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
    public  function   getOfflineInfo(Request $request)
    {
        $id =  $request->get('id');
        $list = OfflineProducts::where(['id'=>$id, 'is_del'=>0])
                ->first();
        return success($list);
    }

    /**
     * @api {get} api/v4/offline/order  线下课程报名记录
     * @apiVersion 4.0.0
     * @apiName  order
     * @apiGroup 直播
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/offline/order
     * @apiParam  {number} id 线下课程id
     * @apiParam  {number} page 分页
     *
     * @apiSuccess {number} price 支付定金
     * @apiSuccess {number} ordernum 订单号
     * @apiSuccess {string} product  线下课程
     * @apiSuccess {string} product.title 课程标题
     * @apiSuccess {string} product.cover_img 课程封面
     * @apiSuccess {string} product.total_price 课程总价
     *
     * @apiSuccessExample  Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "code": 200,
     *       "msg" : '成功',
     *       "data":[
     *               {
                     "relation_id": 1,
                     "price": "99.00",
                     "ordernum": "20091100211190416747499",
                     "product": {
                         "id": 1,
                         "title": "经营能量线下品牌课",
                         "cover_img": "/live/jynl/jynltjlb.jpg",
                         "total_price": "1000.00"
                     }
                 }
     *         ]
     *     }
     *
     */
    public  function getOfflineOrder(Request $request)
    {
        $id = $request->get('id');
        $lists = Order::where(['relation_id'=> $id, 'status'=>1, 'type'=>14])
                ->select('relation_id','price','ordernum')
                ->paginate(10)
                ->toArray();
        if ($lists['data']){
            foreach ($lists['data'] as &$v) {
                $product = OfflineProducts::where('id', $v['relation_id'])
                    ->select('id','title','cover_img','total_price')
                    ->first();
                $v['product'] = $product ?? [];
            }
        }
        return success($lists['data']);
    }

    /**
     * @api {post} api/v4/live/check_password 直播验证密码
     * @apiVersion 4.0.0
     * @apiName  check_password
     * @apiGroup 直播
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/live/check_password
     * @apiParam  {number} id 直播id
     * @apiParam  {number} password 密码
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
    public  function  checkLivePassword(Request $request)
    {
        $input  =  $request->all();
        $list   = Live::where('id', $input['id'])->first();
        if (!Hash::check($input['password'], '$2y$10$5ASiOopyFLJunWOCdfGrfuwDit7NsO.0s3JsWm6dmx8VKPsyTQ/uO')){
            return  error('密码无效');
        }
        return  success();
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
