<?php


namespace App\Http\Controllers\Api\V4;


use App\Http\Controllers\Controller;
use App\Models\Column;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\PayRecord;
use App\Models\PayRecordDetail;
use App\Models\Subscribe;
use App\Models\User;
use App\Models\Works;
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

        //1专栏 2会员 5打赏 8商品(包括书籍)    9精品课 听课 10借书 月卡 季卡 违约金 11直播视频 12预约回放（必填）
        if ($data['attach'] == 1) { //处理专栏  OK
            return self::PayColumn($data);
        } elseif ($data['attach'] == 2) { //处理会员
            return self::PayVip($data);
        } elseif ($data['attach'] == 5) { //打赏 OK
            return self::Areward($data);
        } elseif ($data['attach'] == 8) { //电商产品支付  OK
            return self::PayProduct($data);
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
        }
    }

    //微信购买专栏问题
    public static function PayColumn($data)
    {
        $time           = time();
        //$out_trade_no   = substr($data['out_trade_no'], 0, -5);
        $out_trade_no   = $data['out_trade_no'];
        $total_fee      = $data['total_fee'];
        $transaction_id = $data['transaction_id'];
        $pay_type = $data['pay_type'];

        //支付处理正确-判断是否已处理过支付状态
        $orderInfo = Order::select()->where(['ordernum'=>$out_trade_no,'status'=>0])->first();
        if (!empty($orderInfo)) {
            $orderInfo = $orderInfo->toArray();
            $starttime = strtotime(date('Y-m-d', $time));
            $endtime   = strtotime(date('Y', $starttime) + 1 . '-' . date('m-d', $starttime)) + 86400;//到期日期

            DB::beginTransaction();
            try {

                $teacher_id = $orderInfo['relation_id']; //专栏老师
                $coupon_id  = $orderInfo['coupon_id']; //优惠券
                $user_id    = $orderInfo['user_id']; //用户
                $orderId    = $orderInfo['id']; //订单id
                $twitter_id = $orderInfo['twitter_id']; //推客id

                //更新订单状态
                $data1     = [
                    'status'     => 1,
                    'pay_time'   => $time,
                    'pay_price'  => $total_fee,
                    'start_time' => $starttime,
                    'end_time'   => $endtime,
                    'pay_type'   => $pay_type,
                ];
                $orderRst = Order::where(['ordernum' => $out_trade_no])->update($data1);

                $couponRst = 1;
                //消除优惠券
                if ($coupon_id > 0) {
                    $couponRst = Coupon::where('id', $coupon_id)->update(['status' => 2, 'used_time' => $time]);
                }
                $phoneRst = 1;
                //防止短信发送不成功
                $AdminInfo = User::find($user_id);
                if (!empty($AdminInfo) && !empty($AdminInfo['username'])) {
                    if (preg_match('/^(1)\d{10}$/', $AdminInfo['username'])) {
                        //添加短信通知
                        $phoneArr = [
                            'type'      => 2,            //短信推送
                            'author_id' => $teacher_id,
                            'user_id'   => $user_id,
                            'ctime'     => $time,
                        ];
                    }
                }

                $record_type = self::$pay_record_type[$data['pay_type']] ?? 0;
                //添加支付记录
                $record = [
                    'ordernum'       => $out_trade_no,            //订单编号
                    'price'          => $total_fee,                //支付金额
                    'transaction_id' => $transaction_id,            //流水号
                    'user_id'        => $user_id,                   //会员id
                    'type'           => $pay_type,          //1：微信  2：支付宝
//                    'client'         => $data['client'],            //app微信
                    'order_type'     => 1,                          //1 专栏 2 会员  3充值  4财务打款 5 打赏
                    'status'         => 1                           //收入
                ];

                $recordRst = PayRecord::firstOrCreate($record);

                $Sy_Rst     = true;
                $shareSyRst = true;
                $map        = [];
                if (!empty($twitter_id) && $orderInfo['twitter_id']!=$orderInfo['service_id'] ) {

                    $isFlag = User::getIncomeFlag($twitter_id,$user_id); //获取是否可返利
                    //$isFlag=Profit::GetIncomeFlag
                    if ( $isFlag ) { //推客是自己不算
                        //查看用户权限
//                        $TwitterInfo = $UserObj->GetLevel($twitter_id);
//                        $is_twitter  = $UserObj->IsTweeter($TwitterInfo);
                        $TwitterInfo = User::find($twitter_id);
                        $is_twitter = User::getLevel($twitter_id);
                        if ($is_twitter > 1) //是推客 皇钻 黑钻
                        {
//                            $ColumnInfo = Column::find($teacher_id);
//                            $ProfitPrice = 0;
//                            switch ($TwitterInfo['level']) {
//                                case 2: //推客
//                                    $ProfitPrice = Profit::IncomeController(0,2,0,1,$teacher_id);
//                                    break;
//                                case 3: //黑钻
//                                    $ProfitPrice = Profit::IncomeController(0,3,0,1,$teacher_id);
//                                    break;
//                                case 4://皇钻
//                                    $ProfitPrice=Profit::IncomeController(0,4,0,1,$teacher_id);
//                                    break;
//                                case 5://代理商
//
//                                    $AgentProfitObj=new AgentProfitLog();
//                                    $where = ['user_id'=>$twitter_id,'type'=>[1,2,3],'status'=>1];
//                                    $ProfitInfo = $AgentProfitObj->getOne($AgentProfitObj::$table,$where,['sum(price) price']);
//                                    if(empty($ProfitInfo['price'])){
//                                        $sumPrice=0;
//                                    }else{
//                                        $sumPrice=$ProfitInfo['price'];
//                                    }
//
//                                    if(($TwitterInfo['level_send_price']-$sumPrice) >= $ColumnInfo['price']){
//                                        //添加记录
//                                        $LogData = [];
//                                        $LogData['ordernum'] =$out_trade_no;
//                                        $LogData['user_id']=$twitter_id;
//                                        $LogData['type'] =2;
//                                        $LogData['column_id']=$teacher_id;
//                                        $LogData['num']=1;
//                                        $LogData['price'] =$ColumnInfo['price'];
//                                        $LogData['ctime'] =$time;
//                                        $AgentProfitObj->add($AgentProfitObj::$table,$LogData); //添加记录
//                                        $ProfitPrice=$ColumnInfo['price']; //返现处理
//                                    }else{ //支付金额已扣除分成
//                                        $ProfitPrice=0;
//                                        if($orderInfo['SurplusPrice']>0){
//                                            $LogData = [];
//                                            $LogData['ordernum'] =$out_trade_no;
//                                            $LogData['user_id']=$twitter_id;
//                                            $LogData['type'] =2;
//                                            $LogData['column_id']=$teacher_id;
//                                            $LogData['num']=1;
//                                            $LogData['price'] =$orderInfo['SurplusPrice'];   //推广产品所获金额
//                                            $LogData['ctime'] =$time;
//                                            $AgentProfitObj->add($AgentProfitObj::$table,$LogData);
//                                            $ProfitPrice = Profit::IncomeController(0, 5, 0, 1, $teacher_id);
//                                            $ProfitPrice=$ProfitPrice-$orderInfo['SurplusPrice'];//返回剩余款项
//                                        }else{
//                                            //                                      $ProfitPrice = Tool::RetainDecimal ($ProfitPrice, 0.45, 1);
//                                            $ProfitPrice = Profit::IncomeController(0, 5, 0, 1, $teacher_id);
//                                        }
//                                    }
//                                    break;
//                            }
//                            if($ProfitPrice > 0) {
//                                $map = [
//                                    'user_id' => $twitter_id,
//                                    "type" => 6,
//                                    "ordernum" => $out_trade_no,
//                                    'price' => $ProfitPrice,
//                                    "ctime" => $time
//                                ];
//                            }
                        } else {
                            $is_sub = Subscribe::isSubscribe($twitter_id,$teacher_id,1);
                            if ($is_sub) { //订阅专栏
                                $ColumnInfo = Column::find($teacher_id);
                                $ProfitPrice = $ColumnInfo['twitter_price'];
                                $map         = [
                                    'user_id' => $twitter_id,
                                    "type" => 6, "ordernum" => $out_trade_no,
                                    'price' => $ProfitPrice,
                                    "ctime" => $time
                                ];
                            }
                        }
                    }
                }

                if (!empty($map)) {

                    //$PayRDObj = new PayRecordDetail();
                    //防止重复添加收入
                    $where = ['user_id'=>$map['user_id'],'type'=>$map['type'],'ordernum'=>$map['ordernum']];
                    $PrdInfo = PayRecordDetail::where($where)->first();
                    if (empty($PrdInfo)) {
                        PayRecordDetail::create($map);
                        //Profit::ServiceIncome($out_trade_no,6,$ProfitPrice,$twitter_id);
                    }
                }

                //添加订阅记录
                $subscribe    = [
                    'user_id'        => $user_id,                //会员id
                    'pay_time'       => $time,                            //支付时间
                    'type'           => 1,
                    'order_id'       => $orderId, //订单id
                    'status'         => 1,
                    'start_time'     => $starttime,
                    'end_time'       => $endtime,
                    'relation_id'    => $teacher_id,
                    'service_id'     =>$orderInfo['service_id'],
                ];
                $subscribeRst = Subscribe::firstOrCreate($subscribe);

//                $user_id = empty($orderInfo['service_id']) ? $user_id : $orderInfo['service_id'];
//                $userRst = WechatPay::UserBalance($pay_type, $user_id, $orderInfo['price']);

                if ($orderRst && $couponRst && $phoneRst && $recordRst && $subscribeRst && $shareSyRst && $Sy_Rst ) {
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

        $time           = time();
        $out_trade_no   = $data['out_trade_no'];
        $total_fee      = $data['total_fee'];
        $transaction_id = $data['transaction_id'];
        $pay_type       = $data['pay_type'];


        //支付处理正确-判断是否已处理过支付状态
        $orderInfo = Order::select()->where(['ordernum'=>$out_trade_no,'status'=>0])->first();
        if (!empty($orderInfo)) {
            $orderInfo = $orderInfo->toArray();

            DB::beginTransaction();
            try {

                //$teacher_id = $orderInfo['teacher_id']; //专栏老师
                $coupon_id  = $orderInfo['coupon_id']; //优惠券
                $user_id    = $orderInfo['user_id']; //用户
                $orderId    = $orderInfo['id']; //订单id
                $twitter_id = $orderInfo['twitter_id']; //推客id
                $works_id   = $orderInfo['relation_id']; //精品课id

                //更新订单状态
                $data1     = [
                    'status'    => 1,
                    'pay_time'  => $time,
                    'pay_price' => $total_fee,
                    'pay_type' => $pay_type,
                ];
                $orderRst =Order::where(['ordernum' => $out_trade_no])->update($data1);

                $couponRst = 1;
                //消除优惠券
                if ($coupon_id > 0) {
                    $couponRst = Coupon::where(['id' => $coupon_id])->update(['status' => 2, 'used_time' => $time]);
                }

                $record_type = self::$pay_record_type[$data['pay_type']] ?? 0;
                //添加支付记录
                $record    = [
                    'ordernum'       => $out_trade_no,            //订单编号
                    'price'          => $total_fee,               //支付金额
                    'ctime'          => $time,                    //支付时间
                    'transaction_id' => $transaction_id,          //流水号
                    'user_id'        => $user_id,                 //会员id
                    'type'           => $pay_type,        //1：微信  2：支付宝
//                    'client'         => $data['client'],          //微信
                    'order_type'     => 11,                       //精品课
                    'status'         => 1                         //收入
                ];
                $recordRst = PayRecord::firstOrCreate([$record]);

                $phoneRst = 1;
                //防止短信发送不成功
                $AdminInfo = User::find($user_id);
                if (!empty($AdminInfo) && !empty($AdminInfo['username'])) {
                    if (preg_match('/^(1)\d{10}$/', $AdminInfo['username'])) {
                        //添加短信通知
                        $phoneArr = [
                            'type'      => 6,            //短信推送
                            //'author_id' => $teacher_id,
                            'user_id'   => $user_id,
                            'works_id'  => $works_id,
                            'ctime'     => $time,
                        ];
//                        $MessObj  = new Messages();
//                        $phoneRst = $MessObj->add($MessObj::$table,$phoneArr);
                    }
                }

                $shareSyRst = true;
                $map        = [];
                //$WorksObj    = new Works();

                if (!empty($twitter_id) && $twitter_id != $user_id && $orderInfo['twitter_id']!=$orderInfo['service_id']) { //推客是自己不算 服务商赠送不返利


                    //查看用户权限
                    $is_twitter = User::getLevel($twitter_id);
                    $TwitterInfo = User::find($twitter_id);
                    if ($is_twitter > 0) {//是推客 皇钻 黑钻
                        $WorksInfo   =Works::find($works_id);
                        $ProfitPrice = $WorksInfo['twitter_price'];
//                        switch ($TwitterInfo['level']) {
//                            case 2: //推客
//                                $ProfitPrice = Profit::IncomeController(0,2,0,2,$teacher_id,$works_id);
//                                break;
//                            case 3: //黑钻
//                                $ProfitPrice = Profit::IncomeController(0,3,0,2,$teacher_id,$works_id);
//                                break;
//                            case 4://皇钻
//                                $ProfitPrice = Profit::IncomeController(0,4,0,2,$teacher_id,$works_id);
//                                break;
//                            case 5://代理商  没有限制购买方的折扣可能存在亏损
//
//                                $AgentProfitObj=new AgentProfitLog();
//
//                                $table = '';
//                                $where = ['type'=>[1,2,3],'status'=>1];
//                                $field = ['sum(price) price'];
//                                $ProfitInfo = $AgentProfitObj->getOne($AgentProfitObj::$table,$where,$field);
//
//                                if(empty($ProfitInfo['price'])){
//                                    $sumPrice=0;
//                                }else{
//                                    $sumPrice=$ProfitInfo['price'];
//                                }
//                                if(($TwitterInfo['level_send_price']-$sumPrice)>=$WorksInfo['price']){
//                                    //添加记录
//                                    $LogData = [];
//                                    $LogData['ordernum'] =$out_trade_no;
//                                    $LogData['user_id']=$twitter_id;
//                                    $LogData['type'] =3;
//                                    $LogData['works_id']=$works_id;
//                                    $LogData['num']=1;
//                                    $LogData['price'] =$WorksInfo['price'];
//                                    $LogData['ctime'] =$time;
//                                    $AgentProfitObj->add($AgentProfitObj::$table,$LogData);
//                                    //$ProfitPrice=$WorksInfo['price'];//返现处理 实际支付金额
//                                    $ProfitPrice=$total_fee;//返现处理 实际支付金额
//                                }else{ //支付金额已经扣除分成
////                                    $ProfitPrice = Tool::RetainDecimal ($WorksInfo['price'], 0.45, 1);
//                                    $ProfitPrice=0;
//                                    if($orderInfo['SurplusPrice']>0){
//                                        $LogData = [];
//                                        $LogData['ordernum'] =$out_trade_no;
//                                        $LogData['user_id']=$twitter_id;
//                                        $LogData['type'] =3;
//                                        $LogData['works_id']=$works_id;
//                                        $LogData['num']=1;
//                                        $LogData['price'] =$orderInfo['SurplusPrice'];
//                                        $LogData['ctime'] =$time;
//                                        $AgentProfitObj->add($AgentProfitObj::$table,$LogData);
//                                        $ProfitPrice=Profit::IncomeController(0,5,0,2,$teacher_id,$works_id);
//                                        $ProfitPrice=$ProfitPrice-$orderInfo['SurplusPrice'];
//                                    }else{//海报
//                                        $ProfitPrice=Profit::IncomeController(0,5,0,2,$teacher_id,$works_id);
//                                    }
//
//                                }
//                                break;
//                        }
                        if($ProfitPrice>0) {
                            $map = array ('user_id' => $twitter_id, "type" => 7, "ordernum" => $out_trade_no, 'price' => $ProfitPrice, "ctime" => $time);

                        }
                    } else {
                        $is_sub = Subscribe::isSubscribe($twitter_id,$works_id,2);
                        if ($is_sub) {
                            $WorksInfo   = Works::find($works_id);
                            $ProfitPrice = $WorksInfo['twitter_price'];
                            $map         = array('user_id' => $twitter_id, "type" => 7, "ordernum" => $out_trade_no, 'price' => $ProfitPrice, "ctime" => $time);
                        }
                    }

                }

                if (!empty($map)) {

                    $PayRDObj = new PayRecordDetail();
                    //防止重复添加收入
                    $where = ['user_id'=>$map['user_id'],'type'=>$map['type'],'ordernum'=>$map['ordernum']];
                    $PrdInfo = PayRecord::where($where)->first();

                    if (empty($PrdInfo)) {
                        $shareSyRst = PayRecordDetail::create($map);
                        //5%返现
                        //Profit::ServiceIncome($out_trade_no,7,$ProfitPrice,$twitter_id);
                    }
                }

                //添加订阅记录
                $subscribe = [
                    'user_id'        => $user_id,                //会员id
                    'type'           => 2, //作品
                    'status'         => 1,
                    'relation_id'    => $works_id, //精品课
                    'order'          => $orderId, //订单id
                    'pay_time'       => $time,                            //支付时间
                    'service_id'     =>$orderInfo['service_id'],
                ];


                $subscribeRst = Subscribe::firstOrCreate($subscribe);

                //精品课购买数量 自增1
                $class_Rst = Works::where(['id'=>$works_id])->increment('subscribe_num',1);

                $user_id = empty($orderInfo['service_id']) ? $user_id : $orderInfo['service_id'];
                //$userRst = WechatPay::UserBalance($pay_type, $user_id, $orderInfo['price']);

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

    // 苹果端 能量币充值
    public static function PayCoinOrder($data)
    {
        $time           = time();
        //$out_trade_no   = substr($data['out_trade_no'], 0, -5);
        $out_trade_no   = $data['out_trade_no'];
        $total_fee      = $data['total_fee'];
        $transaction_id = $data['transaction_id'];
        $pay_type       = $data['pay_type'];

        //支付处理正确-判断是否已处理过支付状态
        $orderInfo = Order::select()->where(['ordernum'=>$out_trade_no,'status'=>0])->first();
        if (!empty($orderInfo)) {
            $orderInfo = $orderInfo->toArray();
            $starttime = strtotime(date('Y-m-d', $time));
            $endtime   = strtotime(date('Y', $starttime) + 1 . '-' . date('m-d', $starttime)) + 86400;//到期日期

            DB::beginTransaction();

            try {

                $user_id = $orderInfo['user_id']; //用户

                //更新订单状态
                $data     = [
                    'status'    => 1,
                    'pay_time'  => $time,
                    'pay_price' => $total_fee,
                    'pay_type'   => $pay_type,
                ];
                $orderRst = Order::where(['ordernum' => $out_trade_no])->update($data);
                //添加支付记录
                $record_type = self::$pay_record_type[$data['pay_type']] ?? 1;

                $record    = [
                    'ordernum'       => $out_trade_no,            //订单编号
                    'price'          => $total_fee,                //支付金额
                    'ctime'          => $time,                            //支付时间
                    'transaction_id' => $transaction_id,    //流水号
                    'user_id'        => $user_id,                //会员id
                    'type'           => $pay_type,  //1：微信  2：支付宝
                    'client'         => $data['client'],    //app微信
                    'order_type'     => 18,                  //能量币充值
                    'status'         => 1                   //收入
                ];
                $recordRst = PayRecord::firstOrCreate($record);
                //添加账户余额
                $userRst = User::where('id', $user_id)->increment('ios_balance',$total_fee);
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