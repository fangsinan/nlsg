<?php

namespace App\Http\Controllers\Admin\V4;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ControllerBackend;
use App\Models\Column;
use App\Models\Live;
use App\Models\Task;
use App\Models\User;
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
            ->paginate(10)
            ->toArray();
        return success($lists);
    }

    public function  send(Request $request)
    {
        $type = $request->get('type');
        $uid  = $request->get('user_id');
        $id   = $request->get('id');
        $title = $request->get('title');
        $send_type    =  $request->get('send_type');
        if ($send_type ==1){
            switch($type){
                case 1:
                   Task::send(1, $uid, $id,0, $title);
                   break;
                case 2:
                   Task::send(2, $uid, $id,0, $title);
                   break;
                case  3:
                   Task::send(3, $uid, $id,0, $title);
                   break;
                case  4:
                   Task::send(4, $uid, $id,0, $title);
                   break;
            }
        } elseif($send_type ==2){
            switch($type){
               case 1:
                  JPush::pushNow('all', $title, ['type'=>1, 'id'=>$id]);
                  break;
               case 2:
                   JPush::pushNow('all', $title, ['type'=>2, 'id'=>$id]);
                  break;
               case  3:
                   JPush::pushNow('all',$title, ['type'=>4, 'id'=>$id]);
                  break;
               case  4:
                   JPush::pushNow('all',$title, ['type'=>5, 'id'=>$id]);
                  break;
           }
        }
    }

    public function getWorks()
    {
        $lists = Works::select('id', 'title')
                      ->where('status', '>', 0)
                      ->where('is_audio_book', 0)
                      ->orderBy('id', 'desc')
                      ->get()
                      ->toArray();
        return $lists;
    }

    public function  getLectures()
    {
        $lists = Column::select('id', 'name')
                   ->where('type', 2)
                   ->where('status', '<>', 3)
                   ->orderBy('created_at', 'desc')
                   ->get()
                   ->toArray();
        return $lists;
    }

    public function getLives()
    {
        $lists = Live::select('id', 'title')
                   ->orderBy('created_at', 'desc')
                   ->get()
                   ->toArray();
        return $lists;
    }

    public function getGoods()
    {
        $lists = MallGoods::select('id','name')
                ->where('status', 2)
                ->orderBy('id','desc')
                ->get()
                ->toArray();
        return $lists;
    }


}
