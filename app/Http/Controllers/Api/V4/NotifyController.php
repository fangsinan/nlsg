<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use App\Models\Notify;
use App\Models\User;
use App\Models\UserFollow;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class NotifyController extends Controller
{

    public function index(Request $request)
    {
        $type =  $request->input('type') ?? 1;
        $lists =  Notify::select('id','from_uid','to_uid','subject','source_id','created_at')
                ->with(
                [
                    'fromUser:id,nickname,intro,headimg',
                    'reply:id,content'
                ])
                ->where(['to_uid'=>$this->user['id'], 'type'=>$type])
                ->orderBy('created_at', 'desc')
                ->paginate(10)
                ->toArray();
        if ($lists['data']){
            foreach ($lists['data'] as &$v) {
                $v['create_time'] =  Carbon::parse($v['created_at'])->diffForHumans();
            }
        }
        return success($lists['data']);
    }



    /**
     * @api {get} api/v4/notify/fans 新增粉丝
     * @apiVersion 4.0.0
     * @apiGroup Api
     *
     * @apiSuccess {String} token   token
     *
     * @apiSuccessExample 成功响应:
     *   {
     *      "code": 200,
     *      "msg" : '成功',
     *      "data": {
     *
     *       }
     *   }
     *
    */
    public function fans()
    {
        User::where('id', 1)->update(['fan_num'=>0]);

        $user  = User::find($this->user['id']);
        $lists = $user->fans()->paginate(10, ['from_uid','to_uid','nickname'])->toArray();
        if ($lists['data']){
            foreach ($lists['data'] as &$v) {
                $v['is_follow'] = UserFollow::where(['from_uid'=>$this->user['id'], 'to_uid'=>$v['from_uid']])->count();
            }
        }
        return  success($lists['data']);
    }

}
