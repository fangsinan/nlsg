<?php
namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use App\Models\Announce;
use App\Models\Banner;
use App\Models\Live;
use App\Models\Recommend;

class IndexController extends Controller
{

    /**
     * @api {get} api/v4/index/index 首页-公告
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
        $list = Announce::select('id','content')
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
     *        ]
     *     }
     *
     */

    public function banner()
    {
        $bannerModel = new Banner();
        $lists = $bannerModel->getIndexBanner();
        return $this->success($lists);
    }


    /**
     * @api {get} api/v4/index/live  首页-直播推荐
     * @apiVersion 4.0.0
     * @apiName  live
     * @apiGroup Index
     *
     * @apiSuccess {string} title       标题
     * @apiSuccess {string} describe    描述
     * @apiSuccess {string} cover_img   封面
     * @apiSuccess {string} live_status 直播状态
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
    public function live()
    {
        $recommendModel = new Recommend();
        $lists = $recommendModel->getIndexRecommend(7, 1);
        return $this->success($lists);
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
     *                   }
     *                }
     *       ]
     *     }
     *
     */
    public function works()
    {
        $recommendModel = new Recommend();
        $lists = $recommendModel->getIndexRecommend(2, 1);
        return $this->success($lists);
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
     * @apiSuccess {string} works 听书作品
     * @apiSuccess {string}   works.title  作品标题
     * @apiSuccess {string}   works.cover_img  作品封面
     * @apiSuccess {string} user  作品用户
     * @apiSuccess {string} user  作品用户
     * @apiSuccess {string} user.id  用户id
     * @apiSuccess {string} user.nickname  用户昵称
     * @apiSuccess {string} user.headimg   用户头像
     *
     * @apiSuccessExample  Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "code": 200,
     *       "msg" : '成功',
     *       "data":[
                 {
                    "id": 1,
                    "title": "世界名著必读，历经岁月经典依旧陪伴成长",
                    "subtitle": "强烈推荐",
                    "cover": "/wechat/works/video/161627/2017121117503851065.jpg",
                    "num"  :  5,
                    "works": [
                        {
                            "user_id": 168934,
                            "title": "不要羞辱你的孩子 他的心很脆弱",
                            "cover_img": "/wechat/works/video/161627/2017061416324725316.jpg",
                            "pivot": {
                                "lists_id": 1,
                                "works_id": 30
                            },
                            "user": {
                                "id": 168934,
                                "nickname": "chandler",
                                "headimg": "/wechat/works/headimg/3833/2017110823004219451.png"
                            }
                        },
                        {
                            "user_id": 168934,
                            "title": "小孩子做噩梦怎么办？九成父母都没当回事",
                            "cover_img": "/wechat/works/video/161627/2017061416393315731.jpg",
                            "pivot": {
                                "lists_id": 1,
                                "works_id": 31
                            },
                            "user": {
                                "id": 168934,
                                "nickname": "chandler",
                                "headimg": "/wechat/works/headimg/3833/2017110823004219451.png"
                            }
                        }
                    ]
                 }
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
    public  function goods()
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

}
