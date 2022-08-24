<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use App\Models\Column;
use App\Models\Comment;
use App\Models\CommentReply;
use App\Models\History;
use App\Models\Like;
use App\Models\Message\Message;
use App\Models\Message\MessageUser;
use App\Models\UserFollow;
use App\Models\VipUser;
use App\Models\WorksInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Predis\Client;

/**
 * MessageController
 * 消息
 */
class MessageController extends Controller
{

    /**
     * @api {post} /api/v5/message/msg_type_list 消息列表
     * @apiName msg_type_list
     * @apiVersion 1.0.0
     * @apiGroup message
     *
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": { }
     * }
     */
    public function msg_type_list(Request $request)
    {
        $user_id = $this->user['id'] ?? 2;

        //9=评论 22=关注 (以真实关注的数据为准) 11=点赞  1=系统消息 4=内容上新  12=收益

        $lists = [];

        return success($lists);
    }

    /**
     * @api {get} /api/v5/message/msg_commen_list 评论消息列表
     * @apiName msg_commen_list
     * @apiVersion 1.0.0
     * @apiGroup message
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": { }
     * }
     */
    public function msg_commen_list(Request $request)
    {

        $user_id = $this->user['id'] ?? 2;

        //type :9=评论 22=关注 (以真实关注的数据为准) 11=点赞  1=系统消息 4=内容上新  12=收益
        $lists = MessageUser::query()
            ->select(['id', 'send_user', 'type','receive_user', 'message_id', 'status', 'created_at'])
            ->with([
                'message:id,type,title,message,action_id',
                'send_user:id,nickname,headimg,is_author',
                'receive_user:id,nickname,headimg,is_author'
            ])
            ->where('type', 0)
            ->where('receive_user', $user_id)
            ->paginate()->toArray();

        foreach ($lists['data'] as &$items) {
            //是不是360vip
            $items['send_user']['is_vip']=VipUser::newVipInfo($v['user']['id']??0)['vip_id'] ?1:0;

            //格式化时间
            $items['created_at'] = History::DateTime($items['created_at']);

            //是否点赞
            $items['is_like'] = 1;
            //Like::isLike($rep_v['id'],2,$uid,$like_type);

            //获取回复
            $CommentReply = CommentReply::query()
                ->with([
                    'from_user:id,nickname,headimg,is_author',
                    'to_user:id,nickname,headimg,is_author'
                ])
                ->where('id', 2397)->first();
            if (!$CommentReply) {
                return $this->error(0, '参数错误');
            }
            $items['comment_reply'] = $CommentReply;

            //获取评论
            $comment_id = $CommentReply->comment_id;
            $Comment = Comment::query()
                ->with([
                    'user:id,nickname,headimg,is_author', 'quote:id,pid,content', 'attach:id,relation_id,img',
                ])
                ->select('id', 'pid', 'user_id', 'relation_id', 'info_id', 'content', 'forward_num',
                    'share_num', 'like_num', 'reply_num', 'created_at', 'is_quality', 'is_top')
                ->where('id', $comment_id)
                ->whereIn('type', [1, 2, 6]) //类型 1.专栏 2.讲座 3.听书 4.精品课 5 百科 6训练营  7短视频
                ->first();
            if (!$Comment) {
                return $this->error(0, '参数错误');
            }
            $items['comment'] = $Comment;

            //获取训练营或专栏
            $Column = Column::query()->where('id', $Comment->relation_id)
                ->select(['id', 'name', 'title', 'index_pic', 'cover_pic', 'details_pic'])->first();
            $items['column'] = $Column;

            //获取章节
            if ($Comment->info_id) {
                $items['works_info'] = WorksInfo::query()->where(['id' => $Comment->info_id])->select('id', 'title')->first();
            }
        }

        return success($lists);
    }

    /**
     * @api {get} /api/v5/message/msg_commen_info 评论消息详情
     * @apiName msg_commen_info
     * @apiVersion 1.0.0
     * @apiGroup message
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": { }
     * }
     */
    public function msg_commen_info(Request $request)
    {

        $user_id = $this->user['id'] ?? 2;

        //type :9=评论 22=关注 (以真实关注的数据为准) 11=点赞  1=系统消息 4=内容上新  12=收益
        $items = MessageUser::query()
            ->select(['id', 'send_user', 'type','receive_user', 'message_id', 'status', 'created_at'])
            ->with([
                'message:id,type,title,message,action_id',
                'send_user:id,nickname,headimg,is_author',
                'receive_user:id,nickname,headimg,is_author'
            ])
            ->where('type', 0)
            ->where('receive_user', $user_id)
            ->first()->toArray();

        //是不是360vip
        $items['send_user']['is_vip']=VipUser::newVipInfo($v['user']['id']??0)['vip_id'] ?1:0;

        //格式化时间
        $items['created_at'] = History::DateTime($items['created_at']);

        //是否点赞
        $items['is_like'] = 1;
        //Like::isLike($rep_v['id'],2,$uid,$like_type);

        //获取回复内容
        $CommentReply = CommentReply::query()
            ->with([
                'from_user:id,nickname,headimg,is_author',
                'to_user:id,nickname,headimg,is_author'
            ])
            ->where('id', 2397)->first();
        if (!$CommentReply) {
            return $this->error(0, '参数错误');
        }
        $items['comment_reply'] = $CommentReply;


        //根据评论
        $comment_id = $CommentReply->comment_id;
        $Comment = Comment::query()
            ->with([
                'user:id,nickname,headimg,is_author', 'quote:id,pid,content', 'attach:id,relation_id,img',
                'reply' => function ($query)  use ($items) {
                    $query->select('id', 'comment_id', 'from_uid', 'to_uid', 'content', 'created_at','reply_pid')
//                        ->whereRaw('(from_uid='.$items['send_user']['id'].' and to_uid='.$items['receive_user']['id'].') or (from_uid='.$items['receive_user']['id'].' and to_uid='.$items['send_user']['id'].')')
                        ->where('status', 1);
                },
                'reply.from_user:id,nickname,headimg,is_author', 'reply.to_user:id,nickname,headimg,is_author'
            ])
            ->select('id', 'pid', 'user_id', 'relation_id', 'info_id', 'content', 'forward_num',
                'share_num', 'like_num', 'reply_num', 'created_at', 'is_quality', 'is_top')
            ->where('id', $comment_id)
            ->whereIn('type', [1, 2, 6]) //类型 1.专栏 2.讲座 3.听书 4.精品课 5 百科 6训练营  7短视频
            ->first();

        if (!$Comment) {
            return $this->error(0, '参数错误');
        }
        $items['comment'] = $Comment;

        //获取训练营或专栏
        $Column = Column::query()->where('id', $Comment->relation_id)
            ->select(['id', 'name', 'title', 'index_pic', 'cover_pic', 'details_pic'])->first();
        $items['column'] = $Column;

        //获取章节
        if ($Comment->info_id) {
            $items['works_info'] = WorksInfo::query()->where(['id' => $Comment->info_id])->select('id', 'title')->first();
        }

        return success($items);

    }

    /**
     * @api {get} /api/v5/message/msg_follow_list 关注消息列表
     * @apiName msg_follow_list
     * @apiVersion 1.0.0
     * @apiGroup message
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": { }
     * }
     */
    public function msg_follow_list(Request $request)
    {

        $user_id = $this->user['id'] ?? 2;

        //9=评论 22=关注 (以真实关注的数据为准) 11=点赞  1=系统消息 4=内容上新  12=收益
        $lists=MessageUser::query()
            ->select([
                DB::raw('GROUP_CONCAT(id) as ids'),
                DB::raw('count(*) as count'),
                DB::raw("date_format(created_at,'%Y-%m-%d') as time"),
            ])->groupBy(DB::raw("date_format(created_at,'%Y-%m-%d')"))
            ->where('type', 0)
            ->where('receive_user', $user_id)
            ->paginate()->toArray();

        foreach ($lists['data'] as &$msg){

            $ids_arr=explode(',',$msg['ids']);
            $follow_lists = MessageUser::query()
                ->select(['id', 'send_user','type', 'receive_user', 'message_id', 'status', 'created_at'])
                ->with([
                    'message:id,type,title,message,action_id',
                    'send_user:id,nickname,headimg,is_author',
                    'receive_user:id,nickname,headimg,is_author'
                ])
                ->whereIn('id', $ids_arr)
                ->where('type', 0)
                ->where('receive_user', $user_id)
                ->get()->toArray();

            foreach ($follow_lists as &$items) {
                //是不是360vip
                $items['send_user']['is_vip']=VipUser::newVipInfo($v['user']['id']??0)['vip_id'] ?1:0;

                $items['created_at'] = History::DateTime($items['created_at']);
                $items['is_follow'] = UserFollow::IsFollow($items['receive_user'], $items['send_user']);
            }

            $msg['follow_list']=$follow_lists;
        }

        return success($lists);
    }


    /**
     * @api {get} /api/v5/message/msg_like_list 评论消息列表
     * @apiName msg_like_list
     * @apiVersion 1.0.0
     * @apiGroup message
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": { }
     * }
     */
    public function msg_like_list(Request $request)
    {
        $user_id = $this->user['id'] ?? 2;

        //type :9=评论 22=关注 (以真实关注的数据为准) 11=点赞  1=系统消息 4=内容上新  12=收益

        $lists = MessageUser::query()
            ->select(['id', 'send_user', 'type','receive_user', 'message_id', 'status', 'created_at'])
            ->with([
                'message:id,type,title,message,action_id',
                'send_user:id,nickname,headimg,is_author',
                'receive_user:id,nickname,headimg,is_author'
            ])
            ->where('type', 0)
            ->where('receive_user', $user_id)
            ->paginate()->toArray();

        foreach ($lists['data'] as &$items) {

            //是不是360vip
            $items['send_user']['is_vip']=VipUser::newVipInfo($v['user']['id']??0)['vip_id'] ?1:0;

            //格式化时间
            $items['created_at'] = History::DateTime($items['created_at']);

            //获取点赞
            $items['like']=Like::query()->where('id',1083)->first();

            //获取回复
            $CommentReply = CommentReply::query()
                ->with([
                    'from_user:id,nickname,headimg,is_author',
                    'to_user:id,nickname,headimg,is_author'
                ])
                ->where('id', 2397)->first();
            if (!$CommentReply) {
                return $this->error(0, '参数错误');
            }
            $items['comment_reply'] = $CommentReply;

            //获取评论
            $comment_id = $CommentReply->comment_id;
            $Comment = Comment::query()
                ->with([
                    'user:id,nickname,headimg,is_author', 'quote:id,pid,content', 'attach:id,relation_id,img',
                ])
                ->select('id', 'pid', 'user_id', 'relation_id', 'info_id', 'content', 'forward_num',
                    'share_num', 'like_num', 'reply_num', 'created_at', 'is_quality', 'is_top')
                ->where('id', $comment_id)
                ->whereIn('type', [1, 2, 6]) //类型 1.专栏 2.讲座 3.听书 4.精品课 5 百科 6训练营  7短视频
                ->first();
            if (!$Comment) {
                return $this->error(0, '参数错误');
            }
            $items['comment'] = $Comment;

            //获取训练营或专栏
            $Column = Column::query()->where('id', $Comment->relation_id)
                ->select(['id', 'name', 'title', 'index_pic', 'cover_pic', 'details_pic'])->first();
            $items['column'] = $Column;

            //获取章节
            if ($Comment->info_id) {
                $items['works_info'] = WorksInfo::query()->where(['id' => $Comment->info_id])->select('id', 'title')->first();
            }
        }

        return success($lists);
    }

    /**
     * @api {get} /api/v5/message/msg_work_new_list 内容上新消息
     * @apiName msg_work_new_list
     * @apiVersion 1.0.0
     * @apiGroup message
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": { }
     * }
     */
    public function msg_work_new_list(Request $request)
    {
        $user_id = $this->user['id'] ?? 2;

        //type :9=评论 22=关注 (以真实关注的数据为准) 11=点赞  1=系统消息 4=内容上新  12=收益

        $lists = MessageUser::query()
            ->select(['id', 'send_user', 'type','receive_user', 'message_id', 'status', 'created_at'])
            ->with([
                'message:id,type,title,message,action_id,relation_type,relation_id,relation_info_id',
            ])
            ->where('type', 0)
            ->where('receive_user', $user_id)
            ->paginate()->toArray();

        foreach ($lists['data'] as &$items) {
            //格式化时间
            $items['created_at'] = History::DateTime($items['created_at']);

        }

        return success($lists);
    }


    /**
     * @api {get} /api/v5/message/msg_system_list 系统消息列表
     * @apiName msg_system_list
     * @apiVersion 1.0.0
     * @apiGroup message
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": { }
     * }
     */
    public function msg_system_list(Request $request)
    {
        $user_id = $this->user['id'] ?? 2;

        //type :9=评论 22=关注 (以真实关注的数据为准) 11=点赞  1=系统消息 4=内容上新  12=收益

        $lists = MessageUser::query()
            ->select(['id', 'send_user', 'type','receive_user', 'message_id', 'status', 'created_at'])
            ->with([
                'message:id,type,title,message,action_id,relation_type,relation_id,relation_info_id',
            ])
            ->where('type', 0)
            ->where('receive_user', $user_id)
            ->paginate()->toArray();

        foreach ($lists['data'] as &$items) {
            //格式化时间
            $items['created_at'] = History::DateTime($items['created_at']);

        }

        return success($lists);
    }

    /**
     * @api {get} /api/v5/message/msg_profit_list 收益消息列表
     * @apiName msg_profit_list
     * @apiVersion 1.0.0
     * @apiGroup message
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     * {
     * "code": 200,
     * "msg": "成功",
     * "data": { }
     * }
     */
    public function msg_profit_list(Request $request)
    {

        $user_id = $this->user['id'] ?? 2;

        //type :9=评论 22=关注 (以真实关注的数据为准) 11=点赞  1=系统消息 4=内容上新  12=收益

        $lists = MessageUser::query()
            ->select(['id', 'send_user', 'type','receive_user', 'message_id', 'status', 'created_at'])
            ->with([
                'message:id,type,title,message,action_id,relation_type,relation_id,relation_info_id',
            ])
            ->where('type', 0)
            ->where('receive_user', $user_id)
            ->paginate()->toArray();

        foreach ($lists['data'] as &$items) {

            $msg_arr=json_decode($items['message']['message'],true);

            if(is_array($msg_arr)){
                $items['message']['message']=$msg_arr;
            }else{
                $items['message']['message']=['content'=>'内容','source'=>'奖励来源','type'=>'奖励类型','amount'=>'奖励数量','time'=>'奖励时间'];
            }

            //格式化时间
            $items['created_at'] = History::DateTime($items['created_at']);
        }

        return success($lists);
    }

}
