<?php


namespace App\Models\Message;


use App\Models\Base;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Message extends Base
{

    const DB_TABLE = 'nlsg_message';

    protected $table = 'nlsg_message';

    protected $fillable = [
        'title', 'message', 'type', 'receive_type', 'relation_id','relation_info_id', 'relation_type',
        'created_at', 'updated_at', 'plan_time', 'status','is_jpush','timing_send_time',
        'is_timing','open_type','url','rich_text','send_count','get_count','read_count','action_id','jpush_msg_id',
    ];



    /**
     * pushMessage 推送消息
     *
     * @param int $sendUid    发送人id   0 为系统消息
     * @param int $receiveUid 接收人id   0为全部
     * @param string $push_type_const  发送的消息类型  新用户注册:REGISTER,   周奖励:WEEK_REWARD,   结营奖励:END_CAMP_REWARD,   课程上新:WORK_NEW,   大咖讲书上新:LISTS_NEW,   讲座上新:LECTURE_NEW,   训练营上新:CAMP_NEW,   评论:COMMENT,   回复:COMMENT_REPLY,   点赞:LIKE,   取消点赞:UNLIKE,   直播奖励:LIVE_PROFIT_REWARD,   优惠券奖励:COUPON_PROFIT_REWARD,   360会员奖励:VIP_PROFIT_REWARD,   即将到期:VIP_SOON_EXPIRE,   已经到期:VIP_EXPIRE   关注:FOLLOW
     * @param array $relation_data relation_type 跳转   relation_id  内容id  relation_info  章节id   action_id 行为表id（评论表 回复表 点赞表 收益表 优惠券表=）
     */
    static function pushMessage(int $sendUid, int $receiveUid, string $push_type_const,$relation_data=[]){

        //处理参数
        $relation_type  = $relation_data['relation_type'] ??0;
        $relation_id    = $relation_data['relation_id'] ??0;
        $relation_info  = $relation_data['relation_info'] ??0;
        $action_id     = $relation_data['action_id'] ??0;

        //校验用户
        // $user = User::select("id","nickname")->whereIn('id',[$sendUid,$receiveUid])->get()->toArray();
        // $users = array_column($user,"nickname","id");

        //查看消息类型是否存在
        $messageView = MessageType::getTypeView($push_type_const,$relation_data);

        if(empty($messageView)){
            return false;
        }
        $receive_type = 1;
        if($receiveUid == 0){
            $receive_type = 2;  // 全员发送
        }

        $time = date("Y-m-d H:i:s");
        // 将消息组装至message
        $messageData = [
            "title"         =>  $messageView['title'],
            "message"       =>  $messageView['message'],
            "type"          =>  $messageView['type'],
            "receive_type"  =>  $receive_type,
            "relation_type" =>  $relation_type,
            "relation_id"   =>  $relation_id,
            "relation_info" =>  $relation_info,
            "action_id"     =>  $action_id,
            "plan_time"     =>  $time,
            "status"        =>  3,
            "is_jpush"      =>  2,
            "open_type"      =>  $relation_data['open_type'] ??0,
        ];
        // 开启事务
        DB::beginTransaction();
        //写消息表
        $msg  = self::create($messageData);
        //写关联user表
        $msgUser = MessageUser::create([
            "send_user"     => $sendUid,
            "receive_user"  => $receiveUid,
            "type"          => $messageView['type'],
            "message_id"    => $msg->id,
            "status"        => 1,
            "is_send"       => 3,
            "plan_time"     => $time
        ]);
        if($msg && $msgUser){
            DB::commit();
            return true;
        }else{
            DB::rollBack();
            // 记录日志
            DB::table('nlsg_log_info')->insert([
                'url'     => 'pushMessage:',
                'parameter'    =>  json_encode(["sendUid" => $sendUid, "receiveUid"=>$receiveUid, "push_type" => $push_type_const,"relation_data"=>$relation_data]),
                'user_id'    =>  $sendUid,
                'created_at' =>$time
            ]);
            return false;
        }

    }


    public function messageUserList(): HasMany
    {
        return $this->hasMany(MessageUser::class,'message_id','id');
    }
}
