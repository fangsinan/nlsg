<?php
namespace App\Servers;

use App\Models\LiveDeal;
use App\Models\Order;
use App\Models\Subscribe;
use App\Models\User;
use App\Models\VipUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Predis\Client;

class DealServers
{

    //产品类型  1  经营能量门票 2  一代天骄门票 3  演说能量门票 4  经营能量+360套餐 5  30天智慧父母(亲子)训练营
    public static function TypeArr(){
        return [
            '1'=>'经营能量门票',
            '2'=>'一代天骄门票',
            '3'=>'演说能量门票',
            '4'=>'经营能量+360套餐',
            '5'=>'30天智慧父母(亲子)训练营'
        ];
    }

    //获取成交订单
    public static function getOrderInfo($data,$live_id,$crontab=0)
    {

        $redisConfig = config('database.redis.default');
        $Redis = new Client($redisConfig);
        $Redis->select(0);

        $time=time();
        $key_minute=date('YmdHi',$time).'_order';
        $flag=$Redis->EXISTS($key_minute);
        if($flag==1) { //存在返回1
            return;
        }
        $Redis->setex($key_minute,60,1);//10分钟

        if(!empty($crontab)){ //定时任务执行
            $now = date('Y-m-d', time());
            if(empty($live_id)) {
                //获取当天未抓取的订单
                $LiveOrderObj = Order::query()->where('pay_time', '>', $now . ' 00:00:00')
                    ->where(['type' => 14, 'status' => 1, 'is_deal' => 0])->select(['live_id'])->where('pay_price', '>', 1)->first();
                if (!empty($LiveOrderObj)) {
                    $live_id = $LiveOrderObj->live_id;
                } else {
                    return ['status' => 1, 'data' => [], 'msg' => '没有直播间开播'];
                }
            }else{
                /*$LiveOrderObj = Order::query()->where('pay_time', '>', $now . ' 00:00:00')
                    ->where(['type' => 14, 'status' => 1, 'is_deal' => 0])->select(['live_id'])->where('pay_price', '>', 1)
                    ->groupBy('live_id')->get()->toArray() ?: [];
                if (!empty($LiveOrderObj)) {
                    $user_arr = array_column($LiveOrderObj, 'live_id');
                } else {
                    return ['status' => 1, 'data' => [], 'msg' => '没有直播间开播'];
                }*/
            }
        }

        $fields=[
            'O.id','O.ordernum','O.live_id','O.relation_id','O.user_id','O.pay_price','O.pay_time','invite.twitter_id as twitter_id','O.live_num',
            'U.phone','U.nickname',
            'TJ.phone as invite_phone','TJ.nickname as invite_nickname',
            'B.parent as protect_phone',
            'BU.id as protect_user_id','BU.nickname as protect_nickname',
            'PRD.user_id as profit_user_id','PRD.price as profit_price'
        ];

        $query=DB::table(LiveDeal::DB_ORDER_TABLE.' as O');
//        $query->where('O.pay_time', '>', $data['start_time'].' 00:00:00')->where('O.pay_time', '<', $data['end_time'].' 23:59:59'); //取消时间为了抓取第一天遗漏订单
        $query->where('O.live_id',$live_id); //有没在直播间购买此条件非必须
        $query->where('O.type',14);
        $query->where('O.status',1);
        $query->whereIn('O.relation_id', [1,2,3,4,5,6,7,8]);
        $query->where('O.pay_price','>', 1);
        $query->whereNotIn('O.user_id', [211370,211172]);
        $query->where('U.is_test_pay',0); //排除测试用户
        $query->where('O.is_shill',0); //未退款
        $query->where('O.is_deal',0); //抓取完更新为1

        $query->select($fields)
            ->leftJoin('nlsg_user as U','O.user_id','=','U.id')
            ->leftjoin('nlsg_order as invite',function($query)use($live_id){
                $query->on('O.user_id','=','invite.user_id')->where('invite.live_id','=',$live_id)->where('invite.type','=',10)->where('invite.status','=',1);
            })
            ->leftJoin('nlsg_user as TJ','TJ.id','=','invite.twitter_id')
            ->leftJoin('nlsg_vip_user_bind as B',function($query){ //查看用户是否被绑定
                $query->on('B.son','=','U.phone')->where('B.status','=',1); //排除绑定失效
            })
            ->leftJoin('nlsg_user as BU','B.parent','=','BU.phone')     //查询绑定人信息
            ->leftJoin('nlsg_pay_record_detail as PRD',function ($join){
                $join->on('PRD.ordernum','=','O.ordernum')->where('PRD.type',11);  //会员收益类型11
            })
            ;
        $query->groupBy('O.id');
        $query->orderBy('O.id','asc')->limit(5); //暂时每次收集5条，10条sql长度需设置my.ini配置

//        echo $query->toSql().PHP_EOL;
//        $query->dd(); //dd 阻断流程
//        $query->dump();

        $i = 0;
        do {
            $list=$query->get()->toArray() ?: [];
            if(!empty($list)){
                self::DealInsert($list);
            }else{
                $i++;
            }
        } while ($i < 1);

    }

    //处理执行数据入库
    public static function DealInsert($list){
        $user_arr = array_column($list, 'user_id'); //用户
        //处理用户等级  邀约人等级
        $invite_user_arr = array_column($list, 'twitter_id');  //邀约人       邀约人关系不清
        $protect_user_arr = array_column($list, 'protect_user_id'); //保护人

        //顶级钻石合伙人
        $DiamondArr=self::DiamondInfo($protect_user_arr);
        $UserArr=self::InviteInfo($user_arr,$invite_user_arr,$protect_user_arr);

        $OrderIdArr = array_column($list, 'id'); //用于更新订单状态
        $map=[];
        foreach ($list as $key=>$val){

            //查询第一次预约时间
            $SubInfo=self::getFirstSub($val->user_id);
            //处理渠道
            $QdInfo=self::getQd($val->user_id,$val->live_id);

            $map[]=[
                'ordernum'=>$val->ordernum,
                'live_id'=>(!empty($val->live_id))?$val->live_id:0,
                'type'=>$val->relation_id,
                'user_id'=>$val->user_id,
                'phone'=>$val->phone,
                'nickname'=>$val->nickname,
                'identity'=>(!empty($UserArr[$val->user_id]))?$UserArr[$val->user_id]:0,
                'pay_price'=>$val->pay_price,
                'num'=>$val->live_num,
                'pay_time'=>$val->pay_time,
                'invite_user_id'=>(empty($val->twitter_id))?0:$val->twitter_id,
                'invite_phone'=>(empty($val->invite_phone))?'':$val->invite_phone,
                'invite_nickname'=>(empty($val->invite_nickname))?'':$val->invite_nickname,
                'invite_identity'=>(!empty($UserArr[$val->twitter_id]))?$UserArr[$val->twitter_id]:0,
                'created_at'=>$val->pay_time,
                'sub_live_id'=>$SubInfo['sub_live_id'],
                'sub_live_pay_price'=>$SubInfo['sub_live_pay_price'],
                'sub_live_pay_time'=>$SubInfo['sub_live_pay_time'],
                'protect_user_id'=>(empty($val->protect_user_id))?0:$val->protect_user_id,
                'protect_phone'=>(empty($val->protect_phone))?'':$val->protect_phone,
                'protect_nickname'=>(empty($val->protect_nickname))?'':$val->protect_nickname,
                'protect_identity'=>(!empty($UserArr[$val->protect_user_id]))?$UserArr[$val->protect_user_id]:0,
                'profit_user_id'=>$val->profit_user_id,
                'profit_price'=>$val->profit_price,
                'diamond_user_id'=>(empty($DiamondArr[$val->protect_user_id]))?0:$DiamondArr[$val->protect_user_id]['diamond_user_id'],
                'diamond_phone'=>(empty($DiamondArr[$val->protect_user_id]))?'':$DiamondArr[$val->protect_user_id]['diamond_phone'],
                'diamond_nickname'=>(empty($DiamondArr[$val->protect_user_id]))?'':$DiamondArr[$val->protect_user_id]['diamond_nickname'],
                'diamond_identity'=>(empty($DiamondArr[$val->protect_user_id]))?0:$DiamondArr[$val->protect_user_id]['diamond_identity'],
                'is_tiktok'=>$QdInfo['is_tiktok'],
                'tiktok_ordernum'=>$QdInfo['tiktok_ordernum'],
                'tiktok_time'=>$QdInfo['tiktok_time'],
                'qd'=>$QdInfo['qd']
            ];
        }
        $now_date = date('Y-m-d H:i:s');
        DB::beginTransaction();
        try {
            $DealAddRst = LiveDeal::Add($map, true);
            if ($DealAddRst === false) {
                DB::rollBack();
//                    Log::channel('aliCrontabPullLog')->info('抓取入库失败：'.json_encode($map));
                return ['status' => 0, 'data' => [],'msg'=>'抓取入库失败'];
            }
            //更新订单抓取状态
            $DealOrderUpRst=DB::table(LiveDeal::DB_ORDER_TABLE)
                ->whereIn('id', $OrderIdArr)
                ->update(['is_deal' => 1,'updated_at' => $now_date]);
            if($DealOrderUpRst===false){
                DB::rollBack();
                return ['status' => 0, 'data' => [],'msg'=>'更新订单状态失败'];
            }
        }catch (\Exception $e){
            DB::rollBack();
            return ['status' => 0, 'data' => [],'msg'=>$e->getMessage()];
        }
        DB::commit();

        return ['status'=>1,'data'=>[],'msg'=>'抓取成功'];

    }

    //处理顶级合伙人信息
    public static function DiamondInfo($user_arr){
        //顶级钻石合伙人
        $query=DB::table('nlsg_vip_user as VIP');
        $query->whereIn('VIP.user_id', $user_arr);
        $query->where('VIP.status',1)->where('VIP.is_default',1);
        $query->where('U.id','>',0);
        $query->select(['VIP.user_id','U.id','U.phone','U.nickname'])
            ->leftJoin('nlsg_user as U','U.id','=','VIP.source')
        ;

//        echo $query->toSql().PHP_EOL;
//        $query->dd(); //dd 阻断流程

        $DiamondInfo=$query->get()->toArray() ?: [];
        $DiamondArr=[];
        if(!empty($DiamondInfo)){
            foreach ($DiamondInfo as $k=>$v){
                $DiamondArr[$v->user_id]=[
                    'diamond_user_id'=>$v->id,
                    'diamond_phone'=>$v->phone,
                    'diamond_nickname'=>$v->nickname,
                    'diamond_identity'=>2 //钻石
                ];
            }
        }

        return $DiamondArr;
    }

    //处理邀约人信息
    public static function InviteInfo($user_arr,$invite_user_arr,$protect_user_arr){

        $arr=array_merge($user_arr,$invite_user_arr,$protect_user_arr);
        $User_list = VipUser::query()->whereIn('user_id', $arr)
            ->where(['is_default'=>1,'status'=>1])
            ->select(['id', 'user_id', 'level'])
            ->get();
        $UserArr=[];
        if($User_list->isNotEmpty()){
            $User_list=$User_list->toArray();
            foreach ($User_list as $key=>$val){
                $UserArr[$val['user_id']]=$val['level'];
            }
        }

        return $UserArr;

    }

    //获取第一次预约时间
    public static function getFirstSub($user_id){

        //预约信息
        $sub_live_id=0;
        $sub_live_pay_price=0;
        $sub_live_pay_time=null;

        //第一优先级订阅表
        $subLiveInfo=Subscribe::query()->where(['user_id'=>$user_id,'type'=>3,'status'=>1])->first();
        if(!empty($subLiveInfo)){
            $sub_live_id=$subLiveInfo->relation_id;
            if(!empty($subLiveInfo->pay_time)){
                $sub_live_pay_time=$subLiveInfo->pay_time;
            }else{
                $sub_live_pay_time=$subLiveInfo->created_at;
            }
            if(!empty($subLiveInfo->channel_order_sku)){ //抖音
                $sub_live_pay_price=9.9; //抖音默认9.9
            }else if(!empty($subLiveInfo->order_id)) { //有订单
                $SubOrder = Order::where(['id' => $subLiveInfo->order_id])->first();
                $sub_live_pay_price = $SubOrder->pay_price;
            }
        }

        return [
            'sub_live_id'=>$sub_live_id,
            'sub_live_pay_price'=>$sub_live_pay_price,
            'sub_live_pay_time'=>$sub_live_pay_time,
        ];

    }

    //处理渠道
    public static function getQd($user_id,$live_id)
    {

        //抖音信息
        $is_tiktok=0;
        $tiktok_ordernum='';
        $tiktok_time=null;
        $qd=0;
        //李婷老师账号  测试推广0.1有1052单（211370  324111  769159朋勇）
        $user_arr=User::query()->where(['is_qd_push'=>2])->get(['id','phone'])->toArray();
        $arr = array_column($user_arr, 'id'); //用户
        //地推账号
        $dt_user_arr=User::query()->where(['is_qd_push'=>1])->get(['id','phone'])->toArray();
        $dt_arr = array_column($dt_user_arr, 'id'); //用户
        //获取当前直播间
        $subLiveInfo=Subscribe::query()->where(['user_id'=>$user_id,'type'=>3,'relation_id'=>$live_id,'status'=>1])->first();
        //渠道 1 抖音 2 李婷 3 自有平台 4地推
        if(!empty($subLiveInfo)){
            if(!empty($subLiveInfo->channel_order_sku)){ //抖音
                $is_tiktok=1;
                $tiktok_ordernum=$subLiveInfo->channel_order_id;
                $tiktok_time=$subLiveInfo->created_at;
                $qd=1;
            }else if(!empty($subLiveInfo->order_id)) { //有订单
                //鉴别是否为李婷老师
                $SubOrder = Order::where(['id' => $subLiveInfo->order_id])->first();
                if(in_array($SubOrder->twitter_id,$arr)) {
                    $qd = 2;
                }
            }else if(!empty($subLiveInfo->twitter_id)){
                if(in_array($subLiveInfo->twitter_id,$arr)) { //李婷
                    $qd = 2;
                }else if(in_array($subLiveInfo->twitter_id,$dt_arr)){ //地推
                    $qd = 4;
                }
            }
        }else{
            //不是当前直播间用户，老用户观看情况
            $subLiveInfo=Subscribe::query()->where(['user_id'=>$user_id,'type'=>3,'status'=>1])->first();
            if(!empty($subLiveInfo)){
                if(!empty($subLiveInfo->channel_order_sku)){ //抖音
                    $is_tiktok=1;
                    $tiktok_ordernum=$subLiveInfo->channel_order_id;
                    $tiktok_time=$subLiveInfo->created_at;
                    $qd=1;
                }else if(!empty($subLiveInfo->order_id)) { //有订单
                    //鉴别是否为李婷老师
                    $SubOrder = Order::where(['id' => $subLiveInfo->order_id])->first();
                    if(in_array($SubOrder->twitter_id,$arr)) {
                        $qd = 2;
                    }
                }
            }
        }

        return [
            'is_tiktok'=>$is_tiktok,
            'tiktok_ordernum'=>$tiktok_ordernum,
            'tiktok_time'=>$tiktok_time,
            'qd'=>$qd
        ];


    }


}
