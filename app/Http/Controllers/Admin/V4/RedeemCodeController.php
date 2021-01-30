<?php


namespace App\Http\Controllers\Admin\V4;


use App\Http\Controllers\ControllerBackend;
use App\Servers\RedeemCodeServers;
use Illuminate\Http\Request;

class RedeemCodeController extends ControllerBackend
{
    /**
     * @api {post} api/admin_v4/redeem_code/create 创建兑换码
     * @apiVersion 4.0.0
     * @apiName   redeem_code/create
     * @apiGroup 后台-课程兑换码
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/redeem_code/create
     * @apiDescription 生成课程讲座兑换码
     *
     * @apiSuccess {number=2,3} redeem_type  兑换类型2是课程3是讲座
     * @apiSuccess {number}  goods_id  目标id
     * @apiSuccess {number} number 生成数量,一次最多1000
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
    public function create(Request $request){
        $servers = new RedeemCodeServers();
        $data = $servers->create($request->input(),$this->user['id']??0);
        return $this->getRes($data);
    }
}
