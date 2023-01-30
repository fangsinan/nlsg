<?php

namespace App\Http\Controllers\Admin\V4;

use App\Http\Controllers\Controller;
use App\Models\Column;
use App\Models\Live;
use App\Models\LiveConsole;
use App\Models\LiveInfo;
use App\Models\LivePush;
use App\Models\Wiki;
use App\Models\WorksInfo;
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
            ->where('status', 1)
            ->where('app_project_type','=',APP_PROJECT_TYPE)
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

    public  function  create(Request $request)
    {
        $id     = $request->get('id');
        $type   = $request->get('type');
        $relationId = $request->get('relation_id');
        $liveId = $request->get('live_id');

        $data = [
            'live_id'   => $liveId,
            'push_type' => $type,
            'push_gid'  => $relationId,
            'is_push'   => 1,
            'is_done'   => 1,
            'push_at'   => date('Y-m-d H:i:s', time()),
            'done_at'   => date('Y-m-d H:i:s', time())
        ];
        if (!empty($id)) {
            LivePush::where('id', $id)->update($data);
        } else {
            LivePush::create($data);
        }
        return success();
    }

    public  function delete(Request $request)
    {
        $id = $request->get('id');
        $res = LivePush::where('id', $id)->delete();
        if ($res){
            return success('删除成功');
        }

        return error(1000,'删除失败');
    }

    public function begin(Request $request){
        $model = new LiveConsole();
        $res = $model->begin($request->input());
        return $this->getRes($res);
    }





    /**
     * 直播自动拉流任务  /api/admin_v4/live/live_url_edit?type=show&live_info_id=760
     */
    public function livePushUrlCreate(Request $request){

        $type = $request->input("type") ?? '';
        $live_info_id = $request->input("live_info_id") ?? '';


        $res = LiveInfo::liveUrlEdit($type,$live_info_id);
        if($res['code'] == 0){
            return error($res['code'], $res['msg'],$res['data']);
        }else{
            return $this->success($res['data']);
        }



    }
}
