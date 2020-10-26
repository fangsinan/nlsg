<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use App\Models\Column;
use App\Models\CommentReply;
use App\Models\Notify;
use App\Models\User;
use App\Models\UserFollow;
use App\Models\Works;
use App\Models\WorksInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use JPush;

class NotifyController extends Controller
{
    /**
     * @api {get} api/v4/notify/list  消息通知
     * @apiVersion 4.0.0
     * @apiName  list
     * @apiGroup Notify
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/notify/list
     * @apiParam  type  1.喜欢精选  2. 评论和@ 3更新消息 4.收益动态 5.系统消息
     * @apiParam  token  用户认证
     *
     * @apiSuccess {string} subject 标题
     * @apiSuccess {string} create_time 时间
     * @apiSuccess {string} from_user   用户相关
     *
     * @apiSuccessExample  Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "code": 200,
     *       "msg" : '成功',
     *         "data": [
             {
                 "id": 4,
                 "from_uid": 1,
                 "to_uid": 303681,
                 "subject": "喜欢了你的想法",
                 "source_id": 92,
                 "created_at": "2020-09-22 11:21:06",
                 "from_user": {
                     "id": 1,
                     "nickname": "测试",
                     "intro": "",
                     "headimg": "test.png"
                 },
                 "reply": null,
                 "create_time": "2天前"
             }
         ]
     *     }
     *
     */
    public function index(Request $request)
    {
        $type =  $request->input('type') ?? 1;
        $lists =  Notify::select('id','from_uid','to_uid','subject','content','task_id','source_id','created_at','relation_type','type')
                ->where(['to_uid'=>$this->user['id'], 'type'=>$type])
                ->orderBy('created_at', 'desc')
                ->paginate(10)
                ->toArray();
        if ($lists['data']){
            foreach ($lists['data'] as &$v) {

                $v['content']     =  !empty($v['content']) ? unserialize($v['content']) : '';
                $v['create_time'] =  Carbon::parse($v['created_at'])->diffForHumans();
                if ($v['type'] ==1){
                    $v['chapter'] = WorksInfo::select('id','title')->where('id', $v['task_id'])->first();
                    $v['from_user'] = User::select('id','nickname','intro','headimg')->where('id', $v['from_uid'])->first();
                }elseif ($v['type']==2){
                    $v['from_user'] = User::select('id','nickname','intro','headimg')->where('id', $v['from_uid'])->first();
                } elseif ($v['type']==3){
                    $v['works'] = Works::select('id','cover_img','title')->where('id', $v['source_id'])->first();
                } elseif($v['type']==4){
                    $v['from_user'] = User::select('id','nickname','intro','headimg')->where('id', $v['from_uid'])->first();
                }

            }
        }
        return success($lists['data']);
    }



    /**
     * @api {get} api/v4/notify/fans 新增粉丝
     * @apiVersion 4.0.0
     * @apiName  fans
     * @apiGroup Notify
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/notify/fans
     *
     * @apiParam {string}  token
     *
     * @apiSuccess {string} nickname  用户昵称
     * @apiSuccess {string} from_uid  用户id
     * @apiSuccess {string} headimg  用户头像
     *
     * @apiSuccessExample  Success-Response:
     * HTTP/1.1 200 OK
     * {
     *   "code": 200,
     *   "msg" : '成功',
     *   "data": [
             {
                 "from_uid": 211185,
                 "to_uid": 303681,
                 "nickname": "丹丹",
                 "headimg": "http://thirdwx.qlogo.cn/mmopen/vi_32/DYAIOgq83eq1iamPt3zKARVHsQMMqap77msicttX4libSBgCIgfrqumbm73uxwwlicAomRHCiawmNBd68TBicUh9IWGQ/132",
                 "pivot": {
                     "to_uid": 303681,
                     "from_uid": 211185
                 },
                 "is_follow": 1
             }
         ]
     * }
     */
    public function fans()
    {
        User::where('id', 1)->update(['fan_num'=>0]);

        $user  = User::find($this->user['id']);
        $lists = $user->fans()->paginate(10, ['from_uid','to_uid','nickname','headimg'])->toArray();
        if ($lists['data']){
            foreach ($lists['data'] as &$v) {
                $list   = UserFollow::where(['from_uid'=>$this->user['id'], 'to_uid'=>$v['from_uid']])->first();
                $v['is_follow']  =  $list ? 1 : 0;
                $v['create_time'] =  Carbon::parse($list['created_at'])->diffForHumans();
            }
        }
        return  success($lists['data']);
    }

    public  function jpush()
    {
        return  JPush::pushNow('303682', '苹果推送');
    }

     /**
     * @api {get} api/v4/notify/systerm 系统消息
     * @apiVersion 4.0.0
     * @apiName  systerm
     * @apiGroup Notify
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/notify/systerm
     *
     * @apiParam {string}  token
     *
     * @apiSuccess {string} title        消息类型标题
     * @apiSuccess {string} subject      消息标题
     * @apiSuccess {string} source_id    来源id
     * @apiSuccess {string} create_time  时间
     *
     * @apiSuccessExample  Success-Response:
     * HTTP/1.1 200 OK
     * {
     *   "code": 200,
     *   "msg" : '成功',
     *   "data": [
             {
                 "subject": "您订阅的《王琨专栏》即将到期",
                 "source_id": 来源id,
                 "title": "过期提醒",
                 "create_time": "1小时前",
             }
         ]
     * }
     */
    public  function systerm()
    {
        $lists = Notify::whereIn('relation_type', [5, 6, 7])->where(['type'=>5,'status'=>1])
                ->orderBy('created_at','desc')
                ->get()
                ->toArray();
        if($lists){
            foreach($lists as &$v){
                if($v['type']==5){
                    $v['title']   = '到期提醒';
                }elseif($v['type'] ==6){
                    $v['title']   = '订单提醒';
                }else{
                    $v['title']   = '审核提醒';
                }
                $v['create_time'] =  Carbon::parse($v['created_at'])->diffForHumans();
            }
        }
        return success($lists);
    }

     /**
     * @api {get} api/v4/notify/course 更新消息
     * @apiVersion 4.0.0
     * @apiName  course
     * @apiGroup Notify
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/notify/systerm
     *
     * @apiParam {string}  token
     *
     * @apiSuccess {string} title        消息类型标题
     * @apiSuccess {string} subject      消息标题
     * @apiSuccess {string} source_id    来源id
     * @apiSuccess {string} create_time  时间
     *
     * @apiSuccessExample  Success-Response:
     * HTTP/1.1 200 OK
     * {
     *   "code": 200,
     *   "msg" : '成功',
     *   "data": [
             {
                 "subject": "您订阅的《王琨专栏》即将到期",
                 "source_id": 来源id,
                 "title": "过期提醒",
                 "create_time": "1小时前",
             }
         ]
     * }
     */
    public function course()
    {
        $lists = Notify::with('works:id,title,cover_img')
            ->where('type', 3)->where('status',1)
            ->orderBy('created_at','desc')
            ->get()
            ->toArray();
        if($lists){
            foreach($lists as &$v){
                $v['create_time'] =  Carbon::parse($v['created_at'])->diffForHumans();
            }
        }
        return success($lists);
    }

}
