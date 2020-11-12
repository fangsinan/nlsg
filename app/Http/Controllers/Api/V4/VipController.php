<?php


namespace App\Http\Controllers\Api\V4;


use App\Http\Controllers\Controller;
use App\Models\VipRedeemCode;
use App\Models\VipRedeemUser;
use App\Models\VipUser;
use Illuminate\Http\Request;

class VipController extends Controller
{
    /**
     * 兑换券列表和详情
     * @api {get} /api/v4/vip/code_list 兑换券列表和详情
     * @apiVersion 4.0.0
     * @apiName /api/v4/vip/code_list
     * @apiGroup  360会员
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/vip/code_list
     * @apiDescription 兑换券和详情
     * @apiParam {number} [id] 如果传id,就是单条
     * @apiParam {number=1,2,3,4,5} flag 状态(1未使用 2已使用 3赠送中 4已送出 5已使用加已送出)
     * @apiParam {string} [ob] 排序(t_asc时间正序,t_desc时间逆序)
     *
     * @apiSuccess {number} id 记录id
     * @apiSuccess {number} redeem_code_id 兑换码id
     * @apiSuccess {number=1,2,3,4} status 状态(1未使用 2已使用 3赠送中 4已送出)
     * @apiSuccess {string} price 价格
     * @apiSuccess {string} [qr_code] 二维码(完整url,当指定id且状态为1未使用时返回)
     * @apiSuccess {string[]} code_info 详情
     * @apiSuccess {number} code_info.name 兑换券名称
     * @apiSuccess {number} code_info.number 兑换券编码
     * @apiSuccess {string[]} user_info 用户详情
     * @apiSuccess {string[]} statistics 生成配额
     * @apiSuccess {number} statistics.can_use 可用配额
     * @apiSuccessExample {json} Request-Example:
     *
     * {
     * "code": 200,
     * "msg": "成功",
     * "now": 1604988837,
     * "data": [
     * {
     * "id": 10,
     * "redeem_code_id": 10,
     * "status": 1,
     * "created_at": "2020-09-22 12:18:06",
     * "price": 360,
     * "code_info": {
     * "id": 10,
     * "name": "360幸福大使",
     * "number": "20265016893400009"
     * }
     * }
     * ]
     * }
     */
    public function redeemCodeList(Request $request)
    {
        $model = new VipRedeemUser();
        $data = $model->list($this->user, $request->input());
        return $this->getRes($data);
    }

    /**
     * 赠送兑换券
     * @api {put} /api/v4/vip/code_send 赠送兑换券
     * @apiVersion 4.0.0
     * @apiName /api/v4/vip/code_send
     * @apiGroup  360会员
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/vip/code_send
     * @apiDescription 赠送兑换券
     * @apiParam {number} id 记录id
     */
    public function redeemCodeSend(Request $request)
    {
        $model = new VipRedeemUser();
        $data = $model->send($this->user, $request->input());
        return $this->getRes($data);
    }

    /**
     * 取消赠送兑换券
     * @api {put} /api/v4/vip/code_take_back 取消赠送兑换券
     * @apiVersion 4.0.0
     * @apiName /api/v4/vip/code_take_back
     * @apiGroup  360会员
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/vip/code_take_back
     * @apiDescription 取消赠送兑换券
     * @apiParam {number} id 记录id
     */
    public function redeemCodeTakeBack(Request $request)
    {
        $model = new VipRedeemUser();
        $data = $model->takeBack($this->user, $request->input());
        return $this->getRes($data);
    }

    /**
     * 领取兑换券
     * @api {post} /api/v4/vip/code_get 领取兑换券
     * @apiVersion 4.0.0
     * @apiName /api/v4/vip/code_get
     * @apiGroup  360会员
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/vip/code_get
     * @apiDescription 领取兑换券
     * @apiParam {number} id 记录id
     */
    public function redeemCodeGet(Request $request)
    {
        $model = new VipRedeemUser();
        $data = $model->get($this->user, $request->input());
        return $this->getRes($data);
    }

    /**
     * 使用兑换券
     * @api {put} /api/v4/vip/code_use 使用兑换券
     * @apiVersion 4.0.0
     * @apiName /api/v4/vip/code_use
     * @apiGroup  360会员
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/vip/code_use
     * @apiDescription 使用兑换券
     * @apiParam {number} id 记录id
     */
    public function redeemCodeUse(Request $request)
    {
        $model = new VipRedeemUser();
        $data = $model->use($this->user, $request->input());
        return $this->getRes($data);
    }

    /**
     * 生成兑换券
     * @api {post} /api/v4/vip/code_create 生成兑换券
     * @apiVersion 4.0.0
     * @apiName /api/v4/vip/code_create
     * @apiGroup  360会员
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/vip/code_create
     * @apiDescription 生成兑换券
     * @apiParam {number} num 数量
     */
    public function redeemCodeCreate(Request $request)
    {
        $model = new VipRedeemCode();
        $data = $model->create($this->user, $request->input());
        return $this->getRes($data);
    }

    /**
     * 会员详情页
     * @api {get} /api/v4/vip/home_page 会员详情页
     * @apiVersion 4.0.0
     * @apiName /api/v4/vip/home_page
     * @apiGroup  360会员
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/vip/home_page
     * @apiDescription 会员详情页
     *
     * @apiSuccess {string[]} card_data
     * @apiSuccess {string} card_data.nickname 昵称
     * @apiSuccess {string} card_data.headimg 头像
     * @apiSuccess {number} card_data.level 级别(1:360 2:钻石)
     * @apiSuccess {string} card_data.expire_time 到期时间
     * @apiSuccess {string} card_data.surplus_days 剩余天数
     * @apiSuccess {string} card_data.price 价钱
     * @apiSuccess {number} card_data.is_open 当前是否开通360(1开了 0没开)
     *
     * @apiSuccess {string[]} author 讲师
     * @apiSuccess {string[]} works_list 课程列表
     * @apiSuccess {string[]} works_list.list 课程列表
     * @apiSuccess {number} works_list.list.works_type 课程类型(1 视频 2音频)
     * @apiSuccess {number} works_list.list.type 类型(1专栏  2讲座)
     * @apiSuccess {number} works_list.list.column_type 专栏类型(1多课程   2单个课程)
     * @apiSuccess {string} detail_image 详情长图
     *
     * @apiSuccessExample {json} Request-Example:
     *
    {
    "code": 200,
    "msg": "成功",
    "now": 1605160146,
    "data": {
    "card_data": {
    "nickname": "chandler",
    "headimg": "https://image.nlsgapp.com/image/202009/13f952e04c720a550193e5655534be86.jpg",
    "level": 2,
    "expire_time": "2020-11-20 23:59:59",
    "surplus_days": 8,
    "price": "360",
    "is_open": 1
    },
    "author": {
    "cover_img": "http://image.nlsgapp.com/nlsg/works/20201112134526746289.png",
    "list": [
    {
    "id": 161904,
    "nickname": "王琨",
    "headimg": "/wechat/authorpt/wk.png",
    "intro_for_360": ""
    }
    ]
    },
    "works_list": {
    "cover_img": "http://image.nlsgapp.com/nlsg/works/20201112134456641863.png",
    "list": [
    {
    "id": 568,
    "works_type": 2,
    "title": "家庭情境教育工具卡",
    "subtitle": "经历过职场迷茫和彷徨的岁月，了解年轻人心中的情怀和现实之间的差异，所以《优秀的人，都敢对自己下狠手》中，没有无聊的励志和温情的鸡汤，而是真实的打拼和真诚的建议！",
    "cover_img": "/nlsg/works/20200304023146969654.jpg",
    "detail_img": "/nlsg/works/20200304023153543701.jpg",
    "price": "0.00",
    "type": 1,
    "column_type": 1
    }
    ]
    },
    "detail_image": "http://image.nlsgapp.com/nlsg/works/20201110171938316421.png"
    }
    }
     */
    public function homePage(Request $request)
    {
        $model = new VipUser();
        $data = $model->homePage($this->user, $request->input());
        return $this->getRes($data);
    }

}
