<?php


namespace App\Http\Controllers\Api\V4;


use App\Http\Controllers\Controller;
use App\Models\CashData;
use App\Models\Column;
use App\Models\MallGoods;
use App\Models\MallOrderDetails;
use App\Models\OfflineProducts;
use App\Models\Order;
use App\Models\PayRecord;
use App\Models\PayRecordDetail;
use App\Models\SendInvoice;
use App\Models\User;
use App\Models\Withdrawals;
use App\Models\Works;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use PHPUnit\Util\Exception;

class IncomeController extends Controller
{

    /**
     * @api {get} /api/v4/income/index  用户钱包首页信息
     * @apiName index
     * @apiVersion 1.0.0
     * @apiGroup income
     *
     * @apiParam {int} user_id
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
    {
    "code": 200,
    "msg": "成功",
    "data": {
    "is_pass": 1, //-1信息未认证  1已认证 2 拒绝
    "nick_name":"房思楠",  //昵称
    "not_pass_reason": "",  //拒绝理由
    "bind_tx": 1,           //1 已绑定
    "bind_tx_type": 1,      //1微信  2支付宝 已绑定
    "amount": 0,
    "type": 0,
    "idcard_type": 1,          // 身份类型  1身份证
    "org_type": 1,              // '1：个人   2：机构'
    "org_name": "",             //机构名称
    "truename": "房思楠"           //真实姓名
    }
    }
     */
    public function index(Request $request)
    {
        //获取 收益
        //账户余额
        //信息认证
        //账户设置

        $user_id = $this->user['id'] ?? 0;
        $cash_info = CashData::where(['user_id'=>$user_id])->first();

        $is_pass        = 0;
        $amount         = 0;
        $bind_tx        = 0;
        $bind_tx_type   = 0;
        $nickname       = '';
        $not_pass_reason= '';
        $type=0;
        if(!empty($cash_info)){
            //是否认证
            if ($cash_info['is_pass'] == 1) {
                $is_pass = 1;
            }else if($cash_info['is_pass'] == 2){
                $is_pass = 2;
                $not_pass_reason = $cash_info['reason'];
            }

            //判断是否绑定账号
            if ($cash_info['is_pass'] == 1 && $cash_info['org_type'] == 1 ) { //个人
                if(!empty($cash_info['app_wx_account'])){ // 微信是1
                    $bind_tx = 1;
                    $bind_tx_type = 1;
                    $nickname = $cash_info['app_WxNickName'];
                }elseif (!empty($cash_info['zfb_account'])){// 支付宝2
                    $bind_tx = 1;
                    $bind_tx_type = 2;
                    $nickname = $cash_info['zfb_account'];
                }
            }
        }else{
            $is_pass=-1;
        }

        $is_withdrawal=1;
        if (in_array($user_id, [317437,60895,92931,158291,164463,171507,174858,215209,229413,233148,234965,238291,247036,250593,303717,306448,314220,
            303717,247036,164463,60895,171507,323009,233148,250593,229413,174858,215209,92931,158291,167756,
            184911,234586,242322,271061,177506])) { //禁止提现
            $is_withdrawal=0;
        }

        $data = [
            'is_pass'           => $is_pass,//-1信息未认证  1已认证 2 拒绝
            'is_withdrawal'     => $is_withdrawal,//1 隐藏提现按钮
            'nick_name'         => $nickname,//昵称
            'not_pass_reason'   => $not_pass_reason,//拒绝理由
            'bind_tx'           => $bind_tx,//1 已绑定
            'bind_tx_type'      => $bind_tx_type,//1微信  2支付宝 已绑定
            'amount'            => $amount,//17年收益
            'type'              => $type,// 1 时 17年有收益
            'idcard_type'       => $cash_info['type'] ?? 0,// 身份证类型
            'org_type'          => $cash_info['org_type'] ??0,// '1：个人   2：机构'
            'org_name'          => $cash_info['org_name'] ??'',
            'truename'          => $cash_info['truename'] ??'',
        ];
        return $this->success($data);
    }



    /**
     * @api {get} /api/v4/income/profit  用户钱包首页 (统计数)
     * @apiName profit
     * @apiVersion 1.0.0
     * @apiGroup income
     *
     * @apiParam {int} user_id
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
    {
    "code": 200,
    "msg": "成功",
    "data": {
    "monthly_earnings": 0,       //获取当月
    "last_month_earnings": 0,    //获取上月结算
    "all_proceeds": 0,           //获取全部收益
    "cashable_income": 0,            //获取提现余额
    "stay_money": 0,                 //待收益
    "ios_balance": null,             //能量币
    "toDay": 0,                    //今日
    "yesterDay": 0,                  //昨天
    "user_status": 0,               // 0 未绑定信息  1认证通过|个人  2认证通过|个人  绑定支付宝或者微信   3认证通过|机构
    "goods_data": 1,                //5电商推客收益
    "column_data": 0,                   //6专栏推客收益
    "work_data": 0,                 //7精品课收益
    "vip_data": 0                    //8会员收益
    }
     */
    public function profit(Request $request)
    {
        $user_id = $this->user['id'] ?? 0;

        $cash_list = CashData::where(['user_id'=>$user_id])->first();
        $user_status  =  0 ;
        if($cash_list){
            if ($cash_list['is_pass']==1 && $cash_list['org_type'] ==1 ) {
                $user_status  =  1 ;
                if ($cash_list['app_wx_account'] || $cash_list['zfb_account']) {
                    $user_status = 2;
                }
            } elseif ($cash_list['is_pass']==1 && $cash_list['org_type'] ==2){
                $user_status  =  3 ;
            } else {
                $user_status  =  0 ;
            }
        }

        //获取当月
        $curr_month_earn = PayRecordDetail::getSumProfit($user_id, 3);
        //获取上月结算
        $before_month_earn = PayRecordDetail::getSumProfit($user_id, 4);
        //获取全部收益
        $sum_earn = PayRecordDetail::getSumProfit($user_id, 2);
        //获取提现余额
        $cash_money = PayRecordDetail::getSumProfit($user_id,5);
        if($cash_money<=0){
            $cash_money=0;
        }
        //待收益
        $stay_money = PayRecordDetail::getSumProfit($user_id, 1);
        //当前用户能量币余额
        $info = User::find($user_id);

        $toDay = PayRecordDetail::getSumProfit($user_id, 11);
        $yesterDay = PayRecordDetail::getSumProfit($user_id, 12);


        //获取推广记录
        $tui_data = PayRecordDetail::select('type',DB::raw('count(*) c'))->where('user_id', $user_id)->whereIn('type',[5,6,7,8])
            ->groupBy('type')->get();
        //5电商推客收益  6专栏推客收益  7精品课收益 8会员收益
        $new_tui = [];
        foreach ($tui_data->toArray() as $item) {
            $new_tui[$item['type']] = $item['c'];
        }

        //权益说明
        $data=[
            'monthly_earnings'=>$curr_month_earn,  //获取当月
            'last_month_earnings'=>$before_month_earn, //获取上月结算
            'all_proceeds'=>$sum_earn,  //获取全部收益
            'cashable_income'=>$cash_money,  //获取提现余额
            'stay_money'=>$stay_money, //待收益
            'ios_balance'=>$info['ios_balance'], //能量币
            'toDay'=>$toDay, //今日
            'yesterDay'=>$yesterDay, //昨天
            'user_status' => $user_status,
            'goods_data'  => $new_tui['5'] ?? 0,   //5电商推客收益
            'column_data' => $new_tui['6'] ?? 0,   //6专栏推客收益
            'work_data'   => $new_tui['7'] ?? 0,   //7精品课收益
            'vip_data'    => $new_tui['8'] ?? 0,   //8会员收益
        ];
        return $this->success($data);
    }

    /**
     * @api {post} /api/v4/income/cash_data  钱包认证信息 || 提交修改认证
     * @apiName cash_data
     * @apiVersion 1.0.0
     * @apiGroup income
     *
     * @apiParam {int} type 1 钱包认证信息[只需要user_id]   2 提交修改认证[所有参数都需要]
     * @apiParam {int} user_id
     *
     * @apiParam {int} status         1个人认证，2企业认证
     * @apiParam {int} org_name         机构名称
     * @apiParam {int} org_area      机构地区
     * @apiParam {int} org_address      机构详细地址
     * @apiParam {int} org_license_picture  营业执照照片
     * @apiParam {int} bank_opening         开户行
     * @apiParam {int} bank_number          银行卡号
     * @apiParam {int} bank_permit_picture  开户许可证照片
     * @apiParam {int} idcard           身份证号
     * @apiParam {int} truename         真实姓名
     * @apiParam {int} idcard_cover         身份证图片
     * @apiParam {int} idcard_type    身份证类型  1:身份证 2:台胞证 3:香港身份证 4:澳门身份证 5:护照
     *
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
    {
    "code": 200,
    "msg": "成功",
    "data": {
    "truename": "房思楠",   姓名
    "idcard_cover": "nlsg/idcard/20200301113714131141.png",     身份证照片
    "idcard": "123456789123456789",         身份证号
    "idcard_type": 1,               1:身份证 2:台胞证 3:香港身份证 4:澳门身份证 5:护照
    "org_name": "",                 机构名称
    "org_address": "",              机构地区
    "org_license_picture": "",      营业执照照片
    "bank_opening": "",             开户行
    "bank_number": "",              银行卡号
    "bank_permit_picture": ""       开户许可证照片
    }
    }
     */
    public function cashData(Request $request)
    {
        $user_id = $this->user['id'] ?? 0;
        $type = $request->input('type', 0);//1 获取是否有提交信息  2修改

        if($type==1){ //获取数据
            $cash_info = CashData::where(['user_id'=>$user_id])->first();
            $data=[];
            if(!empty($cash_info)){
                $data['truename']       = $cash_info['truename'];
                $data['idcard_cover']   = $cash_info['idcard_cover'];
                $data['idcard']         = $cash_info['idcard'];
                $data['idcard_type']    = $cash_info['type'];
                $data['org_name']       = $cash_info['org_name'];
                $data['org_area']    = $cash_info['org_area'];
                $data['org_address']    = $cash_info['org_address'];
                $data['org_license_picture'] = $cash_info['org_license_picture'];
                $data['bank_opening']       = $cash_info['bank_opening'];
                $data['bank_number']        = $cash_info['bank_number'];
                $data['bank_permit_picture']= $cash_info['bank_permit_picture'];
            }
            return $this->success($data);

        }else{ //修改或者新增
            $status = $request->input('status', 1); //1个人认证，2企业认证

            //机构认证信息
            $org_name = $request->input('org_name', ''); //机构名称
            $org_address = $request->input('org_address', ''); //机构地址
            $org_area = $request->input('org_area', ''); //机构地址
            $org_license_picture = $request->input('org_license_picture', ''); //营业执照照片
            $bank_opening = $request->input('bank_opening', ''); //开户行
            $bank_number = $request->input('bank_number', ''); //银行卡号
            $bank_permit_picture = $request->input('bank_permit_picture', ''); //开户许可证照片

            //个人(机构通用)认证信息
            $idcard = $request->input('idcard', ''); //身份证号
            $truename = $request->input('truename', ''); //真实姓名
            $idcard_cover = $request->input('idcard_cover', ''); //身份证图片
            $idcard_type = $request->input('idcard_type', ''); //身份证类型

            if(empty($idcard) || empty($truename) || empty($idcard)){
                return $this->error(0,'信息不能为空');
            }
            if($status == 2 && ( empty($org_name) || empty($org_address) || empty($org_license_picture) || empty($bank_opening) || empty($bank_number) || empty($bank_permit_picture) ))
            {
                return $this->error(0,'企业信息不能为空');
            }
            $cash_info = CashData::where(['user_id'=>$user_id])->first();

            //新增时验证是否被认证
//            $obj = CashData::where(['idcard'=>$idcard,'org_type'=>$status])->first();
//
//            if (  !empty($obj) && $obj['user_id'] != $user_id ) {
//                return $this->error(0, '该身份证已被认证，请尝试其他身份证绑定');
//            }

//            if ( empty($cash_info) && !empty($obj)) {
//                if ($obj['user_id'] != $user_id) {
//                    return $this->error(0, '该身份证已被认证，请尝试其他身份证绑定');
//                }
//            }
            if(!empty($cash_info)){
                $data=[
                    'truename'=>$truename,
                    'idcard'=>$idcard,
                    'idcard_cover'=>$idcard_cover,
                    'reason'=>'',
                    'is_pass'=>0,
                    //机构信息
                    'org_name'=>$org_name,
                    'org_address'=>$org_address,
                    'org_area'=>$org_area,
                    'org_license_picture'=>$org_license_picture,
                    'bank_opening'=>$bank_opening,
                    'bank_number'=>$bank_number,
                    'bank_permit_picture'=>$bank_permit_picture,
                    'type'=>$idcard_type,
                ];
                $status = CashData::where(['user_id'=>$user_id])->update($data);
            }else{
                $data=[
                    'user_id'=>$user_id,
                    'truename'=>$truename,
                    'idcard'=>$idcard,
                    'idcard_cover'=>$idcard_cover,
                    //机构信息
                    'org_name'=>$org_name,
                    'org_address'=>$org_address,
                    'org_license_picture'=>$org_license_picture,
                    'bank_opening'=>$bank_opening,
                    'bank_number'=>$bank_number,
                    'bank_permit_picture'=>$bank_permit_picture,
                    'type'=>$idcard_type,
                    'org_type'=>$status,
                ];
                $status = CashData::create($data);
            }

            if ($status) {
                return $this->success();
            } else {
                return $this->error(0, '提交失败');
            }
        }

    }




    /**
     * @api {get} /api/v4/income/present  绑定提现微信|支付宝账户
     * @apiName present
     * @apiVersion 1.0.0
     * @apiGroup income
     *
     * @apiParam {int} type 1 1微信  2支付宝
     * @apiParam {int} user_id
     * @apiParam {int} nickname 昵称
     * @apiParam {int} openid openid
     * @apiParam {int} zfb_account 支付宝账号
     * @apiParam {int} phone 手机号
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
    {
    "code": 200,
    "msg": "成功",
    "data": []
    }
     */
    public function present(Request $request)
    {
        $type = $request->input('type', 0); //1微信  2支付宝
        $user_id = $this->user['id'] ?? 0;

        $CashInfo = CashData::where(['user_id'=> $user_id,'is_pass'=>1])->first('id');
        //已通过审核
        if (empty($CashInfo)) {
            return $this->error(0,'认证未通过审核不能绑定');
        }

        $nick_name = $request->input('nickname', ''); //昵称
        $open_id = $request->input('openid', 0);
        $zfb_account = $request->input('zfb_account', 0);//支付宝账号
        $phone = $request->input('phone', 0); //手机号


        if( $type == 1 && (empty($nick_name) || empty($open_id))  ){
            return $this->error(0,'微信信息空');
        }
        if( $type == 2 && ( empty($zfb_account) || empty($phone) ) ){
            return $this->error(0,'支付宝信息空');
        }

        $map=[];
        if( $type == 1 ){
            $map['app_WxNickName'] = $nick_name;
            $map['app_wx_account'] = $open_id;
        }
        if( $type == 2 ){
            $map['phone'] = $phone;
            $map['zfb_account'] = $zfb_account;
        }

        $rst = CashData::where(['user_id' => $user_id])->update($map);
        if ($rst) {
            return $this->success();
        }else{
            return $this->error(0,'fail');
        }
    }


    /**
     * @api {get} /api/v4/income/get_withdraw  提现 个税计算
     * @apiName get_withdraw
     * @apiVersion 1.0.0
     * @apiGroup income
     *
     * @apiParam {int} money 提现金额
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
    {
    "code": 200,
    "msg": "成功",
    "now": 1597030864,
    "data": {
    "balance": 0,    可提现余额
    "income_tax": 0,    个税
    }
    }
     */
    public  function  getWithdraw(Request $request)
    {
        $money = $request->input('money', 0);

        $user_id    = $this->user['id'];

        $cash_list  = CashData::where(['user_id'=>$user_id])->first();
        $bind_type = 0;
        if ($cash_list['is_pass'] == 1 && $cash_list['org_type'] == 1 ) {
            if (!$cash_list['app_wx_account']) {
                $bind_type = 1;
            } elseif(!$cash_list['zfb_account']){
                $bind_type = 2;
            }
        }
        if ($cash_list['status']==2) {
            return $this->error(0,'企业不能直接提现');
        }

        $balance = PayRecordDetail::getSumProfit($user_id,5);
        //$pay_model->getSumProfit($user_id,5);

        if ($balance < $money) {
            return $this->error(0,'提现金额不能大于收益');

        }
        $income_tax = PayRecordDetail::getIncomeTax($user_id,$money);

        $data=[
//            'bind_type'    => $bind_type,
            'balance'      => $balance > 0 ? $balance : 0,
            'income_tax'   => $income_tax,
//            'remain_money' => $income_tax >0 ? round($balance - $income_tax, 2) : $money,
        ];

        return $this->success($data);

    }


    /**
     * @api {get} /api/v4/income/withdrawals  提现操作
     * @apiName withdrawals
     * @apiVersion 1.0.0
     * @apiGroup income
     *
     * @apiParam {int} user_id
     * @apiParam {int} money 金额
     * @apiParam {int} channel   ali|WeChat  支付宝或微信
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
    {
    "code": 200,
    "msg": "成功",
    "data": []
    }
     */
    public function withdrawals(Request $request)
    {
        $user_id = $this->user['id'] ?? 0;
        $type = $request->input('type', 0);
        $amount = $request->input('money', 0);
        $channel = $request->input('channel', 0);//ali  |  WeChat
        $os_type = $request->input('os_type', 1);//  1 安卓 2ios 3微信


        if (in_array($user_id, [317437,60895,92931,158291,164463,171507,174858,215209,229413,233148,234965,238291,247036,250593,303717,306448,314220,
            303717,247036,164463,60895,171507,323009,233148,250593,229413,174858,215209,92931,158291,167756,
            184911,234586,242322,271061,177506])) { //禁止提现
            return $this->error(0,'此账号被锁定提现,请联系客服');
        }

        //加锁操作  防止多端口同时提现
        //提现操作加锁
        self::lock($user_id);


        $Time_Interval = Config('web.Withdrawals.Time_Interval');
//        $Objflag = $this->RedisFlag($user_id,1);
//        if ($Objflag) {  //防止多台设备请求
//            return $this->error(0,'您操作太频繁');
//        }else{
//            $this->RedisFlag($user_id,2);
//        }

        //用户提现金额
        $min_price = Config('web.Withdrawals.Min_Price');
        $Test_User = Config('web.Withdrawals.Test_User');

        $money = PayRecordDetail::getSumProfit($user_id,5);
        if (empty($Test_User) || !in_array($user_id, $Test_User)) { //测试放开
            if ($amount < $min_price) { //最少提10块
                $this->RedisFlag($user_id,3);
                return $this->error(0,'最少提现' . $min_price . '元哟');
            }
        }

        if ($amount > $money) {
            $this->RedisFlag($user_id,3);
            return $this->error(0,'非法操作，没有足够余额');
        }
        //时间间隔15秒
        $Tx_Time = PayRecord::where('user_id', $user_id)->whereIn('order_type',[7, 8])->orderBy('id','desc')->first();
        $time = time();
        if (!empty($Tx_Time)) {
            $bj_time = strtotime($Tx_Time['created_at']) + $Time_Interval;
            if ($time <= $bj_time) {
                $this->RedisFlag($user_id,3);
                //return $this->error(0,'操作太频繁，稍后重试');
            }
        }

        //获取每天提现次数
        $day = (date('Y-m-d', time()));
        //先确定app企业付款是否为同一途径
        $Pay_Count = PayRecord::where(['user_id'=>$user_id,'order_type'=>0])
            ->where('created_at','> ',$day)->count();

        $PzCount = Config('web.Withdrawals.Pay_Count'); //10次上限
        if ($Pay_Count >= $PzCount) {
            $this->RedisFlag($user_id,3);
            return $this->error(0,'每天提现次数已达上限');
        }
        //获取个人当日总计提现金额
        $Single_Quota = PayRecord::where(['user_id'=>$user_id,'order_type'=>8,'client'=>1])
            ->where('created_at','>',$day)->sum('price');
        $PzPrice = Config('web.Withdrawals.Single_Quota');
        if ($amount > $PzPrice || $Single_Quota > $PzPrice) {
            $this->RedisFlag($user_id,3);
            return $this->error(0,'同一用户单笔或单日已达2万上限');
        }

        //获取当天总提现金额
        $Single_QuotaAll = PayRecord::where(['order_type'=>8,'client'=>1,])->where('created_at','>',$day)->sum('price');

        $PzPriceAll = Config('web.Withdrawals.PayMoney_Sum');
        if ($Single_QuotaAll > $PzPriceAll) {
            $this->RedisFlag($user_id,3);
            return $this->error(0,'单日限额已超200万');
        }
        //获取对应提现用户
        $Info = CashData::where(['user_id'=>$user_id,'is_pass'=>1])->first();
        if (empty($Info)) {
            $this->RedisFlag($user_id,3);
            return $this->error(0,'认证未通过');
        }
        $orderid = date('YmdHis');
        $ip = $request['ip'];

        $order_type = 12;//12机构提现  8微信  7支付宝
        if ($channel == 'ali' ) {
            if(empty($Info['zfb_account'])){
                $this->RedisFlag($user_id,3);
                return $this->error(0,'请绑定提现支付宝账户');
            }else{
                $zh_account = $Info['zfb_account'];
            }
            $order_type = 7;//12机构提现  8微信  7支付宝

        }

        if ($channel == 'WeChat' ) {
            if(empty($Info['app_wx_account'])){
                $this->RedisFlag($user_id,3);
                return $this->error(0,'请绑定提现微信账户');
            }else{
                $zh_account = $Info['app_wx_account'];
            }
            $order_type = 8;//12机构提现  8微信  7支付宝

        }
        $tax = PayRecordDetail::cal_tax($user_id, $amount);

        $WithdrawalsObj= new Withdrawals();
        //加入处理中数据 防止多平台重复提现  提现金额  下订单
        $Record_Id = $WithdrawalsObj->TxRecord($amount, $zh_account, $user_id, $Info['truename'], $orderid, $tax,$order_type,$ip,$os_type);
        if (!$Record_Id) {
            $this->RedisFlag($user_id,3);
            return $this->error(0,'提现失败请重试');
        }
        //处理提现操作
        $pay_res = $WithdrawalsObj->Pay($user_id, $zh_account, ($amount - $tax) * 100, $Info['truename'], $Info['truename'], $orderid, $Record_Id->id,$ip,$channel,$order_type,$os_type);




        $this->RedisFlag($user_id,3);
        //释放锁
        self::unlock($user_id);

        if ($pay_res['status'] == 200) {
            return $this->Success($pay_res['result']);
        } else {
            return $this->error(0,$pay_res['msg']);
        }

    }


    public static function lock(string $key): string
    {

        //$redis = self::getInstance(0);
        $redis = new Redis();
        $key .= '_lock';
        $value = '';

        $curTime = time();
        while (true) { // 独占式抢锁
            $value = microtime(true).mt_rand(1000, 9999);
            if ($redis::setnx($key, $value)) { // 获取到锁
                // 锁过期时间5秒
                $redis::expire($key, 5);
                break;
            } else  {
                if (time() - $curTime >= 10) { // 超过10秒抛异常
                    throw new Exception('lock error', 1);
                    break;
                }

                $time = $redis::ttl($key);
                if(($time / 2) > 0){
                    sleep($time / 2);
                }
            }
        }
        return $value;
    }

    /**
     * 释放分布式锁
     * @param string $key
     * @return bool
     */
    public static function unlock(string $key): bool
    {
        //$redis = self::getInstance(0);
        $redis = new Redis();

        $key .= '_lock';
        $redis::del($key);

        return true;
    }


    //处理标记
    public function RedisFlag($user_id,$type){

        $filename='swoole_income_pay'.$user_id;
        if($type==1){
            return Cache::store('redis')->get($filename);

        }else if($type==2){
            return Cache::store('redis')->set($filename,$user_id,'300');
        }else{
            Cache::store('redis')->delete($filename);
        }
    }




    /**
     * @api {get} /api/v4/income/get_list  收支明细[默认显示支出的  不可同时显示支出和收入]
     * @apiName get_list
     * @apiVersion 1.0.0
     * @apiGroup income
     *
     * @apiParam {int} user_id
     * @apiParam {int} earn_type 1支出 2收入
     * @apiParam {int} type 收支类型 earn_type=1时type[type类型  1电商支付    2内容支付   3 会员       4 所有提现]
     *                             earn_type=2时 type [type类型  1 电商收益   2内容收益   3会员收益  4直播收益]
     * @apiParam {int} date 格式化的时间精确到月
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
    {
    "code": 200,
    "msg": "成功",
    "data": {
    "expenditure_price": "10.00",
    "income_price": "110.00",
    "list": {
    "current_page": 1,
    "data": [
    {
    "id": 1,
    "ordernum": "202005231631148119",  所属订单号
    "created_at": "2020-06-03 14:12:34",
    "type": 2,          同请求参数
    "user_id": 211172,
    "price": "110.00",    金额
    "order_detail_id": 0,
    "subsidy_type": 0,
    "earn_type": 2,             //1支出 2收入
    "pay_content": "到账成功",   状态描述
    "content": "分享收益",      类型描述
    "name": "王琨专栏"          支出|收益 主体
    }
    ],
    "first_page_url": "http://nlsgv4.com/api/v4/income/get_list?page=1",
    "from": 1,
    "last_page": 1,
    "last_page_url": "http://nlsgv4.com/api/v4/income/get_list?page=1",
    "next_page_url": null,
    "path": "http://nlsgv4.com/api/v4/income/get_list",
    "per_page": 50,
    "prev_page_url": null,
    "to": 1,
    "total": 1
    }
    }
    }
     */
    public function getList(Request $request){

        $user_id = $this->user['id'] ?? 0;
        $type    = $request->input('type',0);
        $date    = $request->input('date',0);
        $earn_type    = $request->input('earn_type',1); //1支出 2收入


        //获取 1 支出 和 2收入
        //现在只显示 提现和个税  用户分享收益     2：用户专栏分享提成  5电商推客收益  6专栏推客收益 7精品课 8会员 9沙画  12
        if($earn_type == 1){
            //earn_type==1时    type[7支付宝提现  8微信提现   9代扣个税  10电商支付  11精品课支付  12机构提现  18能量币充值 19讲座 20赠送  21线下门票]
            $order_type_val = [1,2,5,7,8,9,10,11,12,16,17,18,19,20,21,22];//默认全部查询
            if( !empty($type) &&  in_array($type,[1,2,3,4,18]) ){
                // 改需求后的 type类型  1电商支付   2内容支付   3 会员  4 所有提现 5能量币充值记录
                switch ($type){
                    case 1:
                        $order_type_val = [10];
                        break;
                    case 2:
                        $order_type_val = [1,11,16,19,20,22];
                        break;
                    case 3:
                        $order_type_val = [2,17,];
                        break;
                    case 4:
                        $order_type_val = [7,8,9];
                        break;
                    case 18:
                        $order_type_val = [18];
                        break;

                }
            }

            $query = PayRecord::select('id','ordernum','created_at','order_type as type','user_id','price','status'
                ,DB::raw('1 as `earn_type`','0 as order_detail_id','0 subsidy_type'))
                ->where('user_id',$user_id)->whereIn('order_type',$order_type_val)->whereIn('status',[1, 2]);

//            $payRecordObj = new PayRecord();
//            $query = DB::table($payRecordObj->getTable(), 'c')
//                    ->select('id','ordernum','created_at','order_type as type','user_id','price','status'
//                        ,DB::raw('1 as `earn_type`','0 as order_detail_id','0 subsidy_type'))
//                    ->where('user_id',$user_id)->whereIn('order_type',$order_type_val)->where('status',2);
//

            //计算总支出和总收入

        }else{
            //earn_type==2时 type[ 2：用户专栏分享提成 5电商推客收益  6专栏推客收益  7精品课收益 8会员收益 9菩提沙画 10直播分享收益]
            $order_type_val = [2,5,6,7,8,9,10,11,12,13];//默认全部查询
            if( !empty($type) &&  in_array($type,[1,2,3,4]) ){
                // 改需求后的 type类型  1 电商收益   2内容收益 3会员收益  4直播收益
                switch ($type){
                    case 1:
                        $order_type_val = [5];
                        break;
                    case 2:
                        $order_type_val = [6,7,];
                        break;
                    case 3:
                        $order_type_val = [8,11];
                        break;
                    case 4:
                        $order_type_val = [10];
                        break;

                }
            }
            $query = PayRecordDetail::select(
                'id','ordernum','created_at','type','user_id','price', 'order_detail_id','subsidy_type'
            ,DB::raw('2 as `earn_type`','2 as status'))
                ->where('user_id',$user_id)->whereIn('type',$order_type_val);



//            $payRecordObj = new PayRecordDetail();
//            $orderObj = new Order();
//            $userObj = new User();
//                DB::table($payRecordObj->getTable(), 'c')
//                ->leftJoin($orderObj->getTable().' as o', 'o.ordernum', '=', 'c.ordernum')
//                ->leftJoin($userObj->getTable().' as s', 's.id', '=', 'o.user_id')
//                ->select('id','ordernum','created_at','type','user_id','price', 'order_detail_id','subsidy_type'
//                    ,'s.headimg','s.nick_name o_nick_name','s.username'
//            ,DB::raw('2 as `earn_type`','2 as status'))
//                ->where('user_id',$user_id)->whereIn('type',$order_type_val);


        }

        //时间筛选
        if($date != '' ){
            $time_arr = explode(',',$date);
            $query->where('created_at', '>', $time_arr[0]);
            $query->where('created_at', '<', $time_arr[1]);

        }
        $query->orderBy('created_at','desc');
        $list = $query->paginate($this->page_per_page);

        //处理数据
        $list = $list->toArray();
        if(empty($list['data'])) return $this->success();

        foreach($list['data'] as $key=>&$val){
            //记录前面图片 7支付宝提现 8 微信提现
            //记录状态 earn_type 1支出 2收入
            //  type 1 专栏 2 会员  3充值  4财务打款 5 打赏  6分享赚钱订单 7支付宝提现  8微信提现   9代扣个税  10电商支付  11精品课支付  12机构提现 18能量币充值 19讲座 20赠送  21线下门票]
            if($val['earn_type']==1 && in_array($val['type'],[7,8])){  //提现
                if($val['status']==2){
                    $val['pay_content'] = '提现成功';
                }elseif($val['status']==1){
                    $val['pay_content'] = '处理中';
                }else{
                    $val['pay_content'] = '提现失败';
                }
            }else{
                $val['pay_content'] = '到账成功';
            }
            //记录名称   支出
            if($val['earn_type']==1){
                $con = $this->detail_content($val['earn_type'],$val['type']);
                $val['content'] = $con['content'] ??'';
                $val['pay_content'] = $con['pay_content'] ??'';
            }else{
                //2：用户专栏分享提成    5电商推客收益  6专栏推客收益  7精品课收益 8会员收益 9菩提沙画
                $con = $this->detail_content($val['earn_type'],$val['type'],$val['ordernum'],$val['order_detail_id']);
                $val['content'] = $con['content'] ??'';
                $val['name'] = $con['name'] ??'';
                $val['o_nick_name'] = $con['o_nick_name'] ??'';
            }

        }

        //计算总支出和总收入
        $res['expenditure_price']   = PayRecord::where(['user_id'=>$user_id,])->whereIn('order_type',[7,8,9,10,12,18])->sum('price');
        $res['income_price']        = PayRecordDetail::where('user_id',$user_id)->whereIn('type',[2,5,6,7,8,9,10,11])->sum('price');
        $res['list'] = $list['data'];
        return $this->success( $res );
    }

    //根据收益类型返回对应文案
    public function detail_content($earn_type,$type,$ordernum=0,$order_detail_id=0){
        $res = [];
        if($earn_type ==1){
            switch($type){
                case 1:$res['content'] = '专栏支付';
                    break;
                case 2:$res['content'] = '会员支付';
                    break;
                case 5:$res['content'] = '打赏';
                    break;
                case 7:$res['content'] = '支付宝收益提现';
                    break;
                case 8:$res['content'] = '微信收益提现';
                    break;
                case 9:$res['content'] = '代扣个税';
                    $res['pay_content'] = '扣税成功';
                    break;
                case 10:$res['content'] = '商城支付';
                    break;
                case 11:$res['content'] = '精品课支付';
                    break;
                case 12:$res['content'] = '机构提现';
                    break;
                case 16:$res['content'] = '直播';
                    break;
                case 17:$res['content'] = '幸福360会员';
                    break;
                case 18:$res['content'] = '能量币充值';
                    break;
                case 19:$res['content'] = '讲座支付';
                    break;
                case 20:$res['content'] = '赠送购买';
                    break;
                case 21:$res['content'] = '线下门票购买';
                    break;
                case 22:$res['content'] = '训练营购买';
                    break;
            }
        }else{
            switch($type){
                case 2:$res['content'] = '分享收益';
                    $teacherInfo = Order::select('relation_id')->where(['ordernum'=>$ordernum])->first();
                    if($teacherInfo){
                        $ColumnInfo = Column::find($teacherInfo['relation_id']);
                        $res['name']=$ColumnInfo['name'];
                    }

                    break;
                case 5:$res['content'] = '推客收益'; //电商
                    $goodsInfo = MallOrderDetails::find($order_detail_id);
                    if($goodsInfo){
                        $name = MallGoods::find($goodsInfo['goods_id']);
                        if($name) $name = $name->toArray();
                        //$val['name']=Tool::SubStr($name['name'],10);
                        $res['name']=$name['name'];
                    }


                    break;
                case 6:$res['content'] = '推客收益'; //专栏

                    $teacherInfo = Order::where(['ordernum'=>$ordernum])->first('relation_id');
                    if($teacherInfo){
                        $ColumnInfo = Column::find($teacherInfo['relation_id']);
                        $res['name']=$ColumnInfo['name'];
                    }

                    break;
                case 7:$res['content'] = '精品课收益';
                    $OrderInfo = Order::where(['ordernum'=>$ordernum])->first('relation_id');
                    if($OrderInfo){
                        $works_id=$OrderInfo['relation_id'];

                        $workName = Works::find($works_id);
                        //$val['name']=Tool::SubStr($workName,8); //截取名称13
                        $res['name']=$workName['title']; //截取名称13
                    }

                    break;
                case 8:
                    $supremacyInfo = Order::where(['ordernum'=>$ordernum])->first('relation_id');
                    if($supremacyInfo['relation_id']==1){
                        $res['content'] = '推广皇钻收益';
                    }else{
                        $res['content'] = '推广黑钻收益';
                    }
                    $res['name']='会员';
                    break;
                case 9:
                    $res['content'] = '推广菩提沙画收益';
                    $res['name']='纱画亲子体验';
                    break;
                case 10:
                    $res['content'] = '直播分享收益';
                    $res['name']='直播分享';
                    $teacherInfo = Order::select('user_id')->where(['ordernum'=>$ordernum])->first();
                    if($teacherInfo){
                        $userInfo = User::find($teacherInfo['user_id']);
                        $res['o_nick_name']=$userInfo['phone'];
                    }
                    break;
                case 11:
                    $res['content'] = '360幸福大使收益';
                    $res['name']='360幸福大使';
                    $res['o_nick_name']='';

                    $teacherInfo = Order::select('user_id')->where(['ordernum'=>$ordernum])->first();
                    if($teacherInfo){
                        $userInfo = User::find($teacherInfo['user_id']);
                        $res['o_nick_name']=$userInfo['phone'];
                    }
                    break;
                case 12:
                    $res['content'] = '线下门票收益';
                    $res['name']='线下门票';
                    $res['o_nick_name']='';

                    $teacherInfo = Order::select('user_id','relation_id')->where(['ordernum'=>$ordernum])->first();
                    if($teacherInfo){
                        $userInfo = User::find($teacherInfo['user_id']);
                        $res['o_nick_name']=$userInfo['phone'];

                        $offline = OfflineProducts::find($teacherInfo['relation_id']);
                        $res['content'] =$offline['title'];
                        $res['name'] =$offline['title'];
                    }
                    break;
                case 13:
                    $res['content'] = '训练营收益';
                    $res['name']='训练营';
                    $res['o_nick_name']='';

                    $teacherInfo = Order::select('user_id','relation_id')->where(['ordernum'=>$ordernum])->first();
                    if($teacherInfo){
                        $userInfo = User::find($teacherInfo['user_id']);
                        $res['o_nick_name']=$userInfo['phone'];

                        $offline = OfflineProducts::find($teacherInfo['relation_id']);
                        $res['content'] =$offline['title'];
                        $res['name'] =$offline['title'];
                    }
                    break;

            }
        }

        return $res;
    }


    /**
     * @api {get} /api/v4/income/detail  收益详情
     * @apiName detail
     * @apiVersion 1.0.0
     * @apiGroup income
     *
     * @apiParam {int} user_id
     * @apiParam {int} id
     * @apiParam {int} earn_type  1支出 2收入
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
    {
    "code": 200,
    "msg": "成功",
    "data": {
    "type": 2,
    "created_at": "2020-06-03T06:12:34.000000Z",
    "price": "110.00",
    "content": "分享收益",
    "name": "王琨专栏",
    "nick_name": null
    }
    }
     */
    public function Detail(Request $request)
    {
        $user_id = $this->user['id'] ?? 0;
        $id = $request->input('id', 0);
        $type = $request->input('earn_type', 0);  //1支出  2收入


        if ($type == 1) { //支出
            $pay_info = PayRecord::where(['id'=>$id,'user_id'=>$user_id])->first()->toArray();
            if(empty($pay_info)) return $this->success();

            if($pay_info['order_type']==9){ //个税
                $info['content'] = '代扣个税';
            }else if(in_array($pay_info['order_type'],[7,8,12])){
                $info['tax']=$pay_info['tax'];
            }
            $info['tx_status'] = $pay_info['status'];
            $info['order_type'] = $pay_info['order_type'];
            $info['price'] = $pay_info['price'];
            $info['created_at'] = $pay_info['created_at'];
            $con = $this->detail_content($type,$pay_info['order_type']);
            $info['content'] = $con['content'] ??'';
            $info['pay_content'] = $con['pay_content']??'';

        } else { //收入


            $pay_info = PayRecordDetail::where(['id'=>$id,'user_id'=>$user_id])->first()->toArray();

            $orderObj = new Order();
            $userObj = new User();
            $pay_info_order = DB::table($orderObj->getTable(), 'o')
                ->leftJoin($userObj->getTable().' as s', 's.id', '=', 'o.user_id')
                ->select('s.headimg','s.nickname as o_nick_name','s.phone')
                ->where(['o.ordernum'=>$pay_info['ordernum']])->first();

            if(empty($pay_info)) return $this->success();
            $o_nick_name = $pay_info_order->o_nick_name ?? '';

            $con = $this->detail_content($type,$pay_info['type'],$pay_info['ordernum'],$pay_info['order_detail_id']);
            $info['type'] = $pay_info['type'] ?? 0;
            $info['created_at'] = $pay_info['created_at'] ?? 0;
            $info['price'] = $pay_info['price'] ?? 0;
            $info['content'] = $con['content'] ?? '';
            $info['name'] = $con['name'] ?? '';
            $info['ordernum'] = $pay_info['ordernum'] ?? '';
            $info['o_nick_name'] = $con['o_nick_name'] ?? $o_nick_name;
        }
        $UserInfo = User::find($user_id);
        $info['nickname']=$UserInfo['nickname'];

        return $this->success($info);
    }




    //
    /**
     * @api {get} /api/v4/income/get_deposit  充值记录
     * @apiName detail
     * @apiVersion 1.0.0
     * @apiGroup income
     *
     * @apiParam {int} user_id
     * @apiParam {int} id
     * @apiParam {int} earn_type  1支出 2收入
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
    {
    "code": 200,
    "msg": "成功",
    "data": [
        {
        "price": "10.00",
        "created_at": "2020-07-09 10:49:16"
        }
    ]
    }
     */
    public function getOrderDepositHistory(Request $request){
        $user_id = $this->user['id'] ?? 0;

        $lists = Order::select('price', 'created_at')->where([
            'user_id'   => $user_id,
            'type'      => 13,
            'pay_type'  => 4,
            'status'    => 1,
        ])->get();
        return $this->success($lists);
    }





    /**
     * @api {get} /api/v4/income/send_invoice  邮寄发票
     * @apiName send_invoice
     * @apiVersion 1.0.0
     * @apiGroup income
     *
     * @apiParam {int} user_id
     * @apiParam {int} express   快递公司快递公司 编码 如：YUNDA
     * @apiParam {int} express_num  快递单号
     * @apiParam {int} img   图片
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
    {
    "code": 200,
    "msg": "成功",
    "data": {
    "id": 1,
    "user_id": 211172,
    "express": "YUNDA",
    "express_num": "12312313",
    "img": "image",
    "created_at": "2020-07-09 14:30:55",
    "updated_at": "2020-07-09 14:30:55",
    "status": 0         //状态 1 审核通过 2 未通过
    }
    }
     */
    public  function  sendInvoice(Request $request)
    {
        $user_id = $this->user['id'] ?? 0;
        $express = $request->input('express', 0);
        $express_num = $request->input('express_num', 0);
        $img = $request->input('img', 0);

        $res = SendInvoice::firstOrCreate([
            'user_id'     =>$user_id,
            'express'     =>$express,
            'express_num' =>$express_num,
            'img'         => $img,
        ]);
        return $this->success($res);
    }



    public function  ToTwitter(){

        $to_twitters = DB::table('nlsg_pay_to_twitter')->where([
            //'type'      => 1,
            'status'    => 0,
        ])->get()->toArray();

        foreach ($to_twitters as $key=>$twitter) {
            $user_data = User::where(['phone'=>$twitter->user_phone,])->first();
            if(!empty($user_data)){
                $add_data = [
                    'type'      =>$twitter->type,
                    'user_id'   =>$user_data['id'],
                    'price'     => $twitter->price,
                    'ctime'     => time(),
                ];
                $order = Order::where(['ordernum'=>$twitter->ordernum,'status'=>1])->first();
                if(empty($order)){ //微信线下返款
                    $add_data['ordernum']='';
                    $add_data['remark']  =$twitter->ordernum.'_线下交易';
                }else{
                    $add_data['ordernum']=$twitter->ordernum;
                }
                $recordRst = PayRecordDetail::firstOrCreate($add_data);
                if($recordRst->wasRecentlyCreated){ //新增
                    DB::table('nlsg_pay_to_twitter')->where([
                        'id'=>$twitter->id,
                    ])->update(['status'=>1]);
                }
            }

        }
        return $this->success();

    }

}
