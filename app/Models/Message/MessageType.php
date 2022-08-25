<?php


namespace App\Models\Message;


use App\Models\Base;
use App\Models\Coupon;
use App\Models\PayRecordDetail;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MessageType extends Base
{
    const DB_TABLE = 'nlsg_message_type';

    protected $table = 'nlsg_message_type';

    protected $fillable = [
        'title', 'created_at', 'updated_at',
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
        //1保留父子层级 2只返回可用

        $list = self::query()
            ->where('pid', '=', 0)
            ->with(['childList:id,title,pid'])
            ->select(['id', 'title', 'pid'])
            ->get();
        if ($flag === 1){
            return $list;
        }

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
        if($messageType['pid'] == 12){
            $time = date("Y-m-d H:i:s");
            $message = "";
            switch ($action_const){
                case "LIVE_PROFIT_REWARD":
                    if(empty($relation_data['action_id'])) return[];
                    // 查询收益金额
                    $price = PayRecordDetail::where("id",$relation_data['action_id'])->value('price');
                    $message = ['content'=>'恭喜你,刚刚获得了一笔收益奖励','source'=>'邀请好友-直播','type'=>'现金奖励','amount'=>$price,'time'=>$time];
                    break;
                case "COUPON_PROFIT_REWARD":
                    if(empty($relation_data['action_id'])) return[];
                    $price = Coupon::where("id",$relation_data['action_id'])->value('price');
                    $message = ['content'=>'恭喜你,刚刚获得了一笔收益奖励','source'=>'邀请好友','type'=>'优惠券','amount'=>$price,'time'=>$time];
                    break;
                case "VIP_PROFIT_REWARD":
                    $message = ['content'=>'恭喜你,刚刚获得了一笔收益奖励','source'=>'邀请好友','type'=>'360会员邀请','amount'=>'108.00','time'=>$time];
                    break;
            }

            $res['message'] = json_encode($message,JSON_UNESCAPED_UNICODE);
        }
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
