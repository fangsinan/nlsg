<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use App\Models\AgentProfitLog;
use App\Models\Column;
use App\Models\Coupon;
use App\Models\GetPriceTools;
use App\Models\MallOrderDetails;
use App\Models\Order;
use App\Models\PayRecord;
use App\Models\PayRecordDetail;
use App\Models\PayRecordDetailStay;
use App\Models\RedeemCode;
use App\Models\Subscribe;
use App\Models\User;
use App\Models\VipRedeemUser;
use App\Models\VipUser;
use App\Models\Works;
use App\Models\MallOrder;
use App\Servers\JobServers;
use Illuminate\Support\Facades\DB;

class WechatPay extends Controller
{

    static public $pay_record_type = [
        //订单表支付方式'1 微信端 2app微信 3app支付宝 4ios'   => 记录表支付方式1 微信  2支付宝 3扣款  4苹果 5六周年兑换卡
        1 => 1,
        2 => 1,
        3 => 2,
        4 => 4,
    ];

    public static function PayStatusUp($data)
    {
        //1专栏 2会员 5打赏 8商品(包括书籍)
        //9精品课 听课 10借书 月卡 季卡 违约金 11直播视频 12预约回放（必填）
        if ($data['attach'] == 1) { //处理专栏  OK
            return self::PayColumn($data);
        } elseif ($data['attach'] == 2) { //处理会员
            return self::PayVip($data);
        } elseif ($data['attach'] == 5) { //打赏 OK
            return self::Areward($data);
        } elseif ($data['attach'] == 8) { //电商产品支付  OK
            return self::mallOrder($data);
        } elseif ($data['attach'] == 9) { //精品课 OK  代理商返佣50%
            return self::PayExcellent($data);
        } elseif ($data['attach'] == 10) { //借书 月卡 季卡 违约金
            return self::BorrowBooks($data);
        } elseif ($data['attach'] == 11) { // 处理直播视频
            return self::PayLive($data);
        } elseif ($data['attach'] == 12) { // 直播预约
            return self::PayLiveAppointment($data);
        } elseif ($data['attach'] == 13) { // 能量币充值
            return self::PayCoinOrder($data);
        } elseif ($data['attach'] == 14) { // 线下产品门票
            return self::PayOfflineProducts($data);
        } elseif ($data['attach'] == 8) { //电商
            return self::mallOrder($data);
        } elseif ($data['attach'] == 15) { //处理讲座
            return self::PayColumn($data);
        } elseif ($data['attach'] == 16) { //处理360
            return self::PayNewVip($data);
        } elseif ($data['attach'] == 17) { //处理赠送
            return self::PaySend($data);
        }
    }



    //微信购买vip---
    public static function PayNewVip($data)
    {

        $time = time();
        $out_trade_no = $data['out_trade_no'];
        $total_fee = $data['total_fee'];
        $transaction_id = $data['transaction_id'];
        $pay_type = $data['pay_type'];


        //支付处理正确-判断是否已处理过支付状态
        $orderInfo = Order::select()->where(['ordernum' => $out_trade_no, 'status' => 0])->first();

        if (!empty($orderInfo)) {
            $orderInfo = $orderInfo->toArray();

            DB::beginTransaction();
            try {
                $orderId = $orderInfo['id'];
                $live_id = $orderInfo['live_id'];
                //更新订单状态
                $starttime = date('Y-m-d H:i:s', $time);
                $endtime   = date("Y-m-d 23:59:59",strtotime("+1years",time()));

                $user_id = $orderInfo['user_id']; //用户
                //更新订单状态
                $data1 = [
                    'status' => 1,
                    'pay_time' => date("Y-m-d H:i:s", $time),
                    'pay_price' => $total_fee,
                    'pay_type' => $pay_type,
                ];
                $orderRst = Order::where(['ordernum' => $out_trade_no])->update($data1);

                //添加支付记录
                $record = [
                    'ordernum' => $out_trade_no, //订单编号
                    'price' => $total_fee, //支付金额
                    'transaction_id' => $transaction_id, //流水号
                    'user_id' => $user_id, //会员id
                    'type' => $pay_type, //1：微信  2：支付宝
                    'client' => 1, //微信
                    'order_type' => 17, //360会员
                    'status' => 1,
                ];
                $recordRst = PayRecord::firstOrCreate($record);
                $Sy_Rst  = true;
                $map     = [];
                $AdminInfo = User::find($user_id);
                //获取用户信息
                $twitter_id    = $orderInfo['twitter_id'];
                $twitter = [];
                if($twitter_id > 0 ){
                    //vip 信息
                    $twitter = VipUser::where(['user_id'=>$twitter_id,'is_default'=>1,'status'=>1])->first()->toArray();
                }

                // 后台查询需要  $source $source_vip_id  推广总代理商一直不变
                if($twitter ){
                    if($twitter['level'] == 2){ // 只要升级为2级代理商  就默认为第一级
                        $source = $twitter_id;
                        $source_vip_id = $twitter['id'];

                    }else{
                        $source = $twitter['source'];
                        $source_vip_id = $twitter['source_vip_id'];
                    }
                }else{
                    $source = 0;
                    $source_vip_id = 0;
                    $twitter['id'] = 0;
                }
                $supremacy_vip = $orderInfo['relation_id']; //会员 1 360会员
                $Userdata['user_id'] = $user_id;
                $Userdata['level'] = $supremacy_vip;
                $Userdata['username'] = $AdminInfo['phone'];
                $Userdata['nickname'] = $AdminInfo['nickname'];
                $Userdata['inviter'] = $twitter_id;
                $Userdata['source']     = $source;
                $Userdata['source_vip_id']     = $source_vip_id;
                $Userdata['is_default'] = 1;
                $Userdata['inviter_vip_id'] = $twitter['id'];
                $Userdata['expire_time'] = $endtime;
                $Userdata['start_time'] = $starttime;
                $Userdata['status'] = 1;
                if($supremacy_vip == 2){ // 支付一千元定金   状态为待审核
                    $Userdata['status'] = 0;
                }
                $vip_order_type = $orderInfo['vip_order_type']; //1开通 2续费

                //$UserAttInfo=$newVipModel->db->where()->getOne($newVipModel::$table,'*');
                $UserAttInfo = VipUser::where(['user_id'=>$user_id])->first();
                if($UserAttInfo){
                    $UserAttInfo = $UserAttInfo->toArray();
                    $level = $UserAttInfo['level'];
                }else{
                    $level = 0;
                }

                $vip_id = 0;
                $newVip_rst = true;
                //当有效身份不是钻石合伙人，对vip_user表进行任何处理

                if($level != 2){
                    if($supremacy_vip == 1){   //支付定金不需要走vip表操作
                        if($vip_order_type == 1){
                            //开通的情况
                            if(!empty($UserAttInfo) ) {
                                //数据存在时  将状态改为0
                                $orderRst = VipUser::where(['user_id'=>$user_id])->update(['is_default'=>0]);
                                //$newVipModel->update($newVipModel::$table,['is_default'=>0],['user_id'=>$user_id]);
                            }
                            $newVip_rst = VipUser::firstOrCreate($Userdata);
                            $vip_id = $newVip_rst;  // 新增时写入
                        }else{
                            //过期时间延长一年   权益归属不发生改变
                            $Userdata = [
                                'expire_time' => date('Y-m-d H:i:s',strtotime($UserAttInfo['expire_time'])+31536000),
                            ];
                            $newVip_rst = VipUser::where(['user_id'=>$user_id])->update($Userdata);

                            $twitter_top = explode('->', $orderInfo['remark']);
                            if($twitter_top[1] > 0){
                                $twitter_id = $twitter_top[1];
                            }else{
                                $twitter_id = $UserAttInfo['inviter'];
                            }

                            //查看当前有效用户
                            $UserAttInfo = VipUser::where(['user_id'=>$user_id,'status'=>1,'is_default'=>1])->first()->toArray();

                            $vip_id = $UserAttInfo['id'];

                            $Userdata['inviter_vip_id'] = $UserAttInfo['inviter_vip_id'];//新增时已经写入

                        }
                    }
                }else{
                    //当有效身份为钻石合伙人，对vip_user表进行任何处理
                    if( $UserAttInfo['is_open_360'] == 1){
                        $VipUserData = [
                            'time_end_360' => date('Y-m-d H:i:s',strtotime($UserAttInfo['time_end_360'])+31536000),
                        ];
                    }else{
                        $VipUserData = [
                            'is_open_360'=>1,
                            'time_begin_360'=>$starttime,
                            'time_end_360'=>$endtime,
                        ];
                    }
                    $newVip_rst = VipUser::where(['user_id'=>$user_id])->update($VipUserData);
                }


                //服务商购买时已是优惠价格
                //购买必须为360会员
                $PayRDObj = new PayRecordDetail();
                if ($supremacy_vip == 1 && !empty($twitter_id)) { //推客是自己不算 服务商赠送不返利
                    $tk_vip = VipUser::IsNewVip($twitter_id);

                    if ( $tk_vip && $supremacy_vip == 1) {   //目前只有360会员有收益
                        $ProfitPrice = GetPriceTools::Income(0,$tk_vip,0,5);

                        if($ProfitPrice>0) {
                            $map = array ('user_id' => $twitter_id, "type" => 11, "ordernum" => $out_trade_no, 'price' => $ProfitPrice, "ctime" => $time,'vip_id'=>$vip_id,'user_vip_id'=>$Userdata['inviter_vip_id']);

                        }
                    }
                    if (!empty($map)) {
                        //防止重复添加收入
                        $where = ['user_id'=>$map['user_id'],'type'=>$map['type'],'ordernum'=>$map['ordernum']];
                        $PrdInfo = PayRecordDetail::where($where)->first('id');
                        if (empty($PrdInfo)) {
                            $Sy_Rst = VipUser::firstOrCreate($map);
                        }
                    }

                }
                //受保护的人 需要给推荐人[非保护者] 加一个收益为0的数据
                $top_Sy_Rst = true;
                $twitter_top = explode('->', $orderInfo['remark']);
                if($twitter_top[0] > 0){
                    $twitter_top_vip_id = VipUser::where(['user_id'=>$twitter_top[0],'is_default'=>1,'status'=>1])->first('id');
                    $top_map = array ('user_id' => $twitter_top[0], "type" => 11, "ordernum" => $out_trade_no, 'price' => 0, "ctime" => $time,'vip_id'=>$vip_id,'user_vip_id'=>$twitter_top_vip_id->id);
                    $top_Sy_Rst = VipUser::firstOrCreate($top_map);
                }

                //  升级续费都需要进行精品课赠送     已经购买的需要折算兑换码
                //查询关注里是否有这些课程   有的话是送优惠券  没有直接添加
                $add_sub_Rst = true;
                if($supremacy_vip == 1){
                    //use
                    $add_sub_Rst = VipRedeemUser::subWorksOrGetRedeemCode($user_id);
                }

                $user_id = empty($orderInfo['service_id']) ? $user_id : $orderInfo['service_id'];
                $userRst = WechatPay::UserBalance($pay_type, $user_id, $orderInfo['price']);

                if ($newVip_rst && $orderRst && $recordRst && $Sy_Rst && $userRst  && $add_sub_Rst && $top_Sy_Rst) {
                    DB::commit();
                    return true;
                } else {
                    DB::rollBack();
                    return false;

                }
            } catch (\Exception $e) {
                DB::rollBack();
                return false;
            }
        } else {
            return true;
        }
    }


    //微信购买线下产品门票
    public static function PayOfflineProducts($data)
    {
        $time = time();
        $out_trade_no = $data['out_trade_no'];
        $total_fee = $data['total_fee'];
        $transaction_id = $data['transaction_id'];
        $pay_type = $data['pay_type'];


        //支付处理正确-判断是否已处理过支付状态
        $orderInfo = Order::select()->where(['ordernum' => $out_trade_no, 'status' => 0])->first();

        if (!empty($orderInfo)) {
            $orderInfo = $orderInfo->toArray();

            DB::beginTransaction();
            try {
                $orderId = $orderInfo['id'];
                $live_id = $orderInfo['live_id'];
                //更新订单状态

                $user_id = $orderInfo['user_id']; //用户
                //更新订单状态
                $data1 = [
                    'status' => 1,
                    'pay_time' => date("Y-m-d H:i:s", $time),
                    'pay_price' => $total_fee,
                    'pay_type' => $pay_type,
                ];
                $orderRst = Order::where(['ordernum' => $out_trade_no])->update($data1);

                //添加支付记录
                $record = [
                    'ordernum' => $out_trade_no, //订单编号
                    'price' => $total_fee, //支付金额
                    'transaction_id' => $transaction_id, //流水号
                    'user_id' => $user_id, //会员id
                    'type' => 1, //1：微信  2：支付宝
                    'client' => 1, //微信
                    'order_type' => 21, //
                    'status' => 1,
                ];
                $recordRst = PayRecord::firstOrCreate($record);

                $subscribe = [
                    'user_id' => $user_id, //会员id
                    'pay_time' => date("Y-m-d H:i:s", $time), //支付时间
                    'type' => 5, //门票
                    'status' => 1,
                    'order_id' => $orderId, //订单id
                    'relation_id' => $orderInfo['relation_id'],
                ];
                $subscribeRst = Subscribe::firstOrCreate($subscribe);


                $userRst = WechatPay::UserBalance($pay_type, $user_id, $orderInfo['price']);
                if ($orderRst  && $recordRst && $subscribeRst && $userRst) {
                    DB::commit();
                    return true;

                } else {
                    DB::rollBack();
                    return false;
                }

            } catch (\Exception $e) {
                DB::rollBack();
                return false;
            }

        } else {
            return true;
        }
    }

    //微信购买直播
    public static function PayLive($data)
    {
        $time = time();
        $out_trade_no = $data['out_trade_no'];
        $total_fee = $data['total_fee'];
        $transaction_id = $data['transaction_id'];
        $pay_type = $data['pay_type'];


        //支付处理正确-判断是否已处理过支付状态
        $orderInfo = Order::select()->where(['ordernum' => $out_trade_no, 'status' => 0])->first();

        if (!empty($orderInfo)) {
            $orderInfo = $orderInfo->toArray();

            DB::beginTransaction();
            try {
                $orderId = $orderInfo['id'];
                $live_id = $orderInfo['live_id'];
                //更新订单状态

                $user_id = $orderInfo['user_id']; //用户
                //更新订单状态
                $data1 = [
                    'status' => 1,
                    'pay_time' => date("Y-m-d H:i:s", $time),
                    'pay_price' => $total_fee,
                    'pay_type' => $pay_type,
                ];
                $orderRst = Order::where(['ordernum' => $out_trade_no])->update($data1);




                //添加支付记录
                $record = [
                    'ordernum' => $out_trade_no, //订单编号
                    'price' => $total_fee, //支付金额
                    'transaction_id' => $transaction_id, //流水号
                    'user_id' => $user_id, //会员id
                    'type' => $pay_type, //1：微信  2：支付宝
                    'order_type' => 16,//nlsg_pay_record表type 16直播
                    'status' => 1
                ];
                $recordRst = PayRecord::firstOrCreate($record);

                $subscribe = [
                    'user_id' => $user_id, //会员id
                    'pay_time' => date("Y-m-d H:i:s", $time), //支付时间
                    'type' => 3, //直播
                    'status' => 1,
                    'order_id' => $orderId, //订单id
                    'relation_id' => $live_id,
                ];
                $subscribeRst = Subscribe::firstOrCreate($subscribe);



                //推客收益
                $twitter_id = $orderInfo['twitter_id'];
                $Profit_Rst = true;
//                if ( !empty($twitter_id) && $twitter_id != $user_id ) {
//                    //固定收益50
//                    $ProfitPrice = 50;
//                    $map = array('user_id' => $twitter_id, "type" => 10, "ordernum" => $out_trade_no, 'price' => $ProfitPrice,);
//                    if (!empty($map)) {
//                        //$PayRDObj = new PayRecordDetail();
//                        //防止重复添加收入
//                        $where = ['user_id' => $map['user_id'], 'type' => $map['type'], 'ordernum' => $map['ordernum']];
//                        $PrdInfo = PayRecordDetail::where($where)->first();
//                        if (empty($PrdInfo)) {
//                            $Profit_Rst = PayRecordDetail::create($map);
//                        }
//                    }
//                }

                $userRst = WechatPay::UserBalance($pay_type, $user_id, $orderInfo['price']);

                if ($orderRst  && $recordRst && $subscribeRst && $userRst && $Profit_Rst) {
                    DB::commit();
                    return true;

                } else {
                    DB::rollBack();
                    return false;
                }

            } catch (\Exception $e) {
                DB::rollBack();
                return false;
            }

        } else {
            //订单状态已更新，直接返回
            return true;
        }
    }



    public static function mallOrder($data)
    {
        $myfile = fopen("pay_cb.txt", "a+") or die("Unable to open file!");
        $txt = date('Y-m-d H:i:s') . " \r\n" . json_encode($data) . "\r\n";
        fwrite($myfile, $txt);
        fclose($myfile);

        $now = time();
        $now_date = date('Y-m-d H:i:s', $now);
        //$ordernum = substr($data['out_trade_no'], 0, -5);
        $ordernum = $data['out_trade_no'];
        $pay_price = $data['total_fee'];
        $transaction_id = $data['transaction_id'];

        $order_obj = MallOrder::where('ordernum', '=', $ordernum)
            ->where('status', '=', 1)->first();

        DB::beginTransaction();
        //修改订单支付状态
        if ($order_obj->post_type == 1) {
            //邮寄
            $order_obj->status = 10; //待发货
        } else {
            $order_obj->status = 20; //待收货
        }
        $order_obj->pay_type = $data['pay_type'];
        $order_obj->pay_time = $now_date;
        $order_obj->pay_price = $pay_price;

        $order_res = $order_obj->save();
        if (!$order_res) {
            DB::rollBack();
            return false;
        }

        //添加支付记录
        $payRecordModel = new PayRecord();
        $payRecordModel->ordernum = $ordernum;
        $payRecordModel->price = $pay_price;
        $payRecordModel->transaction_id = $transaction_id;
        $payRecordModel->user_id = $order_obj->user_id;
        $payRecordModel->type = $data['pay_type'];
        $payRecordModel->order_type = 10;
        $payRecordModel->status = 1;
        $pr_res = $payRecordModel->save();
        if (!$pr_res) {
            DB::rollBack();
            return false;
        }

        //如果是拼团订单  需要查看拼团订单是否成功
        if ($order_obj->order_type == 3) {
            $temp_data = DB::table('nlsg_mall_group_buy_list')
                ->where('user_id', '=', $order_obj->user_id)
                ->where('order_id', '=', $order_obj->id)
                ->first();
            if (!$temp_data) {
                DB::rollBack();
                return false;
            }
            $group_buy_id = $temp_data->group_buy_id;
            $sp_info = DB::table('nlsg_special_price')
                ->find($group_buy_id);
            $need_num = $sp_info->group_num;

            $now_num = DB::table('nlsg_mall_group_buy_list')
                ->where('group_key', '=', $order_obj->group_key)
                ->count();

            if ($now_num >= $need_num) {
                $gb_res = MallOrderGroupBuy::where(
                    'group_key', '=', $order_obj->group_key
                )->update(
                    [
                        'is_success' => 1,
                        'success_at' => $now_date
                    ]
                );
                if (!$gb_res) {
                    DB::rollBack();
                    return false;
                }
            }
        }

        //收益表
        $order_details = MallOrderDetails::where('order_id', '=', $order_obj->id)->get();

        foreach ($order_details as $od_v) {

            if ($od_v->inviter) {

                $temp_stay_data = [
                    'type' => 5, //电商推客
                    'ordernum' => $order_obj->ordernum,
                    'order_detail_id' => $od_v->id,
                    'user_id' => $od_v->inviter,
                    'price' => GetPriceTools::PriceCalc('*', $od_v->t_money, $od_v->num),
                ];

                $check_stay = PayRecordDetailStay::where('ordernum', '=', $order_obj->ordernum)
                    ->where('order_detail_id', '=', $od_v->id)
                    ->where('user_id', '=', $od_v->inviter)
                    ->first();

                if (!$check_stay) {
                    $stay_res = DB::table('nlsg_pay_record_detail_stay')->insert($temp_stay_data);
                    if (!$stay_res) {
                        DB::rollBack();
                        return false;
                    }
                }

            }

        }
        DB::commit();
        return true;
    }

    //微信购买专栏问题
    public static function PayColumn($data)
    {
        $time = time();
        //$out_trade_no   = substr($data['out_trade_no'], 0, -5);
        $out_trade_no = $data['out_trade_no'];
        $total_fee = $data['total_fee'];
        $transaction_id = $data['transaction_id'];
        $pay_type = $data['pay_type'];

        //支付处理正确-判断是否已处理过支付状态
        $orderInfo = Order::select()->where(['ordernum' => $out_trade_no, 'status' => 0])->first();
        if (!empty($orderInfo)) {
            $orderInfo = $orderInfo->toArray();
            $starttime = strtotime(date('Y-m-d', $time));
            $endtime = strtotime(date('Y', $starttime) + 1 . '-' . date('m-d', $starttime)) + 86400; //到期日期

            DB::beginTransaction();
            try {

                $teacher_id = $orderInfo['relation_id']; //专栏老师
                $coupon_id = $orderInfo['coupon_id']; //优惠券
                $user_id = $orderInfo['user_id']; //用户
                $orderId = $orderInfo['id']; //订单id
                $twitter_id = $orderInfo['twitter_id']; //推客id
                //更新订单状态
                $data1 = [
                    'status' => 1,
                    'pay_time' => date("Y-m-d H:i:s", $time),
                    'pay_price' => $total_fee,
                    'start_time' => date("Y-m-d H:i:s", $starttime),
                    'end_time' => date("Y-m-d H:i:s", $endtime),
                    'pay_type' => $pay_type,
                ];
                $orderRst = Order::where(['ordernum' => $out_trade_no])->update($data1);

                $couponRst = 1;
                //消除优惠券
                if ($coupon_id > 0) {
                    $couponRst = Coupon::where('id', $coupon_id)->update(['status' => 2, 'used_time' => date("Y-m-d H:i:s",$time)]);
                }
                $phoneRst = 1;
                //防止短信发送不成功
                $AdminInfo = User::find($user_id);
                if (!empty($AdminInfo) && !empty($AdminInfo['username'])) {
                    if (preg_match('/^(1)\d{10}$/', $AdminInfo['username'])) {
                        //添加短信通知
                        $phoneArr = [
                            'type' => 2, //短信推送
                            'author_id' => $teacher_id,
                            'user_id' => $user_id,
                        ];
                    }
                }

                $record_type = self::$pay_record_type[$data['pay_type']] ?? 0;
                //添加支付记录
                $order_type = 1;
                if ($orderInfo['type'] == 15) {
                    $order_type = 19;  //讲座
                }
                $record = [
                    'ordernum' => $out_trade_no, //订单编号
                    'price' => $total_fee, //支付金额
                    'transaction_id' => $transaction_id, //流水号
                    'user_id' => $user_id, //会员id
                    'type' => $pay_type, //1：微信  2：支付宝
//                    'client'         => $data['client'],            //app微信
                    'order_type' => $order_type, //1 专栏 2 会员  3充值  4财务打款 5 打赏
                    'status' => 1                           //收入
                ];

                $recordRst = PayRecord::firstOrCreate($record);

                $Sy_Rst = true;
                $shareSyRst = true;
                $map = [];


                if (!empty($twitter_id) && $orderInfo['twitter_id'] != $orderInfo['service_id']) {

                    $isFlag = User::getIncomeFlag($twitter_id, $user_id); //获取是否可返利
                    //$isFlag=Profit::GetIncomeFlag
                    if ($isFlag) { //推客是自己不算
                        //查看用户权限
//                        $TwitterInfo = $UserObj->GetLevel($twitter_id);
//                        $is_twitter  = $UserObj->IsTweeter($TwitterInfo);
                        $TwitterInfo = User::find($twitter_id);
                        $is_twitter = User::getLevel($twitter_id);
                        if ($is_twitter > 1) { //是推客 皇钻 黑钻
                            $ColumnInfo = Column::find($teacher_id)->toArray();
//                            $ColumnObj   = new Column();
//                            $ColumnInfo  = $ColumnObj->getOne($ColumnObj::$table,['user_id'=>$teacher_id],['price,twitter_price']);

                            $ProfitPrice = 0;
                            if (in_array($TwitterInfo['level'], [2, 3, 4])) {
                                $ProfitPrice = GetPriceTools::Income(0, $TwitterInfo['level'], 0, 1, $teacher_id);
                            } else if ($TwitterInfo['level'] == 5) {

                                //服务商
//                                $AgentProfitObj=new AgentProfitLog();
//                                $where = ['user_id'=>$twitter_id,'type'=>[1,2,3],'status'=>1];
//                                $ProfitInfo = $AgentProfitObj->getOne($AgentProfitObj::$table,$where,['sum(price) price']);

                                $ProfitInfoPrice = AgentProfitLog::where(['user_id' => $twitter_id, 'status' => 1])->wherIn('type', [1, 2, 3])->sum('price');
                                if (empty($ProfitInfoPrice)) {
                                    $sumPrice = 0;
                                } else {
                                    $sumPrice = $ProfitInfoPrice;
                                }

                                if (($TwitterInfo['level_send_price'] - $sumPrice) >= $ColumnInfo['price']) {
                                    //添加记录
                                    $LogData = [];
                                    $LogData['ordernum'] = $out_trade_no;
                                    $LogData['user_id'] = $twitter_id;
                                    $LogData['type'] = 2;
                                    $LogData['column_id'] = $teacher_id;
                                    $LogData['num'] = 1;
                                    $LogData['price'] = $ColumnInfo['price'];
                                    $LogData['ctime'] = $time;
                                    AgentProfitLog::create($LogData);
                                    //$AgentProfitObj->add($AgentProfitObj::$table,$LogData); //添加记录
                                    $ProfitPrice = $ColumnInfo['price']; //返现处理
                                } else { //支付金额已扣除分成
                                    $ProfitPrice = 0;
                                    if ($orderInfo['SurplusPrice'] > 0) {
                                        $LogData = [];
                                        $LogData['ordernum'] = $out_trade_no;
                                        $LogData['user_id'] = $twitter_id;
                                        $LogData['type'] = 2;
                                        $LogData['column_id'] = $teacher_id;
                                        $LogData['num'] = 1;
                                        $LogData['price'] = $orderInfo['SurplusPrice'];   //推广产品所获金额
                                        $LogData['ctime'] = $time;
//                                        $AgentProfitObj->add($AgentProfitObj::$table,$LogData);
                                        AgentProfitLog::create($LogData);
//                                        $ProfitPrice = Profit::Income(0, 5, 0, 1, $teacher_id);
                                        $ProfitPrice = GetPriceTools::Income(0, $TwitterInfo['level'], 0, 1, $teacher_id);

                                        $ProfitPrice = $ProfitPrice - $orderInfo['SurplusPrice'];//返回剩余款项
                                    } else {
                                        //                                      $ProfitPrice = Tool::RetainDecimal ($ProfitPrice, 0.45, 1);
//                                        $ProfitPrice = Profit::Income(0, 5, 0, 1, $teacher_id);
                                        $ProfitPrice = GetPriceTools::Income(0, $TwitterInfo['level'], 0, 1, $teacher_id);

                                    }
                                }


                            }

                            if ($ProfitPrice > 0) {
                                $map = [
                                    'user_id' => $twitter_id,
                                    "type" => 6,
                                    "ordernum" => $out_trade_no,
                                    'price' => $ProfitPrice,
                                ];
                            }


                        } else {
                            $is_sub = Subscribe::isSubscribe($twitter_id, $teacher_id, 1);
                            if ($is_sub) { //订阅专栏
                                $ColumnInfo = Column::find($teacher_id);
                                $ProfitPrice = $ColumnInfo['twitter_price'];
                                $map = [
                                    'user_id' => $twitter_id,
                                    "type" => 6, "ordernum" => $out_trade_no,
                                    'price' => $ProfitPrice,
                                ];
                            }
                        }
                    }
                }

                if (!empty($map)) {
                    //$PayRDObj = new PayRecordDetail();
                    //防止重复添加收入
                    $where = ['user_id' => $map['user_id'], 'type' => $map['type'], 'ordernum' => $map['ordernum']];
                    $PrdInfo = PayRecordDetail::where($where)->first();
                    if (empty($PrdInfo)) {
                        PayRecordDetail::create($map);
                        //Profit::ServiceIncome($out_trade_no,6,$ProfitPrice,$twitter_id);
                        GetPriceTools::ServiceIncome($out_trade_no, 6, $ProfitPrice, $twitter_id);

                    }
                }

                //添加订阅记录
                $sub_type = 1;
                if ($orderInfo['type'] == 15) {
                    $sub_type = 6;  //讲座
                }
                $subscribe = [
                    'user_id' => $user_id, //会员id
                    'pay_time' => date("Y-m-d H:i:s", $time), //支付时间
                    'type' => $sub_type,
                    'order_id' => $orderId, //订单id
                    'status' => 1,
                    'start_time' => date("Y-m-d H:i:s", $starttime),
                    'end_time' => date("Y-m-d H:i:s", $endtime),
                    'relation_id' => $teacher_id,
                    'service_id' => $orderInfo['service_id'],
                ];
                $subscribeRst = Subscribe::firstOrCreate($subscribe);

                //订阅量处理
                Column::where(['id' => $teacher_id])->increment('subscribe_num');
//                $user_id = empty($orderInfo['service_id']) ? $user_id : $orderInfo['service_id'];
//                $userRst = WechatPay::UserBalance($pay_type, $user_id, $orderInfo['price']);
                $user_id = empty($orderInfo['service_id']) ? $user_id : $orderInfo['service_id'];
                $userRst = WechatPay::UserBalance($pay_type, $user_id, $orderInfo['price']);


                if ($orderRst && $couponRst && $phoneRst && $recordRst && $subscribeRst && $shareSyRst && $Sy_Rst) {
                    DB::commit();
//                    $content = "订单修改:$orderRst--优惠券:$couponRst--短信发送:$phoneRst--支付记录:$recordRst--分成记录:$shareSyRst--订阅:$subscribeRst--分享收益:$Sy_Rst";
//                    Io::WriteFile('', '', $content, true);
//                    self::$user_id = $user_id;
                    return true;
                } else {

                    DB::rollBack();
                    return false;
                }
            } catch (\Exception $e) {
                DB::rollBack();

                return false;
            }
        } else {
            //订单状态已更新，直接返回true
            return true;
        }
    }

    //微信购买精品课
    public static function PayExcellent($data)
    {

        $time = time();
        $out_trade_no = $data['out_trade_no'];
        $total_fee = $data['total_fee'];
        $transaction_id = $data['transaction_id'];
        $pay_type = $data['pay_type'];


        //支付处理正确-判断是否已处理过支付状态
        $orderInfo = Order::select()->where(['ordernum' => $out_trade_no, 'status' => 0])->first();
        if (!empty($orderInfo)) {
            $orderInfo = $orderInfo->toArray();

            DB::beginTransaction();
            try {

                //$teacher_id = $orderInfo['teacher_id']; //专栏老师
                $coupon_id = $orderInfo['coupon_id']; //优惠券
                $user_id = $orderInfo['user_id']; //用户
                $orderId = $orderInfo['id']; //订单id
                $twitter_id = $orderInfo['twitter_id']; //推客id
                $works_id = $orderInfo['relation_id']; //精品课id
                //更新订单状态
                $data1 = [
                    'status' => 1,
                    'pay_time' => date("Y-m-d H:i:s", $time),
                    'pay_price' => $total_fee,
                    'pay_type' => $pay_type,
                ];
                $orderRst = Order::where(['ordernum' => $out_trade_no])->update($data1);

                $couponRst = 1;
                //消除优惠券
                if ($coupon_id > 0) {
                    $couponRst = Coupon::where(['id' => $coupon_id])->update(['status' => 2, 'used_time' => date('Y-m-d H:i:s', $time)]);
                }

                $record_type = self::$pay_record_type[$data['pay_type']] ?? 0;
                //添加支付记录
                $record = [
                    'ordernum' => $out_trade_no, //订单编号
                    'price' => $total_fee, //支付金额
                    'transaction_id' => $transaction_id, //流水号
                    'user_id' => $user_id, //会员id
                    'type' => $pay_type, //1：微信  2：支付宝
//                    'client'         => $data['client'],          //微信
                    'order_type' => 11, //精品课
                    'status' => 1                         //收入
                ];
                $recordRst = PayRecord::firstOrCreate($record);

                $phoneRst = 1;
                //防止短信发送不成功
                $AdminInfo = User::find($user_id);
                if (!empty($AdminInfo) && !empty($AdminInfo['username'])) {
                    if (preg_match('/^(1)\d{10}$/', $AdminInfo['username'])) {
                        //添加短信通知
                        $phoneArr = [
                            'type' => 6, //短信推送
                            //'author_id' => $teacher_id,
                            'user_id' => $user_id,
                            'works_id' => $works_id,
                        ];
//                        $MessObj  = new Messages();
//                        $phoneRst = $MessObj->add($MessObj::$table,$phoneArr);
                    }
                }

                $shareSyRst = true;
                $map = [];
                //$WorksObj    = new Works();

                if (!empty($twitter_id) && $twitter_id != $user_id && $orderInfo['twitter_id'] != $orderInfo['service_id']) { //推客是自己不算 服务商赠送不返利
                    //查看用户权限
                    $is_twitter = User::getLevel($twitter_id);
                    $TwitterInfo = User::find($twitter_id);
                    if ($is_twitter > 0) {//是推客 皇钻 黑钻
                        $WorksInfo = Works::find($works_id);
                        $ProfitPrice = $WorksInfo['twitter_price'];

                        if (in_array($TwitterInfo['level'], [2, 3, 4])) {
                            $ProfitPrice = GetPriceTools::Income(0, $TwitterInfo['level'], 0, 2, 0, $works_id);

                        } else if ($TwitterInfo['level'] == 5) {
                            //$AgentProfitObj=new AgentProfitLog();
                            $table = '';
//                                $where = ['type'=>[1,2,3],'status'=>1];
//                                $field = ['sum(price) price'];
//                                $ProfitInfo = $AgentProfitObj->getOne($AgentProfitObj::$table,$where,$field);

                            $ProfitInfoPrice = AgentProfitLog::where('status', 1)->whereIn('type', [1, 2, 3])->sum('price');
                            if (empty($ProfitInfoPrice)) {
                                $sumPrice = 0;
                            } else {
                                $sumPrice = $ProfitInfoPrice;
                            }
                            if (($TwitterInfo['level_send_price'] - $sumPrice) >= $WorksInfo['price']) {
                                //添加记录
                                $LogData = [];
                                $LogData['ordernum'] = $out_trade_no;
                                $LogData['user_id'] = $twitter_id;
                                $LogData['type'] = 3;
                                $LogData['works_id'] = $works_id;
                                $LogData['num'] = 1;
                                $LogData['price'] = $WorksInfo['price'];
//                                    $AgentProfitObj->add($AgentProfitObj::$table,$LogData);
                                AgentProfitLog::create($LogData);
                                //$ProfitPrice=$WorksInfo['price'];//返现处理 实际支付金额
                                $ProfitPrice = $total_fee;//返现处理 实际支付金额
                            } else { //支付金额已经扣除分成
//                                    $ProfitPrice = Tool::RetainDecimal ($WorksInfo['price'], 0.45, 1);
                                $ProfitPrice = 0;
                                if ($orderInfo['SurplusPrice'] > 0) {
                                    $LogData = [];
                                    $LogData['ordernum'] = $out_trade_no;
                                    $LogData['user_id'] = $twitter_id;
                                    $LogData['type'] = 3;
                                    $LogData['works_id'] = $works_id;
                                    $LogData['num'] = 1;
                                    $LogData['price'] = $orderInfo['SurplusPrice'];
                                    $LogData['ctime'] = $time;
//                                        $AgentProfitObj->add($AgentProfitObj::$table,$LogData);
                                    AgentProfitLog::create($LogData);

//                                        $ProfitPrice=Profit::IncomeController(0,5,0,2,$teacher_id,$works_id);
                                    $ProfitPrice = GetPriceTools::Income(0, $TwitterInfo['level'], 0, 2, 0, $works_id);
                                    $ProfitPrice = $ProfitPrice - $orderInfo['SurplusPrice'];
                                } else {//海报
//                                        $ProfitPrice=Profit::IncomeController(0,5,0,2,$teacher_id,$works_id);
                                    $ProfitPrice = GetPriceTools::Income(0, $TwitterInfo['level'], 0, 2, 0, $works_id);

                                }

                            }
                        }

                        if ($ProfitPrice > 0) {
                            $map = array('user_id' => $twitter_id, "type" => 7, "ordernum" => $out_trade_no, 'price' => $ProfitPrice,);
                        }
                    } else {
                        $is_sub = Subscribe::isSubscribe($twitter_id, $works_id, 2);
                        if ($is_sub) {
                            $WorksInfo = Works::find($works_id);
                            $ProfitPrice = $WorksInfo['twitter_price'];
                            $map = array('user_id' => $twitter_id, "type" => 7, "ordernum" => $out_trade_no, 'price' => $ProfitPrice,);
                        }
                    }
                }

                if (!empty($map)) {

                    $PayRDObj = new PayRecordDetail();
                    //防止重复添加收入
                    $where = ['user_id' => $map['user_id'], 'type' => $map['type'], 'ordernum' => $map['ordernum']];
                    $PrdInfo = PayRecord::where($where)->first();

                    if (empty($PrdInfo)) {
                        $shareSyRst = PayRecordDetail::create($map);
                        //5%返现
                        //Profit::ServiceIncome($out_trade_no,7,$ProfitPrice,$twitter_id);
                        GetPriceTools::ServiceIncome($out_trade_no, 7, $ProfitPrice, $twitter_id);
                    }
                }

                //添加订阅记录
                $subscribe = [
                    'user_id' => $user_id, //会员id
                    'type' => 2, //作品
                    'status' => 1,
                    'relation_id' => $works_id, //精品课
                    'order_id' => $orderId, //订单id
                    'pay_time' => date("Y-m-d H:i:s", $time), //支付时间
                    'service_id' => $orderInfo['service_id'],
                ];


                $subscribeRst = Subscribe::firstOrCreate($subscribe);

                //精品课购买数量 自增1
                $class_Rst = Works::where(['id' => $works_id])->increment('subscribe_num', 1);

                $user_id = empty($orderInfo['service_id']) ? $user_id : $orderInfo['service_id'];
                $userRst = WechatPay::UserBalance($pay_type, $user_id, $orderInfo['price']);

                if ($phoneRst && $orderRst && $couponRst && $recordRst && $subscribeRst && $shareSyRst && $class_Rst) {
                    DB::commit();
//                    $content = "订单修改:$orderRst--优惠券:$couponRst--支付记录:$recordRst--分成记录:$shareSyRst--订阅:$subscribeRst";
//                    Io::WriteFile('', '', $content, true);
//                    self::$user_id = $user_id;
                    return true;
                } else {
                    DB::rollBack();
                    return false;
                }
            } catch (\Exception $e) {
                DB::rollBack();
                return false;
            }
        } else {
            //订单状态已更新，直接返回
            return true;
        }
    }


    //打赏
    public static function Areward($data)
    {


        $time = time();
        //$out_trade_no   = substr($data['out_trade_no'], 0, -5);
        $out_trade_no = $data['out_trade_no'];
        $total_fee = $data['total_fee'];
        $transaction_id = $data['transaction_id'];
        $pay_type = $data['pay_type'];


        //支付处理正确-判断是否已处理过支付状态
        $orderInfo = Order::select()->where(['ordernum' => $out_trade_no, 'status' => 0])->first();

        if (!empty($orderInfo)) {
            $orderInfo = $orderInfo->toArray();

            DB::beginTransaction();
            try {

                $user_id = $orderInfo['user_id']; //用户
                //更新订单状态
                $data1 = [
                    'status' => 1,
                    'pay_time' => date("Y-m-d H:i:s", $time),
                    'pay_price' => $total_fee,
                    'pay_type' => $pay_type,
                ];
                $orderRst = Order::where(['ordernum' => $out_trade_no])->update($data1);


                //添加支付记录
                $record = [
                    'ordernum' => $out_trade_no, //订单编号
                    'price' => $total_fee, //支付金额
                    'transaction_id' => $transaction_id, //流水号
                    'user_id' => $user_id, //会员id
                    'type' => $pay_type, //1：微信  2：支付宝
//                    'client'         => $data['client'],          //微信
                    'order_type' => 5, //精品课
                    'status' => 1                         //收入
                ];
                $recordRst = PayRecord::firstOrCreate($record);


                $userRst = WechatPay::UserBalance($pay_type, $user_id, $orderInfo['price']);

                if ($orderRst && $recordRst && $userRst) {
                    if (!empty($orderInfo['live_id'])){
                        JobServers::pushToSocket($orderInfo['live_id'], $orderInfo['relation_id'], 12);
                    }
                    DB::commit();
                    return true;
                } else {
                    DB::rollBack();
                    return false;
                }

            } catch (\Exception $e) {
                DB::rollBack();
                return false;
            }

        } else {
            //订单状态已更新，直接返回
            return true;
        }
    }


    // 苹果端 能量币充值
    public static function PayCoinOrder($data)
    {
        $time = time();
        //$out_trade_no   = substr($data['out_trade_no'], 0, -5);
        $out_trade_no = $data['out_trade_no'];
        $total_fee = $data['total_fee'];
        $transaction_id = $data['transaction_id'];
        $pay_type = $data['pay_type'];

        //支付处理正确-判断是否已处理过支付状态
        $orderInfo = Order::select()->where(['ordernum' => $out_trade_no, 'status' => 0])->first();

        if (!empty($orderInfo)) {
            $orderInfo = $orderInfo->toArray();
            $starttime = strtotime(date('Y-m-d', $time));
            $endtime = strtotime(date('Y', $starttime) + 1 . '-' . date('m-d', $starttime)) + 86400; //到期日期

            DB::beginTransaction();

            try {

                $user_id = $orderInfo['user_id']; //用户
                //更新订单状态
                $data = [
                    'status' => 1,
                    'pay_time' => date('Y-0m-d H:i:s', $time),
                    'pay_price' => $total_fee,
                    'pay_type' => $pay_type,
                ];
                $orderRst = Order::where(['ordernum' => $out_trade_no])->update($data);

                //添加支付记录
                $record_type = self::$pay_record_type[$data['pay_type']] ?? 1;

                $record = [
                    'ordernum' => $out_trade_no, //订单编号
                    'price' => $total_fee, //支付金额
                    'transaction_id' => $transaction_id, //流水号
                    'user_id' => $user_id, //会员id
                    'type' => $pay_type, //1：微信  2：支付宝
                    //'client' => $data['client'], //app微信
                    'order_type' => 18, //能量币充值
                    'status' => 1                   //收入
                ];
                $recordRst = PayRecord::firstOrCreate($record);

                //添加账户余额
                $userRst = User::where('id', $user_id)->increment('ios_balance', $total_fee);
                if ($orderRst && $recordRst && $userRst) {
                    DB::commit();
                    return true;
                } else {
                    DB::rollBack();
                    return false;
                }
            } catch (\Exception $e) {
                DB::rollBack();
                return false;
            }
        } else {
            //订单状态已更新，直接返回
            return true;
        }
    }


    public static function UserBalance($pay_type, $user_id, $price, $edit = '-')
    {
        $res = true;
        if ($pay_type == 4) {   // 只有虚拟产品才可以支付能量币
            if ($edit == '-') {
                $res = User::where('id', $user_id)->decrement('ios_balance', $price);
            } else if ($edit == '+') {
                $res = User::where('id', $user_id)->increment('ios_balance', $price);
            }
        }
        return $res;
    }



    //赠送
    public static function PaySend($data)
    {
        $time = time();
        $out_trade_no = $data['out_trade_no'];
        $total_fee = $data['total_fee'];
        $transaction_id = $data['transaction_id'];
        $pay_type = $data['pay_type'];


        //支付处理正确-判断是否已处理过支付状态
        $orderInfo = Order::select()->where(['ordernum' => $out_trade_no, 'status' => 0])->first();

        if (!empty($orderInfo)) {
            $orderInfo = $orderInfo->toArray();

            DB::beginTransaction();
            try {
                //更新订单状态
                $user_id = $orderInfo['user_id']; //用户
                //更新订单状态
                $data1 = [
                    'status' => 1,
                    'pay_time' => date("Y-m-d H:i:s", $time),
                    'pay_price' => $total_fee,
                    'pay_type' => $pay_type,
                ];
                $orderRst = Order::where(['ordernum' => $out_trade_no])->update($data1);

                //添加支付记录
                $record = [
                    'ordernum' => $out_trade_no, //订单编号
                    'price' => $total_fee, //支付金额
                    'transaction_id' => $transaction_id, //流水号
                    'user_id' => $user_id, //会员id
                    'type' => $pay_type, //1：微信  2：支付宝
                    'order_type' => 20,//nlsg_pay_record表type 16直播
                    'status' => 1
                ];
                $recordRst = PayRecord::firstOrCreate($record);

//                $subscribe = [
//                    'user_id' => $user_id, //会员id
//                    'pay_time' => date("Y-m-d H:i:s", $time), //支付时间
//                    'type' => 3, //直播
//                    'status' => 1,
//                    'order_id' => $orderId, //订单id
//                    'relation_id' => $live_id,
//                ];
//                $subscribeRst = Subscribe::firstOrCreate($subscribe);

                $userRst = WechatPay::UserBalance($pay_type, $user_id, $orderInfo['price']);

                if ($orderRst  && $recordRst  && $userRst ) {
                    DB::commit();
                    return true;

                } else {
                    DB::rollBack();
                    return false;
                }

            } catch (\Exception $e) {
                DB::rollBack();
                return false;
            }

        } else {
            //订单状态已更新，直接返回
            return true;
        }
    }



}
