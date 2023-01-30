<?php

namespace App\Servers\Bliss;

use App\Models\Bliss\AccountModel;
use App\Models\Bliss\ProfitLogModel;
use App\Models\Bliss\ProfitModel;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;
use Predis\Client;

class ProfitServer
{
    /**
     * 收益结算提现
     * todo 定时任务
     */
    public static function profit_settle(){

    }

    /**
     * 保存收益
     */
    public static function profit_add($order_id,$user_id){

        $Order=Order::query()->where('id',$order_id)->first();
        if(!$Order){
            return '订单不存在';
        }
        $ProfitModel=ProfitModel::query()->where('order_id',$order_id)->first();
        if($ProfitModel){
            return '收益已发放';
        }
        $ProfitModel =new ProfitModel();
        $ProfitModel->user_id=$user_id;
        $ProfitModel->order_id=$order_id;
        $ProfitModel->source_user_id=$Order->user_id;
        $ProfitModel->profit=77400;
        $ProfitModel->plan_settle_time=date("Y-m-d H:i:s",strtotime("+30 day"));
        $ProfitModel->save();

        //保存日志
        $ProfitLogModel = new ProfitLogModel();
        $ProfitLogModel->order_id=$order_id;
        $ProfitLogModel->user_id=$user_id;
        $ProfitLogModel->money=$ProfitModel->profit;
        $ProfitLogModel->type=1;//类型 1收益 2提现
        $ProfitLogModel->remark='成功推广商品';
        $ProfitLogModel->save();

        return true;
    }

    /**
     * @param $order_id
     * @return bool|string
     * 订单退款
     */
    public static function profit_refund($order_id){

        $Order=Order::query()->where('id',$order_id)->where('is_shill',1)->first();
        if(!$Order){
            return '订单不存在';
        }

        $ProfitModel=ProfitModel::query()->where('order_id',$order_id)->where('status',0)->first();
        if(!$ProfitModel){
            return '收益不存在';
        }
        $ProfitModel->status=3;//不结算
        $ProfitModel->remark='订单退款';
        $ProfitModel->save();

        return true;
    }

    /**
     * 获取收益账号
     */
    public function profit_account($user_id){
        $AccountModel=AccountModel::query()->where('user_id',$user_id)->first();
        return $AccountModel;
    }

    /**
     * 收益账号保存
     */
    public function profit_account_save($user_id,$data){

        $validator = Validator::make($data, [
            'username' => 'required',
            'bank_name' => 'required',
            'card_no' => 'required',
            'IDnumber' => 'required',
            'IDcard' => 'required',
        ],[
            'username.required' => '银行卡户名不能为空',
            'bank_name.required' => '开户行不能为空',
            'card_no.required' => '银行卡号不能为空',
            'IDnumber.required' => '身份证号不能为空',
            'IDcard.required' => '身份证正面不能为空',
        ]);

        if($validator->fails()){
            return $validator->messages()->first();
        }

        $AccountModel=AccountModel::query()->where('user_id',$user_id)->first();
        if(!$AccountModel){
            $AccountModel= new AccountModel();
        }
        $AccountModel->user_id=$user_id;
        $res=$AccountModel->fill($data)->save();
        if(!$res){
            return '保存失败';
        }

        return true;
    }


    /**
     * 收益统计
     */
    public  function profit_statistics($user_id,$data=[]){
        //已结算
        //待结算
        //未结算
        //累计客户
        //累计合伙人
        //订单数
        return [
            'settled_money'=>100,
            'tobe_settled_money'=>100,
            'unsettled_money'=>100,
            'unsettled_count'=>100,
            'tobe_settled_count'=>100,
            'settled_count'=>100,
            'order_count'=>100,
            'customer_count'=>100,
            'partner_count'=>100,
        ];
    }

    /**
     * 收益列表
     */
    public  function profit_search_list($user_id,$data=[]){

        $query=ProfitModel::getQueryWhere([
            ['status','=','status'],
            ['created_at','>=','start_time'],
            ['created_at','<=','end_time'],
        ],$data)->where('user_id',$user_id)
            ->with([
                'order:id,status,is_shill,price,pay_time,user_id',
                'order.user:id,nickname,phone',
            ])
            ->orderBy('id','desc');

        $list=$query->paginate(get_page_size($data));
        return $list;
    }


    /**
     * 账号明细
     */
    public function profit_log_search_list($user_id,$data=[]){

        $query=ProfitLogModel::getQueryWhere([
            ['created_at','>=','start_time'],
            ['created_at','<=','end_time'],
            ['type','=','type'],
        ],$data)
            ->where('user_id',$user_id)

            ->orderBy('id','desc');
        $list=$query->paginate(get_page_size($data));

        foreach ($list->items() as &$item){
            if($item->username){
                $strlen=mb_strlen($item->username, 'utf-8');
                $item->username=str_repeat("*", $strlen - 1) .mb_substr($item->username, -1, 1, 'utf-8');
            }
        }
        return $list;
    }

}
