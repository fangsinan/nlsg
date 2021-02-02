<?php

namespace App\Http\Controllers\Admin\V4;

use App\Http\Controllers\Controller;
use App\Models\Column;
use App\Models\Live;
use App\Models\LivePush;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LiveController extends Controller
{

    /**
     * @api {get} api/admin_v4/live/index 直播列表
     * @apiVersion 4.0.0
     * @apiName   live/index
     * @apiGroup 后台-直播列表
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/live/index
     * @apiDescription 直播列表
     *
     * @apiParam {number} page 分页
     *
     * @apiSuccess {string} title    标题
     * @apiSuccess {array}  price    价格
     * @apiSuccess {number} is_finish 是否结束  1 是0 否
     * @apiSuccess {number} status    直播状态 1:待审核  2:已取消 3:已驳回  4:通过
     * @apiSuccess {number} created_at  创建时间
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
    public function index()
    {
        $lists = Live::select('id', 'user_id', 'title', 'price', 'is_finish', 'finished_at', 'status', 'created_at')
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->toArray();
        return success($lists);
    }


    /**
     * 审核直播
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\JsonResponsew
     */
    public function pass(Request $request)
    {
        $id = $request->get('id');
        $res = Live::where('id', $id)
            ->update([
                'status'     => 4,
                'check_time' => date('Y-m-d H:i:s'),
            ]);
        if ($res) {
            return success();
        }
        return error(1000, '审核失败');
    }


    public function push(Request $request)
    {
        $live_id  = $request->get('live_id');
        $query = LivePush::when($live_id, function ($query) use ($live_id) {
                $query->where('live_id', $live_id);
            });

        $lists = $query->select('id', 'live_id', 'push_type', 'push_gid', 'is_push', 'is_done', 'push_at')
            ->orderBy('push_at', 'desc')
            ->paginate(10)
            ->toArray();
        if ($lists){
            $push = LivePush::parsePushList($lists['data']);
        }
        return success($push);
    }
}
