<?php

namespace App\Servers\V5;

use App\Models\Lists;
use App\Models\Message\Message;
use App\Models\Message\MessageUser;
use App\Models\Message\MessageType;
use App\Models\Message\MessageView;
use Illuminate\Support\Facades\DB;
use JPush;
use JPush\Client as JPushClient;

//https://github.com/mitoop/laravel-jpush
//https://docs.jiguang.cn/jpush/server/push/rest_api_v3_push
class JpushService
{

    protected $appkey;
    protected $secret;
    protected $logFile;
    const DB_TABLE = 'nlsg_log_jpush';

    //初始化
    public function __construct()
    {
        $this->appkey = config('services.jpush.app_key');
        $this->secret = config('services.jpush.master_secret');
        $this->logFile = config('services.jpush.log_file');
    }

    //极光别名删除，超过10个别名，删除历史重新绑定
    //https://app.v4.api.nlsgapp.com/api/v4/jpush/remove_alias?user_id=211370
    //http://127.0.0.1:8000/api/v4/jpush/remove_alias?user_id=211370
    public function DeleteAlias($user_id){

        $client   = new JPushClient($this->appkey, $this->secret,$this->logFile);
        $response = $client->device()->deleteAlias(strval($user_id));

        $http_code=isset($response['http_code'])?$response['http_code']:0;
        $now_date=date('Y-m-d H:i:s',time());
        DB::table(self::DB_TABLE)->insertGetId(['http_code'=>$http_code,'type_tag'=>'JPush-DeleteAlias','user_id' => $user_id,'message' => json_encode($response),'created_at'=>$now_date]);

        return true;

    }

    //定时任务执行推送  每分钟执行一次
    public static function TimedPush()
    {
        $time=time();
        $CronTabTime=date('Y-m-d H:i',$time);
        $lists = Message::query()->select(['id','title','message','type','receive_type','relation_type','relation_id','relation_info_id','open_type','url'])//->where('is_timing', 1)
            ->where('status',1)->where('is_jpush',1)->where('timing_send_time','<=',$CronTabTime)
            ->where('id','>',126) //测试时使用，生成环境需注释
            ->orderBy('id','asc')->limit(5)->get()->toArray();
        if (!empty($lists)){
//            self::Printout($lists);
            try {
                foreach ($lists as $item) {
                    //获取拼接扩展字段
                    $extras = self::TypeConversion($item);
                    if ($item['receive_type'] == 1) { //个人
//                        self::Printout($extras);
                        $NowTime=date('Y-m-d H:i:s',time());
                        //任务更新为发送中
                        Message::where('id', $item['id'])->update(['status' => 2, 'plan_time' => $NowTime]);

                        //获取需要发送的分组
                        $GroupIdArr=MessageUser::query()->where('message_id',$item['id'])->groupBy('group_id')->pluck('group_id')->toArray();
                        foreach ($GroupIdArr as $v){
//                            self::Printout($GroupIdArr);
                            //获取每组数据用户
                            $UserInfo=MessageUser::query()->where('message_id',$item['id'])->where('group_id',$v)->where('jpush_msg_id','')
                                ->select(['id','receive_user'])->limit(1000)->get()->toArray();
//                            self::Printout($UserInfo);
                            $UserArr=[];
                            $IdArr=[];
                            foreach ($UserInfo as $key => $val) { //遍历拼接别名
                                $UserArr[]=strval($val['receive_user']);
                                $IdArr[]=$val['id'];
                            }
                            //推送极光
                            $msg_id=self::JPushMsgId($UserArr, ['title' => $item['title'], 'body' => $item['message']], $extras,$item['id'].'_'.$v); //标记分组
                            //更新极光推送id
                            $NowTime=date('Y-m-d H:i:s',time());
                            MessageUser::whereIn('id', $IdArr)->update(['is_send' => 3, 'plan_time' => $NowTime,'jpush_msg_id'=>$msg_id]);
                        }

                        //任务更新为已发送
                        $NowTime=date('Y-m-d H:i:s',time());
                        Message::where('id', $item['id'])->update(['status' => 3, 'plan_time' => $NowTime]);
                    } else if ($item['receive_type'] == 2) { //全员
                        return true;
                        //推送极光
                        $msg_id=self::JPushMsgId('all',  ['title' => $item['title'], 'body' => $item['message']], $extras,$item['id']);
                        //任务更新为已发送
                        $NowTime=date('Y-m-d H:i:s',time());
                        Message::where('id', $item['id'])->update(['status' => 3, 'plan_time' => $NowTime,'jpush_msg_id'=>$msg_id]);
                    }

                }
            }catch (\Exception $e){
                $now_date=date('Y-m-d H:i:s',time());
                DB::table(self::DB_TABLE)->insertGetId(['http_code'=>0,'type_tag'=>'JPush-Send-error','message' => $e->getMessage(),'created_at'=>$now_date]);
            }
        }
    }

//    同步推送   推送速度(共享20万条/秒)  PushAPI-QPS (共享)  推送任务-别名接口 (共享200万/天) 推荐批量任务上午发送     https://www.jiguang.cn/push
//    JPush::pushNow('别名', '通知', '附加信息');        单次推送
//    JPush::pushNow(['别名数组'], '通知', '附加信息');  一次推送最多 1000 个   每一个 alias 的长度限制为 40 字节
//    JPush::pushNow('all', '通知', '附加信息');        每天只能推10次
    //极光推送消息处理，并返回消息id
    public static function JPushMsgId($receive_user,$messageArr,$extras,$mess_id){

        $now_date=date('Y-m-d H:i:s',time());
        $msg_id='';
        try {
            //发送极光消息
            $response=JPush::pushNow($receive_user, $messageArr, $extras);
            $http_code=isset($response['http_code'])?$response['http_code']:0;
            if(isset($response['http_code']) && $response['http_code']==200){
                if(isset($response['body']['msg_id'])){
                    $msg_id= $response['body']['msg_id'];
                }
            }
            $map=[
                'http_code'=>$http_code,
                'type_tag'=>'JPush-Send',
                'user_id' => json_encode($receive_user), //多别名情况(1000人)
                'message' => json_encode($response),
                'mess_id'=>$mess_id,
                'created_at'=>$now_date
            ];
            DB::table(self::DB_TABLE)->insertGetId($map);

        } catch (\JPush\Exceptions\APIConnectionException $e) {
            DB::table(self::DB_TABLE)->insertGetId(['http_code'=>0,'type_tag'=>'JPush-Send-error','message' => $e->getMessage(),'created_at'=>$now_date]);
        } catch (\JPush\Exceptions\APIRequestException $e) {
            DB::table(self::DB_TABLE)->insertGetId(['http_code'=>0,'type_tag'=>'JPush-Send-error','message' => $e->getMessage(),'created_at'=>$now_date]);
        }

        return $msg_id;

    }

    /**
     * 消息类型转换
     * @param $item
     * @return array
     */
    public static function TypeConversion($item)
    {
        //兼容老版本  type  id  info_id  is_sub
        $data=[
            'open_type'=>$item['open_type'], //打开操作 1应用 2链接  3指定页 4图文
            'id' => $item['relation_id'],            //课程id
            'info_id' => $item['relation_info_id'],  //章节id
            'is_sub' => 0,  //评论+点赞 暂时不对接极光
        ];
        if($item['open_type']==2){ //第三方h5
            $data['url'] = $item['url']; //外部h5
        }else if($item['open_type']==4){
            $data['message_id'] = $item['id']; //图文类型 消息id
//            $data['rich_text'] = $item['rich_text']; //图文类型 不在此返回，提供接口获取
        }
        if(in_array($item['relation_type'],[161,162,163])){
            $ListsObj=Lists::query()->where('id',$item['relation_id'])->select('title')->first();
            $data['name']='精彩集锦';
            if(!empty($ListsObj)){
                $data['name']=$ListsObj->title;
            }
        }
        switch ($item['relation_type']){
            case 101 : $data['type']= 1;    break;  //课程详情                id
            case 102 : $data['type']= 102;  break;  //课程章节播放器           id  info_id
            case 111 : $data['type']= 2;    break;  //讲座详情                id
            case 112 : $data['type']= 112;  break;  //讲座章节播放器           id  info_id
            case 121 : $data['type']= 121;  break;  //商城首页
            case 122 : $data['type']= 4;    break;  //电商-商品详情            id
            case 131 : $data['type']= 5;    break;  //直播间详情               id
            case 141 : $data['type']= 7;    break;  //训练营父类               id
            case 142 : $data['type']= 142;  break;  //训练营期数               id
            case 151 : $data['type']= 3;    break;  //360会员介绍页
            case 161 : $data['type']= 161;  break;  //课程列表页               id    name
            case 162 : $data['type']= 162;  break;  //讲座列表页               id    name
            case 163 : $data['type']= 163;  break;  //专题列表页               id    name
            case 171 : $data['type']= 171;  break;  //大咖讲书详情             id
            case 172 : $data['type']= 172;  break;  //大咖讲书章节播放器        id  info_id
            //老版本类型
            case 6 : $data['type']= 6;  break;  //幸福套餐1360
            case 8 : $data['type']= 8;  break;  //商品发货完成
            case 9 : $data['type']= 9;  break;  //认证审核通过
            case 10: $data['type']= 10; break;  //认证审核没有通过
            case 11: $data['type']= 11; break;  //收益返佣提醒收益返佣提醒
            case 12: $data['type']= 12; break;  //回复想法
            case 13: $data['type']= 13; break;  //喜欢你的想法
            case 14: $data['type']= 14; break;  //优惠券到期
            case 15: $data['type']= 15; break;  //钻石会员过期
            case 16: $data['type']= 16; break;  //幸福大使权益到期
            case 17: $data['type']= 17; break;  //专栏到期
            default:  $data['type']= 100;  break;    //APP首页
        }

        return $data;
    }

    //获取极光发送量  到达量  打开量  最多支持100个消息id查询
    //https://docs.jiguang.cn/jpush/server/push/rest_api_v3_report/#_7
    //每5分钟执行一次
    public function Statistics(){

        //每5分钟请求一次，超过发送时间2小时停止请求
        $cron_time=date('Y-m-d H:i:s',time()-3600*2);
        $MessageInfo=Message::query()->where('is_timing', 1)->where('status',3)->where('is_jpush',1)
                    ->where('plan_time','>',$cron_time)->select(['id','jpush_msg_id','receive_type'])->get()->toArray();
//        self::Printout($MessageInfo);
        foreach ($MessageInfo as $k=>$v) {
            if($v['receive_type']==1) { //个人
                $MsgIdArr = MessageUser::query()->where('message_id', $v['id'])->groupBy('group_id')->pluck('jpush_msg_id')->toArray();
            }else if($v['receive_type']==2){ //全员
                $MsgIdArr=[$v['jpush_msg_id']];
            }else{//跳过
                continue;
            }
//            self::Printout($MsgIdArr,2);
            $client = new JPushClient($this->appkey, $this->secret, $this->logFile);
            $response = $client->report()->getReceivedDetail($MsgIdArr);
//            self::Printout($response,2);
//            jpush_received 极光通道用户送达数；包含普通Android用户的通知+自定义消息送达，iOS用户自定义消息送达；如果无此项数据则为 null。
//            android_pns_sent Android厂商用户推送到厂商服务器成功数，计算方式同 Android厂商成功数；如果无此项数据则为 null。
//            android_pns_received Android厂商用户推送达到设备数，计算方式以厂商回调数据为准；如果无此项数据则为 null。20200324新增指标
//            ios_apns_sent iOS 通知推送到 APNs 成功。如果无此项数据则为 null。
//            ios_apns_received iOS 通知送达到设备并成功展示。如果无项数据则为 null。统计该项请参考 集成指南高级功能-通知展示统计 。
//            ios_msg_received iOS 自定义消息送达数。如果无此项数据则为 null。
//            wp_mpns_sent winphone通知送达。如果无此项数据则为 null。
//            quickapp_jpush_received 快应用推送走极光通道送达设备成功的用户数量。
//            quickapp_pns_sent 快应用推送走厂商通道请求成功的用户数量。
            if (isset($response['http_code']) && $response['http_code'] == 200) {
                $android_pns_sent_sum = 0; //安卓发送总数
                $ios_apns_sent_sum = 0; //ios发送总数
                $android_pns_received_sum = 0; //安卓收到总数
                $ios_apns_received_sum = 0; //ios收到总数
                $jpush_received = 0; //总数

                foreach ($response['body'] as $key => $val) {
                    $android_pns_sent_sum += $val['android_pns_sent'];
                    $ios_apns_sent_sum += $val['ios_apns_sent'];
                    $android_pns_received_sum += $val['android_pns_received'];
                    $ios_apns_received_sum += $val['ios_apns_received'];
                }
                $sent_sum = $android_pns_sent_sum + $ios_apns_sent_sum;
                $received_sum = $android_pns_received_sum + $ios_apns_received_sum;

                $jpush_received = ($jpush_received <= 0) ? $sent_sum : $jpush_received; //个人发送不返回数量，默认发送量
                echo '请求发送总量：' . $jpush_received . ' 发送量：' . $sent_sum . ' 到达量：' . $received_sum.PHP_EOL;

                Message::where('id', $v['id'])->update(['send_count' => $sent_sum, 'get_count' => $received_sum]);
            }
        }

    }

    //调试输出
    public static function Printout($data,$type=1){

        var_dump($data);
        if($type==1){ //中断执行
            exit;
        }
    }

    public function test(){

//        self::TimedPush(); //调用定时任务
//        $this->Statistics(); //执行查询发送量
        return ;
//        211370	18810355387  233785	13522507683  324111 15611108302

        $UserArr=MessageUser::query()->where('message_id',1)->pluck('receive_user')->toArray();
        var_dump($UserArr);
        $arr=[];
//        foreach ($UserArr as $key=>$val){
//            $arr[]=strval($val);
//        }
//        $rst=JPush::pushNow($arr, ['title' => '汤蓓老师力荐', 'body' => '汤蓓说养育：100+案例分析，解决带娃难题'], ['type'=>1,'id'=>689]);
//        var_dump($rst);
        exit;

        $rst=JPush::pushNow(strval(211370), ['title' => '汤蓓老师力荐', 'body' => '汤蓓说养育：100+案例分析，解决带娃难题'], ['type'=>1,'id'=>689]);
        var_dump($rst);
        echo PHP_EOL;
        if(isset($rst['http_code']) && $rst['http_code']==200){
            if(isset($rst['body']['msg_id'])){
                echo $rst['body']['msg_id'];
            }
        }
//        $rst=JPush::pushNow(strval(233785), ['title' => '汤蓓老师力荐', 'body' => '汤蓓说养育：100+案例分析，解决带娃难题'], ['type'=>1,'id'=>689]);
//        var_dump($rst);

    }

}
