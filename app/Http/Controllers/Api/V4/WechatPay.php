<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use App\Models\AgentProfitLog;
use App\Models\ChannelWorksList;
use App\Models\Column;
use App\Models\ConfigModel;
use App\Models\Coupon;
use App\Models\GetPriceTools;
use App\Models\ImGroup;
use App\Models\Live;
use App\Models\LiveCountDown;
use App\Models\MallErpList;
use App\Models\MallGroupBuyList;
use App\Models\MallOrder;
use App\Models\MallOrderDetails;
use App\Models\MeetingSales;
use App\Models\OfflineProducts;
use App\Models\Order;
use App\Models\PayRecord;
use App\Models\PayRecordDetail;
use App\Models\PayRecordDetailStay;
use App\Models\Subscribe;
use App\Models\User;
use App\Models\VipRedeemUser;
use App\Models\VipUser;
use App\Models\VipUserBind;
use App\Models\Works;
use App\Servers\ImGroupServers;
use App\Servers\JobServers;
use App\Servers\MallOrderServers;
use EasyWeChat\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

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
        } elseif ($data['attach'] == 18) { //处理训练营
            return self::PayColumn($data);
        }
    }

    static function  filterEmoji($str)
    {
        return preg_replace_callback(
            '/./u',
            function (array $match) {
                return strlen($match[0]) >= 4 ? '' : $match[0];
            },
            $str);
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
                $endtime = date("Y-m-d 23:59:59", strtotime("+1years", time()));

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
                $Sy_Rst = true;
                $map = [];
                $AdminInfo = User::find($user_id);
                //获取用户信息
                $twitter_id = $orderInfo['twitter_id'];
                $twitter = [];
                if ($twitter_id > 0) {
                    //vip 信息
                    $twitter = VipUser::where(['user_id' => $twitter_id, 'is_default' => 1, 'status' => 1])->first()->toArray();
                }

                // 后台查询需要  $source $source_vip_id  推广总代理商一直不变
                if ($twitter) {
                    if ($twitter['level'] == 2) { // 只要升级为2级代理商  就默认为第一级
                        $source = $twitter_id;
                        $source_vip_id = $twitter['id'];

                    } else {
                        $source = $twitter['source'];
                        $source_vip_id = $twitter['source_vip_id'];
                    }

                    //写入关系保护
                    $twitter_data = User::find($twitter);
                    $check_bind = VipUserBind::getBindParent($AdminInfo['phone']);
                    //没有绑定记录,则绑定
                    if (($check_bind === 0) && strlen($twitter_data['phone']) === 11 && strlen($AdminInfo['phone']) === 11) {
                        $bind_data = [
                            'parent' => $twitter_data['phone'],
                            'son' => $AdminInfo['phone'],
                            'life' => 2,
                            'begin_at' => date('Y-m-d H:i:s'),
                            'end_at' => date('Y-m-d 23:59:59', strtotime('+1 years')),
                            'channel' => 2
                        ];
                        if($orderInfo['activity_tag'] === "2021-11-1" ) { //支付1元
                            $bind_data['end_at'] = date("Y-m-d 23:59:59", strtotime("+7day"));
                        }elseif($orderInfo['activity_tag'] === "2021-11-2" ) { //支付1元
                            $bind_data['end_at'] = date("Y-m-d 23:59:59", strtotime("+1years +100day"));
                        }else{
                            $bind_data['end_at'] = date("Y-m-d 23:59:59", strtotime("+1years"));
                        }
                        DB::table('nlsg_vip_user_bind')->insert($bind_data);
                    }


                } else {
                    $source = 0;
                    $source_vip_id = 0;
                    $twitter['id'] = 0;
                }
                $supremacy_vip = $orderInfo['relation_id']; //会员 1 360会员
                $Userdata['user_id'] = $user_id;
                $Userdata['level'] = $supremacy_vip;
                $Userdata['username'] = $AdminInfo['phone'];
                $Userdata['nickname'] = self::filterEmoji($AdminInfo['nickname']);
                $Userdata['inviter'] = $twitter_id;         //推荐人user_id
                $Userdata['source'] = $source;              //代理商user_id
                $Userdata['source_vip_id'] = $source_vip_id;//代理商的vip表id
                $Userdata['is_default'] = 1;
                $Userdata['inviter_vip_id'] = $twitter['id'];//推荐人的vip_user表id
                $Userdata['expire_time'] = $endtime;
                $Userdata['start_time'] = $starttime;
                $Userdata['status'] = 1;
                $Userdata['order_id'] = $orderId; //关联订单
                if ($supremacy_vip == 2) { // 支付一千元定金   状态为待审核
                    $Userdata['status'] = 0;
                }
                $vip_order_type = $orderInfo['vip_order_type']; //1开通 2续费

                //$UserAttInfo=$newVipModel->db->where()->getOne($newVipModel::$table,'*');
                $UserAttInfo = VipUser::where(['user_id' => $user_id, 'status' => 1, 'is_default' => 1])->first();
                if ($UserAttInfo) {
                    $UserAttInfo = $UserAttInfo->toArray();
                    $level = $UserAttInfo['level'];
                } else {
                    $level = 0;
                }

                $vip_id = 0;
                $newVip_rst = true;
                $Sales_Rst = true;
                $pay_record_flag = 0;
                $top_Sy_Rst = true;
                //当有效身份不是钻石合伙人，对vip_user表进行任何处理
                $e_time = 31536000;
                if($orderInfo['activity_tag'] == "2021-11-1" ) { //支付1元
                    $e_time = 86400*7;   //7天
                    $Userdata['expire_time'] = date("Y-m-d 23:59:59", strtotime("+7day", time()));
                }elseif($orderInfo['activity_tag'] == "2021-11-2" ) { //支付1元
                    $e_time = 31536000+86400*100;  //一年零100天
                    $Userdata['expire_time'] = date("Y-m-d 23:59:59", strtotime("+1years +100day", time()));
                }

                if ($level != 2) {
                    if ($supremacy_vip == 1) {   //支付定金不需要走vip表操作
                        if ($vip_order_type == 1) {
                            //开通的情况
                            if (!empty($UserAttInfo)) {
                                //数据存在时  将状态改为0
                                $orderRst = VipUser::where(['user_id' => $user_id])->update(['is_default' => 0]);
                                //$newVipModel->update($newVipModel::$table,['is_default'=>0],['user_id'=>$user_id]);
                            }
                            $newVip_rst = VipUser::firstOrCreate($Userdata);
                            $vip_id = $newVip_rst->id;  // 新增时写入

                        } else {
                            //过期时间延长一年   权益归属不发生改变
                            $Userdata = [
                                'expire_time' => date('Y-m-d H:i:s', strtotime($UserAttInfo['expire_time']) + $e_time),
                            ];
                            $newVip_rst = VipUser::where(['user_id' => $user_id])->update($Userdata);
                            $twitter_top = explode('->', $orderInfo['remark']);

                            if (!empty($twitter_top[1]) && $twitter_top[1] > 0) {
                                $twitter_id = $twitter_top[1] ?? 0;
                            } else {
                                $twitter_id = $UserAttInfo['inviter'] ?? 0;
                            }


                            //查看当前有效用户
                            $UserAttInfo = VipUser::where(['user_id' => $user_id, 'status' => 1, 'is_default' => 1])->first()->toArray();

                            $vip_id = $UserAttInfo['id'];

                            $Userdata['inviter_vip_id'] = $UserAttInfo['inviter_vip_id'];//新增时已经写入

                        }
                    }
                } else {
                    //当有效身份为钻石合伙人，对vip_user表进行任何处理
                    if ($UserAttInfo['is_open_360'] == 1) {
                        $VipUserData = [
                            'time_end_360' => date('Y-m-d H:i:s', strtotime($UserAttInfo['time_end_360']) + $e_time),
                        ];
                    } else {
                        $VipUserData = [
                            'is_open_360' => 1,
                            'time_begin_360' => $starttime,
                            'time_end_360' => $endtime,
                        ];
                    }
                    $newVip_rst = VipUser::where(['user_id' => $user_id])->update($VipUserData);
                    $twitter_id = $user_id;
                }

                if(!in_array($orderInfo['activity_tag'],["2021-11-1","2021-11-2"])) { //活动无实际收益
                    //服务商购买时已是优惠价格
                    //购买必须为360会员
                    $PayRDObj = new PayRecordDetail();
                    // 开通续费为360   并且  推客id有 或者  销讲老师id有[推客可能为空]
                    $sales_id = $orderInfo['sales_id']; //销讲老师
                    if ($supremacy_vip == 1 && (!empty($twitter_id) || !empty($sales_id))) { //推客是自己不算 服务商赠送不返利
                        $tk_vip = VipUser::IsNewVip($twitter_id);

                        if ($tk_vip && $supremacy_vip == 1) {   //目前只有360会员有收益
                            $ProfitPrice = GetPriceTools::Income(0, $tk_vip, 0, 5);

                            if ($ProfitPrice > 0) {
                                $map = array('user_id' => $twitter_id, "type" => 11, "ordernum" => $out_trade_no, 'price' => $ProfitPrice, "ctime" => $time, 'vip_id' => $vip_id, 'user_vip_id' => $Userdata['inviter_vip_id']);

                            }
                        }


                        /*****************     开通360   有销讲老师的划分收益【】  ****************/

                        if (!empty($map) && (empty($sales_id) || $vip_order_type == 2)) {  //收益存在 并且 (销讲老师表id为空 或者 续费) 正常执行收益流程
                            //防止重复添加收入
                            $where = ['user_id' => $map['user_id'], 'type' => $map['type'], 'ordernum' => $map['ordernum']];
                            $PrdInfo = PayRecordDetail::where($where)->first('id');
                            if (empty($PrdInfo)) {
                                $pay_record_flag = 1;
                                $Sy_Rst = PayRecordDetail::firstOrCreate($map);
                            }
                        } else if (!empty($sales_id) && $vip_order_type == 1) {  //仅开通360  销讲老师表id存在时 执行 销讲老师收益100 代理商收益126  公司134
                            //老师收益
                            $salesData = MeetingSales::where(['id' => $sales_id, 'status' => 1])->first();
                            $sales_map = array('user_id' => $salesData['user_id'], "type" => 11, "ordernum" => $out_trade_no, 'price' => 100, "ctime" => $time, 'vip_id' => $vip_id, 'user_vip_id' => $Userdata['inviter_vip_id']);
                            $Sales_Rst = PayRecordDetail::firstOrCreate($sales_map);

                            //正常是 代理商收益126  公司134
                            $map = array('user_id' => $twitter_id, "type" => 11, "ordernum" => $out_trade_no, 'price' => 126, "ctime" => $time, 'vip_id' => $vip_id, 'user_vip_id' => $Userdata['inviter_vip_id']);
                            //if( $salesData['type'] == 2 ){  } //需要查绑定关系   钻石合伙人是126   360是54  没有则只有老师有收益
                            $is_vip = VipUser::IsNewVip($twitter_id);
                            switch ($is_vip) {
                                case 1:
                                    $map['price'] = 54;
                                    break;
                                case 2:
                                    $map['price'] = 126;
                                    break;
                                default :
                                    $map = [];  // 如果没有绑定  则只有老师有收益
                                    break;
                            }

                            //代理商收益
                            if ($map) {
                                $pay_record_flag = 1;
                                $Sy_Rst = PayRecordDetail::firstOrCreate($map);
                            }
                        }

                    }
                    //受保护的人 需要给推荐人[非保护者] 加一个收益为0的数据

                    $twitter_top = explode('->', $orderInfo['remark']);
                    if ($twitter_top[0] > 0) {
                        $twitter_top_vip_id = VipUser::where(['user_id' => $twitter_top[0], 'is_default' => 1, 'status' => 1])->first('id');
                        $top_map = array('user_id' => $twitter_top[0], "type" => 11, "ordernum" => $out_trade_no, 'price' => 0, "ctime" => $time, 'vip_id' => $vip_id, 'user_vip_id' => $twitter_top_vip_id->id);
                        $top_Sy_Rst = PayRecordDetail::firstOrCreate($top_map);
                    }
                }

                //  升级续费都需要进行精品课赠送     已经购买的需要折算兑换码
                //查询关注里是否有这些课程   有的话是送优惠券  没有直接添加
                $add_sub_Rst = true;
                if ($supremacy_vip == 1) {
                    //use
                    $add_sub_Rst = VipRedeemUser::subWorksOrGetRedeemCode($user_id,$orderInfo['activity_tag']);
                }

                $user_id = empty($orderInfo['service_id']) ? $user_id : $orderInfo['service_id'];
                $userRst = WechatPay::UserBalance($pay_type, $user_id, $orderInfo['price']);

                if ($newVip_rst && $orderRst && $recordRst && $Sy_Rst && $userRst && $add_sub_Rst && $top_Sy_Rst && $Sales_Rst) {
                    DB::commit();
                    self::LiveRedis(16, 1, $AdminInfo['nickname'], $live_id, $orderId, $orderInfo['live_num']);

                    //短信
                    if ($AdminInfo['phone'] && strlen($AdminInfo['phone']) == 11) {
                        $easySms = app('easysms');
                        $result = $easySms->send($AdminInfo['phone'], [
                            'template' => 'SMS_211001614',
                        ], ['aliyun']);
                    }
//                    Task::send(3, $user_id, $orderInfo['relation_id']);
//                    if($pay_record_flag == 1){
//                        Task::send(11, $user_id, $orderInfo['relation_id'],'','360会员','','','',$AdminInfo['nickname']);
//                    }
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
                $twitter_id = $orderInfo['twitter_id'];

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
                    'client' => 1, //微信
                    'order_type' => 21, //
                    'status' => 1,
                ];
                $recordRst = PayRecord::firstOrCreate($record);


//                if($orderInfo['relation_id'] == 5){
//                    // 直播间的线下产品   训练营
//                    $starttime = strtotime(date('Y-m-d', $time));
//                    $endtime = strtotime(date('Y', $starttime) + 1 . '-' . date('m-d', $starttime)) + 86400; //到期日期
//
//                    $subscribe = [
//                        'user_id' => $user_id, //会员id
//                        'pay_time' => date("Y-m-d H:i:s", $time), //支付时间
//                        'type' => 7,
//                        'order_id' => $orderId, //订单id
//                        'status' => 1,
//                        'start_time' => date("Y-m-d H:i:s", $starttime),
//                        'end_time' => date("Y-m-d H:i:s", $endtime),
//                        'relation_id' => 517, //{训练营表id}
//                    ];
//
//                }else{
//                }
                $subscribe = [
                    'user_id' => $user_id, //会员id
                    'pay_time' => date("Y-m-d H:i:s", $time), //支付时间
                    'type' => 5, //门票
                    'status' => 1,
                    'order_id' => $orderId, //订单id
                    'relation_id' => $orderInfo['relation_id'],
                ];


                $subscribeRst = Subscribe::firstOrCreate($subscribe);

                $total_fee_line = ConfigModel::getData(50, 1);
                if (empty($total_fee_line)) {
                    $total_fee_line = 0;
                } else {
                    $total_fee_line = 1;
                }

                $vip_res = true;
                //1360
                if (in_array($orderInfo['relation_id'],[4]) && $total_fee > $total_fee_line && $orderInfo['type'] == 14) {
                    $vipModel = new VipUser();
                    $vip_res = $vipModel->jobOf1360($orderInfo['user_id'], $orderInfo['id'], $orderInfo['live_id']);
                    $vip_res = $vip_res['code'];
                } else if ($orderInfo['relation_id'] == 5 && $total_fee > $total_fee_line && $orderInfo['type'] == 14 && !empty($twitter_id) && $twitter_id != $user_id) {
                    $tk_vip = VipUser::IsNewVip($twitter_id);
                    if ($tk_vip) {   //目前只有360会员有收益
                        $ProfitPrice = GetPriceTools::Income(0, $tk_vip, 0, 6);
                        if ($ProfitPrice > 0) {
                            $map = array('user_id' => $twitter_id, "type" => 12, "ordernum" => $out_trade_no, 'price' => $ProfitPrice, "ctime" => $time,);
                            //防止用户退款   暂时走线下 名单返款
                            //$vip_res = PayRecordDetail::firstOrCreate($map);
                        }
                    }
                }

                $userRst = WechatPay::UserBalance($pay_type, $user_id, $orderInfo['price']);
                if ($orderRst && $recordRst && $subscribeRst && $userRst && $vip_res) {
                    DB::commit();
                    $AdminInfo = User::find($user_id);
                    self::LiveRedis(14, $orderInfo['relation_id'], $AdminInfo['nickname'], $live_id, $orderId, $orderInfo['live_num']);
//                    Task::send(6, $user_id, $orderInfo['relation_id']);
                    if ($orderInfo['relation_id'] == 5){
                        //加群
                        self::joinImGroup($orderInfo['relation_id'],$user_id,$orderInfo['type']);

                    }
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


    static function LiveRedis($type, $relation_id, $nickname, $live_id = 0, $orderid = 0, $live_num = 1)
    {

        if ($live_id == 0) {
            return 0;
        }
        //  向直播间 设置直播间 redis
        $key = 'live_PushOrder_' . $live_id;
        Redis::select(0);

        if ($type == 16) {
            $res = $nickname . ':您已成功购买' . $live_num . '个幸福360会员';
        } else if ($type == 14) {
            switch ($relation_id) {
                case 1: //经营能量
                    $res = $nickname . ':您已成功购买' . $live_num . '张经营能量门票';
                    break;
                case 2: //一代天骄
                    $res = $nickname . ':您已支付' . $live_num . '单一代天骄定金';
                    break;
                case 3: //演说能量
                    $res = $nickname . ':您已支付' . $live_num . '单演说能量定金';
                    break;
                case 4: //幸福套餐
                    $res = $nickname . ':您已支付' . $live_num . '张幸福套餐';
                    break;
                case 5: //幸福套餐
                    $res = $nickname . ':您已支付' . $live_num . '张30天智慧父母(亲子)训练营';
                    break;
                case 6: //学业规划训练营
                    $res = $nickname . ':您已支付' . $live_num . '张学业规划训练营';
                    break;
                case 7: //30天智慧父母(亲子)训练营-直播专享
                    $res = $nickname . ':您已支付' . $live_num . '张30天智慧父母(亲子)训练营-直播专享';
                    break;
                case 8: //抖音直播内部教材
                    $res = $nickname . ':您已支付' . $live_num . '套抖音直播内部教材';
                    break;
                case 9: //经营幸福人生
                    $res = $nickname . ':您已支付' . $live_num . '张经营幸福人生';
                    break;
            }
        } else if ($type == 18) {
            $data = Column::find($relation_id);
            $res = $nickname . ':您已购买' . $data['name'];
        } else if ($type == 11) { //购买9.9直播间
            $res = $nickname . ':您已订阅' . $relation_id;
        }
        Redis::rpush($key, $res);
//        Redis::setex($key,600,json_encode($res,true));
        if ($orderid) {
            Order::where(['id' => $orderid])->update(['is_live_order_send' => 1]);
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

                //发送优惠券
                if (intval($live_id) === 1) {
                    $couponModel = new Coupon();
                    $couponModel->sendCouponRun([50], $user_id);

                    $easySms = app('easysms');
                    try {
                        $user_info = User::whereId($user_id)->first();
                        $phone = $user_info->phone ?? '';
                        if (!empty($phone) && strlen($phone) == 11) {
                            $easySms->send($phone, [
                                'template' => 'SMS_209470584',
                                'data' => [],
                            ], ['aliyun']);
                        }
                    } catch (\Overtrue\EasySms\Exceptions\NoGatewayAvailableException $exception) {
                    }

                }

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
                    'twitter_id' => $orderInfo['twitter_id'],
                ];

                $userdata = User::find($user_id);
                $subscribeRst=true;
                if($userdata['is_test_pay']==0){ //测试用户不添加订阅记录
                    $subscribeRst = Subscribe::firstOrCreate($subscribe);
                }

                $liveData = Live::find($live_id);

                Live::where('id', $live_id)->increment('order_num');
                if ($liveData['relation_live'] > 0) {
                    $subscribe = [
                        'user_id' => $user_id, //会员id
                        'pay_time' => date("Y-m-d H:i:s", $time), //支付时间
                        'type' => 3, //直播
                        'status' => 1,
                        'order_id' => $orderId, //订单id
                        'relation_id' => $liveData['relation_live'],
                    ];
                    Subscribe::firstOrCreate($subscribe);
                    Live::where('id', $liveData['relation_live'])->increment('order_num');
                }

                //21-03-22 补充的课程
                if (in_array($liveData['user_id'] ?? 0, [161904, 250550, 423403])) {
                    Subscribe::appendSub([$user_id], 1);
                }
                //写入关系保护
                if (!empty($orderInfo['twitter_id'])) {
                    $twitter_data = User::find($orderInfo['twitter_id']);
                    $check_bind = VipUserBind::getBindParent($userdata['phone']);
//                    if ($check_bind == 0) {
                    //没有绑定记录,则绑定
                    if (($check_bind === 0) && strlen($twitter_data['phone']) === 11 && strlen($userdata['phone']) === 11) {
                        $bind_data = [
                            'parent' => $twitter_data['phone'],
                            'son' => $userdata['phone'],
                            'life' => 2,
                            'begin_at' => date('Y-m-d H:i:s'),
                            'end_at' => date('Y-m-d 23:59:59', strtotime('+1 years')),
                            'channel' => 2
                        ];
                        DB::table('nlsg_vip_user_bind')->insert($bind_data);
                    }
                }

                //添加短信数据
                $liveCountDown = [
                    'live_id' => $orderInfo['relation_id'],
                    'user_id' => $user_id,
                    'phone' => $userdata['phone'],
                ];
                //推客收益
                $twitter_id = $orderInfo['twitter_id'];
                $Profit_Rst = true;
                $pay_record_flag = 0;
                if (!empty($twitter_id) && $twitter_id !== $user_id && $liveData['twitter_money'] > 0 && $total_fee > $liveData['twitter_money']) {
//                if (!empty($twitter_id) && $twitter_id != $user_id && $liveData['twitter_money'] > 0 && $total_fee > $liveData['twitter_money']) {
                    $liveCountDown['new_vip_uid'] = $twitter_id;
                    //固定收益50
                    $ProfitPrice = $liveData['twitter_money'];
                    $map = array('user_id' => $twitter_id, "type" => 10, "ordernum" => $out_trade_no, 'price' => $ProfitPrice,);
                    if (!empty($map)) {
                        //防止重复添加收入
                        $where = ['user_id' => $map['user_id'], 'type' => $map['type'], 'ordernum' => $map['ordernum']];
                        $PrdInfo = PayRecordDetail::where($where)->first();
                        if (empty($PrdInfo)) {
                            $pay_record_flag = 1;
                            $Profit_Rst = PayRecordDetail::create($map);

                        }
                    }
                }
                LiveCountDown::create($liveCountDown);

                $userRst = WechatPay::UserBalance($pay_type, $user_id, $orderInfo['price']);

                if ($orderRst && $recordRst && $subscribeRst && $userRst && $Profit_Rst) {
                    DB::commit();
                    //SMS_211275363
                    //短信
                    if ($userdata['phone'] && $live_id == 12 && strlen($userdata['phone']) == 11) {
                        $easySms = app('easysms');
                        $easySms->send($userdata['phone'], [
                            'template' => 'SMS_211275363',
                        ], ['aliyun']);
                    }
                    //9.9刷单推送
                    if (!empty($orderInfo['remark']) && $orderInfo['remark'] > 0) {
                        self::LiveRedis(11, $liveData['title'], $userdata['nickname'], $orderInfo['remark'], $orderId, 1);
                    }

                    //暂时先不启用直接分账
//                    //调用直播分账
//                    if ( !empty($twitter_id) && $twitter_id != $user_id ) {
//                        if( $liveData['profit_sharing'] == 1 && $liveData['twitter_money'] > 0 ){
//                            self::OrderProfit($transaction_id,$out_trade_no,$liveData['twitter_money'],$twitter_id);
//                        }
//                    }
//                    Task::send(5, $user_id, $orderInfo['relation_id']);
//                    if($pay_record_flag == 1){
//                        Task::send(11, $user_id, $orderInfo['relation_id'],'',$liveData['title'],'','','',$userdata['nickname']);
//                    }
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

    /**
     * 分账
     *
     * @param string $transaction_id 微信支付订单号
     * @param string $out_trade_no 商户系统内部的分账单号
     * @param   $amount            分账金额  此处单位为元
     * @param   $twitterData       分账人员信息
     */
    public static function OrderProfit($transaction_id, $out_trade_no, $amount, $twitter_id)
    {

        $twitterData = User::find($twitter_id);

        if (empty($twitterData['wxopenid'])) {
            return;
        }

        $config = Config('wechat.payment.wx_wechat');
        $app = Factory::payment($config);

        //添加分账账号
        $app->profit_sharing->addReceiver([
            'type' => 'PERSONAL_OPENID',
            'account' => $twitterData['wxopenid'],
            'relation_type' => "USER",
        ]);

//                dump($addRes);
//                $addRes = [
//                      "return_code" => "SUCCESS",
//                      "result_code" => "SUCCESS",
//                      "mch_id" => "1460495202",
//                      "appid" => "wxe24a425adb5102f6",
//                      "receiver" => '"{"type":"PERSONAL_OPENID","account":"oVWHQwegrYvlUC4qwqdfte4DsJc8","relation_type":"USER"}"',
//                      "nonce_str" => "c7ea74540376e2cb",
//                      "sign" => "2AC52D6F7B55D8158FE515FCB10B4C4E33A19DDBA9145AD3D045D53CD80760D2",
//                    ];
        //分账
        $return_data = $app->profit_sharing->share($transaction_id, 'NLSGFZ' . $out_trade_no, [
            'type' => 'PERSONAL_OPENID',
            'account' => $twitterData['wxopenid'],
            'amount' => $amount * 100,  //此处金额 分为单位
            'description' => '直播分账-个人-' . $twitterData['nickname'],
//                                'name' => '个人名称',  //非必填
        ]);
        //分账返回信息
//                $return_data = [
//                    "return_code" => "SUCCESS",
//                    "result_code" => "SUCCESS",
//                    "mch_id" => "1460495202",
//                    "appid" => "wxe24a425adb5102f6",
//                    "nonce_str" => "f5efdc2d94dc0f3c",
//                    "sign" => "5C5654E5CAA2C174BF586367A643D3C188C3A2675EFAC1B48813086D0CA0CF0B",
//                    "transaction_id" => "4200000793202101124499850971",
//                    "out_order_no" => "NLSGFZ21011200211172503223703",  //自己的分账orderid
//                    "order_id" => "30000300342021011206844776406",        微信平台的分账订单
//                ];

        \Log::info('Wechat profit sharing' . json_encode($return_data));

        if ($return_data['result_code'] == 'SUCCESS') {
            //成功后 单次分账 不需要进行完结分账  多次需要请求完结接口
            Order::where(['ordernum' => $out_trade_no])->update([
                'profit_ordernum' => $return_data['out_order_no'],
                'wx_profit_ordernum' => $return_data['order_id'],
            ]);

        }
    }


    public static function mallOrder($data)
    {
        $now = time();
        $now_date = date('Y-m-d H:i:s', $now);
        $ordernum = $data['out_trade_no'];
        $pay_price = $data['total_fee'];
        $transaction_id = $data['transaction_id'];

        $check_pay_record = PayRecord::where('ordernum', '=', $ordernum)
            ->where('type', '=', 10)
            ->first();
        if (!empty($check_pay_record)) {
            return true;
        }

        $order_obj = MallOrder::where('ordernum', '=', strval($ordernum))->first();

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


            if ($temp_data->is_captain == 1) {
                $end_time = date('Y-m-d H:i:02', $now + $sp_info->group_life * 60);

                $end_time_res = MallGroupBuyList::whereId($temp_data->id)
                    ->update(['end_at' => $end_time]);

                if ($end_time_res === false) {
                    DB::rollBack();
                    return false;
                }
            } else {
                $get_captain_data = MallGroupBuyList::where('group_key', '=', $temp_data->group_key)
                    ->where('is_captain', '=', 1)
                    ->first();

                $end_time_res = MallGroupBuyList::whereId($temp_data->id)
                    ->update([
                        'begin_at' => $now_date,
                        'end_at' => $get_captain_data->end_at
                    ]);
                if ($end_time_res === false) {
                    DB::rollBack();
                    return false;
                }
            }

            $need_num = $sp_info->group_num;

            $now_num = DB::table('nlsg_mall_group_buy_list as bl')
                ->join('nlsg_mall_order as o', 'bl.order_id', '=', 'o.id')
                ->where('group_key', '=', $temp_data->group_key)
                ->where('o.status', '>', 1)
                ->count();

            if ($now_num >= $need_num) {
                //拼团成功
                $gb_res = MallGroupBuyList::where(
                    'group_key', '=', $temp_data->group_key
                )->update(
                    [
                        'is_success' => 1,
                        'success_at' => $now_date
                    ]
                );

                $change_gp_status_order_ids = MallGroupBuyList::where(
                    'group_key', '=', $temp_data->group_key
                )->pluck('order_id')->toArray();

                MallOrder::whereIn('id', $change_gp_status_order_ids)
                    ->update([
                        'gp_status' => 2
                    ]);

                if (!$gb_res) {
                    DB::rollBack();
                    return false;
                }
            }
        }

        //收益表
        $order_details = MallOrderDetails::where('order_id', '=', $order_obj->id)->get();

        foreach ($order_details as $od_v) {

            if ($od_v->inviter > 0) {

                $temp_stay_data = [
                    'type' => 5, //电商推客
                    'ordernum' => $order_obj->ordernum,
                    'order_detail_id' => $od_v->id,
                    'user_id' => $od_v->inviter,
                    'price' => GetPriceTools::PriceCalc('*', $od_v->t_money, $od_v->num),
                ];

                if ($od_v->user_id == $od_v->inviter) {
                    continue;
                }

                if (empty($temp_stay_data['price'])) {
                    continue;
                }

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

        //erp队列
        MallErpList::addList($order_obj->id);
        DB::commit();

        //如果是拼团订单,立刻填充机器人
        if ($order_obj->order_type === 3) {
            $mos = new MallOrderServers();
            $mos->makeGroupSuccess([
                'id'=>$order_obj->id
            ]);
        }
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
                    $couponRst = Coupon::where('id', $coupon_id)->update(['status' => 2, 'used_time' => date("Y-m-d H:i:s", $time)]);
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
                } elseif ($orderInfo['type'] == 18) {
                    $order_type = 22;  //训练营
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
                $pay_record_flag = 0;  //是否有收益

                //添加订阅记录
                $sub_type = 1;
                if ($orderInfo['type'] == 15) {
                    $sub_type = 6;  //讲座
                } else if ($orderInfo['type'] == 18) {
                    $sub_type = 7;  //训练营
                }
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
//                                    if ($orderInfo['SurplusPrice'] > 0) {
//                                        $LogData = [];
//                                        $LogData['ordernum'] = $out_trade_no;
//                                        $LogData['user_id'] = $twitter_id;
//                                        $LogData['type'] = 2;
//                                        $LogData['column_id'] = $teacher_id;
//                                        $LogData['num'] = 1;
//                                        $LogData['price'] = $orderInfo['SurplusPrice'];   //推广产品所获金额
//                                        $LogData['ctime'] = $time;
////                                        $AgentProfitObj->add($AgentProfitObj::$table,$LogData);
//                                        AgentProfitLog::create($LogData);
////                                        $ProfitPrice = Profit::Income(0, 5, 0, 1, $teacher_id);
//                                        $ProfitPrice = GetPriceTools::Income(0, $TwitterInfo['level'], 0, 1, $teacher_id);
//
//                                        $ProfitPrice = $ProfitPrice - $orderInfo['SurplusPrice'];//返回剩余款项
//                                    } else {
//                                        //                                      $ProfitPrice = Tool::RetainDecimal ($ProfitPrice, 0.45, 1);
////                                        $ProfitPrice = Profit::Income(0, 5, 0, 1, $teacher_id);
//                                        $ProfitPrice = GetPriceTools::Income(0, $TwitterInfo['level'], 0, 1, $teacher_id);
//
//                                    }
                                    $ProfitPrice = GetPriceTools::Income(0, $TwitterInfo['level'], 0, 1, $teacher_id);

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
                            $is_sub = Subscribe::isSubscribe($twitter_id, $teacher_id, $sub_type);
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

                if (!empty($map) && $orderInfo['type'] != 18) {
                    //$PayRDObj = new PayRecordDetail();
                    //防止重复添加收入
                    $where = ['user_id' => $map['user_id'], 'type' => $map['type'], 'ordernum' => $map['ordernum']];
                    $PrdInfo = PayRecordDetail::where($where)->first();
                    if (empty($PrdInfo)) {
                        $pay_record_flag = 1;
                        PayRecordDetail::create($map);
                        //Profit::ServiceIncome($out_trade_no,6,$ProfitPrice,$twitter_id);
                        GetPriceTools::ServiceIncome($out_trade_no, 6, $ProfitPrice, $twitter_id);

                    }
                }


                $check_channel_works_list = ChannelWorksList::where('works_id', '=', $teacher_id)
                    ->where('type', '=', 1)
                    ->first();
                if (empty($check_channel_works_list)) {
                    $channel_works_list_id = 0;
                } else {
                    $channel_works_list_id = $check_channel_works_list->id;
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
                    'channel_works_list_id' => $channel_works_list_id,
                ];
                $subscribeRst = Subscribe::firstOrCreate($subscribe);

                //订阅量处理
                Column::where(['id' => $teacher_id])->increment('real_subscribe_num');
                if ($orderInfo['type'] == 18) {   //训练营不需要虚拟订阅数据
                    Column::where(['id' => $teacher_id])->increment('subscribe_num');
                } else {
                    Works::edit_view_num($teacher_id, 2, 2); //虚拟数 3000以下1：50   以上1：5
                }


//                $user_id = empty($orderInfo['service_id']) ? $user_id : $orderInfo['service_id'];
//                $userRst = WechatPay::UserBalance($pay_type, $user_id, $orderInfo['price']);
                $user_id = empty($orderInfo['service_id']) ? $user_id : $orderInfo['service_id'];
                $userRst = WechatPay::UserBalance($pay_type, $user_id, $orderInfo['price']);


                if ($orderRst && $couponRst && $phoneRst && $recordRst && $subscribeRst && $shareSyRst && $Sy_Rst) {
                    DB::commit();
                    $live_id = $orderInfo['live_id'];
                    self::LiveRedis(18, $orderInfo['relation_id'], $AdminInfo['nickname'], $live_id, $orderId, $orderInfo['live_num']);
                    //发送通知、

                    if ($orderInfo['type'] == 18) {
                        //  加入社群
                        self::joinImGroup($orderInfo['relation_id'],$user_id);
                    }


//                    Task::send($send_type, $user_id, $orderInfo['relation_id']);
//                    if($pay_record_flag == 1){
//                        $ColumnInfo = Column::find($teacher_id)->toArray();
//                        Task::send(11, $user_id, $orderInfo['relation_id'],'',$ColumnInfo['name'],'','','',$AdminInfo['nickname']);
//                    }
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
    public static function joinImGroup($relation_id,$uid,$type=0){
        if($type == 14 ){
            if($relation_id == 5){//训练营
                //查找当前门票类型对应的训练营
                $relation_id = OfflineProducts::where('id',$relation_id)->value("column_id");
            }else{
                return '';
            }
        }

        if($relation_id <= 0){
            return ;
        }
        $groups = ImGroup::where(['status'=>1,'column_id'=>$relation_id])
                    ->where('member_num','<',200)  //群满员两千人  规定一期200人
                    ->first();
        if(empty($groups)){
            return ;
        }
        $params=[
            'type'      => 'add',
            'group_id'  => $groups['group_id'],
            'user_id'   => [$uid],
        ];

        $servers = new ImGroupServers();
        $res = $servers->editJoinGroup($params);
        return ;
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
                $starttime = strtotime(date('Y-m-d', $time));
                $endtime = strtotime(date('Y', $starttime) + 1 . '-' . date('m-d', $starttime)) + 86400; //到期日期

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
                $pay_record_flag = 0;
                $map = [];
                //$WorksObj    = new Works();
                $WorksInfo = Works::find($works_id);

                if (!empty($twitter_id) && $twitter_id != $user_id && $orderInfo['twitter_id'] != $orderInfo['service_id']) { //推客是自己不算 服务商赠送不返利
                    //查看用户权限
                    $is_twitter = User::getLevel($twitter_id);
                    $TwitterInfo = User::find($twitter_id);
                    if ($is_twitter > 0) {//是推客 皇钻 黑钻
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
                                //没有服务商了
//                                if ($orderInfo['SurplusPrice'] > 0) {
//                                    $LogData = [];
//                                    $LogData['ordernum'] = $out_trade_no;
//                                    $LogData['user_id'] = $twitter_id;
//                                    $LogData['type'] = 3;
//                                    $LogData['works_id'] = $works_id;
//                                    $LogData['num'] = 1;
//                                    $LogData['price'] = $orderInfo['SurplusPrice'];
//                                    $LogData['ctime'] = $time;
////                                        $AgentProfitObj->add($AgentProfitObj::$table,$LogData);
//                                    AgentProfitLog::create($LogData);
//
////                                        $ProfitPrice=Profit::IncomeController(0,5,0,2,$teacher_id,$works_id);
//                                    $ProfitPrice = GetPriceTools::Income(0, $TwitterInfo['level'], 0, 2, 0, $works_id);
//                                    $ProfitPrice = $ProfitPrice - $orderInfo['SurplusPrice'];
//                                } else {//海报
////                                        $ProfitPrice=Profit::IncomeController(0,5,0,2,$teacher_id,$works_id);
//                                    $ProfitPrice = GetPriceTools::Income(0, $TwitterInfo['level'], 0, 2, 0, $works_id);
//
//                                }
                                $ProfitPrice = GetPriceTools::Income(0, $TwitterInfo['level'], 0, 2, 0, $works_id);


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
                        $pay_record_flag = 1;
                        $shareSyRst = PayRecordDetail::create($map);
                        //5%返现
                        //Profit::ServiceIncome($out_trade_no,7,$ProfitPrice,$twitter_id);
                        GetPriceTools::ServiceIncome($out_trade_no, 7, $ProfitPrice, $twitter_id);
                    }
                }

                //添加订阅记录
                $check_channel_works_list = ChannelWorksList::where('works_id', '=', $works_id)
                    ->where('type', '=', 2)
                    ->first();
                if (empty($check_channel_works_list)) {
                    $channel_works_list_id = 0;
                } else {
                    $channel_works_list_id = $check_channel_works_list->id;
                }
                $subscribe = [
                    'user_id' => $user_id, //会员id
                    'type' => 2, //作品
                    'status' => 1,
                    'relation_id' => $works_id, //精品课
                    'order_id' => $orderId, //订单id
                    'pay_time' => date("Y-m-d H:i:s", $time), //支付时间
                    'service_id' => $orderInfo['service_id'],
                    'start_time' => date("Y-m-d H:i:s", $starttime),
                    'end_time' => date("Y-m-d H:i:s", $endtime),
                    'channel_works_list_id' => $channel_works_list_id,
                ];


                $subscribeRst = Subscribe::firstOrCreate($subscribe);

                //精品课购买数量 自增1
                $class_Rst = Works::where(['id' => $works_id])->increment('real_subscribe_num', 1);
                Works::edit_view_num($works_id, 1, 2); //虚拟数 3000以下1：50   以上1：5

                $user_id = empty($orderInfo['service_id']) ? $user_id : $orderInfo['service_id'];
                $userRst = WechatPay::UserBalance($pay_type, $user_id, $orderInfo['price']);

                if ($phoneRst && $orderRst && $couponRst && $recordRst && $subscribeRst && $shareSyRst && $class_Rst) {
                    DB::commit();
//                    $content = "订单修改:$orderRst--优惠券:$couponRst--支付记录:$recordRst--分成记录:$shareSyRst--订阅:$subscribeRst";
//                    Io::WriteFile('', '', $content, true);
//                    self::$user_id = $user_id;
                    //创业天下推送队列
                    if (($orderInfo['activity_tag'] ?? '') == 'cytx') {
                        JobServers::pushToCytx($orderInfo['id']);
                    }


//                    Task::send(1, $user_id, $orderInfo['relation_id']);
//                    if($pay_record_flag == 1){
//                        Task::send(11, $user_id, $orderInfo['relation_id'],'',$WorksInfo['title'],'','','',$AdminInfo['nickname']);
//                    }
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
                    if (!empty($orderInfo['live_id'])) {
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


}
