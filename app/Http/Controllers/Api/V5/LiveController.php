<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use App\Models\ConfigModel;
use App\Models\Live;
use App\Models\LiveConsole;
use App\Models\LiveCountDown;
use App\Models\LiveForbiddenWords;
use App\Models\LiveInfo;
use App\Models\LiveLogin;
use App\Models\LiveSonFlagPoster;
use App\Models\LiveWorks;
use App\Models\OfflineProducts;
use App\Models\Subscribe;
use App\Models\User;
use App\Models\LivePush;
use App\Models\LivePushQrcode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Predis\Client;

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
     *  api/v4/live/lists  直播更多列表
     * @apiVersion 4.0.0
     * @apiName  lists
     * @apiGroup 直播
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/live/lists
     * @apiParam  {number}  page  分页
     *
     * @apiSuccess {string}  title 同直播首页返回值
     */
    public function getLiveLists()
    {
        $uid = $this->user['id'] ?? 0;

        $testers = explode(',', ConfigModel::getData(35, 1));
        $user = User::where('id', $uid)->first();

		$day_time=date("Y-m-d",strtotime("-1 day"));
        // 获取用户管理员权限
        // $provilege_liveids = LiveUserPrivilege::where(['user_id'=>$uid,'pri_level'=>1,'is_del'=>0])->pluck("live_id")->toArray();
        $fills = ['id', 'user_id', 'title', 'describe', 'price','cover_img', 'begin_at', 'type', 'end_at','steam_begin_time','playback_price', 'is_free', 'password', 'order_num','sort','hide_sub_count'];
        $query = Live::query();
        if (!$uid || ($user && !in_array($user->phone, $testers))) {
            $query->where('is_test', '=', 0);
            $is_test = 0;
        } else {
            $query->whereIn('is_test', [0, 1]);
            $is_test = 1;
        }

        $query->with('user:id,nickname')
            ->select($fills)
			->where('begin_at','>', $day_time)
            ->where('status', 4)
            ->where('is_finish', 0)
            ->where('is_del', 0);
//            ->where('app_project_type','=',APP_PROJECT_TYPE);

        //  如果测试用户 可不做校验直接展示两个平台的直播  主播可以看自己的直播
        if(empty($this->user['is_test_pay'])){
            $query->where(function ($query)use($uid){
                $query->where('app_project_type','=',APP_PROJECT_TYPE)
                    ->Orwhere('user_id', $uid);;
            });
        }
            // 不查询测试直播的情况下
            // 需要查询当前用户是否管理员  单独查询管理员的
            if($is_test == 0 && !empty($this->user['phone'])){
                $query->unionAll(Live::select($fills)
                            ->where('begin_at','>', $day_time)
                            ->where('status', 4)
                            ->where('is_finish', 0)
                            ->where('is_del', 0)
                            ->where('helper', 'like', '%'.$this->user['phone'].'%'));
            }
            $lists = Live::fromSub($query,'table')->select('*')
                ->groupBy('id')
                ->orderBy('sort', 'asc')
                ->orderBy('begin_at', 'asc')
                ->paginate(10)
                ->toArray();

        if (!empty($lists['data'])) {
            foreach ($lists['data'] as &$v) {
                $channel = LiveInfo::where('live_pid', $v['id'])
                    ->where('status', 1)
                    ->orderBy('id', 'desc')
                    ->first();
                if ($channel) {
                //    1未开始  2已结束  3直播中
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

		if( isset($list['user']['intro']) ){
			$list['user']['intro'] = ''; //  直播间不显示讲师简介  3月24日需求
		}

        //初始化人气值
        $redisConfig = config('database.redis.default');
        $redis = new Client($redisConfig);
        $redis->select(0);

        $live_son_flag_num=0;
        if ($list) {
            $userId = $this->user['id'] ?? 0;
            $user = new User();
            $subLive = Subscribe::isSubscribe($userId, $list->live_pid, 3);
            //全员禁言
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

            $list['column_id'] = 0;
            $list['is_sub'] = $subLive ?? 0;
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

        $data = [
            'info' => $list,
            'live_son_flag_num' => $live_son_flag_num,
            'push_live' => $push_live,
            'is_push_goods' => $is_push_goods,
        ];
        return success($data);

    }




    /**
     * {get} api/v5/live/live_push_qrcode 直播推送二维码上传
     */
    public function livePushQrcode(Request $request)
    {
        $user_id    = $this->user['id'] ?? 0;
        $qr_image  = $request->input('qr_image',0);
        $os_type = $request->input('os_type',0);

        $validator = Validator::make($request->all(), [
            'qr_image' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->error(0,$validator->messages()->first(),0);
        }
        $qr_image = str_replace("http://nlsgapp.oss-cn-beijing.aliyuncs.com","",$qr_image);
        $id = LivePushQrcode::create([
            'qr_url' => $qr_image,
        ])->id;
        return success($id);

    }
    /**
     * {get} api/v5/live/sell_short_state 修改权限
     */
    public function SellShortState(Request $request)
    {

        $user_id    = $this->user['id'] ?? 0;
        $pushid  = $request->input('id',0);
        $is_sell_short  = $request->input('is_sell_short',0);

        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
            'is_sell_short' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return $this->error(0,$validator->messages()->first(),'');
        }
        $pushData = LivePush::where('id', $pushid)->first();

        $check_is_admin = LiveConsole::isAdmininLive($user_id, $pushData['live_id']);
        if ($check_is_admin === false) {
            return $this->error(0,'需要管理员权限','');
        }

        LivePush::where([
            'live_id'   => $pushData['live_id'],
            'push_type' => $pushData['push_type'],
            'push_gid'  => $pushData['push_gid'],
        ])->update([
            'is_sell_short' => $is_sell_short ?? 0,
        ]);

        //删除缓存
        $cache_live_name = 'live_push_works_'.$pushData['live_id'];
        Cache::delete($cache_live_name);

        return success('');

    }


    /**
     * getZeroActivity 获取0元购宣传活动页面
     *
     * @param Request $request
     *
     * @return mixed|string 直播id
     */
    public function getZeroActivityLiveId(Request $request){
        $user_id    = $this->user['id'] ?? 0;
        $id= Live::where("zero_poster_show",1)->value('id');
        $is_sub = 0;
        if($id > 0){
            $is_sub = Subscribe::isSubscribe($user_id,$id,3);
        }
        return success([
            'id' => $id,
            'is_sub' => $is_sub,
        ]);
    }
}
