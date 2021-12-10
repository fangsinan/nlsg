<?php
/**
 * Created by PhpStorm.
 * User: nlsg2017
 * Date: 2019/7/11
 * Time: 15:43
 */

namespace App\Models;

use EasyWeChat\Factory;
use Illuminate\Support\Facades\DB;
use Yansongda\Pay\Pay;

class Withdrawals extends Base
{


    protected $mchid;
    protected $appid;
    protected $key;

    public function __construct()
    {
        //微信支付参数获取
        $this->mchid = Config('web.app_wechat.wechat_mchid');
        $this->appid = Config('web.app_wechat.wechat_appid');
        $this->key   = Config('web.app_wechat.wechat_key');
    }

    //计算扣税
    public static function cal_tax($money){

        if($money<=800){
            return 0;
        }else if($money>800 && $money<=4000){
            return ($money - 800) * 0.2;
        }else if($money>4000 && $money<=20000){
            return $money*0.8*0.2;
        }else if($money>20000 && $money<=50000){
            return $money*0.3-2000;
        }else if($money>50000){
            return $money*0.4-7000;
        }

    }

    //提现操作
    public function TxRecord($amount,$zh_account,$user_id,$truename,$orderid,$tax=0,$order_type=0,$ip,$os_type=1){

        DB::beginTransaction();

        try{
            //添加提现记录
            $record=[];
            $record['order_type']=$order_type; //12机构提现  8微信  7支付宝

            if($os_type == 1 ){  //1 安卓 2ios 3微信
                $type = $order_type == 7?3:2;
            }else if($os_type == 2){
                $type = 4;
            }else{
                $type = 1;
            }


            $record['status']=1; //提现  1处理中  2成功
            $record['price']=$amount;
            $record['type']=$type; //1微信端   2app微信    3app支付宝  4ios
            $record['ordernum']=$orderid; //订单号
            $record['product_id']=$truename.':'.$zh_account;
            $record['user_id']=$user_id; //用户id
//            if($order_type){
//                $record['client']=6; //6机构提现
//            }else{
//                $record['client']=1; //微信端
//            }
            $record['tax']=$tax; //税费
            $record_id = PayRecord::create($record);


            //写提现订单
            $data=[];
            $data['type'] = $order_type; //微信提现
            $data['user_id'] = $user_id;
            $data['ordernum'] = $orderid;
            $data['ip'] = $ip;
            $data['os_type'] = $os_type; //x
            $data['cost_price'] = $amount;
            $data['price'] = $amount;
            //$status = $OrderObj->add($OrderObj::$table, $data);
            $status = Order::create($data);
            if($record_id && $status){
//                $content="提现记录:$record_id";
//                Io::WriteFile('','',$content,true);
                DB::commit();
                return $record_id;
            }else{
                DB::rollBack();
                return false;
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }

    }

    //https://pay.weixin.qq.com/wiki/doc/api/tools/mch_pay.php?chapter=14_2
    public function Pay($user_id,$zh_account, $amount, $desc,$truename, $orderid,$pay_id,$ip='127.0.0.1',$channel,$order_type,$os_type)
    {

        $amount = ($amount/100);     // 0.01-0时候 会得到0

        //查询是否有添加过订单，防止多次点击
        //$order_type  7
        if($os_type == 1 ){  //1 安卓 2ios 3微信
            $type = $order_type == 7?3:2;
        }else if($os_type == 2){
            $type = 4;
        }else{
            $type = 1;
        }

        $Submission_flag = PayRecord::where([
            'user_id'=>$user_id,
            'type'=>$type,
            'order_type'=>$order_type,
            'price'=>$amount,
        ])->where('created_at','>',(date('Y-m-01')))
            ->whereIn('status',[1,2])->count();

        if(!empty($Submission_flag) && $Submission_flag>1){
            return ['status'=>0, 'msg'=>'请勿重复提现',];
        }

        $Test_User = Config('web.Withdrawals.Test_User');
        if(in_array($user_id, $Test_User)){
            $amount=1;   // 元单位
        }
        if($channel == 'WeChat'){
            //微信提现
//            $config = Config('wechat.payment.default');
            $config = Config('wechat.payment.old_default');// 提现用老账户

            $app    = Factory::payment($config);
            $result = $app->transfer->toBalance([
                'partner_trade_no' => $orderid, // 商户订单号，需保持唯一性(只能是字母或者数字，不能包含有符号)
                'openid' => $zh_account,
                'check_name' => 'FORCE_CHECK', // NO_CHECK：不校验真实姓名, FORCE_CHECK：强校验真实姓名
                're_user_name' => $truename, // 如果 check_name 设置为FORCE_CHECK，则必填用户真实姓名
                'amount' => ($amount* 100), // 企业付款金额，单位为分
                'desc' => $orderid, // 企业付款操作说明信息。必填
            ]);
//    //成功后返回参数
//            $result=[
//              "return_code" => "SUCCESS",
//              "return_msg" => null,
//              "mch_appid" => "wx3296e2b7430df182",
//              "mchid" => "1460495202",
//              "nonce_str" => "5f053dcc9371f",
//              "result_code" => "SUCCESS",
//              "partner_trade_no" => "20200708113020",
//              "payment_no" => "10100244512772007083685544540676",
//              "payment_time" => "2020-07-08 11:30:21",
//            ];

        }else if($channel == 'ali'){
            //支付宝提现

            $order = [
                'out_biz_no' => $orderid,
                'trans_amount' => $amount,  // 企业付款金额，单位为元
                'product_code' => 'TRANS_ACCOUNT_NO_PWD',
                'biz_scene' => 'DIRECT_TRANSFER',
                'payee_info' => [
                    'identity' => $zh_account,
                    'identity_type' => 'ALIPAY_LOGON_ID',
                    'name' => $truename,
                ],
            ];
            $config = Config('pay.alipay');
            $result = Pay::alipay($config)->transfer($order);
//
////  成功返回参数
//            $result = [
//                "code" => "10000",
//                "msg" => "Success",
//                "order_id" => "20200709110070000006310011153064",
//                "out_biz_no" => "20200709104916",
//                "pay_fund_order_id" => "20200709110070001506310012874792",
//                "status" => "SUCCESS",
//                "trans_date" => "2020-07-09 10:49:17",
//            ];
            $result['return_msg'] = $result['sub_msg'] ??'';

        }

        if ( ($channel == 'WeChat' && strtolower($result['result_code']) == 'success') ||
            ($channel == 'ali' && strtolower($result['msg']) == 'success')) {
            DB::beginTransaction();
            try{
               if($channel == 'WeChat') {
                   $payment_no = $result['payment_no'];
               }else{
                   $payment_no = $result['order_id'];
               }

                $map=[
                    'transaction_id' => $payment_no, //交易单号 支付宝或微信交易号
                    'product_id'=>$truename.':'.$zh_account, //如果是重试的去掉重试标志
                    'status'=>2 //更改正常状态
                ];
                $payRst=PayRecord::where(['id'=>$pay_id])->update($map);
                //提现成功更改订单
                $OrderRst=Order::where(['ordernum'=>$orderid,'user_id'=>$user_id])->update(['status'=>1,'pay_price'=>$amount,'pay_time'=>date('Y-m-d H:i:s',time())]);

                //处理提现节点
                $CashRst=true;
                $CashInfo = CashData::where(['user_id'=>$user_id])->first();
                if(empty($CashInfo['balance2017_cash_time'])){
                    $CashRst = CashData::where(['user_id'=>$user_id])->update(['balance2017_cash_time'=>time()]);
                }
                if($payRst && $OrderRst && $CashRst){
                    DB::commit();
                    return ['status'=>200, 'msg'=>'提现成功', 'result'=>['ali_order_id'=>$payment_no]];
                }else{
                    DB::rollBack();
                    return ['status'=>0, 'msg'=>'提现失败',];
                }
            }catch(\Exception $e){
                DB::rollBack();
                return ['status'=>0, 'msg'=>'提现失败',];
            }
        } else {
            if (strtoupper ($result['err_code']) == 'SYSTEMERROR') { //系统繁忙 原单号触发防止重复支付
                $time = time ();
                $str = '系统繁忙,系统自动重试';
                $map=['created_at'=>$time, 'product_id'=>$truename.':'.$zh_account.':'.$str,];
            } else if (strtoupper ($result['err_code']) == 'NOTENOUGH') { //余额不足 提示信息隐藏弊端
                $str = '用户异常,请联系能量时光解决';
                $map=['status'=>5, 'product_id'=>'余额不足|'.$str,];
            }else if (strtoupper ($result['err_code']) == 'MONEY_LIMIT') { //当日同一用户限额
                $str = '您当日提现已限额，请明天重试';
                $map=['status'=>4, 'product_id'=>$result['err_code']."|".$str,];
            } else if (strtoupper ($result['err_code']) == 'NAME_MISMATCH') { //付款人身份校验不通过
                $str = '身份校验不通过';
                $map=['status'=>6, 'product_id'=>$result['err_code']."|".$str,];
            }else {
                $str = $result['return_msg'];
                $map=['status'=>3, 'product_id'=>$result['err_code']."|".$str,];
            }
            PayRecord::where(['id'=>$pay_id])->update($map);
            return ['status' => 0, 'msg' => $str,];
        }
    }



    //查询提现是否支付成功
    public function GetTxSuccess($order_num,$channel,$user_id){

        $flag = false;   //查询微信或支付宝结果、
        if($channel=="WeChat"){
            $prams['partner_trade_no'] = $order_num;
            $prams['appid'] = $this->appid;
            $prams['mch_id'] = $this->mchid;//商户号
            $prams['nonce_str'] = Tool::randstr();// 随机字符串
        }elseif($channel=="ali"){
            $prams['order_num'] = $order_num;
        }

        //提现所需数据
        $pay = new PayContext();
        $pay->initInstance($channel);//支付方式 微信 支付宝
        $result =  $pay->GetTxSuccess($prams); //查询提现

        if ( strtolower($result['status']) == 'success' ) {
            $flag = true;
            //转账成功
            $OrderObj=new Order();
            $OrderObj->db->startTransaction();

            try{
                $PayRecordObj=new PayRecord();
                $Info = $PayRecordObj->getOne($PayRecordObj::$table,['ordernum'=>$order_num],'status,price');
                //$Info=$PayRecordObj->db->where('id',$pay_id)->getOne($PayRecordObj->tableName,'status');
                if(!empty($Info['status']) && $Info['status']==2){
                    //return true;
                    return $flag;
                }else {

                    //处理提现节点
                    $CashDataObj = new CashData();
                    //$CashInfo = $CashDataObj->db->where ('user_id', $user_id)->getOne ($CashDataObj->tableName);
                    $CashInfo = $CashDataObj->getOne($CashDataObj::$table,['user_id'=>$user_id]);
                    $CashRst = true;
                    if ( empty($CashInfo['balance2017_cash_time']) ) {
                        $CashRst = $CashDataObj->update ($CashDataObj::$table, ['balance2017_cash_time' => time ()], ['user_id' => $user_id]);
                    }
                    $zh_account = $Info['app_wx_account'];
                    if ($channel == 'ali'){
                        $zh_account = $Info['zfb_account'];
                    }

                    $map = [
                        'created_at' => strtotime ($result['payment_time']),
                        'product_id' => $CashInfo['truename'] . ':' . $zh_account, //如果是重试的去掉重试标志
                        'transaction_id' => $result['detail_id'], //交易号
                        'status' => 2 //更改正常状态
                    ];
                    $payRst = $PayRecordObj->update ($PayRecordObj::$table, $map, ['ordernum'=>$order_num]);
                    $OrderInfo = $OrderObj->getOne($OrderObj::$table,['ordernum'=>$order_num,'type'=>8,'user_id'=>$user_id]);
                    //提现成功更改订单
                    $OrderRst = true;
                    if ( !empty($OrderInfo) ) {
                        $data = [];
                        $data['status'] = 1;
                        $data['pay_price'] = $Info['price'];
                        $data['pay_time'] = strtotime ($result['payment_time']);
                        $OrderRst = $OrderObj->update ($OrderObj::$table, $data, ['ordernum' => $order_num]);
                    }

                    if ( $payRst && $OrderRst && $CashRst ) {
                        $OrderObj->db->commit ();
                        //return true;
                    } else {
                        $OrderObj->db->rollBack ();
                        //return false;
                    }
                    return $flag; // 返回的是微信和支付宝结果  与本地程序 成与否无关
                }
            }catch(\Exception $e){
                $OrderObj->db->rollBack();
                //return false;
                return $flag;
            }

        }else{
            return $flag;
        }

    }

}
