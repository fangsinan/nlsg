<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use App\Models\ChannelWorksList;
use App\Models\Column;
use App\Models\ConfigModel;
use App\Models\Live;
use App\Models\LiveCheckPhone;
use App\Models\LiveComment;
use App\Models\LiveConsole;
use App\Models\LiveCountDown;
use App\Models\LiveForbiddenWords;
use App\Models\LiveInfo;
use App\Models\LiveLogin;
use App\Models\LivePlayback;
use App\Models\LiveSonFlagPoster;
use App\Models\LiveStatistics;
use App\Models\LiveUrl;
use App\Models\LiveWorks;
use App\Models\MallGoods;
use App\Models\MallOrder;
use App\Models\OfflineProducts;
use App\Models\Order;
use App\Models\OrderTwitterLog;
use App\Models\PayRecord;
use App\Models\Qrcodeimg;
use App\Models\Subscribe;
use App\Models\User;
use App\Models\LivePush;
use App\Models\Works;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redis;
use App\Servers\PhoneRegionServers;
use Predis\Client;

class LiveController extends Controller
{

    //http://127.0.0.1:8000/api/v4/live/getphone
    public function getphone(Request $request)
    {
        PhoneRegionServers::getPhoneRegion();
    }
    //直播在线人数入库
    //https://app.v4.api.nlsgapp.com/api/v4/live/onlineuser?name=111online_user_list_202109261106
    public function OnlineUser(Request $request)
    {

        $key_name = $request->input('name', '');
        if(!empty($key_name)){
            $result=self::CrontabOnlineUser($key_name);
            echo $result.'<br>';
        }else{
            echo 'key值为空<br>';
        }

    }
    //渠道王琨老师直播回放单独开通
    //https://app.v4.api.nlsgapp.com/api/v4/live/playbacksub
    public function PlayBackSub(Request $request)
    {

        $phone = $request->input('phone', '');
        if(empty($phone)){
            echo '开通手机号不能为空<br>';
            return ;
        }
        $list=explode(',',$phone);
        $map=[];
        $error='';
        $now_date = date('Y-m-d H:i:s');
        foreach ($list as $key=>$val){
            $phone_str=trim($val); //去掉空格
            $num=strlen($phone_str);
            if($num==11){
                $map[]=[
                    'phone'=>$phone_str,
                    'created_at'=>$now_date
                ];
            }else{
                $error.=$val.'|';
            }

        }
        if(!empty($map)) {
            DB::beginTransaction();
            try {
                $rst = LivePlayback::query()->insert($map);
                if ($rst === false) {
                    DB::rollBack();
                    echo '开通失败<br>';
                    return ;
                }
            } catch (\Exception $e) {
                DB::rollBack();
                echo '开通失败'.$e->getMessage().'<br>';
                return ;
            }
            DB::commit();
            echo '开通成功<br>';
        }
        if(!empty($error)) {
            echo "开通失败用户：" . $error;
        }

    }

    //渠道王琨老师直播回放单独处理
    //https://app.v4.api.nlsgapp.com/api/v4/live/playback
    //第一天：https://wechat.nlsgapp.com/appv4/playBackNew?live_info_id=123&day_num=1
    //第二天：https://wechat.nlsgapp.com/appv4/playBackNew?live_info_id=123&day_num=2
    public function PlayBack(Request $request)
    {

        $day_num = $request->input('day_num', 1);
        $user_id = $this->user['id'] ?? 0;
        //检测下单参数有效性
        if (empty($user_id)) {
            return $this->error(0, '用户未登录');
        }

        $OrderInfo=Order::query()->where('pay_time', '<', '2021-09-10 14:00:00')
            ->where(['user_id'=>$user_id,'type'=>10,'live_id'=>123,'status'=>1,'is_shill'=>0])
            ->first();
        $data=[];
        $data['user_id']=$user_id;
        if(empty($OrderInfo)){
            //查询单独开通用户
            $UserInfo=User::query()->where(['id'=>$user_id])->first();
            $PlayBack=LivePlayback::query()->where(['phone'=>$UserInfo->phone])->first();
            if(!empty($PlayBack)){
                $data['sub']=1;
            }else{
                $data['sub']=0;
            }
        }else {
            $data['sub']=1;
        }
        if(!empty($data['sub']) && $data['sub']==1){
            if ($day_num == 1) { //第一天
                $data['url']='http://1253639599.vod2.myqcloud.com/e6c8f55bvodtransgzp1253639599/4939b6c33701925924026354682/v.f100020.mp4';
            } else {
                $data['url']='http://1253639599.vod2.myqcloud.com/e6c8f55bvodtransgzp1253639599/152e32df3701925924022337528/v.f100020.mp4';
            }
        }

        return success($data);
    }

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
    public function index(Request $request)
    {
        $uid = $this->user['id'] ?? 0;

        $live = new Live();
        $liveLists = $live->getRecommendLive($uid);

        $os_type = intval($request->input('os_type', 0));
        if ($os_type === 3) {
            $info = new LiveInfo();
            $backLists = $info->getBackLists($uid);
        } else {
            $backLists = [];
        }

        $product = new OfflineProducts();
        $offline = $product->getIndexLists();

        $liveWork = new LiveWorks();
        $recommend = $liveWork->getLiveWorks(0, 1, 6);
        $data = [
            'banner' => 'nlsg/works/20201228165453965824.jpg',
            'live_lists' => $liveLists,
            'back_lists' => $backLists ?? [],
            'offline' => [],
            'recommend' => $recommend
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
        $uid = $this->user['id'] ?? 0;

        $testers = explode(',', ConfigModel::getData(35, 1));
        $user = User::where('id', $uid)->first();

        $query = Live::query();
        if (!$uid || ($user && !in_array($user->phone, $testers))) {
            $query->where('is_test', '=', 0);
        } else {
            $query->whereIn('is_test', [0, 1]);
        }
        $lists = $query->with('user:id,nickname')
            ->select('id', 'user_id', 'title', 'describe', 'price',
                'cover_img', 'begin_at', 'type', 'end_at','steam_begin_time',
                'playback_price', 'is_free', 'password', 'order_num')
            ->where('status', 4)
            ->where('is_finish', 0)
            ->where('is_del', 0)
            ->orderBy('sort', 'asc')
            ->orderBy('begin_at', 'desc')
            ->paginate(10)
            ->toArray();

        if (!empty($lists['data'])) {
            foreach ($lists['data'] as &$v) {
                $channel = LiveInfo::where('live_pid', $v['id'])
                    ->where('status', 1)
                    ->orderBy('id', 'desc')
                    ->first();
                if ($channel) {
//                    1未开始  2已结束  3直播中
                    if ($channel->is_begin == 0 && $channel->is_finish == 0) {
                        $v['live_status'] = 1;
                    } elseif ($channel->is_begin == 1 && $channel->is_finish == 0) {
                        $v['live_status'] = 3;
                    } elseif ($channel->is_begin == 0 && $channel->is_finish == 1) {
                        $v['live_status'] = 2;
                    }
                    $v['info_id'] = $channel->id;
                }
                $isSub = Subscribe::isSubscribe($uid, $v['id'], 3);
                $isAdmin = LiveConsole::isAdmininLive($uid, $v['id']);
                $v['is_sub'] = $isSub ?? 0;
                $v['is_admin'] = $isAdmin ? 1 : 0;

                $v['is_password'] = $v['password'] ? 1 : 0;

                //判断显示
                $begin_at_time = strtotime($v['begin_at']);
                $v['live_time'] = date('Y.m.d H:i', strtotime($v['begin_at']));
                if( $begin_at_time > strtotime(date("Y-1-1")) &&  $begin_at_time < strtotime(date("Y-1-1",strtotime("+1 year")))){
                    $v['live_time'] = date('m.d H:i', strtotime($v['begin_at']));
                }

//                if (empty($v['steam_begin_time'])){
//                    $v['begin_at'] = date('Y.m.d H:i', strtotime($v['begin_at']));
//                }else{
//                    $v['begin_at'] = date('Y.m.d H:i', strtotime($v['steam_begin_time']));
//                }

//                if($v['live_status'] == 3){
//                    $v['live_time'] = "正在直播";
//                }


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
    public function getLiveBackLists(Request $request)
    {
        $os_type = intval($request->input('os_type', 0));
        if ($os_type === 3) {
            $uid = $this->user['id'] ?? 0;
            $lists = LiveInfo::with('user:id,nickname',
                'live:id,title,describe,price,cover_img,begin_at,type,playback_price,is_free,password')
                ->select('id', 'live_pid', 'user_id')
                ->where('status', 1)
                ->where('playback_url', '!=', '')
                ->orderBy('begin_at', 'desc')
                ->paginate(10)
                ->toArray();

            $backLists = [];
            if (!empty($lists['data'])) {
                foreach ($lists['data'] as &$v) {
                    $isSub = Subscribe::isSubscribe($uid, $v['live_pid'], 3);
                    $isAdmin = LiveConsole::isAdmininLive($uid, $v['live_pid']);
                    $backLists[] = [
                        'id' => $v['live']['id'],
                        'title' => $v['live']['title'],
                        'is_password' => $v['live']['password'] ? 1 : 0,
                        'describe' => $v['live']['describe'],
                        'price' => $v['live']['price'],
                        'cover_img' => $v['live']['cover_img'],
                        'playback_price' => $v['live']['playback_price'],
                        'live_time' => date('Y.m.d H:i', strtotime($v['live']['begin_at'])),
                        'is_free' => $v['live']['is_free'],
                        'info_id' => $v['id'],
                        'is_sub' => $isSub ?? 0,
                        'is_admin' => $isAdmin ? 1 : 0,
                        'user' => $v['user'],
                    ];
                }
            }
            return success($backLists);
        } else {
            $backLists = [];
            return success($backLists);
        }
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
            ->select('id', 'user_id', 'live_pid', 'begin_at', 'end_at', 'is_begin', 'is_finish')
            ->where('status', 1)
            ->where('live_pid', $id)
            ->orderBy('begin_at', 'desc')
            ->paginate(10)
            ->toArray();

        if (!empty($lists['data'])) {
            foreach ($lists['data'] as &$v) {
                if ($v->is_begin == 0 && $v->is_finish == 0) {
                    $v['live_status'] = 1;
                } elseif ($v->is_begin == 1 && $v->is_finish == 0) {
                    $v['live_status'] = 3;
                } elseif ($v->is_begin == 0 && $v->is_finish == 1) {
                    $v['live_status'] = 2;
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
     * @apiSuccess {string} info.is_sub_column 是否订阅专栏
     * @apiSuccess {string} info.is_sub 是否付费订阅
     * @apiSuccess {string} info.is_appmt 是否免费订阅
     * @apiSuccess {string} info.is_forbid 是否全体禁言(1禁了,0没禁)
     * @apiSuccess {string} info.is_silence 当前用户是否禁言中(0没有 其他剩余秒数)
     * @apiSuccess {string} info.level  当前用户等级
     * @apiSuccess {string} info.is_begin  1是直播中
     * @apiSuccess {string} info.is_admin  1是管理员(包括创建人和助手) 0不是
     * @apiSuccess {string} info.column_id   专栏id
     * @apiSuccess {string} info.begin_at   直播开始时间
     * @apiSuccess {string} info.end_at     直播结束时间
     * @apiSuccess {string} info.length     直播时长
     * @apiSuccess {string} info.user   用户
     * @apiSuccess {string} info.user.nickname  用户昵称
     * @apiSuccess {string} info.user.headimg   用户头像
     * @apiSuccess {string} info.user.intro     用户简介
     * @apiSuccess {string} info.is_password  是否需要密码 0 不需要 1需要
     * @apiSuccess {string} info.live   直播
     * @apiSuccess {string} info.live.title   直播标题
     * @apiSuccess {string} info.live.cover_img   直播封面
     * @apiSuccess {string} info.live.is_free    是否免费 0免费 1付费
     * @apiSuccess {string} info.live.price        价格
     * @apiSuccess {string} info.live.twitter_money   分销金额
     * @apiSuccess {string} info.live.playback_price   回放金额
     * @apiSuccess {string} info.live.is_show   是否公开 1显示  0不显示
     * @apiSuccess {string} info.live.helper   助理电话
     * @apiSuccess {string} info.live.msg      直播预约公告
     * @apiSuccess {string} info.live.describe  直播简介
     * @apiSuccess {string} info.live.content  直播内容
     * @apiSuccess {string} info.live.can_push  允许推送 1允许 2不允许
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
    //旧版
    public function show_1(Request $request)
    {
        $id = $request->get('live_id');
        $live_son_flag = intval($request->get('live_son_flag',0));
        $os_type = intval($request->input('os_type', 0)); //1 安卓 2ios 3微信
        if(!empty($os_type) && $os_type==3){
            $selectArr=['id','live_url', 'live_url_flv', 'live_pid', 'user_id', 'begin_at', 'is_begin', 'length', 'playback_url', 'file_id', 'is_finish', 'pre_video'];
        }else{
            $selectArr=['id', 'push_live_url', 'live_url', 'live_url_flv', 'live_pid', 'user_id', 'begin_at', 'is_begin', 'length', 'playback_url', 'file_id', 'is_finish', 'pre_video'];
        }
        $list = LiveInfo::with([
            'user:id,nickname,headimg,intro,honor',
            'live:id,title,price,cover_img,content,twitter_money,is_free,playback_price,is_show,helper,msg,describe,can_push,password,is_finish,virtual_online_num',
            'live.livePoster'=>function($q){
                $q->where('status','=',1);
            }
        ])
            ->select($selectArr)
            ->where('id', $id)
            ->first();

        if ($list) {
            $column = Column::where('user_id', $list['user_id'])
                ->orderBy('created_at', 'desc')
                ->first();
            $userId = $this->user['id'] ?? 0;
            $user = new User();

            $columnId = $column ? $column->id : 0;

            $isSub = Subscribe::isSubscribe($userId, $columnId, 1);
            $subLive = Subscribe::isSubscribe($userId, $list->live_pid, 3);

            $is_forbid = LiveForbiddenWords::where('live_info_id', '=', $id)
                ->where('user_id', '=', 0)->where('is_forbid', '=', 1)
                ->first();
            $list['is_forbid'] = $is_forbid ? 1 : 0;

            $is_silence = LiveForbiddenWords::where('live_info_id', '=', $id)
                ->where('user_id', '=', $this->user['id'])
                ->where('is_forbid', '=', 1)
                ->select(['id', 'forbid_at', 'length'])
                ->first();
            if ($is_silence) {
                $list['is_silence'] = intval(strtotime($is_silence->forbid_at)) + intval($is_silence->length) - time();
                $list['is_silence'] = $list['is_silence'] < 0 ? 0 : $list['is_silence'];
            } else {
                $list['is_silence'] = 0;
            }

            $is_appmt = LiveCountDown::where(['user_id' => $this->user['id'], 'live_id' => $id])->first();
            $list['is_appmt'] = $is_appmt ? 1 : 0;
            $is_admin = LiveConsole::isAdmininLive($this->user['id'] ?? 0, $list['live_pid']);
            $list['is_admin'] = $is_admin ? 1 : 0;

            $list['column_id'] = $columnId;
            $list['is_sub'] = $subLive ?? 0;
            $list['is_sub_column'] = $isSub ?? 0;
            $list['level'] = $user->getLevel($userId);
            $list['welcome'] = '欢迎来到直播间，能量时光倡导绿色健康直播，不提倡未成年人进行打赏。直播内容和评论内容严禁包含政治、低俗、色情等内容。';
            $list['nick_name'] = $this->user['nickname'] ?? '';

            if ($list->user_id == $userId) {
                $list['is_password'] = 0;
            } elseif ($is_admin) {
                $list['is_password'] = 0;
            } else {
                $list['is_password'] = $list->live->password ? 1 : 0;
            }
            $list['live_son_flag_count'] = 0;
            $list['live_son_flag_status'] = 0;
            if(!empty($live_son_flag)){
                $list['live_son_flag_count'] = 10000;
//                    Subscribe::where([
//                    "relation_id" => $list->live_pid,
//                    "type" => 3,
//                    "status" => 1,
//                    "twitter_id" => $live_son_flag,
//                ])->count();

                //渠道是否开启直播
                $list['live_son_flag_status'] = LiveSonFlagPoster::where([
                    'live_id'   =>$list->live_pid,
                    'son_id'    =>$live_son_flag,
                    'is_del'    =>0,
                ])->value('status');
                $list['live_son_flag_status'] = $list['live_son_flag_status']??0;
            }
//
        }
        //初始化人气值
        $redisConfig = config('database.redis.default');
        $redis = new Client($redisConfig);
        $redis->select(0);
        if(empty($live_son_flag)){
            //数据库实时数据
            $live_son_flag_num = LiveLogin::where('live_id', '=', $id)->count();
            $key="live_number_$id"; //此key值只要直播间live_key_存在(有socket连接)就会15s刷新一次
            $key_num=$redis->get($key);
            if(!empty($list['live']['virtual_online_num']) && $list['live']['virtual_online_num']>0){
                $live_son_flag_num=$live_son_flag_num+$list['live']['virtual_online_num']; //虚拟值
            }
            if(empty($key_num) || $key_num<$live_son_flag_num){
                $redis->setex($key,86400*5,$live_son_flag_num);
            }
        }else{
            $key='live_son_flag_'.$id.'_'.$live_son_flag;
            $live_son_flag_num=$redis->get($key);
            if(empty($live_son_flag_num)){
                $live_son_flag_num=1;
            }
        }

        $data = [
            'info' => $list,
            'live_son_flag_num' => $live_son_flag_num,
        ];
        return success($data);

    }

    public function show(Request $request)
    {
        $id = intval($request->get('live_id',0));
        $live_son_flag = intval($request->get('live_son_flag',0));
        $os_type = intval($request->input('os_type', 0)); //1 安卓 2ios 3微信
        if(!empty($os_type) && $os_type==3){
            $selectArr=['id','live_url', 'live_url_flv', 'live_pid', 'user_id', 'begin_at', 'is_begin', 'length', 'playback_url', 'file_id', 'is_finish', 'pre_video'];
        }else{
            $selectArr=['id', 'push_live_url', 'live_url', 'live_url_flv', 'live_pid', 'user_id', 'begin_at', 'is_begin', 'length', 'playback_url', 'file_id', 'is_finish', 'pre_video'];
        }
        $list = LiveInfo::with([
            'user:id,nickname,headimg,intro,honor',
            'live:id,title,price,cover_img,content,twitter_money,is_free,playback_price,is_show,helper,msg,describe,can_push,password,is_finish,virtual_online_num',
            'live.livePoster'=>function($q){
                $q->where('status','=',1);
            }
        ])
            ->select($selectArr)
            ->where('id', $id)
            ->first();

        //初始化人气值
        $redisConfig = config('database.redis.default');
        $redis = new Client($redisConfig);
        $redis->select(0);

        $live_son_flag_num=0;
        if ($list) {
//            $column = Column::where('user_id', $list['user_id'])
//                ->orderBy('created_at', 'desc')
//                ->first();
            $userId = $this->user['id'] ?? 0;
            $user = new User();

//            $columnId = $column ? $column->id : 0;
            //专栏订阅
//            $isSub = Subscribe::isSubscribe($userId, $columnId, 1);
            $subLive = Subscribe::isSubscribe($userId, $list->live_pid, 3);
            //全员禁言
            /*$is_forbid = LiveForbiddenWords::where('live_info_id', '=', $id)
                ->where('user_id', '=', 0)->where('is_forbid', '=', 1)
                ->first();
            $list['is_forbid'] = $is_forbid ? 1 : 0;*/
            $list['is_forbid'] = 0;

            $is_silence = LiveForbiddenWords::where('live_info_id', '=', $id)
                ->where('user_id', '=', $userId)
                ->where('is_forbid', '=', 1)
                ->select(['id', 'forbid_at', 'length'])
                ->first();
            if ($is_silence) {
                $list['is_silence'] = intval(strtotime($is_silence->forbid_at)) + intval($is_silence->length) - time();
                $list['is_silence'] = $list['is_silence'] < 0 ? 0 : $list['is_silence'];
            } else {
                $list['is_silence'] = 0;
            }

            $is_appmt = LiveCountDown::where(['user_id' => $userId, 'live_id' => $id])->first();
            $list['is_appmt'] = $is_appmt ? 1 : 0;
            $is_admin = LiveConsole::isAdmininLive($userId ?? 0, $list['live_pid']);
            $list['is_admin'] = $is_admin ? 1 : 0;

//            $list['column_id'] = $columnId;
            $list['column_id'] = 0;
            $list['is_sub'] = $subLive ?? 0;
//            $list['is_sub_column'] = $isSub ?? 0;
//            $list['level'] = $user->getLevel($userId);
            $list['is_sub_column'] = 0;
            $list['level'] = 0;
            $list['welcome'] = '欢迎来到直播间，能量时光倡导绿色健康直播，不提倡未成年人进行打赏。直播内容和评论内容严禁包含政治、低俗、色情等内容。';
            $list['nick_name'] = $this->user['nickname'] ?? '';

            if ($list->user_id == $userId) {
                $list['is_password'] = 0;
            } elseif ($is_admin) {
                $list['is_password'] = 0;
            } else {
                $list['is_password'] = $list->live->password ? 1 : 0;
            }
            $list['live_son_flag_count'] = 0;
            $list['live_son_flag_status'] = 0;
			$list['live_son_flag_brush_status'] = 1;
            $list['show_wechat_button_status'] = 1;
            if(!empty($live_son_flag)){

                $key = "live_son_flag:".$list->live_pid . '_' . $live_son_flag;
                $key_num=$redis->get($key);
                if(!empty($key_num)){
                    $list['live_son_flag_count'] =  $key_num;
                }else{
                    $list['live_son_flag_count']= LiveLogin::query()->where(['live_id'=>$list->live_pid,'live_son_flag'=>$live_son_flag])->count();
                    /*$list['live_son_flag_count'] = Subscribe::where([
                        "relation_id" => $list->live_pid,
                        "type" => 3,
                        "status" => 1,
                        "twitter_id" => $live_son_flag,
                    ])->count();*/

                    $redis->setex($key,3600*5,$list['live_son_flag_count']);
                }

                $live_son_flag_num=$list['live_son_flag_count'];

                //渠道是否开启直播
				$SonFlagInfo= LiveSonFlagPoster::query()->where([
                    'live_id'   =>$list->live_pid,
                    'son_id'    =>$live_son_flag,
                    'is_del'    =>0,
                ])->first();
                if(!empty($SonFlagInfo)){
                    $list['show_wechat_button_status']=$SonFlagInfo->show_wechat_button;
                    $list['live_son_flag_status']=$SonFlagInfo->status;
                    $list['live_son_flag_brush_status'] = $SonFlagInfo->live_son_flag_brush_status;
                }
            }else{
                $key="live_number:$id"; //此key值只要直播间live_key_存在(有socket连接)就会15s刷新一次
                $key_num=$redis->get($key);
                if( empty($key_num) ){
                    //数据库实时数据
                    $live_son_flag_num = LiveLogin::where('live_id', '=', $id)->count();
                    if(!empty($list['live']['virtual_online_num']) && $list['live']['virtual_online_num']>0){
                        $live_son_flag_num=$live_son_flag_num+$list['live']['virtual_online_num']; //虚拟值
                    }
                    $redis->setex($key,3600*5,$live_son_flag_num);
                }else{
                    $live_son_flag_num = $key_num;
                }

            }

        }

        //微信端渠道直播结束,不会链接socket,人气值返回0
        if(!empty($live_son_flag) && isset($list->is_finish) && $list->is_finish==1){
            $list['live_son_flag_count']=0;
            $live_son_flag_num=0;
        }

        //如果有推送则在show接口返回
        $push_live = NULL;
        $is_push_goods = 0;
        if(time() > strtotime(date("Y-m-d 09:00:0"))){
            $push_gid = LivePush::where([
                'live_info_id'=>$id,
                'push_type'=>4,
                'push_gid'=>10,
            ])->first();


            $is_push_goods = empty($push_gid) ? 0 : 1;
        }

        /*$time=time();
        if(in_array($id,[19])){
            $start_time=strtotime(date("Y-m-d 09:00:0"));
        }else {
            $start_time = strtotime(date("Y-m-d 20:00:0"));
        }
        if( !empty($live_son_flag) &&  $time >= $start_time ){
            $push_gid = LivePush::where([
                'live_info_id'=>$id,
                'push_type'=>9,
            ])->orderBy('created_at', 'desc')->value('push_gid');

            if(!empty($push_gid)){
                $push_live = Live::select("id","title", "price","type","cover_img","is_free")->where(['id'=>$push_gid])->first();

            }
        }*/
        $data = [
            'info' => $list,
            'live_son_flag_num' => $live_son_flag_num,
            'push_live' => $push_live,
            'is_push_goods' => $is_push_goods,
        ];
        return success($data);

    }

    //公众号调用
    public function showWechat(Request $request)
    {
        $id = intval($request->get('live_id',0));
        $live_son_flag = intval($request->get('live_son_flag',0));
        $os_type = intval($request->input('os_type', 0)); //1 安卓 2ios 3微信
        if(!empty($os_type) && $os_type==3){
            $selectArr=['id','live_url', 'live_url_flv', 'live_pid', 'user_id', 'begin_at', 'is_begin', 'length', 'playback_url', 'file_id', 'is_finish', 'pre_video'];
        }else{
            $selectArr=['id', 'push_live_url', 'live_url', 'live_url_flv', 'live_pid', 'user_id', 'begin_at', 'is_begin', 'length', 'playback_url', 'file_id', 'is_finish', 'pre_video'];
        }
        $list = LiveInfo::with([
            'user:id,nickname,headimg,intro,honor',
            'live:id,title,price,cover_img,content,twitter_money,is_free,playback_price,is_show,helper,msg,describe,can_push,password,is_finish,virtual_online_num',
            'live.livePoster'=>function($q){
                $q->where('status','=',1);
            }
        ])
            ->select($selectArr)
            ->where('id', $id)
            ->first();

        //初始化人气值
        $redisConfig = config('database.redis.default');
        $redis = new Client($redisConfig);
        $redis->select(0);

        $live_son_flag_num=0;
        if ($list) {
//            $column = Column::where('user_id', $list['user_id'])
//                ->orderBy('created_at', 'desc')
//                ->first();
            $userId=0;
            if(isset($this->user['id']) && !empty($this->user['id'])){
                $userId = $this->user['id'] ?? 0;
            }
            $user = new User();

//            $columnId = $column ? $column->id : 0;
            //专栏订阅
//            $isSub = Subscribe::isSubscribe($userId, $columnId, 1);
            $subLive = Subscribe::isSubscribe($userId, $list->live_pid, 3);
            //全员禁言
            /*$is_forbid = LiveForbiddenWords::where('live_info_id', '=', $id)
                ->where('user_id', '=', 0)->where('is_forbid', '=', 1)
                ->first();
            $list['is_forbid'] = $is_forbid ? 1 : 0;*/
            $list['is_forbid'] = 0;

            $is_silence = LiveForbiddenWords::where('live_info_id', '=', $id)
                ->where('user_id', '=', $userId)
                ->where('is_forbid', '=', 1)
                ->select(['id', 'forbid_at', 'length'])
                ->first();
            if ($is_silence) {
                $list['is_silence'] = intval(strtotime($is_silence->forbid_at)) + intval($is_silence->length) - time();
                $list['is_silence'] = $list['is_silence'] < 0 ? 0 : $list['is_silence'];
            } else {
                $list['is_silence'] = 0;
            }

            $is_appmt = LiveCountDown::where(['user_id' => $userId, 'live_id' => $id])->first();
            $list['is_appmt'] = $is_appmt ? 1 : 0;
            $is_admin = LiveConsole::isAdmininLive($userId ?? 0, $list['live_pid']);
            $list['is_admin'] = $is_admin ? 1 : 0;

//            $list['column_id'] = $columnId;
            $list['column_id'] = 0;
            $list['is_sub'] = $subLive ?? 0;
//            $list['is_sub_column'] = $isSub ?? 0;
//            $list['level'] = $user->getLevel($userId);
            $list['is_sub_column'] = 0;
            $list['level'] = 0;
            $list['welcome'] = '欢迎来到直播间，能量时光倡导绿色健康直播，不提倡未成年人进行打赏。直播内容和评论内容严禁包含政治、低俗、色情等内容。';
            $list['nick_name'] = $this->user['nickname'] ?? '';

            if ($list->user_id == $userId) {
                $list['is_password'] = 0;
            } elseif ($is_admin) {
                $list['is_password'] = 0;
            } else {
                $list['is_password'] = $list->live->password ? 1 : 0;
            }
            $list['live_son_flag_count'] = 0;
            $list['live_son_flag_status'] = 0;
            $list['live_son_flag_brush_status'] = 1;
            $list['show_wechat_button_status'] = 1;
            if(!empty($live_son_flag)){

                $key = "live_son_flag:".$list->live_pid . '_' . $live_son_flag;
                $key_num=$redis->get($key);
                if(!empty($key_num)){
                    $list['live_son_flag_count'] =  $key_num;
                }else{
                    $list['live_son_flag_count']= LiveLogin::query()->where(['live_id'=>$list->live_pid,'live_son_flag'=>$live_son_flag])->count();
                    /*$list['live_son_flag_count'] = Subscribe::where([
                        "relation_id" => $list->live_pid,
                        "type" => 3,
                        "status" => 1,
                        "twitter_id" => $live_son_flag,
                    ])->count();*/

                    $redis->setex($key,3600*5,$list['live_son_flag_count']);
                }

                $live_son_flag_num=$list['live_son_flag_count'];

                //渠道是否开启直播
                $SonFlagInfo= LiveSonFlagPoster::query()->where([
                    'live_id'   =>$list->live_pid,
                    'son_id'    =>$live_son_flag,
                    'is_del'    =>0,
                ])->first();
                if(!empty($SonFlagInfo)){
                    $list['show_wechat_button_status']=$SonFlagInfo->show_wechat_button;
                    $list['live_son_flag_status']=$SonFlagInfo->status;
                    $list['live_son_flag_brush_status'] = $SonFlagInfo->live_son_flag_brush_status;
                }
            }else{
                $key="live_number:$id"; //此key值只要直播间live_key_存在(有socket连接)就会15s刷新一次
                $key_num=$redis->get($key);
                if( empty($key_num) ){
                    //数据库实时数据
                    $live_son_flag_num = LiveLogin::where('live_id', '=', $id)->count();
                    if(!empty($list['live']['virtual_online_num']) && $list['live']['virtual_online_num']>0){
                        $live_son_flag_num=$live_son_flag_num+$list['live']['virtual_online_num']; //虚拟值
                    }
                    $redis->setex($key,3600*5,$live_son_flag_num);
                }else{
                    $live_son_flag_num = $key_num;
                }

            }

        }

        //微信端渠道直播结束,不会链接socket,人气值返回0
        if(!empty($live_son_flag) && isset($list->is_finish) && $list->is_finish==1){
            $list['live_son_flag_count']=0;
            $live_son_flag_num=0;
        }

        //如果有推送则在show接口返回
        $push_live = NULL;
        /*$time=time();
        if(in_array($id,[19])){
            $start_time=strtotime(date("Y-m-d 09:00:0"));
        }else {
            $start_time = strtotime(date("Y-m-d 20:00:0"));
        }
        if( !empty($live_son_flag) &&  $time >= $start_time ){
            $push_gid = LivePush::where([
                'live_info_id'=>$id,
                'push_type'=>9,
            ])->orderBy('created_at', 'desc')->value('push_gid');

            if(!empty($push_gid)){
                $push_live = Live::select("id","title", "price","type","cover_img","is_free")->where(['id'=>$push_gid])->first();

            }
        }*/
        $data = [
            'info' => $list,
            'live_son_flag_num' => $live_son_flag_num,
            'push_live' => $push_live,
        ];
        return success($data);

    }

//    public function recommend(Request $request)
//    {
//        $id = $request->get('live_id');
//        $liveWork = new LiveWorks();
//        $recommend = $liveWork->getLiveWorks($id, 2, 10);
//        return success($recommend);
//    }

    public function recommend(Request $request)
    {
        $id = intval($request->get('live_id',0));

        //直播结束后第二天上午9点后购物车返回空
        $key_name='live:'.$id;
        $RedisInfo = Redis::get($key_name);
        if(empty($RedisInfo)){
            $LiveInfo=LiveInfo::query()->where('live_pid',$id)->select(['is_finish','begin_at'])->first()->toArray();
            if(!empty($LiveInfo)){
                Redis::setex($key_name, 600, json_encode($LiveInfo));
            }
        }else{
            $LiveInfo=json_decode($RedisInfo,true);
        }
        if(!empty($LiveInfo) && $LiveInfo['is_finish']==1){ //直播已结束
            $second_day=strtotime($LiveInfo['begin_at'])+86400; //直播结束第二天
            $end_time=strtotime(date('Y-m-d 09:00:00',$second_day));
            $time=time();
            if($time>$end_time){
                return success([]);
            }
        }

        $livePush = new LivePush();
        $recommend = $livePush->getPushWorks($id);
        return success($recommend);
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
    public function getOfflineInfo(Request $request)
    {
        $id = $request->get('id');
        $list = OfflineProducts::where(['id' => $id, 'is_del' => 0])
            ->first();
        if (!$list) {
            return error(1000, '没有数据');
        }
//        $array = [1];
//        if (in_array($id, $array)) {
//            $list->describe_type = 2;
//            $list->url = 'appv4/offLineAppDesc';
//        } else {
//            $list->describe_type = 1;
//            $list->url = '';
//        }
        return success($list);
    }

    /**
     * @api {get} api/v4/offline/order  线下课程报名记录
     * @apiVersion 4.0.0
     * @apiName  order
     * @apiGroup 直播
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/offline/order
     * @apiParam  {number} token 当前用户
     * @apiParam  {number} page 分页
     *
     * @apiSuccess {number} price 支付定金
     * @apiSuccess {number} ordernum 订单号
     * @apiSuccess {number} status 状态 0 待支付  1已支付  2取消
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
     * "relation_id": 1,
     * "price": "99.00",
     * "ordernum": "20091100211190416747499",
     * "product": {
     * "id": 1,
     * "title": "经营能量线下品牌课",
     * "cover_img": "/live/jynl/jynltjlb.jpg",
     * "total_price": "1000.00"
     * }
     * }
     *         ]
     *     }
     *
     */
    public function getOfflineOrder(Request $request)
    {
        $id = $request->get('id');
        $lists = Order::where(['relation_id' => $id, 'type' => 14])
            ->select('id', 'relation_id', 'price', 'ordernum', 'status')
            ->where('user_id', $this->user['id'])
            ->paginate(10)
            ->toArray();
        if ($lists['data']) {
            foreach ($lists['data'] as &$v) {
                $product = OfflineProducts::where('id', $v['relation_id'])
                    ->select('id', 'title', 'cover_img', 'total_price')
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
    public function checkLivePassword(Request $request)
    {
        $input = $request->all();
        $list = Live::where('id', $input['id'])->first();
        if (!Hash::check($input['password'], $list->password)) {
            return error(1000, '密码无效');
        }
        return success();
    }

    /**
     * @api {get} api/v4/live/ranking  排行榜
     * @apiVersion 4.0.0
     * @apiName  ranking
     * @apiGroup 直播
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/live/ranking
     * @apiParam  {number} live_id 直播id
     * @apiParam  {number} liveinfo_id 直播info_id
     * @apiParam  {number} page 分页
     *
     * @apiSuccess {string}  user_ranking 自己排名
     * @apiSuccess {string}  user_invite_num 自己邀请数量
     * @apiSuccess {string}  ranking 排行
     * @apiSuccess {string}  ranking.username 用户昵称
     * @apiSuccess {string}  ranking.headimg  用户头像
     * @apiSuccess {string}  ranking.invite_num  邀请数量
     * @apiSuccess {string}  ranking.is_self  是否是当前用户
     *
     * @apiSuccessExample  Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "code": 200,
     *       "msg" : '成功',
     *       "data": {
     * "user_ranking": 2,
     * "user_invite_num": 10,
     * "ranking": [
     * {
     * "username": "亚梦想",
     * "headimg": "/wechat/authorpt/lzh.png",
     * "invite_num": 30
     * },
     * {
     * "username": "小雨衣",
     * "headimg": "/wechat/authorpt/lzh.png",
     * "invite_num": 20
     * }
     * ]
     * }
     *     }
     *
     */
    public function ranking(Request $request)
    {
        $live_id = $request->input('live_id', 0);
        $liveinfo_id = $request->input('liveinfo_id', 0);
        $page = $request->input('page', 0);

        if ($page > 1) {
            return success();
        }
        $user_id = $this->user['id'] ?? 0;


        //获取自己的邀请人数
        $user_ranking = LiveCountDown::select(DB::raw('count(*) c'))
            ->where(['live_id' => $liveinfo_id])->where(['new_vip_uid' => $user_id])->first()->toArray();
//        dd($user_ranking);

        //查看大于自己邀请人数的数量
        $num = LiveCountDown::select(DB::raw('count(*) num'), 'new_vip_uid')
            ->where(['live_id' => $liveinfo_id])
            ->where('new_vip_uid', '>', 0)
            ->groupBy('new_vip_uid')
            ->having('num', '>=', $user_ranking['c'])
            //->first()->toArray();
            ->paginate($this->page_per_page)->toArray();

        $ranking_data = LiveCountDown::select(DB::raw('count(*) c'), 'new_vip_uid')
            ->where(['live_id' => $liveinfo_id])
            ->where('new_vip_uid', '>', 0)
            ->groupBy('new_vip_uid')
            ->orderBy('c', 'desc')->orderBy('new_vip_uid', 'desc')->take($this->page_per_page)->get()->toArray();

        $new_data = [];
        foreach ($ranking_data as $key => $val) {
            $new_data[$key]['user_ranking'] = ($key + 1);
            $new_data[$key]['invite_num'] = $val['c'];
            $userdata = User::find($val['new_vip_uid']);
            $new_data[$key]['user_id'] = $userdata['id'];
            $new_data[$key]['username'] = $userdata['phone'];
            $new_data[$key]['nickname'] = $userdata['nickname'];
            $new_data[$key]['headimg'] = $userdata['headimg'];

            $new_data[$key]['is_self'] = 0;
            if ($val['new_vip_uid'] == $user_id) {
                $new_data[$key]['is_self'] = 1;
                $self_ranking = $new_data[$key]['user_ranking'];
            }
        }
        $data = [
            'user_ranking' => $self_ranking ?? $num['total'],
            'user_invite_num' => $user_ranking['c'],
            'ranking' => $new_data
        ];
        if ($data['user_invite_num'] == 0) {
            $data['user_ranking'] = 0;
        }


        return success($data);
    }

    /**
     * @api {POST} api/v4/live/free_order 免费预约
     * @apiVersion 4.0.0
     * @apiName  free_order
     * @apiGroup 直播
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/live/free_order
     *
     * @apiParam {number} live_id 直播间id
     * @apiParam {number}  token 用户认证
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
    public function freeLiveOrder(Request $request)
    {
        $input = $request->all();

        if( empty($input) ){
            return error(0, '参数异常');
        }

        if(!empty($input['is_check_phone'])){
            //虚拟用户
            if($input['os_type'] ==3 && (empty($this->user['phone']) || substr($this->user['phone'],0,1) == 2) ){
                return error(4000, '请修改手机号');
            }
        }



        if( in_array($this->user['id'], [878644, 882057, 882861]) ){
            return error(0, '用户异常');
        }

        if(empty($input['info_id'])){
            return error(0, '参数有误');
        }
        $info_id=intval($input['info_id']);

        $live = LiveInfo::where('id', $info_id)->first();
        if (!$live) {
            return error(0, '直播不存在');
        }

        $live_data = Live::where('id', $live['live_pid'])->first();
        if ($live_data['flag'] > 0) {   //flag > 0 为限定直播  限定值与flag一致
            $flag = LiveCheckPhone::where([
                'phone' => $this->user['phone'],
                'flag' => $live_data['flag'],
            ])->first();

            if (empty($flag)) {
                return error(0, '您不可参与该直播，请联系慧宇服务顾问');
            }
        }

        $list = Subscribe::where(['relation_id' => $info_id, 'type'=>3,'user_id' => $this->user['id'],'status'=>1])
                        ->first();
        if ( !empty($list) ) {
            return error(0, '已经预约');
        }

        $user = User::where('id', $this->user['id'])->first();
//        if ($user->phone && (preg_match("/^1((34[0-8]\d{7})|((3[0-3|5-9])|(4[5-7|9])|(5[0-3|5-9])|(66)|(7[2-3|5-8])|(8[0-9])|(9[1|8|9]))\d{8})$/",
//                $user->phone) || strlen($user->phone)==13)) {
        if ( !empty($user->phone) ) {

            $twitter_id = 0;
            if(!empty($input['inviter'])) {
                $twitter_id = $input['inviter'];
            }else if(!empty($input['live_son_flag'])){
                $twitter_id = $input['live_son_flag'];
            }

            $is_flag='';
            if(!empty($input['is_flag'])){
                $is_flag=$input['is_flag'];
            }
            Subscribe::create([
                'user_id' => $this->user['id'],
                'type' => 3,
                'relation_id' => $info_id,
                'status' => 1,
                'is_flag' => $is_flag,
                'twitter_id' => $twitter_id,
            ]);
            //按渠道更新预约人数
            LiveStatistics::countsJob($info_id,1,$twitter_id);

            LiveCountDown::create([
                'live_id' => $info_id,
                'user_id' => $this->user['id'],
                'phone' => $user->phone,
                'new_vip_uid' => $twitter_id,
            ]);

            Live::where(['id' => $live['live_pid']])->increment('order_num');

            //334 团中央预约保护 18511111002
            //276 电视渠道  18522222291
            //添加关系保护
            $twitter_id = (int)$twitter_id;
            if (
                ($info_id === 276 && $twitter_id === 884066) ||
                ($info_id === 334 && $twitter_id === 5920535)
            ){
                if ($twitter_id == 884066){
                    $t_phone = 18522222291;
                }else{
                    $t_phone = 18511111002;
                }

                $temp_user_bind_array = [
                    'parent'   => $t_phone,
                    'son'      => $this->user['phone'],
                    'life'     => 2,
                    'begin_at' => date('Y-m-d 00:00:00'),
                    'end_at' => date('Y-m-d 23:59:59', strtotime("+1 years")),
                    'channel'  => 4,
                    'status'   => 1
                ];
                DB::table('nlsg_vip_user_bind')->insertOrIgnore($temp_user_bind_array);
            }

            return success('发送成功');

//            $easySms = app('easysms');

            /*try {
                if(strlen($user->phone)==11) {
                    $result = $easySms->send($user->phone, [
                        'template' => 'SMS_169114800',
                    ], ['aliyun']);
                }
                return success('发送成功');
            } catch (\Overtrue\EasySms\Exceptions\NoGatewayAvailableException $exception) {
                $message = $exception->getResults();
                return $message;
            }*/

        } else {
            return error(0, '手机号不存在或者错误');
        }
    }

    /**
     * @api {post} api/v4/live/pay_order 付费预约
     * @apiVersion 4.0.0
     * @apiName  pay_order
     * @apiGroup 直播
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/live/pay_order
     *
     * @apiParam {number} live_id 直播房间id
     * @apiParam {number} token 当前用户
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
    public function payLiveOrder(Request $request)
    {
        $input = $request->all();
//        $tweeterCode    = $input['inviter'] ?intval($input['inviter']??0): 0;
//        $liveId         = $input['live_id'] ? intval($input['live_id']??0): 0;
//        $liveInfoId     = $input['info_id'] ? intval($input['info_id']??0): 0;
//        $osType         = $input['os_type'] ? intval($input['os_type']??1): 1;
//        $payType        = $input['pay_type'] ? intval($input['pay_type']??0): 0;


        $tweeterCode    = intval($input['inviter'] ?? 0);
        $liveId         = intval($input['live_id'] ?? 0);
        $liveInfoId     = intval($input['info_id'] ?? 0);
        $osType         = intval($input['os_type'] ?? 1);
        $payType        = intval($input['pay_type'] ?? 0);

        $activity_tag   = $input['activity_tag'] ??'';

        if( in_array($this->user['id'], [878644, 882057, 882861]) ){
            return error(0, '用户异常');
        }

        //虚拟用户
        if($osType ==3 && (empty($this->user['phone']) || substr($this->user['phone'],0,1) == 2) ){
            return error(4000, '请修改手机号');
        }
        $list = Subscribe::where(['relation_id' => $input['info_id'], 'type'=>3,'user_id' => $this->user['id']])
            ->first();
        if ( !empty($list) ) {
            return error(201, '已经预约',['order_id'=>$list['order_id']]);
        }

        $model = new Order();
        $checked = $model->addOrderLiveCheck($this->user['id'], $tweeterCode, $liveId, 3);
        if ($checked['code'] == 0) {
            return error(0, $checked['msg']);
        }
        //校验推客id是否有效
        $tweeter_code = $checked['tweeter_code'];

        $from_live_info_id = '';
        if (isset($input['from_live_info_id']) && $input['from_live_info_id'] > 0) {   //大于0 时说明在直播间买的
            //查看是否有免费直播间的推荐人
            $liveCountDown = LiveCountDown::select('live_id', 'user_id', 'new_vip_uid')
                ->where('live_id', $input['from_live_info_id'])
                ->where('user_id', $this->user['id'])
                ->first();

            $tweeter_code = $liveCountDown['new_vip_uid'];
            $live_pid = LiveInfo::Find($input['from_live_info_id']);  //推荐 remark
            if(!empty($live_pid['live_pid'])){
                $from_live_info_id = $live_pid['live_pid'];
            }
        }

        $list = Live::select('id', 'title', 'price', 'twitter_money', 'is_free')
            ->where('id', $liveId)
            ->first();
        if (!$list) {
            return error(0, '直播不存在');
        }

        //创业天下的订单
        $type_config = ConfigModel::getData(53, 1);
        if ($type_config == 1 && $activity_tag === 'cytx') {
            //校验用户本月是否能继续花钱
            $check_this_money = PayRecord::thisMoneyCanSpendMoney($this->user['id'], 'cytx', $list['price']);
            if ($check_this_money == 0) {
                return error(0, '本月已超消费金额', 0);
            }
        }

        $ordernum = MallOrder::createOrderNumber($this->user['id'], 3);
        $data = [
            'ordernum' => $ordernum,
            'type' => 10,
            'user_id' => $this->user['id'],
            'relation_id' => $liveInfoId,
            'cost_price' => $list['price'],
            'price' => $list['price'],
            'twitter_id' => $tweeter_code??0,
            'coupon_id' => 0,
            'ip' => $this->getIp($request),
            'os_type' => $osType,
            'live_id' => $liveId,
            'pay_type' => $payType,
            'activity_tag' => $activity_tag,
            'remark' => $from_live_info_id,

        ];
        $order = Order::firstOrCreate($data);
        if ($order) {
            $data = [
                'order_id' => $order['id']
            ];
            if(!empty($tweeterCode) && empty($tweeter_code)){ //推荐人不为空，但是没有订阅
                $map=[
                    'user_id' => $this->user['id'],
                    'order_id' => $order['id'],
                    'ordernum' => $ordernum,
                    'live_id' => $liveId,
                    'twitter_id'=>$tweeterCode,//邀请人记录
                    'created_at'=>date('Y-m-d H:i:s'),
                ];
                //记录推荐下单历史
                OrderTwitterLog::Add($map, true);
            }
            return success($data);
        }
        return error(1004, '下单失败');
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

    public function checkLiveSub(Request $request)
    {
        $model = new Order();
        $data = $model->checkLiveSub($request->input('live_id', 0), $this->user['id'] ?? 0);
        return $this->getRes($data);
    }

//    function test(){
//        $a = Live::sendLiveCountDown();
//        return success($a);
//
//    }


    //nlsg_live_check_phone表关注
    public function checkPhoneAddSub()
    {


        $flag = true;
        while ($flag) {

            $checkArr = LiveCheckPhone::select('*')->where(['is_scanning' => 0, 'status' => 1])->limit(100)->get()->toArray();

            //dd($checkArr);
            //先校验是否注册用户
            if (empty($checkArr)) {
                $flag = false;
                return $this->getRes([-1]);
            }
//            $checkArr = LiveCheckPhone::select('*')->where(['is_scanning'=>0,'status'=>1])->get()->toArray();
//            //先校验是否注册用户
//            if(empty($checkArr)){
//                return $this->getRes([-1]);
//
//            }
            DB::beginTransaction();
            //所有用户手机号
            $checkPhoneArr = array_column($checkArr, 'phone');
            //已注册用户手机号
            $user = User::whereIn('phone', $checkPhoneArr)->get()->toArray();
            $UserPhoneArr = array_column($user, 'phone');
            $resultPhone = array_diff($checkPhoneArr, $UserPhoneArr);
            $ures = true;
            if ($resultPhone) {
                //进行注册操作createMany
                $addUser = [];
                foreach ($resultPhone as $k => $v) {


                    $addUser[] = [
                        "phone" => $v,
                        "nickname" => substr_replace($v, '****', 3, 4),
                    ];
                }
                $ures = User::insert($addUser);
            }

            //重新查询所有用户的uid
            $LiveCountDownUser = [];
            $subscribeUser = [];
            $up_ids = [];
            foreach ($checkArr as $k => $v) {
                $up_ids[] = $v['id'];
                //查询用户信息
                $user = User::where('phone', $v['phone'])->first();
                $info_id = LiveInfo::where(['live_pid' => $v['live_id']])->first();


                $liveCountDown = LiveCountDown::where('phone', $v['phone'])->first();
                if (empty($liveCountDown)) {
                    $LiveCountDownUser[] = [
                        "live_id" => $info_id['id'],  //info表id
                        "user_id" => $user['id'],
                        "phone" => $v['phone'],
                    ];
                }


                $subscribe = Subscribe::where([
                    'user_id' => $user['id'], 'type' => 3, 'status' => 1, 'relation_id' => $v['live_id'],])->first();
                if (empty($subscribe)) {
                    $subscribeUser[] = [
                        'user_id' => $user['id'], //会员id
                        'pay_time' => date("Y-m-d H:i:s", time()), //支付时间
                        'type' => 3, //直播
                        'status' => 1,
                        'relation_id' => $v['live_id'],  //live表id
                    ];
                }

            }

            $lres = true;
            $dres = true;
            if ($LiveCountDownUser) {
                //直播预约表
                $dres = LiveCountDown::insert($LiveCountDownUser);
                $lres = Live::where(['id' => $v['live_id']])->increment('order_num', count($checkArr));
            }

            $sres = true;
            if ($subscribeUser) {
                //直播关注表
                $sres = Subscribe::insert($subscribeUser);
            }

            //修改标记
            $lupres = true;
            if ($up_ids) {
                $lupres = LiveCheckPhone::whereIn('id', $up_ids)->update([
                    'is_scanning' => 1,
                ]);
            }


            if ($ures && $dres && $lres && $sres && $lupres) {
                DB::commit();
            } else {
                $flag = false;
                DB::rollBack();
            }
        }
        return $this->getRes([$flag]);


    }

    public function liveTeam(Request $request)
    {
        $live_team = Live::teamInfo($request->input('id', 1), 1);
        return [
            'live' => $live_team
        ];
    }





    /**
     * @api {post} api/v4/live/live_comment_his 直播间评论上滑
     * @apiVersion 4.0.0
     * @apiName  live_comment_his
     * @apiGroup 直播
     *
     * @apiParam {number} live_id 直播间id
     * @apiParam {number} live_son_flag 渠道
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
     *
     */
    public function liveCommentHis(Request $request) {

        if (0) {
            $live_id       = $request->input('live_id') ?? 0;
            $live_son_flag = $request->input('live_son_flag') ?? null;

            $liveCommentObj = new LiveComment();
            $userObj        = new User();
            $res            = DB::table($liveCommentObj->getTable(), ' lc')
                ->leftJoin($userObj->getTable() . ' as u', 'u.id', '=', 'lc.user_id')
                ->select('lc.content', 'lc.created_at', "u.nickname", "u.level", 'u.expire_time', DB::raw("2 as type"), DB::raw("0 as gift"), DB::raw("0 as num"))
                ->where([
                    "lc.live_id"       => intval($live_id),
                    "lc.status"        => 1,
                    "lc.type"          => 0,
                    "lc.comment_type"  => 1,
                    "lc.live_son_flag" => $live_son_flag,
                ])->paginate(10)->toArray();

            foreach ($res['data'] as $key => &$val) {
                $val->level = 0;
                if (!empty($val->expire_time) && $val->expire_time > date('Y-m-d H:i:s')) {
                    $val->level = $val->level;
                }
            }
            return success($res);
        }

        $live_id       = $request->input('live_id', 0);
        $live_son_flag = $request->input('live_son_flag', '');
        if (empty($live_id) || empty($live_son_flag)) {
            return $this->success([]);
        }
        $min_id = LiveComment::query()->where('live_id', '=', $live_id)->min('id');
        if (empty($min_id)) {
            $min_id = LiveComment::query()->max('id');
        }

        $res = LiveComment::query()
            ->with(['user:id,nickname,level,expire_time'])
            ->where('id', '>=', $min_id)
            ->where('live_id', '=', $live_id)
            ->where('live_son_flag', '=', $live_son_flag)
            ->where('status', '=', 1)
            ->where('type', '=', 0)
            ->where('comment_type', '=', 1)
            ->orderBy('id')
            ->select(['content', 'created_at', 'user_id', DB::raw('2 as type'), DB::raw('0 as gift'), DB::raw('0 as num')])
            ->paginate(10);

        foreach ($res as $v) {
            $v->nickname    = $v->user->nickname??'';
            $v->level       = 0;
            $v->expire_time = '';
        }

        return $this->success($res);

    }


    /**
     * @api {get} api/v4/live/live_push_one 直播间推送最后一条
     * @apiVersion 4.0.0
     * @apiName  live_push_one
     * @apiGroup 直播
     *
     * @apiParam {number} live_id 直播间id
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
    public function livePushOne(Request $request){
        $live_id = $request->input('live_id')??0;

        $res = LivePush::select('id','live_id','live_info_id','push_type','push_gid','user_id','click_num','close_num','is_push','is_done','length')->where([
            "live_id"       => intval($live_id),
            "is_push"       => 1,
            "is_done"       => 1,
        ])->where('done_at','>',date("Y-m-d",time()))->orderBy('done_at', 'desc')->first();


        //push_type 产品type  1专栏 2精品课 3商品 4 经营能量 5 一代天骄 6 演说能量 7:讲座 8:听书  9直播   10 直播外链  11 训练营
        //push_gid 推送产品id，专栏id  精品课id  商品id
        if(!empty($res['push_gid'])){
            switch ($res['push_type']){
                case 1:
                case 7:
                case 11:
                    $Info = Column::select('id','name','price','subtitle','details_pic as img','user_id')->where(['id'=>$res['push_gid'],'status'=>1])->first();
                    break;

                case 2:
                case 8:
                    $Info = Works::select('id','title as name','price','subtitle','details_pic as img','user_id')->where(['id'=>$res['push_gid'],'status'=>4])->first();
                    break;
                case 3:
                    $Info = MallGoods::select('id','name','price','subtitle','picture as img')->where(['id'=>$res['push_gid'],'status'=>2])->first();
                    break;
                case 4:
                    /*$H=date('H');
                    if($H<20){
                        $Info=[];
                    }else {

                    }*/
//                    $Info = OfflineProducts::where(['id'=>$res['push_gid']])->first();
                    $Info = OfflineProducts::select('id','title as name','price','subtitle','image as img','cover_img as image')->where(['id'=>$res['push_gid']])->first();
                    break;
                case 6:
                    $Info=[
                        'name'=>'幸福360会员',
                        'price'=>360,
                        'subtitle'=>'',
                        'image'=>'/nlsg/works/20201124144228445465.png', //方图
                        'img'=>'/nlsg/works/20201124144228445466.png'  //长图
                    ];
                    break;
                case 9:
                    $Info=Live::select('id','id as live_info_id','title as name','price','describe as subtitle','cover_img as img','cover_img as image','begin_at','end_at','user_id','is_free')->where(['id'=>$res['push_gid']])->first();
                    break;
                case 10:

                    $Info=LiveUrl::select('id','name','describe','url','image','img')->where(['id'=>$res['push_gid']])->first();
                    break;
            }
            if(!empty($Info)){
                //与socket数据格式一致
                $suc = [
                    'type' => 6,
                    'ios_content' =>[[
                        'push_info' => $res,
                        'son_info' => $Info,
                    ]],
                    'content' =>[[
                        'push_info' => $res,
                        'son_info' => $Info,
                    ]],
                ];
                return success($suc);
            }
        }
        return success( (object)[] );
    }
    /**
     * @api {get} api/v4/live/get_qr_code 二维码弹窗
     * @apiVersion 4.0.0
     * @apiName  get_qr_code
     * @apiGroup 直播
     *
     * @apiParam {number} relation_type 类型 1.精品课程2.商城3.直播   4 购买360   5 大咖讲书
     * @apiParam {number} relation_id   数据id 课程id  商品id  直播id
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
    public function GetQrCode(Request $request){

        $relation_type = $request->input('relation_type')??0;
        $relation_id = $request->input('relation_id')??0;
        $order_id = $request->input('order_id')??0;
        $is_wechat = $request->input('is_wechat')??0;
        if($relation_type == 3){

            //relation_type=3时    免费传relation_id=live_id   付费传order_id
//            if(empty($order_id)){ //直播免费预约取消二维码
//                return success((object)[] );
//            }
            if(empty($order_id) && empty($is_wechat)){ //免费并且是渠道不弹
                return success((object)[] );
            }

            //付费客户端不传直播id  需要查询
            if(!empty($order_id)){  //付费
                $order = Order::where(['id'=>$order_id])->first();
                $relation_id = $order['relation_id'];
//                if(!in_array($relation_id,[119,123,124,177,182])){
//                    return success((object)[] );
//                }
            }

        }else{
            //目前除了直播 其他不需要根据各个具体产品返二维码
            $relation_id = 0;
        }

        $res = Qrcodeimg::select("id","qr_url")->where([
            'relation_type' => $relation_type,
            'relation_id'   => $relation_id,
            'status'   => 1,
        ])->first();

        if(empty($res)){
            $res=(object)[];
        }

        return success($res);
    }

}
