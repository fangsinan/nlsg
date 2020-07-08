<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserFollow;
use Illuminate\Http\Request;

class NotifyController extends Controller
{

    public function index()
    {


    }



    /**
     * @api {get} api/v4/notify/fans 新增粉丝
     * @apiVersion 4.0
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

        $user  = User::findOrFail(1);
        $lists = $user->fans()->paginate(10, ['from_uid','to_uid','nickname'])->toArray();
        if ($lists['data']){
            foreach ($lists['data'] as &$v) {
                $v['is_follow'] = UserFollow::where(['from_uid'=>1, 'to_uid'=>$v['from_uid']])->count();
            }
        }
        return  success($lists);
    }

}
