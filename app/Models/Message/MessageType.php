<?php


namespace App\Models\Message;


use App\Models\Base;
use App\Models\Column;
use App\Models\Coupon;
use App\Models\PayRecordDetail;
use App\Models\User;
use App\Models\Works;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MessageType extends Base
{
    const DB_TABLE = 'nlsg_message_type';

    protected $table = 'nlsg_message_type';

    protected $fillable = [
        'title', 'created_at', 'updated_at','action_const','show_in_create_job_page','pid'
    ];

    public function childList(): HasMany
    {
        return $this->hasMany(self::class, 'pid', 'id');
    }

    //校验id是否可以用于创建模板
    public function checkUsableById(int $id = 0): array
    {
        if (empty($id)) {
            return ['code' => false, 'msg' => 'id错误'];
        }

        $check = self::query()->where('pid', '=', $id)->first();
        if ($check) {
            return ['code' => false, 'msg' => '该类型不能直接用于创建模板'];
        }

        return ['code' => true, 'msg' => 'ok'];
    }

    public function getTypeList(int $flag = 1){
        //1保留父子层级 2返回创建人工推送页面可用的 3只返回可用

        $query = self::query()
            ->where('pid', '=', 0)
            ->with(['childList:id,title,pid'])
            ->select(['id', 'title', 'pid']);

        if ($flag == 1){
            return $query->get();
        }

        if ($flag == 2){
            return $query->where('show_in_create_job_page','=',2)->get();
        }

        $list = $query->get();

        $temp = [];
        foreach ($list as $v){
            if (empty($v->childList)){
                $temp[] = [
                    'id'=>$v->id,
                    'title'=>$v->title,
                ];
            }else{
                foreach ($v->childList as $vv){
                    $temp[] = [
                        'id'=>$vv->id,
                        'title'=>$vv->title,
                    ];
                }
            }
        }
        return $temp;
    }

    /**
     * getTypeView  获取消息模板
     *
     * @param $action_const
     *
     * @return array
     */
    static function getTypeView($action_const,$relation_data): array{
        //查看消息类型是否存在
        $messageType = MessageType::where(['action_const' =>$action_const,])->first();
        if(empty($messageType)){
            return [];
        }
        //获取消息类型模板
        $res =  MessageView::select("title","message","type")->where(['type'=>$messageType['id'],'status'=>1,])->first();
        if(empty($res)){
            return [];
        }
        $res = $res->toArray();

        // 收益通知   组装json
        // if($messageType['pid'] == 12){
            $time = date("Y-m-d H:i:s");
            switch ($action_const){
                case "LIVE_PROFIT_REWARD":
                    if(empty($relation_data['action_id'])) return[];
                    // 查询收益金额
                    $price = PayRecordDetail::where("id",$relation_data['action_id'])->value('price');
                    $message = ['content'=>$res['message'],'source'=>'邀请好友-直播','type'=>'现金奖励','amount'=>$price,'time'=>$time];
                    $res['message'] = json_encode($message,JSON_UNESCAPED_UNICODE);
                    break;
                case "COUPON_PROFIT_REWARD":
                    if(empty($relation_data['action_id'])) return[];
                    $price = Coupon::where("id",$relation_data['action_id'])->value('price');
                    $message = ['content'=>$res['message'],'source'=>'邀请好友','type'=>'优惠券','amount'=>$price,'time'=>$time];
                    $res['message'] = json_encode($message,JSON_UNESCAPED_UNICODE);
                    break;
                case "VIP_PROFIT_REWARD":
                    $message = ['content'=>$res['message'],'source'=>'邀请好友','type'=>'360会员邀请','amount'=>'108.00','time'=>$time];
                    $res['message'] = json_encode($message,JSON_UNESCAPED_UNICODE);
                    break;

                case "WEEK_REWARD":
                    // 周奖励
                    $column = Column::where("id",$relation_data['week_column_id']??0)->value("name");
                    $works = Works::where("id",$relation_data['week_works_id']??0)->value("title");
                    $res['message'] = str_replace("{{column_title}}",$column??"",$res['message']);
                    $res['message'] = str_replace("{{works_title}}",$works??"",$res['message']);

                    break;


                case "END_CAMP_REWARD":
                    // 结营奖励
                    $column = Column::where("id",$relation_data['week_column_id']??0)->value("name");
                    $res['message'] = str_replace("{{column_title}}",$column??"",$res['message']);

                    break;
                case "REGISTER":
                    $user = User::where("id",$relation_data['user_id']??0)->value("nickname");
                    $res['message'] = str_replace("{{user_name}}",$user??"",$res['message']);
                    break;
                case "SYS_USER_SEND_HELP":
                    $message = ['content'=>$res['message'],'source'=>'留言通知','type'=>'留言通知','amount'=>0,'time'=>$time];
                    $res['message'] = json_encode($message,JSON_UNESCAPED_UNICODE);
                    break;
                case 'SYS_FEEDBACK_REPLY':
                    $message = [
                        'nickname'=>$relation_data['user_info']['nickname'],
                        'user_id'=>$relation_data['user_id'],
                        'created_at'=>$relation_data['created_at'],
                        'content'=>$relation_data['content'],
                        'reply'=>$relation_data['reply_content'],
                        'reply_at'=>$relation_data['reply_at'],
                    ];
                    $res['message'] = json_encode($message,JSON_UNESCAPED_UNICODE);
                    break;
                case 'SYS_FEEDBACK_REPORT':
                    // 举报
                    $message = ['content'=>$res['message'],'source'=>'举报通知','type'=>'举报通知','amount'=>0,'time'=>$time];
                    $res['message'] = json_encode($message,JSON_UNESCAPED_UNICODE);
                    break;
            }
        // }
        return  $res;

    }

    static function get_like_msg_type(){
        return  MessageType::query()->whereRaw('id=11 or pid=11')->pluck('id')->toArray();
    }
    static function get_follow_msg_type(){
        return  MessageType::query()->whereRaw('id=22 or pid=22')->pluck('id')->toArray();
    }
    static function get_comment_msg_type(){
        return  MessageType::query()->whereRaw('id=9 or pid=9')->pluck('id')->toArray();
    }
    static function get_profit_msg_type(){
        return  MessageType::query()->whereRaw('id=12 or pid=12')->pluck('id')->toArray();
    }
    static function get_work_new_msg_type(){
        return  MessageType::query()->whereRaw('id=4 or pid=4')->pluck('id')->toArray();
    }
    static function get_system_msg_type(){
        return  MessageType::query()->whereRaw('id=1 or pid=1')->pluck('id')->toArray();
    }

}
