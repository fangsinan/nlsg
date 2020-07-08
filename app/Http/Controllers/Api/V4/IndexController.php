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
     * @apiVersion 4.0.0
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
     /**
      * @api {post} api/v4/index/book 首页-听书推荐
      * @apiVersion 1.0.0
      * @apiName  book
      * @apiGroup Index
      *
      * @apiSuccessExample Success-Response:
      * // 20200609161832
     // http://v4.com/api/v4/index/book

     {
     "code": 200,
     "msg": "成功",
     "data": [
     {
     "id": 1,
     "title": "世界名著必读，历经岁月经典依旧陪伴成长",
     "subtitle": "强烈推荐",
     "cover": "/wechat/works/video/161627/2017121117503851065.jpg",
     "created_at": "2020-06-08T10:00:00.000000Z",
     "updated_at": "2020-06-08T10:00:00.000000Z",
     "status": 1,
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
     "nick_name": "chandler_v4",
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
     "nick_name": "chandler_v4",
     "headimg": "/wechat/works/headimg/3833/2017110823004219451.png"
     }
     }
     ]
     }
     ]
     }
      */

    public function book()
    {
        $recommendModel = new Recommend();
        $lists = $recommendModel->getIndexRecommend(4, 1);
        return $this->success($lists);
    }

    /**
     * @api {post} api/v4/index/goods 首页-好物推荐
     * @apiVersion 1.0.0
     * @apiName  goods
     * @apiGroup Index
     *
     * @apiSuccessExample Success-Response:
     * {
    "code": 200,
    "msg": "成功",
    "data": [
    {
    "id": 58,
    "name": "得力 儿童益智绘画套装",
    "picture": "/wechat/mall/mall/goods/7587_1520590551.png",
    "original_price": "90.00"
    },
    {
    "id": 59,
    "name": "Novomill e-Button纽扣便携蓝牙音箱",
    "picture": "/wechat/mall/mall/goods/3785_1519697155.png",
    "original_price": "298.00"
    }
    ]
    }
     */
    public  function goods()
    {
        $recommendModel = new Recommend();
        $lists = $recommendModel->getIndexRecommend(8, 1);
        return $this->success($lists);
    }

    /**
     * @api {post} api/v4/index/wiki 首页-百科推荐
     * @apiVersion 1.0.0
     * @apiName  wiki
     * @apiGroup Index
     *
     * @apiSuccessExample Success-Response:
     * {
    "code": 200,
    "msg": "成功",
    "data": [
    {
    "id": 1,
    "name": "室内空气污染对孩子的危害",
    "content": "社会的进步，工业的发展，导致污染越来越严重，触目惊心",
    "cover": "",
    "view_num": 10,
    "like_num": 2,
    "comment_num": 5
    },
    {
    "id": 2,
    "name": "世界名著必读岁月经典",
    "content": "每个时代都有极其红极广受好评",
    "cover": "",
    "view_num": 5,
    "like_num": 6,
    "comment_num": 5
    }
    ]
    }
     */
    public function wiki()
    {
        $recommendModel = new Recommend();
        $lists = $recommendModel->getIndexRecommend(5, 1);
        return $this->success($lists);
    }

}
