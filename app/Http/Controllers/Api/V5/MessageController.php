<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use App\Models\Column;
use App\Models\Comment;
use App\Models\CommentReply;
use App\Models\History;
use App\Models\Like;
use App\Models\Message\Message;
use App\Models\Message\MessageType;
use App\Models\Message\MessageUser;
use App\Models\UserFollow;
use App\Models\VipUser;
use App\Models\Works;
use App\Models\WorksInfo;
use App\Servers\V5\MessageServers;
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
//        $user_id = $this->user['id'] ?? 233785;
        $user_id = 233785;

        // 1=系统消息 4=内容上新 9=评论 11=点赞 12=收益 22=关注

        $lists = [];
        //点赞
        $lists['like_count']=MessageServers::get_user_unread_count(MessageType::get_like_msg_type(),$user_id);

        //关注
        $lists['follow_count']=MessageServers::get_user_unread_count(MessageType::get_follow_msg_type(),$user_id);

        //评论
        $lists['comment_count']=MessageServers::get_user_unread_count(MessageType::get_comment_msg_type(),$user_id);

        //收益12
        $type_arr=MessageType::get_profit_msg_type();
        $profit=MessageServers::get_user_new_msg($type_arr,$user_id);
        if($profit){
            $message=json_decode($profit->message->message,true);
            $lists['message'][]=['count'=>MessageServers::get_user_unread_count($type_arr,$user_id),'created_at'=>strtotime($profit->created_at),'type'=>12,'title'=>$profit->message->title,'message'=>$message['content']??$message];
        }

        //内容上新type=4;
        $type_arr=MessageType::get_work_new_msg_type();
        $work_new=MessageServers::get_user_new_msg($type_arr,$user_id);
        if($work_new){
            $lists['message'][]=['count'=>MessageServers::get_user_unread_count($type_arr,$user_id),'created_at'=>strtotime($work_new->created_at),'type'=>4,'title'=>$work_new->message->title,'message'=>$work_new->message->message];
        }

        //系统消息type=1;
        $type_arr=MessageType::get_system_msg_type();
        $system=MessageServers::get_user_new_msg($type_arr,$user_id);;
        if($system){
            $lists['message'][]=['count'=>MessageServers::get_user_unread_count($type_arr,$user_id),'created_at'=>strtotime($system->created_at),'type'=>1,'title'=>$system->message->title,'message'=>$system->message->message];
        }

        $message_arr=$lists['message']??[];
        $created_at=array_column($message_arr,'created_at');
        array_multisort($message_arr,SORT_DESC,$created_at);

        foreach ($message_arr as &$val){
            $val['created_at']=History::DateTime(date('Y-m-d H:i:s',$val['created_at']));
        }
        $lists['message']=$message_arr;
        return success($lists);
    }

    /**
     * @api {get} /api/v5/message/msg_comment_list 评论消息列表
     * @apiName msg_comment_list
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
    public function msg_comment_list(Request $request)
    {
        $user_id = 233785;

//        $user_id = $this->user['id'] ?? 233785;

        //type :9=评论 10=回复
        $type_arr=MessageType::get_comment_msg_type();
        MessageServers::clear_msg($user_id,$type_arr);

        $lists = MessageUser::query()
            ->select(['id', 'send_user', 'type', 'message_id', 'status', 'created_at'])
            ->with([
                'message:id,type,title,message,action_id',
                'send_user:id,nickname,headimg,is_author',
            ])
            ->whereIn('type', $type_arr)
            ->where('receive_user', $user_id)
            ->paginate()->toArray();

        foreach ($lists['data'] as &$items) {

            //是不是360vip
            $items['send_user']['is_vip']=VipUser::newVipInfo($v['user']['id']??0)['vip_id'] ?1:0;

            //格式化时间
            $items['created_at'] = History::DateTime($items['created_at']);

            //是否点赞
            $items['is_like'] = Like::isLike($items['message']['action_id'],$items['type']==9?1:2,$items['send_user']['id'],1);

            $items['comment_id']=$items['message']['action_id'];
            if($items['type']==10){
                //获取回复
                $CommentReply = CommentReply::query()
                    ->with([
                        'from_user:id,nickname,headimg,is_author',
                        'to_user:id,nickname,headimg,is_author'
                    ])->where('id', $items['message']['action_id'])->first();
                if (!$CommentReply) {
                    return $this->error(0, '参数错误1');
                }

                $items['comment_id'] = $CommentReply->comment_id;//评论id
                $items['reply_id'] = $CommentReply->id;//回复id
            }

            //获取评论关联的课程内容
            $items=MessageServers::get_info_by_comment( $items['comment_id'],$items);

            if($items['type']==9){
                $items['message']='评论了你：'.$items['comment']['content'];
            }else{
                $items['message']='回复了你：'.$CommentReply->content;
            }
        }

        return success($lists['data']);
    }

    /**
     * @api {get} /api/v5/message/msg_comment_info 评论消息详情
     * @apiName msg_comment_info
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
    public function msg_comment_info(Request $request)
    {

//        $user_id = $this->user['id'] ?? 233785;
        $user_id = 233785;

        $id =$request->query('id',60);

        if(empty($id)){
            return $this->error(0, '参数错误');
        }

        //type :9=评论 10=回复
        $items = MessageUser::query()
            ->select(['id', 'send_user', 'type','receive_user', 'message_id', 'status', 'created_at'])
            ->with([
                'message:id,type,title,message,action_id',
                'send_user:id,nickname,headimg,is_author',
                'receive_user:id,nickname,headimg,is_author'
            ])
            ->whereIn('type', MessageType::get_comment_msg_type())
            ->where('id', $id)
            ->where('receive_user', $user_id)
            ->first()->toArray();

        //是不是360vip
        $items['send_user']['is_vip']=VipUser::newVipInfo($v['user']['id']??0)['vip_id'] ?1:0;

        //格式化时间
        $items['created_at'] = History::DateTime($items['created_at']);

        //是否点赞
        $items['is_like'] = Like::isLike($items['message']['action_id'],$items['type']==9?1:2,$items['send_user']['id'],1);

        //获取评论
        $items['comment_id']=$items['message']['action_id'];
        if($items['type']==10){
            //获取回复
            $CommentReply = CommentReply::query()
                ->with([
                    'from_user:id,nickname,headimg,is_author',
                    'to_user:id,nickname,headimg,is_author'
                ])->where('id', $items['message']['action_id'])->first();
            if (!$CommentReply) {
                return $this->error(0, '参数错误1');
            }
            $items['comment_id'] = $CommentReply->comment_id;//评论id
            $items['reply_id'] = $CommentReply->id;//回复id
        }

        //获取评论关联的课程内容
        $items=MessageServers::get_info_by_comment( $items['comment_id'],$items);

        //获取评论列表
        $items['reply_list']=CommentReply::query()
            ->where('comment_id', $items['comment_id'])
            ->whereRaw('(from_uid='.$items['send_user']['id'].' and to_uid='.$items['receive_user']['id'].') or (from_uid='.$items['receive_user']['id'].' and to_uid='.$items['send_user']['id'].')')
            ->where('status', 1)
            ->select('id', 'comment_id', 'from_uid', 'to_uid', 'content', 'created_at','reply_pid')
            ->with([
                'from_user:id,nickname,headimg,is_author',
                'to_user:id,nickname,headimg,is_author'
            ])->get();

        $items['reply_count']=count($items['reply_list']);

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

//        $user_id = $this->user['id'] ?? 233785;
        $user_id = 233785;

        //22=关注
        $type_arr=MessageType::get_follow_msg_type();
        MessageServers::clear_msg($user_id,$type_arr);

        $lists=MessageUser::query()
            ->select([
                DB::raw('GROUP_CONCAT(id) as ids'),
                DB::raw('count(*) as count'),
                DB::raw("date_format(created_at,'%Y-%m-%d') as time"),
            ])->groupBy(DB::raw("date_format(created_at,'%Y-%m-%d')"))
            ->whereIn('type', $type_arr)
            ->where('receive_user', $user_id)
            ->paginate()->toArray();

        foreach ($lists['data'] as &$msg){

            $ids_arr=explode(',',$msg['ids']);
            $follow_lists = MessageUser::query()
                ->select(['id', 'send_user','type', 'receive_user', 'message_id', 'status', 'created_at'])
                ->with([
                    'message:id,type,title,message,action_id',
                    'send_user:id,nickname,headimg,is_author,intro',
                ])
                ->whereIn('id', $ids_arr)
                ->whereIn('type', MessageType::get_follow_msg_type())
                ->where('receive_user', $user_id)
                ->get()->toArray();

            foreach ($follow_lists as &$items) {

                //是不是360vip
                $items['send_user']['is_vip']=VipUser::newVipInfo($v['user']['id']??0)['vip_id'] ?1:0;
                $items['created_at'] = History::DateTime($items['created_at']);

                $is_follow_me=UserFollow::IsFollow( $items['send_user'],$items['receive_user']);
                $is_follow_he=UserFollow::IsFollow($items['receive_user'], $items['send_user']);

                $items['is_follow_me']=$is_follow_me;
                $items['is_follow_he']=$is_follow_he;

                if($is_follow_me && $is_follow_he){
                    $items['is_follow']=2;
                }elseif ($is_follow_he){
                    $items['is_follow']=1;
                }else{
                    $items['is_follow']=0;
                }

            }

            $msg['follow_list']=$follow_lists;
        }

        return success($lists['data']);
    }


    /**
     * @api {get} /api/v5/message/msg_like_list 点赞
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
//        $user_id = $this->user['id'] ?? 233785;
        $user_id = 233785;

        // 11=点赞
        $type_arr=MessageType::get_like_msg_type();
        MessageServers::clear_msg($user_id,$type_arr);

        $lists = MessageUser::query()
            ->select(['id', 'send_user', 'type','receive_user', 'message_id', 'status', 'created_at'])
            ->with([
                'message:id,type,title,message,action_id',
                'send_user:id,nickname,headimg,is_author',
            ])
            ->whereIn('type', $type_arr)
            ->where('receive_user', $user_id)
            ->paginate()->toArray();

        foreach ($lists['data'] as &$items) {

            //是不是360vip
            $items['send_user']['is_vip']=VipUser::newVipInfo($v['user']['id']??0)['vip_id'] ?1:0;

            //格式化时间
            $items['created_at'] = History::DateTime($items['created_at']);

            //获取点赞
            $like=Like::query()->where('id',$items['message']['action_id'])->first();
            if(empty($like)){
                return $this->error(0, '参数错误');
            }

            $items['like'] = $like;

                //获取回复
            if($like['comment_type']==2){

                $CommentReply = CommentReply::query()
                    ->with([
                        'from_user:id,nickname,headimg,is_author',
                        'to_user:id,nickname,headimg,is_author'
                    ])
                    ->where('id', $like->relation_id)->first();
                if (!$CommentReply) {
                    return $this->error(0, '参数错误');
                }

                $items['comment_reply'] =$CommentReply;
                $items['comment_id'] = $CommentReply->comment_id;
                $items['reply_id'] = $CommentReply->reply_id;
                $comment_id = $CommentReply->comment_id;

            }else{
                $comment_id = $like->relation_id;
                $items['comment_id'] = $comment_id;
            }

            //获取评论关联的课程内容
            $items=MessageServers::get_info_by_comment($comment_id,$items);

        }

        return success($lists['data']);
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
//        $user_id = $this->user['id'] ?? 233785;
        $user_id = 233785;

        //4=内容上新
        $type_arr=MessageType::get_work_new_msg_type();
        MessageServers::clear_msg($user_id,$type_arr);

        $lists = MessageUser::query()
            ->select(['id', 'send_user', 'type','receive_user', 'message_id', 'status', 'created_at'])
            ->with([
                'message:id,type,title,message,action_id,relation_type,relation_id,relation_info_id,open_type,rich_text',
            ])
            ->whereIn('type', $type_arr)
            ->where('receive_user', $user_id)
            ->paginate()->toArray();

        foreach ($lists['data'] as &$items) {

            if(in_array($items['type'],[5,6])){
                $works = Works::query()->where('id',  $items['message']['relation_id'])
                    ->select(['id', 'title', 'cover_img'])->first();
                $items['message']['cover_pic'] = $works->cover_img??'';//封面
            }else{

                //获取训练营、专栏、讲座
                $Column = Column::query()->where('id',$items['message']['relation_id'])
                    ->select(['id','title', 'cover_pic', 'details_pic'])->first();
                $items['message']['cover_pic'] = $Column->cover_pic??'';//封面

            }

            //格式化时间
            $items['message']['created_at'] = WorkNewDateTime( $items['created_at']);
            $items['created_at'] =formatDataTime($items['created_at']);

        }

        return success($lists['data']);
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

//        $user_id = $this->user['id'] ?? 233785;
        $user_id = 233785;

        //1=系统消息

        $type_arr=MessageType::get_system_msg_type();
        MessageServers::clear_msg($user_id,$type_arr);

        $lists = MessageUser::query()
            ->select(['id', 'send_user', 'type','receive_user', 'message_id', 'status', 'created_at'])
            ->with([
                'message:id,type,title,message,action_id,created_at,relation_type,relation_id,relation_info_id,open_type,rich_text,url',
            ])
            ->whereIn('type', $type_arr)
            ->where('receive_user', $user_id)
            ->paginate()->toArray();

        foreach ($lists['data'] as &$items) {
            //格式化时间
            $items['message']['created_at'] = formatDataTime($items['created_at'],2);;
            $items['created_at'] =formatDataTime($items['created_at']);

        }

        return success($lists['data']);
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

//        $user_id = $this->user['id'] ?? 233785;
        $user_id = 233785;

        // 12=收益
        $type_arr=MessageType::get_profit_msg_type();
        MessageServers::clear_msg($user_id,$type_arr);

        $lists = MessageUser::query()
            ->select(['id', 'send_user', 'type','receive_user', 'message_id', 'status', 'created_at'])
            ->with([
                'message:id,type,title,message,action_id,relation_type,relation_id,relation_info_id',
            ])
            ->whereIn('type', $type_arr)
            ->where('receive_user', $user_id)
            ->paginate()->toArray();

        foreach ($lists['data'] as &$items) {

            $msg_arr=json_decode($items['message']['message'],true);

            $items['message']['message']=[
                'content'=>$msg_arr['content']??'',
                'source'=>$msg_arr['source']??'',
                'type'=>$msg_arr['type']??'',
                'amount'=>$msg_arr['amount']??'',
                'time'=>$msg_arr['time']??''
            ];

            //格式化时间
            $items['message']['created_at'] = formatDataTime($items['created_at'],2);;
            $items['created_at'] =formatDataTime($items['created_at']);
        }

        return success($lists['data']);
    }

    /**
     * @api {get} /api/v5/message/clear_msg 清除未读消息
     * @apiName clear_msg
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
    public function clear_msg(Request $request)
    {
        $user_id = $this->user['id'] ?? 233785;
        MessageServers::clear_msg($user_id);
        return success([]);
    }

}
