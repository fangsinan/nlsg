<?php
namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use App\Models\Announce;
use App\Models\Banner;
use App\Models\Live;
use App\Models\Recommend;

class IndexController extends Controller
{

    public function index()
    {
        return  'hello world';
    }

    /**
     * @api {post} api/v4/index/announce  获取首页公告
     * @apiVersion 1.0.0
     * @apiName  announce
     * @apiGroup Index
     *
     */
    public function announce()
    {
        $list = Announce::select('id','content')
            ->first()->toArray();
        return $this->success($list);
    }
    /**
     * @api {post} api/v4/index/banner  获取首页banner
     * @apiVersion 1.0.0
     * @apiName Banner
     * @apiGroup Index
     *
     * @apiSuccessExample Success-Response:
     * {
    "code": 200,
    "msg": "成功",
    "data": [
    {
    "id": 274,
    "pic": "https://image.nlsgapp.com/nlsg/banner/20191118184425289911.jpg",
    "title": "电商弹窗课程日历套装",
    "url": "/mall/shop-detailsgoods_id=448&time=201911091925"
    },
    {
    "id": 296,
    "pic": "https://image.nlsgapp.com/nlsg/banner/20191227171346601666.jpg",
    "title": "心里学",
    "url": "/mall/shop-details?goods_id=479"
    }
    ]
    }
     */
    public function banner()
    {
        $bannerModel = new Banner();
        $lists = $bannerModel->getIndexBanner();
        return $this->success($lists);
    }

    /**
     * @api {post} api/v4/index/live 首页-直播推荐
     * @apiVersion 1.0.0
     * @apiName  live
     * @apiGroup Index
     *
     * @apiSuccessExample Success-Response:
     * {
        "code": 200,
        "msg": "成功",
        "data": [
        {
        "id": 274,
        "pic": "https://image.nlsgapp.com/nlsg/banner/20191118184425289911.jpg",
        "title": "电商弹窗课程日历套装",
        "url": "/mall/shop-detailsgoods_id=448&time=201911091925"
        },
        {
        "id": 296,
        "pic": "https://image.nlsgapp.com/nlsg/banner/20191227171346601666.jpg",
        "title": "心里学",
        "url": "/mall/shop-details?goods_id=479"
        }
        ]
     }
     */

    public function live()
    {
        $recommendModel = new Recommend();
        $lists = $recommendModel->getIndexRecommend(7, 1);
        return $this->success($lists);
    }

     /**
      * @api {post} api/v4/index/column 首页-直播推荐
      * @apiVersion 1.0.0
      * @apiName  column
      * @apiGroup Index
      *
      * @apiSuccessExample Success-Response:
      * {
     "code": 200,
     "msg": "成功",
     "data": [
     {
     "id": 1,
     "name": "王琨专栏",
     "title": "顶尖导师 经营能量",
     "subtitle": "顶尖导师 经营能量",
     "message": "",
     "price": "0.00",
     "cover_pic": "/wechat/works/video/161627/2017121117503851065.jpg"
     },
     {
     "id": 3,
     "name": "王复燕专栏",
     "title": "高级家庭教育指导师",
     "subtitle": "测试",
     "message": "",
     "price": "0.00",
     "cover_pic": "/wechat/works/video/161627/2017121117503851065.jpg"
     }
     ]
     }
      */
    public function column()
    {
        $recommendModel = new Recommend();
        $lists = $recommendModel->getIndexRecommend(1, 1);
        return $this->success($lists);
    }
    /**
     * @api {post} api/v4/index/column 首页-直播推荐
     * @apiVersion 1.0.0
     * @apiName  column
     * @apiGroup Index
     *
     * @apiSuccessExample Success-Response:
     * {
    "code": 200,
    "msg": "成功",
    "data": [
    {
    "id": 16,
    "user_id": 168934,
    "title": "如何经营幸福婚姻",
    "cover_img": "/nlsg/works/20190822150244797760.png",
    "subtitle": "",
    "price": "29.90",
    "user": {
    "id": 168934,
    "username": "18624078563"
    }
    },
    {
    "id": 18,
    "user_id": 161910,
    "title": "如何培养高情商孩子",
    "cover_img": "/wechat/works/video/161910/1639_1525340866.png",
    "subtitle": "",
    "price": "0.00",
    "user": null
    },
    {
    "id": 19,
    "user_id": 161911,
    "title": "如何走进孩子的内心",
    "cover_img": "/wechat/works/video/161911/9129_1525340984.png",
    "subtitle": "",
    "price": "99.00",
    "user": null
    }
    ]
    }
     */

    public function works()
    {
        $recommendModel = new Recommend();
        $lists = $recommendModel->getIndexRecommend(2, 1);
        return $this->success($lists);
    }

}
