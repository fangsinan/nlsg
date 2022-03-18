<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\ConfigModel;
use App\Models\Live;
use App\Models\Recommend;
use App\Models\RecommendConfig;
use App\Models\Works;
use Illuminate\Http\Request;

class IndexController extends Controller
{

    /**
     * @api {get} api/v5/index/get_top_img  各个列表头图
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
     * @api {get} api/v5/index/index_position  首页位置API
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
    public function indexPosition(Request $request)
    {

        $os_type = $request->get('os_type') ?? 1;
        $version = $request->get('version') ?? 1;
        $version_show = [0,1];
        $config_version = ConfigModel::getData(52);
        if($os_type == 2 && $version == $config_version){
            $version_show = [0];
        }


        $filed = ["id","title","icon_pic","jump_type","modular_type","sort","jump_url",'icon_mark','icon_mark_rang','lists_id'];

        $head   = RecommendConfig::select($filed)->where(['show_position'=>1, 'is_show'=>1, ])->whereIn('version_show',$version_show)->OrderBy("sort",'asc')->get()->toArray();
        $bottom = RecommendConfig::select($filed)->where(['show_position'=>3, 'is_show'=>1, ])->whereIn('version_show',$version_show)->OrderBy("sort",'asc')->get()->toArray();
        $icon   = RecommendConfig::select($filed)->where(['show_position'=>2, 'is_show'=>1, ])->whereIn('version_show',$version_show)->OrderBy("sort",'asc')->get()->toArray();
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
     * @api {get} api/v5/index/index_middle  首页   大咖主讲人-精品专题-热门榜单【合并】
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
     * @api {get} api/v5/index/lives  首页-直播推荐
     * @apiVersion 5.0.0
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
       // 3月18日 直播更改显示当天最新的直播  时间不显示 时分秒   因为推荐直播时间不确定
        // $list = Live::select('id', 'title', 'describe', 'cover_img', 'begin_at', 'end_at', 'price', 'order_num',
        //     'is_free', 'helper')
        //     ->where('begin_at', '>=', date("Y-m-d"))
        //     ->where('end_at',   '<=', date('Y-m-d',strtotime('+1 day')))
        //     ->where('is_del', 0)
        //     ->where('is_show', 1)
        //     ->where('is_test', 0)
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
     * @api {get} api/v5/index/index_works  首页-精选课程-主题课程模块-专题课
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
        $res['theme_works'] = $recommendModel->getIndexRecommend(2, 36);
        $res['work_lists'] = $recommendModel->getIndexRecommend(2, 44);
        //主题课程模块
        //专题课模块
        $res['special_works'] = $recommendModel->getIndexRecommend(2, 39);

        return $this->success($res);
    }


    /**
     * @api {get} api/v5/index/recommend  首页-每日琨说
     * @apiVersion 5.0.0
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

}
