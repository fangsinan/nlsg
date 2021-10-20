<?php

namespace App\Http\Controllers\Admin\V4;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ControllerBackend;
use App\Models\Column;
use App\Models\Live;
use App\Models\Task;
use App\Models\User;
use App\Models\Works;
use App\Models\MallGoods;
use Carbon\Carbon;
use Illuminate\Http\Request;
use JPush;

class TaskController extends ControllerBackend
{
    /**
     * @api {get} api/admin_v4/task/index 任务列表
     * @apiVersion 4.0.0
     * @apiName  task/index
     * @apiGroup 后台-消息任务
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/task/index
     * @apiDescription 任务列表
     *
     * @apiParam {number} page 分页
     * @apiParam {string} type  类型
     * @apiParam {number} status 是否发送
     * @apiParam {string} start  开始时间
     * @apiParam {string} end    结束时间
     *
     * @apiSuccess {string} subject  标题
     * @apiSuccess {string} type     类型
     * @apiSuccess {number}  status  状态
     * @apiSuccess {string}  created_at  创建时间
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
        $start = $request->get('start');
        $end = $request->get('end');
        $query = Task::with('user:id,nickname')
            ->when($start && $end, function ($query) use ($start, $end) {
                $query->whereBetween('created_at', [
                    Carbon::parse($start)->startOfDay()->toDateTimeString(),
                    Carbon::parse($end)->endOfDay()->toDateTimeString(),
                ]);
            });

        $lists = $query->select('id', 'user_id', 'subject', 'type', 'created_at', 'plan_time', 'status')
            ->orderBy('created_at', 'desc')
            ->orderBy('status', 'desc')
            ->paginate(10)
            ->toArray();
        return success($lists);
    }

    public function  send(Request $request)
    {
        $input = $request->all();
        if ($input['send_type'] ==1){
            $user = User::where('phone', $input['phone'])->first();
            if (!$user){
                return error(1000,'没有找到用户');
            }
            Task::send($input['type'], $user->id, $input['id'],0, $input['title']);
        } elseif($input['send_type'] ==2){
            if($input['type']==5 && $input['id']==163){
                JPush::pushNow('all', '②不爱学习的孩子竟然可以对学习上瘾！', ['type'=>$input['type'], 'id'=>$input['id']]);
            }else{
                JPush::pushNow('all', $input['title'], ['type'=>$input['type'], 'id'=>$input['id']]);
            }
        }
        return success();
    }

    public function getWorks()
    {
        $lists = Works::select('id', 'title')
                      ->where('status', 4)
                      ->orderBy('id', 'desc')
                      ->get()
                      ->toArray();
        return success($lists);
    }

    public function  getLectures()
    {
        $lists = Column::select('id', 'name')
                   ->where('type', 2)
                   ->where('status', 1)
                   ->orderBy('created_at', 'desc')
                   ->get()
                   ->toArray();
        return success($lists);
    }

    public function getLives()
    {
        $lists = Live::select('id', 'title')
                   ->where('status', 4)
                   ->orderBy('created_at', 'desc')
                   ->get()
                   ->toArray();
        return success($lists);
    }

    public function getGoods()
    {
        $lists = MallGoods::select('id','name')
                ->where('status', 2)
                ->orderBy('id','desc')
                ->get()
                ->toArray();
        return success($lists);
    }


}
