<?php

namespace App\Http\Controllers\Live\V4;

use App\Http\Controllers\ControllerBackend;

use App\Http\Controllers\Controller;
use App\Models\Live;
use Illuminate\Http\Request;

class IndexController extends ControllerBackend
{
    public function index()
    {
        echo '直播后台首页';
    }

    /**
     * @api {get} api/live_v4/index/lives 直播列表
     * @apiVersion 4.0.0
     * @apiName  index/lives
     * @apiGroup 直播后台-直播列表
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/live_v4/index/lives
     * @apiDescription  直播列表
     *
     * @apiParam {number} page 分页
     * @apiParam {string} title 名称
     * @apiParam {number} status   0 待支付  1已支付
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
    public function lives(Request $request)
    {
        $title  = $request->get('title');
        $status = $request->get('status');
        $query = Live::with('user:id,nickname')
            ->when($title, function ($query) use ($title) {
                $query->where('title', 'like', '%'.$title.'%');
            })
            ->when(! is_null($status), function ($query) use ($status) {
               $query->where('status', $status);
            });
        $lists = $query->select('id', 'user_id', 'title', 'price',
            'order_num', 'status', 'created_at')
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->toArray();
        return success($lists);
    }

    

}
