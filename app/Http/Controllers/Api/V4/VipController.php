<?php


namespace App\Http\Controllers\Api\V4;


use App\Http\Controllers\Controller;
use App\Models\VipRedeemCode;
use App\Models\VipRedeemUser;
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
     * @apiSuccess {string} [qr_code] 二维码(base64,当指定id且状态为3赠送中时返回)
     * @apiSuccess {string[]} code_info 详情
     * @apiSuccess {number} code_info.name 兑换券名称
     * @apiSuccess {number} code_info.number 兑换券编码
     * @apiSuccess {string[]} user_info 用户详情
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


    //领取兑换券
    public function redeemCodeGet(){

    }

    //使用兑换券
    public function redeemCodeUse(Request $request)
    {
        $model = new VipRedeemUser();
        $data = $model->use($this->user, $request->input());
        return $this->getRes($data);
    }

    //生成兑换券
    public function redeemCodeCreate(Request $request)
    {
        $model = new VipRedeemCode();
        $data = $model->create($this->user, $request->input());
        return $this->getRes($data);
    }

    //会员详情页
    public function homePage(Request $request)
    {

    }

}
