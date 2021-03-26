<?php

namespace App\Http\Controllers\Live\V4;

use App\Http\Controllers\Controller;
use App\Models\Live;
use App\Models\Order;
use App\Models\Subscribe;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubscribeController extends Controller
{

    /**
     * @api {get} api/live_v4/sub/index 预约列表
     * @apiVersion 4.0.0
     * @apiName  sub/index
     * @apiGroup 直播后台-评论列表
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/live_v4/sub/index
     * @apiDescription  预约列表
     *
     * @apiParam {number} page      分页
     * @apiParam {string} ordernum  订单号
     * @apiParam {string} title     直播标题
     * @apiParam {string} phone     用户账号
     * @apiParam {string} date      格式化时间
     *
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
    public function index(Request $request)
    {
        $title = $request->get('title') ?? '';
        $ordernum = $request->get('ordernum') ?? '';
        $phone = $request->get('phone') ?? '';
        $date = $request->get('date') ?? '';

        $query = Subscribe::with(['user:id,nickname,phone', 'live:id,title', 'order:id,ordernum,pay_time']);
        if(!empty($phone)){
            $query->whereHas('user', function ($q) use($phone){
                $q->where('phone', $phone);
            });
        }
        if(!empty($title)){
            $query->whereHas('live', function ($q) use($title){
                $q->where('title', 'like', '%'.$title.'%');
            });
        }
        if(!empty($ordernum)){
            $query->whereHas('order', function ($q) use($ordernum){
                $q->where('ordernum', $ordernum);
            });
        }
        if(!empty($date)){
            $query->whereHas('order', function ($q) use($date){
                $q->where('pay_time','>=', $date[0]);
                $q->where('pay_time','<', $date[1]);
            });
        }

        $lists = $query->select('id', 'type', 'user_id', 'relation_id', 'pay_time','order_id')
            ->where('is_del',0)
            ->where('status',1)
            ->where('type',3)
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->toArray();

        return success($lists);

    }
}
