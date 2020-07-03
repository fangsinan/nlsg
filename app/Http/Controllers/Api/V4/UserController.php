<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use GuzzleHttp\Client;
use JWTAuth;
use App\Models\User;
use App\Models\FeedBack;
use App\Models\UserFollow;

class UserController extends Controller
{
    public function __construct()
    {
//        $this->user = auth('api')->user();
    }

    /**
     * @api {get} api/v4/user/index 个人主页
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
     *          'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ'
     *       }
     *   }
     *
    */
    public function index()
    {
        $user = User::select('id','nickname','headimg', 'headcover', 'intro','follow_num', 'fan_num','is_teacher')
            ->findOrFail(1);

        if ($user['is_teacher'] ==1){
            $user->works  =  $user
                    ->select('id','nickname')
                    ->with(['works'=>function($query){
                        $query->select('user_id','title','cover_img','subscribe_num', 'original_price')
                            ->where('is_audio_book', 0);
                    }])
                    ->first();
            $user->column =  $user
                    ->select('id','nickname')
                    ->with(['columns'=>function($query){
                        $query->select('user_id','name','title','subtitle', 'original_price');
                    }])
                    ->first();
        }

        return  $this->success($user);
    }

    /**
     * @api {get} api/v4/user/follow 关注
     * @apiVersion 4.0
     * @apiName  from_uid 用户id
     * @apiName  to_uid   被关注用户id
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
    public function  followed(Request $request)
    {
        $input = $request->all();

        $list = UserFollow::where([
                    'from_uid' => 1 ,
                    'to_uid'   => $input['to_uid']
                ])->first();

        if($list){
            return error(1000, '不要重复关注');
        }

        UserFollow::create([
            'from_uid' => 1,
            'to_uid'   => $input['to_uid']
        ]);

        User::where('id', $input['to_uid'])->increment('fan_num');

        return $this->success();
    }
    /**
     * @api {get} api/v4/user/unfollow 取消关注
     * @apiVersion 4.0
     * @apiName  to_uid  被关注用户id
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
    public function unfollow(Request $request)
    {
        $follow = UserFollow::where([
            'from_uid' =>1 ,
            'to_uid'   =>2
        ])->first();

        if (!$follow->delete()){
            return $this->error(1000,'取消失败');
        }
        return $this->success();
    }

    /**
     * @api {get} api/v4/user/base 基本资料
     * @apiVersion 4.0
     * @apiName  nickname 昵称
     * @apiName  headimg  头像
     * @apiName  sex  性别
     * @apiName  intro  简介
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
    public function base(Request $request)
    {
        $input = $request->all();
        if (!$input['nickname']){
            return  $this->error(1000,'昵称不能为空');
        }
        $res = User::where('id', 1)->update([
            'nickname' => $input['nickname'],
            'headimg'  => $input['headimg'],
            'sex'      => $input['sex'],
            'intro'    => $input['intro']
        ]);
        if ($res){
            return $this->success();
        }

    }


    /**
     * @api {get} api/v4/user/feedback 我要吐槽
     * @apiVersion 4.0
     * @apiParam {string} type 10:使用建议 11:内容漏缺 12:购物相关 13:物流配送 14:客服体验 15:节约相关
     * @apiParam {string} content 内容  不能大于200字
     * @apiParam {string} pic  图片url(数组格式)
     * @apiGroup Api
     *
     * @apiSuccess {String} token   token
     *
     * @apiSuccessExample 成功响应:
     *   {
     *      "code": 200,
     *      "msg" : '成功',
     *      "data": []
     *   }
     *
    */
    public function feedback(Request $request)
    {
        $input = $request->all();
        if (!$input['content']){
            return $this->error(1000, '描述不能为空');
        }

//        $pics  = implode(',', $input['pic']);
//        if (count($pics) > 3) {
//            return $this->error(1000,'图片过多');
//        }
        $res = FeedBack::create([
            'type'      => $input['type'],
            'user_id'   => 1,
            'content'   => $input['content'],
            'pic'       => $input['pic']
        ]);
        if ($res){
            return $this->success();
        }

    }
    /**
     * @api {get} api/v4/user/fan 我关注的
     * @apiVersion 4.0
     * @apiGroup Api
     *
     * @apiSuccess {String}
     *
     * @apiSuccessExample 成功响应:
     *
    {
    "code": 200,
    "msg": "成功",
    "data": {
    "current_page": 1,
    "data": [
    {
    "id": 168934,
    "phone": "18624078563",
    "nickname": "chandler",
    "openid": null,
    "unionid": null,
    "sex": null,
    "birthday": null,
    "province": null,
    "city": null,
    "headimg": "/wechat/works/headimg/3833/2017110823004219451.png",
    "intro": null,
    "level": 0,
    "created_at": null,
    "updated_at": null,
    "expire_time": null,
    "status": 1,
    "is_staff": 0,
    "pivot": {
        "from_uid": 1,
        "to_uid": 168934
        }
    }
    ],
    "first_page_url": "http://v4.com/api/v4/user/follower?page=1",
    "from": 1,
    "last_page": 1,
    "last_page_url": "http://v4.com/api/v4/user/follower?page=1",
    "next_page_url": null,
    "path": "http://v4.com/api/v4/user/follower",
    "per_page": 10,
    "prev_page_url": null,
    "to": 2,
    "total": 2
    }
    }
     *   }
     *
     */
    public function fan()
    {
        $user  = User::findOrFail(1);
        $lists = $user->fans()->paginate(10);
        return $this->success($lists);
    }
   /**
    * @api {get} api/v4/user/follower 我关注的
    * @apiVersion 4.0
    * @apiGroup Api
    *
    * @apiSuccess {String}
    *
    * @apiSuccessExample 成功响应:
    *
        {
           "code": 200,
           "msg": "成功",
           "data": {
               "current_page": 1,
               "data": [
                   {
                       "id": 168934,
                       "phone": "18624078563",
                       "nickname": "chandler",
                       "openid": null,
                       "unionid": null,
                       "sex": null,
                       "birthday": null,
                       "province": null,
                       "city": null,
                       "headimg": "/wechat/works/headimg/3833/2017110823004219451.png",
                       "intro": null,
                       "level": 0,
                       "created_at": null,
                       "updated_at": null,
                       "expire_time": null,
                       "status": 1,
                       "is_staff": 0,
                       "pivot": {
                       "from_uid": 1,
                       "to_uid": 168934
                       }
                   }
               ],
               "first_page_url": "http://v4.com/api/v4/user/follower?page=1",
               "from": 1,
               "last_page": 1,
               "last_page_url": "http://v4.com/api/v4/user/follower?page=1",
               "next_page_url": null,
               "path": "http://v4.com/api/v4/user/follower",
               "per_page": 10,
               "prev_page_url": null,
               "to": 2,
               "total": 2
               }
           }
    *   }
    *
   */
    public function follower()
    {
        $user  = User::findOrFail(1);
        $lists = $user->follow()->paginate(10);
        return  $this->success($lists);
    }


}
