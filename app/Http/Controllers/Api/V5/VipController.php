<?php


namespace App\Http\Controllers\Api\V5;


use App\Http\Controllers\Controller;
use App\Models\VipUser;
use App\Models\VipUserBind;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VipController extends Controller
{


    /**
     * 会员详情页
     * @api {get} /api/v5/vip/home_page 会员详情页
     * @apiVersion 5.0.0
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
     * @apiSuccess {number} card_data.is_login 当前是否登陆状态(1是 0不是)
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
     * {
     * "code": 200,
     * "msg": "成功",
     * "now": 1605160146,
     * "data": {
     * "card_data": {
     * "nickname": "chandler",
     * "headimg": "https://image.nlsgapp.com/image/202009/13f952e04c720a550193e5655534be86.jpg",
     * "level": 2,
     * "expire_time": "2020-11-20 23:59:59",
     * "surplus_days": 8,
     * "price": "360",
     * "is_open": 1
     * },
     * "author": {
     * "cover_img": "http://image.nlsgapp.com/nlsg/works/20201112134526746289.png",
     * "list": [
     * {
     * "id": 161904,
     * "nickname": "王琨",
     * "headimg": "/wechat/authorpt/wk.png",
     * "intro_for_360": ""
     * }
     * ]
     * },
     * "works_list": {
     * "cover_img": "http://image.nlsgapp.com/nlsg/works/20201112134456641863.png",
     * "list": [
     * {
     * "id": 568,
     * "works_type": 2,
     * "title": "家庭情境教育工具卡",
     * "subtitle": "经历过职场迷茫和彷徨的岁月，了解年轻人心中的情怀和现实之间的差异，所以《优秀的人，都敢对自己下狠手》中，没有无聊的励志和温情的鸡汤，而是真实的打拼和真诚的建议！",
     * "cover_img": "/nlsg/works/20200304023146969654.jpg",
     * "detail_img": "/nlsg/works/20200304023153543701.jpg",
     * "price": "0.00",
     * "type": 1,
     * "column_type": 1
     * }
     * ]
     * },
     * "detail_image": "http://image.nlsgapp.com/nlsg/works/20201110171938316421.png"
     * }
     * }
     */



    public function newHomePage(Request $request)
    {
        $model = new VipUser();
        $data = $model->homePage($this->user, $request->input());
        return $this->getRes($data);
    }

    /**
     * getVipSonList 获取钻石合伙人名下数据
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getVipSonList(Request $request): JsonResponse
    {
        $son = $request->input("phone","");
        $status = $request->input("status",0);   //1保护有效   2保护失效

        $phone = $this->user['phone'];

        $query = VipUserBind::query()
            ->with([
                'SonUser:id,phone,nickname'
            ]);
        $query->where("source",$phone);
        $total = $query->count();
        if(!empty($son)){
            $query->where("son",$son);
        }
        if(!empty($status)){
            $query->where("status",$status);
        }

        //
        // if($status == 2){
        //     $query->where("status",1);
        //     // 永久保护life =1   或者 结束时间在有效范围内
        //     $query->where(function ($query)use($now){
        //         $query->orWhere('life',1);
        //         $query->orWhere("end_at",">=",$now);
        //     });
        // }else if($status == 3){
        //     $query->where("status",2);
        //     $query->where("end_at","<",$now);
        // }

        $data = $query->select("source","son","life","begin_at","end_at","status")
            ->orderBy('status')
            ->orderBy('begin_at', 'desc')
            ->paginate(15)
            ->toArray();

        foreach($data['data'] as &$val){
            $val['end_at'] = date('Y.m.d',strtotime($val['end_at']));
        }
        return $this->getRes([
            "data" => $data['data'],
            "total" => $total??0,
        ]);

    }



}
