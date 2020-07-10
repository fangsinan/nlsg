<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use App\Models\Column;
use App\Models\History;
use App\Models\Works;
use App\Models\WorksInfo;
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
     * @apiVersion 4.0.0
     * @apiGroup Api
     *
     * @apiSuccess {String} token
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
     * @apiVersion 4.0.0
     * @apiName  from_uid 用户id
     * @apiName  to_uid   被关注用户id
     * @apiGroup Api
     *
     * @apiSuccess {String} token   token
     *
     * @apiSuccessExample  成功响应:
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
     * @apiVersion 4.0.0
     * @apiName  to_uid  被关注用户id
     * @apiGroup Api
     *
     * @apiSuccess {String} token
     *
     * @apiSuccessExample  成功响应:
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
     * @apiVersion 4.0.0
     * @apiName  nickname 昵称
     * @apiName  headimg  头像
     * @apiName  sex  性别
     * @apiName  intro  简介
     * @apiGroup Api
     *
     * @apiSuccess {String} token
     *
     * @apiSuccessExample  成功响应:
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
     * @apiVersion 4.0.0
     * @apiParam {string} type 10:使用建议 11:内容漏缺 12:购物相关 13:物流配送 14:客服体验 15:节约相关
     * @apiParam {string} content 内容  不能大于200字
     * @apiParam {string} pic  图片url(数组格式)
     * @apiGroup Api
     *
     * @apiSuccess {String} token
     *
     * @apiSuccessExample  成功响应:
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
     * @apiVersion 4.0.0
     * @apiGroup Api
     *
     * @apiSuccess {String} token
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
    * @apiVersion 4.0.0
    * @apiGroup Api
    *
    * @apiSuccess {String} token
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




    /**
     * @api {get} api/v4/user/history  我的--历史记录
     * @apiVersion 4.0.0
     * @apiGroup user
     *
     * @apiParam {string} user_id 用户id
     * @apiParam {string} page 页数
     * @apiParam {string} order  desc|asc
     *
     * @apiSuccess {string} result json
     * @apiSuccess {string} column_name 名称   优先级 专栏 > 课程  >章节
     * @apiSuccess {string} works_name 名称   优先级 专栏 > 课程  >章节
     * @apiSuccess {string} works_info_name 名称   优先级 专栏 > 课程  >章节
     * @apiSuccess {string} column_cover_img  封面 优先级 专栏 > 课程
     * @apiSuccess {string} works_cover_img  封面 优先级 专栏 > 课程
     * @apiSuccess {string} user_id  用户id
     *
     * @apiSuccessExample 成功响应:
    {
    "code": 200,
    "msg": "成功",
    "data": {
    "07-05 ": [
    {
    "id": 8,
    "column_id": 0,
    "works_id": 16,
    "worksinfo_id": 1,
    "user_id": 211172,
    "time_leng": "10",
    "time_number": "5",
    "is_del": 0,
    "created_at": "2020-07-04T19:47:22.000000Z",
    "updated_at": "2020-06-04T20:07:36.000000Z",
    "column_name": "",
    "column_cover_img": "",
    "works_name": "如何经营幸福婚姻",
    "works_cover_img": "/nlsg/works/20190822150244797760.png",
    "worksInfo_name": "01何为坚毅"
    },
    {
    "id": 9,
    "column_id": 1,
    "works_id": 16,
    "worksinfo_id": 2,
    "user_id": 211172,
    "time_leng": "0",
    "time_number": "",
    "is_del": 0,
    "created_at": "2020-07-04T19:47:22.000000Z",
    "updated_at": null,
    "column_name": "王琨专栏",
    "column_cover_img": null,
    "works_name": "如何经营幸福婚姻",
    "works_cover_img": "/nlsg/works/20190822150244797760.png",
    "worksInfo_name": "02坚毅品格的重要性"
    }
    ]
    }
    }
     */
    public function history(Request $request)
    {
        $user_id = $request->input('user_id',0);
        $order = $request->input('order','desc');

        $lists = History::where(['user_id' => $user_id, 'is_del' => 0,])
            ->orderBy('created_at',$order)->paginate($this->page_per_page)->toArray();

        if(empty($lists['data'])){
            return $this->success();
        }
        $new_list = [];
        foreach ($lists['data'] as $key => $val){
            //查询所属专栏 课程 以及章节
            $val['column_name']      = '';
            $val['column_cover_img'] = '';
            $val['works_name']       = '';
            $val['works_cover_img']  = '';
            $val['worksInfo_name']   = '';



            if($val['column_id']){
                $column = Column::find($val['column_id']);
                $val['column_name']  = $column['name'];
                $val['column_cover_img']  = $column['cover_img'];
            }
            if($val['works_id']){
                $works = Works::find($val['works_id']);
                $val['works_name']  = $works['title'];
                $val['works_cover_img']  = $works['cover_img'];
            }
            if($val['worksinfo_id']){
                $worksInfo = WorksInfo::find($val['worksinfo_id']);
                $val['worksInfo_name']  = $worksInfo['title'];
            }


            $new_list[History::DateTime($val['created_at'])][]=$val;
        }


        return  $this->success($new_list);
    }



    /**
     * @api {get} api/v4/user/clear_history  我的--清空学习记录
     * @apiVersion 4.0.0
     * @apiGroup user
     *
     * @apiParam {string} user_id 用户id
     * @apiParam {string} his_id  清空全部值为all  否则传id 多个用英文逗号拼接 如1,2,3
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample 成功响应:
    {
    "code": 200,
    "msg": "成功",
    "data": []
    }
     */
    public function clearHistory(Request $request)
    {
        $user_id  = $request->input('user_id',0);
        $his_id = $request->input('his_id',0);
        if ( empty($his_id) ) {
            return $this->error(0,'fail:his_id参数有误');
        }

        if($his_id == 'all'){
            $res = History::where('user_id',$user_id)->update(['is_del' => 1]);
        }else{
            $his_id = explode(',',$his_id);
            $res = History::where('user_id',$user_id)
                ->whereIn('id',$his_id)->update(['is_del' => 1]);
        }
        if ($res) {
            return $this->success();
        } else {
            return $this->error(0,'fail');
        }
    }


}
