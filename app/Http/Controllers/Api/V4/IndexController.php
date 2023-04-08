<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use App\Models\Announce;
use App\Models\Banner;
use App\Models\CacheTools;
use App\Models\Column;
use App\Models\ConfigModel;
use App\Models\Coupon;
use App\Models\Lists;
use App\Models\Live;
use App\Models\Recommend;
use App\Models\RecommendConfig;
use App\Models\User;
use App\Models\Versions;
use App\Models\Works;
use App\Servers\LiveConsoleServers;
use App\Servers\StatisticsServers;
use App\Servers\V5\JpushService;
use EasyWeChat\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use App\Models\Task;
use Symfony\Component\Cache\Adapter\RedisAdapter;

class IndexController extends Controller
{

    /**
     *手机号归属地查询
     */
    public function phoneRegion(Request $request)
    {

        $data=LiveConsoleServers::getPhoneRegion(1);


        return $this->success($data);
    }

    /**
     * @api {get} api/v4/index/get_top_img  各个列表头图
     * @apiVersion 5.0.0
     * @apiName  get_top_img
     * @apiGroup five_Index
     *
     * @apiSuccess {number} live_img    直播
     * @apiSuccess {number} teacher_img  大咖讲书
     * @apiSuccess {number} works_img   课程
     *
     * @apiSuccessExample  Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "code": 200,
     *       "msg" : '成功',
     *       "data":[]
     *     }
     *
     */
    public function getTopImg(Request $request) {

        $res['live_img']    = Banner::getBannerImg(81);//直播
        $res['teacher_img'] = Banner::getBannerImg(82);//大咖讲书
        $res['works_img']   = Banner::getBannerImg(83);//课程首页

        return success($res);

    }

    /**
     * @api {get} api/v4/index/index_position  首页位置API
     * @apiVersion 5.0.0
     * @apiName  index_position
     * @apiGroup five_Index
     *
     * @apiSuccess {number} head    头部按钮
     * @apiSuccess {number} bottom  底部模块
     * @apiSuccess {number} title   标题
     * @apiSuccess {string} icon_pic   icon
     * @apiSuccess {string} jump_type 跳转类型 1首页  2每日琨说  3专栏 4课程 5讲座 6 360会员 7训练营  8商场  9线下门票   10直播  11大咖主持人 13精品专题 14热门榜单   15课程全部分类页面  16 banner   17短视频 18活动类型
     * @apiSuccess {string} modular_type  模块类型：  1   banner, 2  金刚区（icon）, 3  每日琨说, 4  直播, 5  精品课程, 6  短视频, 7  大咖主讲人, 8  1-3岁父母  主题课程, 9  精品专题, 10 热门榜单, 11 亲子专题',
     * @apiSuccess {string} sort     排序
     *
     * @apiSuccessExample  Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "code": 200,
     *       "msg" : '成功',
     *       "data":[]
     *     }
     *
     */
    public function indexPosition()
    {


        $filed = ["id","title","icon_pic","jump_type","modular_type","sort","jump_url",'icon_mark','icon_mark_rang','lists_id'];

        $head   = RecommendConfig::select($filed)->where(['show_position'=>1, 'is_show'=>1,])->OrderBy("sort",'asc')->get()->toArray();
        $bottom = RecommendConfig::select($filed)->where(['show_position'=>3, 'is_show'=>1,])->OrderBy("sort",'asc')->get()->toArray();
        $icon   = RecommendConfig::select($filed)->where(['show_position'=>2, 'is_show'=>1,])->OrderBy("sort",'asc')->get()->toArray();
        foreach ($bottom as &$value){
            if($value['modular_type'] == 2){ //icon
                $value['icon'] = $icon;
            }
        }

        $res = [
            'head' => $head,
            'bottom' => $bottom,
        ];
        return $this->success($res);
    }


    /**
     * 5.0 第一版首页接口的合并
     * @api {get} api/v4/index/index_middle  首页   大咖主讲人-精品专题-热门榜单【合并】
     * @apiVersion 5.0.0
     * @apiName  index_middle
     * @apiGroup five_Index
     *
     * @apiSuccess {number} teacher_list    大咖主讲人
     * @apiSuccess {number} special_list 精品专题
     * @apiSuccess {number} hot_list  热门榜单
     *
     * @apiSuccessExample  Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "code": 200,
     *       "msg" : '成功',
     *       "data":[]
     *     }
     *
     */
    function indexMiddle(){
        $recommendModel = new Recommend();
        //大咖主讲人
        $res["teacher_list"] = $recommendModel->getIndexRecommend(14, 35);
        //精品专题
        $res['special_list'] = $recommendModel->getIndexRecommend(15, 37);

        //榜单
        $res['hot_list'] = $recommendModel->getIndexRecommend(11, 38,3);

//        $model = new Lists();
//        $res['hot_list'] = $model->getList();

        return $this->success($res);
    }




    /**
     * @api {get} api/v4/index/announce 首页-公告
     * @apiVersion 4.0.0
     * @apiName  index
     * @apiGroup Index
     *
     * @apiSuccess {string} content 内容
     *
     * @apiSuccessExample  Success-Response:
     * HTTP/1.1 200 OK
     * {
     *      "code": 200,
     *      "msg" : '成功',
     *      "data": {
     *          "id": 1,
     *          "content": "测试"
     *       }
     * }
     *
     */
    public function announce()
    {
        $list = Announce::select('id', 'content')
            ->first()->toArray();
        return $this->success($list);
    }

    /**
     * @api {get} api/v4/index/banner  首页-轮播图
     * @apiVersion 4.0.0
     * @apiName  banner
     * @apiGroup Index
     *
     * @apiSuccess {string} pic  图片地址
     * @apiSuccess {string} title 标题
     * @apiSuccess {string} url  链接地址
     *
     * @apiSuccessExample  Success-Response:
     *     HTTP/1.1 200 OKr
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
     *        ]
     *     }
     *
     */

    public function banner(Request $request)
    {
        $os_type = $request->get('os_type') ?? 0; // 1 安卓 2ios 3微信
        //临时解决  安卓未传 os_type参数
        if($os_type == 0){
            if (preg_match('/Android/', $_SERVER['HTTP_USER_AGENT'])) {
                $os_type = 1;
            }
        }

        //show_type  0:全部    1 :客户端   3:h5端
        $show_type = 0;
        if( in_array($os_type,[1,2]) ){
            $show_type = 1;
        }else if( $os_type == 3 ) {
            $show_type = 2;
        }

        $bannerModel = new Banner();
        $lists = $bannerModel->getIndexBanner($show_type);
        return $this->success($lists);
    }

    public  function  live()
    {
        return success();
    }

    /**
     * @api {get} api/v4/index/lives  首页-直播推荐
     * @apiVersion 4.0.0
     * @apiName  live
     * @apiGroup Index
     *
     * @apiSuccess {string} title       标题
     * @apiSuccess {string} describe    描述
     * @apiSuccess {string} cover_img   封面
     * @apiSuccess {string} order_num   预约人数
     * @apiSuccess {string} price       预约价格
     *
     * @apiSuccessExample  Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "code": 200,
     *       "msg" : '成功',
     *       "data":[
     *               {
     *                   "id": 1,
     *                   "title": "第85期《经营能量》直播",
     *                   "describe": "经营能量",
     *                   "cover_img": "/live/look_back/live-1-9.jpg",
     *                   "start_time": null,
     *                   "end_time": null,
     *                   "live_status": "已结束"
     *               }
     *
     *        ]
     *     }
     *
     */
    public function lives(Request $request)
    {
        $os_type = $request->get('os_type') ?? 1;
        $version = $request->get('version') ?? 1;

        $config_version = ConfigModel::getData(52);
        if($os_type == 2 && $version == $config_version){
            return success();
        }

        $user_id = $this->user['id'] ?? 0;


        $recommendModel = new Recommend();
        $lists = $recommendModel->getLiveRecommend($user_id, 7, 1);
        // $list = Live::select('id', 'title', 'describe', 'cover_img', 'begin_at', 'end_at', 'price', 'order_num',
        //     'is_free', 'helper')
        //     ->where('begin_at', '>', date("Y-m-d"))
        //     ->where('end_at',   '<', date('Y-m-d',strtotime('+1 day')))
        //     ->where('is_del', 0)
        //     ->orderBy('created_at', 'desc')
        //     ->first();

        // if (!empty($list)){
        //     $list->live_length = strtotime($list->end_at)-strtotime($list->begin_at);
        //     $list->begin_at =  date('H:i:s',strtotime($list->begin_at));

        //     $lists = $recommendModel->getLiveRelation($user_id, $list);
        // }else{
        //     $lists = (object)[];
        // }

        return success($lists);



    }

    /**
     * @api {get} api/v4/index/column  首页-大咖专栏
     * @apiVersion 4.0.0
     * @apiName  column
     * @apiGroup Index
     *
     * @apiSuccess {string} name     专栏名称
     * @apiSuccess {string} title    标题
     * @apiSuccess {string} subtitle 副标题
     * @apiSuccess {number} price    价格
     * @apiSuccess {number} is_new   是否新上架 1是 0 否
     * @apiSuccess {string} cover_pic 封面
     *
     * @apiSuccessExample  Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "code": 200,
     *       "msg" : '成功',
     *       "data":[
     *               {
     *                   "id": 1,
     *                   "name": "王琨专栏",
     *                   "title": "顶尖导师 经营能量",
     *                   "subtitle": "顶尖导师 经营能量",
     *                   "message": "",
     *                   "price": "99.00",
     *                   "is_new": 1,
     *                   "cover_pic": "/wechat/works/video/161627/2017121117503851065.jpg"
     *               }
     *
     *        ]
     *     }
     *
     */
    public function column()
    {
        $recommendModel = new Recommend();
        $lists = $recommendModel->getIndexRecommend(1, 1);
        return $this->success($lists);
    }


    /**
     * @api {get} api/v4/index/works  首页-精选课程
     * @apiVersion 4.0.0
     * @apiName  works
     * @apiGroup Index
     *
     * @apiSuccess {number} user_id   用户id
     * @apiSuccess {string} title     标题
     * @apiSuccess {string} cover_img 封面
     * @apiSuccess {string} subtitle  副标题
     * @apiSuccess {string} price     价格
     * @apiSuccess {string} is_new    是否为新上架 1 是 0 否
     * @apiSuccess {string} is_free   是否为限免   1 是 0 否
     * @apiSuccess {string} user    用户
     * @apiSuccess {number} user.id      用户id
     * @apiSuccess {string} user.nickname 用户昵称
     *
     * @apiSuccessExample  Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "code": 200,
     *       "msg" : '成功',
     *       "data":[
     *               {
     *                   "id": 16,
     *                   "user_id": 168934,
     *                   "title": "如何经营幸福婚姻",
     *                   "cover_img": "/nlsg/works/20190822150244797760.png",
     *                   "subtitle": "",
     *                   "price": "29.90",
     *                   "user": {
     *                      "id": 168934,
     *                      "nickname": "chandler"
     *                   },
     *                   "is_new": 1,
     *                   "is_free": 1
     *                }
     *       ]
     *     }
     *
     */
    //12月30后废弃该接口
    public function works()
    {
        $recommendModel = new Recommend();
        $lists = $recommendModel->getIndexRecommend(2, 1);
        return $this->success($lists);
    }

    /**
     * @api {get} api/v4/index/index_works  首页-精选课程-主题课程模块-专题课
     * @apiVersion 5.0.0
     * @apiName  index_works
     * @apiGroup five_Index
     *
     * @apiSuccess {number} work_lists   精品课程模块
     * @apiSuccess {number} theme_works   主题课程模块
     * @apiSuccess {number} special_works   专题课模块
     * @apiSuccess {number} user_id   用户id
     * @apiSuccess {string} title     标题
     * @apiSuccess {string} cover_img 封面
     * @apiSuccess {string} subtitle  副标题
     * @apiSuccess {string} price     价格
     * @apiSuccess {string} is_new    是否为新上架 1 是 0 否
     * @apiSuccess {string} is_free   是否为限免   1 是 0 否
     * @apiSuccess {string} user    用户
     * @apiSuccess {number} user.id      用户id
     * @apiSuccess {string} user.nickname 用户昵称
     *
     * @apiSuccessExample  Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "code": 200,
     *       "msg" : '成功',
     *       "data":[
     *               {
     *                   "id": 16,
     *                   "user_id": 168934,
     *                   "title": "如何经营幸福婚姻",
     *                   "cover_img": "/nlsg/works/20190822150244797760.png",
     *                   "subtitle": "",
     *                   "price": "29.90",
     *                   "user": {
     *                      "id": 168934,
     *                      "nickname": "chandler"
     *                   },
     *                   "is_new": 1,
     *                   "is_free": 1
     *                }
     *       ]
     *     }
     *
     */
    public function indexWorks()
    {
        $recommendModel = new Recommend();
        //精品课程模块
        $res['work_lists'] = $recommendModel->getIndexRecommend(2, 44);
        //主题课程模块
        $res['theme_works'] = $recommendModel->getIndexRecommend(2, 36);
        //专题课模块
        $res['special_works'] = $recommendModel->getIndexRecommend(2, 39);

        return $this->success($res);
    }
    /**
     * @api {get} api/v4/index/book  首页-听书推荐
     * @apiVersion 4.0.0
     * @apiName  book
     * @apiGroup Index
     *
     * @apiSuccess {string} title 标题
     * @apiSuccess {string} subtitle 副标题
     * @apiSuccess {string} cover 封面
     * @apiSuccess {number}  num  数量
     * @apiSuccess {string} list_works
     * @apiSuccess {string} list_works.type   2听书 4讲座
     * @apiSuccess {string} list_works.works  讲座和听书
     *
     * @apiSuccessExample  Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "code": 200,
     *       "msg" : '成功',
     *       "data":[
     * {
     * "id": 1,
     * "title": "世界名著必读，历经岁月经典依旧陪伴成长",
     * "subtitle": "强烈推荐",
     * "cover": "/wechat/works/video/161627/2017121117503851065.jpg",
     * "num"  :  5,
     * "works": [
     * {
     *                      "works_id": 18,
     * "user_id": 168934,
     * "title": "不要羞辱你的孩子 他的心很脆弱",
     * "cover_img": "/wechat/works/video/161627/2017061416324725316.jpg",
     * "pivot": {
     * "lists_id": 1,
     * "works_id": 30
     * },
     * "user": {
     * "id": 168934,
     * "nickname": "chandler",
     * "headimg": "/wechat/works/headimg/3833/2017110823004219451.png"
     * }
     * },
     * {
     * "user_id": 168934,
     * "title": "小孩子做噩梦怎么办？九成父母都没当回事",
     * "cover_img": "/wechat/works/video/161627/2017061416393315731.jpg",
     * "pivot": {
     * "lists_id": 1,
     * "works_id": 31
     * },
     * "user": {
     * "id": 168934,
     * "nickname": "chandler",
     * "headimg": "/wechat/works/headimg/3833/2017110823004219451.png"
     * }
     * }
     * ]
     * }
     *         ]
     *     }
     *
     */
    public function book()
    {
        $recommendModel = new Recommend();
        $lists = $recommendModel->getIndexRecommend(4, 1);
        return $this->success($lists);
    }

    /**
     * @api {get} api/v4/index/goods  首页-精选好物
     * @apiVersion 4.0.0
     * @apiName  goods
     * @apiGroup Index
     *
     * @apiSuccess {string} name 名称
     * @apiSuccess {string} picture 封面
     * @apiSuccess {string} original_price 原价
     * @apiSuccess {string} price  现价
     *
     * @apiSuccessExample  Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "code": 200,
     *       "msg" : '成功',
     *       "data":[
     *               {
     *                   "id": 48,
     *                   "name": " 香港Mcomb儿童专用智能牙刷",
     *                   "picture": "/wechat/mall/mall/goods/8671_1519697106.png",
     *                   "original_price": "220.00",
     *                   "price" : 220
     *               }
     *         ]
     *     }
     *
     */
    public function goods()
    {
        $recommendModel = new Recommend();
        $lists = $recommendModel->getIndexRecommend(8, 1);
        return $this->success($lists);
    }

    /**
     * @api {get} api/v4/index/wiki  首页-小百科
     * @apiVersion 4.0.0
     * @apiName  wiki
     * @apiGroup Index
     *
     * @apiSuccess {string} name  标题
     * @apiSuccess {string} content 内容
     * @apiSuccess {string} cover  图片
     * @apiSuccess {number} view_num 阅读数
     * @apiSuccess {number} like_num 收藏数
     * @apiSuccess {number} comment_num 评论数
     *
     * @apiSuccessExample  Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "code": 200,
     *       "msg" : '成功',
     *       "data":[
     *               {
     *                      "id": 1,
     *                       "name": "室内空气污染对孩子的危害",
     *                       "content": "社会的进步，工业的发展，导致污染越来越严重，触目惊心",
     *                       "cover": "/wechat/mall/goods/3264_1512448129.jpg",
     *                       "view_num": 10,
     *                       "like_num": 2,
     *                       "comment_num": 5
     *               },
     *               {
     *                      "id": 2,
     *                      "name": "世界名著必读岁月经典",
     *                      "content": "每个时代都有极其红极广受好评",
     *                      "cover": "/wechat/mall/mall/goods/389_1519697199.png",
     *                      "view_num": 5,
     *                      "like_num": 6,
     *                      "comment_num": 5
     *               }
     *         ]
     *     }
     *
     */
    public function wiki()
    {
        $recommendModel = new Recommend();
        $lists = $recommendModel->getIndexRecommend(5, 1);
        return $this->success($lists);
    }

    /**
     * @api {get} api/v4/index/course  首页-课程集合
     * @apiVersion 4.0.0
     * @apiName  course
     * @apiGroup Index
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/index/course
     *
     * @apiSuccess {string} title 标题
     * @apiSuccess {string} subtitle 副标题
     * @apiSuccess {string} cover 封面
     * @apiSuccess {number}  num  数量
     * @apiSuccess {string}  works 听书作品
     * @apiSuccess {string}  works.works_id  作品id
     * @apiSuccess {string}  works.title  作品标题
     * @apiSuccess {string}  works.cover_img  作品封面
     *
     * @apiSuccessExample  Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "code": 200,
     *       "msg" : '成功',
     *       "data":[
     * {
     * "id": 1,
     * "title": "世界名著必读，历经岁月经典依旧陪伴成长",
     * "subtitle": "强烈推荐",
     * "cover": "/wechat/works/video/161627/2017121117503851065.jpg",
     * "num"  :  5,
     * "works": [
     * {
     * "user_id": 168934,
     * "title": "不要羞辱你的孩子 他的心很脆弱",
     * "cover_img": "/wechat/works/video/161627/2017061416324725316.jpg",
     * "pivot": {
     * "lists_id": 1,
     * "works_id": 30
     * },
     * "user": {
     * "id": 168934,
     * "nickname": "chandler",
     * "headimg": "/wechat/works/headimg/3833/2017110823004219451.png"
     * }
     * },
     * {
     * "user_id": 168934,
     * "title": "小孩子做噩梦怎么办？九成父母都没当回事",
     * "cover_img": "/wechat/works/video/161627/2017061416393315731.jpg",
     * "pivot": {
     * "lists_id": 1,
     * "works_id": 31
     * },
     * "user": {
     * "id": 168934,
     * "nickname": "chandler",
     * "headimg": "/wechat/works/headimg/3833/2017110823004219451.png"
     * }
     * }
     * ]
     * }
     *         ]
     *     }
     *
     */
    public function course()
    {
        $recommendModel = new Recommend();
        $lists = $recommendModel->getCourseLists();
        return success($lists);
    }

    /**
     * @api {get} api/v4/index/rank  首页-热门榜单
     * @apiVersion 4.0.0
     * @apiName  rank
     * @apiGroup Index
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/index/rank
     *
     * @apiSuccess {string} title 标题
     * @apiSuccess {string} subtitle 副标题
     * @apiSuccess {string} cover 封面
     * @apiSuccess {number}  num  数量
     * @apiSuccess {string}  works 听书作品
     * @apiSuccess {string}  works.works_id 作品id
     * @apiSuccess {string}  works.title  作品标题
     * @apiSuccess {string}  works.cover_img  作品封面
     * @apiSuccess {string}  goods 商品排行榜
     * @apiSuccess {string}  goods.list_goods.id 商品id
     * @apiSuccess {string}  goods.list_goods.name 商品名称
     * @apiSuccess {string}  goods.list_goods.price 商品价格
     *
     * @apiSuccessExample  Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "code": 200,
     *       "msg" : '成功',
     * "data": {
     * "works": [
     * {
     * "id": 8,
     * "title": "热门课程榜单",
     * "works": [
     * {
     * "works_id": 30,
     * "user_id": 168934,
     * "title": "不要羞辱你的孩子 他的心很脆弱",
     * "subtitle": "家庭教育",
     * "cover_img": "/wechat/works/video/161627/2017061416324725316.jpg",
     * "chapter_num": 8,
     * "subscribe_num": 0,
     * "is_free": 1,
     * "price": "0.00",
     * "pivot": {
     * "lists_id": 8,
     * "works_id": 30
     * },
     * "user": {
     * "id": 168934,
     * "nickname": "chandler"
     * }
     * },
     * {
     * "works_id": 31,
     * "user_id": 168934,
     * "title": "小孩子做噩梦怎么办？九成父母都没当回事",
     * "subtitle": "家庭教育",
     * "cover_img": "/wechat/works/video/161627/2017061416393315731.jpg",
     * "chapter_num": 5,
     * "subscribe_num": 0,
     * "is_free": 1,
     * "price": "0.00",
     * "pivot": {
     * "lists_id": 8,
     * "works_id": 31
     * },
     * "user": {
     * "id": 168934,
     * "nickname": "chandler"
     * }
     * },
     * {
     * "works_id": 32,
     * "user_id": 1,
     * "title": "时间就像你手中的冰淇淋",
     * "subtitle": "",
     * "cover_img": "/wechat/works/video/161627/2017061416424169642.jpg",
     * "chapter_num": 0,
     * "subscribe_num": 0,
     * "is_free": 0,
     * "price": "0.00",
     * "pivot": {
     * "lists_id": 8,
     * "works_id": 32
     * },
     * "user": {
     * "id": 1,
     * "nickname": "刘先森"
     * }
     * }
     * ]
     * }
     * ],
     * "wiki": [
     * {
     * "id": 9,
     * "title": "热门百科榜单",
     * "content": null,
     * "list_works": [
     * {
     * "id": 16,
     * "lists_id": 9,
     * "works_id": 1,
     * "wiki": {
     * "id": 1,
     * "name": "室内空气污染对孩子的危害",
     * "content": "社会的进步，工业的发展，导致污染越来越严重，触目惊心",
     * "view_num": 10,
     * "like_num": 2,
     * "comment_num": 5
     * }
     * },
     * {
     * "id": 17,
     * "lists_id": 9,
     * "works_id": 2,
     * "wiki": {
     * "id": 2,
     * "name": "世界名著必读岁月经典",
     * "content": "每个时代都有极其红极广受好评",
     * "view_num": 5,
     * "like_num": 6,
     * "comment_num": 5
     * }
     * }
     * ]
     * }
     * ]
     *  "goods": [
     * {
     * "id": 10,
     * "title": "热门商品榜单",
     * "num": 2,
     * "cover": "/wechat/works/video/161627/2017121117503851065.jpg",
     * "goods": [
     * {
     * "works_id": 48,
     * "name": " 香港Mcomb儿童专用智能牙刷",
     * "price": "220.00",
     * "pivot": {
     * "lists_id": 10,
     * "works_id": 48
     * }
     * },
     * {
     * "works_id": 58,
     * "name": "得力 儿童益智绘画套装",
     * "price": "90.00",
     * "pivot": {
     * "lists_id": 10,
     * "works_id": 58
     * }
     * },
     * {
     * "works_id": 60,
     * "name": "汉字奇遇-识字启蒙卡片",
     * "price": "198.00",
     * "pivot": {
     * "lists_id": 10,
     * "works_id": 60
     * }
     * }
     * ]
     * }
     * ]
     * }
     *     }
     *
     */
    public function rank(Request $request)
    {


        $cache_key_name = 'index_rank_data';
        $data = Cache::get($cache_key_name);

        if (empty($data)) {
            $model = new Lists();
            $data = [
                'works' => $model->getRankWorks(),
                'wiki' => $model->getRankWiki(),
                'goods' => $model->getRankGoods()
            ];
            $expire_num = CacheTools::getExpire($cache_key_name);
            Cache::put($cache_key_name, $data, $expire_num);
        }


        return $this->success($data);
    }

    /**
     * @api {get} api/v4/index/recommend  首页-每日琨说
     * @apiVersion 4.0.0
     * @apiName  recommend
     * @apiGroup Index
     *
     * @apiSuccess {number} subscribe_num 订阅数量
     * @apiSuccess {string} title 课程标题
     * @apiSuccess {string[]} work_info 章节
     * @apiSuccess {number} work_info.duration 时长
     * @apiSuccess {string} work_info.title   章节标题
     * @apiSuccess {number} work_info.view_num 学习人数
     * @apiSuccess {number} work_info.is_new  是否更新
     *
     * @apiSuccess {string} user 作者
     * @apiSuccess {string} user.headimg 作者头像
     *
     * @apiSuccessExample  Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "code": 200,
     *       "msg" : '成功',
     *       "data":{
     * "id": 34,
     * "subscribe_num": 34234,
     * "title": "每日琨说",
     * "work_info": [
     * {
     * "duration": "06:14",
     * "id": 15,
     * "is_new": 1,
     * "online_time": "2020-08-10 08:10:00",
     * "pid": 34,
     * "rank": 7,
     * "title": "006 | 父母和孩子为什么有沟通障碍？",
     * "view_num": 27426
     * },
     * {
     * "duration": "10:29",
     * "id": 14,
     * "is_new": 0,
     * "online_time": "2020-05-10 08:10:00",
     * "pid": 34,
     * "rank": 6,
     * "title": "005 | 六个字就可以让家族富过三代？",
     * "view_num": 30097
     * }
     * ]
     *
     *     }
     *
     */

    public function recommend(Request $request)
    {
        $version = $request->get('version');
        $os_type = intval($request->get('os_type')) ?? 1;
        $user_id = $this->user['id'] ?? 0;
        $works = new Works();
        $lists = $works->getRecommendWorks(566, $user_id,$os_type,$version);
        return success($lists);
    }

    /**
     * @api {get} api/v4/index/free  免费专区
     * @apiVersion 4.0.0
     * @apiName  free
     * @apiGroup Index
     *
     * @apiSuccess {string} works  课程
     * @apiSuccess {string} works.title 标题
     * @apiSuccess {string} works.subtitle 副标题
     * @apiSuccess {string} works.cover_img 封面
     * @apiSuccess {number} works.is_new  是否为new
     * @apiSuccess {number} works.chapter_num  课程章节数
     * @apiSuccess {string} book  听书
     * @apiSuccess {string} book.title 标题
     * @apiSuccess {string} book.subtitle 副标题
     * @apiSuccess {string} book.cover_img 封面
     * @apiSuccess {number} book.is_new  是否为new
     * @apiSuccess {number} book.chapter_num  听书章节数
     *
     * @apiSuccessExample  Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "code": 200,
     *       "msg" : '成功',
     *       "data":{
     * "works": [
     * {
     * "id": 20,
     * "user_id": 1,
     * "title": "理解孩子行为背后的原因",
     * "subtitle": "",
     * "cover_img": "/wechat/works/video/161627/2017061411282192073.jpg",
     * "is_new": 1,
     * "user": {
     * "id": 1,
     * "nickname": "刘先森"
     * }
     * }
     * ],
     * "book": [
     * {
     * "id": 30,
     * "user_id": 168934,
     * "title": "不要羞辱你的孩子 他的心很脆弱",
     * "subtitle": "家庭教育",
     * "cover_img": "/wechat/works/video/161627/2017061416324725316.jpg",
     * "is_new": 1,
     * "user": {
     * "id": 168934,
     * "nickname": "chandler"
     * }
     * }
     * ]
     * }
     *     }
     *
     */
    public function free()
    {
        $uid = $this->user['id'] ?? 0;
        $work = new Works();
        $lists = $work->getFreeWorks($uid);
        return success($lists);
    }

    /**
     * @api {get} api/v4/index/editor  首页-主编推荐
     * @apiVersion 4.0.0
     * @apiName  editor
     * @apiGroup Index
     *
     * @apiSuccess {string} reason 推荐理由
     * @apiSuccess {string} relation_id   跳转id
     * @apiSuccess {string} relation_type 1.课程 2.听书 3.专栏 4.讲座
     * @apiSuccess {string} works  课程
     * @apiSuccess {string} works.title    标题
     * @apiSuccess {string} works.subtitle 副标题
     * @apiSuccess {string} works.cover_img 封面
     * @apiSuccess {string} works.chapter_num 章节数
     * @apiSuccess {string} works.subscibe_num 学习数
     * @apiSuccess {string} user     用户
     * @apiSuccess {string} user.nickname  用户昵称
     *
     * @apiSuccessExample  Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "code": 200,
     *       "msg" : '成功',
     * "data": [
     * {
     * "id": 17,
     * "relation_id": "16",
     * "relation_type": 1,
     * "reason": "欣赏是一种享受，是一种实实在在的享受",
     * "works": {
     * "id": 16,
     * "user_id": 168934,
     * "title": "如何经营幸福婚姻",
     * "subtitle": "",
     * "cover_img": "/nlsg/works/20190822150244797760.png",
     * "price": "29.90",
     * "chapter_num": 0,
     * "subscribe_num": 287,
     * "user": {
     * "id": 168934,
     * "nickname": "chandler"
     * }
     * }
     * },
     * {
     * "id": 18,
     * "relation_id": "2",
     * "relation_type": 4,
     * "reason": "值得学习",
     * "works": {
     * "id": 2,
     * "user_id": 1,
     * "name": "张宝萍专栏",
     * "title": "国家十百千万工程心灵导师",
     * "subtitle": "心灵导师 直击人心",
     * "cover_pic": "/wechat/works/video/161627/2017121117503851065.jpg",
     * "price": "0.00",
     * "user": {
     * "id": 1,
     * "nickname": "刘先森"
     * }
     * }
     * },
     * {
     * "id": 19,
     * "relation_id": "1",
     * "relation_type": 3,
     * "reason": "很好",
     * "works": {
     * "id": 1,
     * "user_id": 211172,
     * "name": "王琨专栏",
     * "title": "顶尖导师 经营能量",
     * "subtitle": "顶尖导师 经营能量",
     * "cover_pic": "/wechat/works/video/161627/2017121117503851065.jpg",
     * "price": "99.00",
     * "user": {
     * "id": 211172,
     * "nickname": "能量时光"
     * }
     * }
     * },
     * {
     * "id": 21,
     * "relation_id": "18",
     * "relation_type": 2,
     * "reason": "欣赏是一种享受，是一种实实在在的享受",
     * "works": {
     * "id": 18,
     * "user_id": 211172,
     * "title": "如何培养高情商孩子",
     * "subtitle": "",
     * "cover_img": "/wechat/works/video/161910/1639_1525340866.png",
     * "price": "0.00",
     * "chapter_num": 0,
     * "subscribe_num": 0,
     * "user": {
     * "id": 211172,
     * "nickname": "能量时光",
     * "headimg" : "test.png"
     * }
     * }
     * }
     * ]
     *     }
     *
     */
    public function editor()
    {
        $uid = $this->user['id'] ?? 0;
        $recommendModel = new Recommend();
        $lists = $recommendModel->getEditorWorks($uid);
        return $this->success($lists);
    }



    /**
     * @api {get} api/v4/index/camp  首页-训练营
     * @apiVersion 4.0.0
     * @apiName  camp
     * @apiGroup Index
     *
     * @apiSuccess {string} name     名称
     * @apiSuccess {string} title    标题
     * @apiSuccess {string} subtitle 副标题
     * @apiSuccess {number} price    价格
     * @apiSuccess {number} is_new   是否新上架 1是 0 否
     * @apiSuccess {string} cover_pic 封面
     *
     * @apiSuccessExample  Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "code": 200,
     *       "msg" : '成功',
     *       "data":[
     *        ]
     *     }
     *
     */
    public function camp()
    {
        $recommendModel = new Recommend();
        $lists = $recommendModel->getIndexRecommend(13, 1);
        if($lists == false)
            $lists = [];
        return $this->success($lists);
    }




    /**
     * @api {get} api/v4/index/version  版本更新
     * @apiVersion 4.0.0
     * @apiName  version
     * @apiGroup Index
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/index/version
     * @apiParam {string} version  版本号 4.0.0
     *
     * @apiSuccess {string}  content  更新内容
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
    public function version(Request $request)
    {

        $version = $request->get('version');
        $os_type = $request->get('os_type') ?? 1;

        $platform_type = 0;
        if(!empty($os_type) && $os_type==1){
            $platform_type = $request->get('platform_type') ?? 0;
            $platform_type = strtolower($platform_type);  //转小写
        }

        $list = Versions::select('id', 'number', 'content', 'url', 'is_force', 'str_at','down_type')
            ->where('status', 1)
            ->where('os_type', $os_type)
            ->where('platform_type', $platform_type)
            ->orderBy('created_at', 'desc')
            ->first();
        if(empty($list)){
            $list = Versions::select('id', 'number', 'content', 'url', 'is_force', 'str_at','down_type')
                ->where(['status'=> 1,'os_type'=> $os_type,'platform_type'=> 0,])
                ->orderBy('created_at', 'desc')
                ->first();
        }
        if(empty($list)){
            return success(['is_force' => 0]);
        }

        if (!empty($list) && version_compare($version, $list->number, '>=')) {
            //当实际版本大于储存版本号时   默认不更新
            return success(['is_force' => 0]);
        }
        //否则按照数据库进行更新状态
        $list->content = $list->content ? explode('；', $list->content) : '';
        return success($list);


    }

    /**
     * @api {get} api/v4/index/event  商城活动标识
     * @apiVersion 4.0.0
     * @apiName  event
     * @apiGroup Index
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/index/event
     *
     * @apiSuccess {string} pic  图片
     * @apiSuccess {string} url  h5跳转链接
     * @apiSuccess {number} type  1 h5 2 app商品
     * @apiSuccess {number} obj_id  商品id
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
    public function event()
    {
        $list = Banner::select('id', 'title', 'pic', 'h5_url', 'url', 'jump_type', 'obj_id')
            ->where('app_project_type','=',APP_PROJECT_TYPE)
            ->where('type', 54)
            ->first();
        return success($list);

    }
    /**
     * @api {get} api/v4/index/market  首页开屏图
     * @apiVersion 4.0.0
     * @apiName  market
     * @apiGroup Index
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/index/market
     *
     * @apiSuccess {string} pic  图片
     * @apiSuccess {string} url  h5跳转链接
     * @apiSuccess {number} type  1 h5 2 app商品  4精品课 5.讲座 6.听书 7 360  8直播详情  13活动开屏图
     * @apiSuccess {number} obj_id  商品id
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
    public function market(Request $request)
    {
        $bannerModel = new Banner();
        $data = $bannerModel->appPopup(60);

        $data = $bannerModel->CheckBannerVersion($data,$request->get('version') ?? 0);


        $data  = !empty($data) ? $data : new \StdClass();
        return success($data);
        //(1).直播详情   (2).精品课  (3).商品  (4).h5页面  (5).讲座  (6).听书 7 专栏
//        $data = [
//            'id' => 538,
//            'info_id' => 0,
//            'type' => 3,
//            'url' => '',
//            'img' => 'https://image.nlsgapp.com/nlsg/authorpt/20210106142306594001.jpg'
//        ];
//        return success($data);
    }

    /**
     * @api {get} api/v4/index/flash_banner  首页闪屏图
     * @apiVersion 4.0.0
     * @apiName  flash_banner
     * @apiGroup Index
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/index/flash_banner
     *
     * @apiSuccess {string} pic  图片
     * @apiSuccess {string} url  h5跳转链接
     * @apiSuccess {number} type  1 h5 2 app商品  4精品课 5.讲座 6.听书 7 360  8直播详情  13活动开屏图
     * @apiSuccess {number} obj_id  商品id
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
    public function flashBanner(Request $request)
    {
        $bannerModel = new Banner();
        $data = $bannerModel->appPopup(61);
        $data = $bannerModel->CheckBannerVersion($data,$request->get('version') ?? 0);
        $data  = !empty($data) ? $data : new \StdClass();
        return success($data);
    }






    public function share(Request $request)
    {
        $input = $request->all();

        if( !empty($input['tag']) && $input['tag'] == 1 ){  //训练营分享参数
            $res = [
                'title' => '训练营',
                'sub_title' => '训练开营了',
                'share_url' => 'https://wechat.nlsgapp.com/trainingCampList',
                'cove_img' => 'https://image.nlsgapp.com/nlsg/authorpt/20210106142306594001.jpg',
            ];
            return success($res);

        }

        $url = $input['url'] ?? '';
        $config = [
            'app_id' => 'wxe24a425adb5102f6',
            'secret' => '2ded804b74f99ae2f342423dd7952620',
            'response_type' => 'array',
            'cache' => "redis"
        ];
        $app = Factory::officialAccount($config);

        // $accessToken = $app->access_token;
        // $token = $accessToken->getToken();
        // dd($token);
        // 创建缓存实例
        $cache = new RedisAdapter(app('redis')->connection()->client());
        $app->rebind('cache', $cache);
        $app->jssdk->setUrl($url);
        $jssdk = $app->jssdk->buildConfig(['updateAppMessageShareData', 'updateTimelineShareData'], $debug = true, $beta = false, $json = true);
        return success($jssdk);
    }

    public function test()
    {
        Task::pushTo();
    }


    //api/v4/index/config
    public function config(Request $request)
    {
        //分享H5域名
        //图片域名
        $data = [
            'h5_url' => ConfigModel::getData(45),
            'img_url' => ConfigModel::getData(44),
            'nlsg_tel' => ConfigModel::getData(64),
        ];
        return success($data);

    }

    public function tempConfig(Request $request)
    {
        $model = new ConfigModel();
        $res = $model->tempConfig($request->input('id', 0), $this->user['id'] ?? 0);
        return $this->success($res);
    }

    public function kunSaid(Request $request)
    {
        $servers = new StatisticsServers();
        $data = $servers->kunSaid($request->input());
        return $this->getRes($data);
    }

    //极光别名删除，重置绑定
    //http://127.0.0.1:8000/api/v4/jpush/remove_alias
    public function  jpushAlias(Request $request)
    {
        $user_id =  $request->get('user_id');
        $JpushObj=new JpushService();
        $JpushObj->DeleteAlias($user_id);
        return success();
    }

}
