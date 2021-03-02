<?php


namespace App\Http\Controllers\Api\V4;


use App\Http\Controllers\Controller;
use App\Models\MeetingSales;
use App\Models\MeetingSalesBind;
use App\Models\VipUser;
use Illuminate\Http\Request;

class MeetingController  extends Controller
{

    /**
     * 老师当前绑定和二维码信息
     * @api {get} /api/v4/meeting_sales/index 老师当前绑定和二维码信息
     * @apiVersion 4.0.0
     * @apiName /api/v4/meeting_sales/index
     * @apiGroup  会场销售
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/meeting_sales/index
     * @apiDescription 老师当前绑定和二维码信息
     * @apiSuccess {string[]} bind 当前生效的绑定经销商,如果空表示没有
     * @apiSuccess {string} bind.end_at 失效时间
     */
    public function salesIndex(Request $request){
        $model = new MeetingSales();
        $data = $model->salesIndex($request->input(), $this->user['id']??0);
        return $this->getRes($data);
    }

    /**
     * 经销商绑定记录
     * @api {get} /api/v4/meeting_sales/bind_record 经销商绑定记录
     * @apiVersion 4.0.0
     * @apiName /api/v4/meeting_sales/bind_record
     * @apiGroup  会场销售
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/meeting_sales/bind_record
     * @apiDescription 经销商绑定记录
     * @apiParam {number} page 页数
     * @apiParam {number} size 条数
     *
     * @apiSuccess {number} status 状态(1当前生效 2已过期)
     */
    public function bindDealerRecord(Request $request){
        $model = new MeetingSalesBind();
        $data = $model->bindDealerRecord($request->input(), $this->user['id']??0);
        return $this->getRes($data);
    }

    /**
     * 校验经销商电话
     * @api {get} /api/v4/meeting_sales/check_dealer 校验经销商电话
     * @apiVersion 4.0.0
     * @apiName /api/v4/meeting_sales/check_dealer
     * @apiGroup  会场销售
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/meeting_sales/check_dealer
     * @apiDescription 校验经销商电话
     * @apiParam {string} phone 电话
     *
     * @apiSuccess {number} status 状态(1当前生效 2已过期)
     */
    public function checkDealer(Request $request){
        $model = new VipUser();
        $data = $model->checkDealer($request->input('phone',''));
        return $this->getRes($data);
    }

    /**
     * 添加绑定经销商
     * @api {post} /api/v4/meeting_sales/bind_dealer 添加绑定经销商
     * @apiVersion 4.0.0
     * @apiName /api/v4/meeting_sales/bind_dealer
     * @apiGroup  会场销售
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/meeting_sales/bind_dealer
     * @apiDescription 添加绑定经销商
     * @apiParam {string} dealer_phone 经销商账号
     * @apiParam {string} dealer_name 经销商名称
     * @apiParam {string} remark 场次备注
     */
    public function bindDealer(Request $request){
        $model = new MeetingSalesBind();
        $data = $model->bindDealer($request->input(), $this->user['id']??0);
        return $this->getRes($data);
    }

}
