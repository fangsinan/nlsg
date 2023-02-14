<?php

namespace App\Servers\Bliss;

use App\Models\Bliss\AccountModel;
use App\Models\Bliss\OrderModel;
use App\Models\Bliss\ProfitLogModel;
use App\Models\Bliss\ProfitModel;
use App\Models\Bliss\UserModel;
use App\Models\Bliss\VipGroupModel;
use App\Models\Bliss\VipUserBindModel;
use App\Models\Bliss\VipUserModel;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;
use Predis\Client;

class ProfitServer
{


    /**
     * 保存收益
     */
    public static function profit_add($order_id, $user_id)
    {

        $Order = OrderModel::query()->where('id', $order_id)->where('status',1)->where('is_shill',0)->first();
        if (!$Order) {
            return '订单不存在';
        }

        //判断是否是合伙人
        $VipUserModel=VipUserModel::query()->where('user_id',$user_id)->where('status',1)->first();
        if(!$VipUserModel){
            return '合伙人用户不存在';
        }

        $VipGroupModel=VipGroupModel::query()->where('id',$VipUserModel->group_id)->first();
        if(!$VipGroupModel){
            return '合伙人分组不存在';
        }

        $profit=$VipGroupModel->price;
        if(empty($profit)){
            return '收益为空';
        }

        $ProfitModel = ProfitModel::query()->where('order_id', $order_id)->first();
        if ($ProfitModel) {
            return '收益已发放';
        }

        $ProfitModel = new ProfitModel();
        $ProfitModel->user_id = $user_id;
        $ProfitModel->order_id = $order_id;
        $ProfitModel->source_user_id = $Order->user_id;
        $ProfitModel->profit = $profit;
        $ProfitModel->plan_settle_time = date("Y-m-d H:i:s", strtotime("+30 day"));//计划结算时间
        $ProfitModel->save();

        //保存日志
        $ProfitLogModel = new ProfitLogModel();
        $ProfitLogModel->order_id = $order_id;
        $ProfitLogModel->user_id = $user_id;
        $ProfitLogModel->money = $ProfitModel->profit;
        $ProfitLogModel->type = 1;//类型 1收益 2提现
        $ProfitLogModel->remark = '成功推广商品';
        $ProfitLogModel->save();

        MessageServer::send_msg($ProfitLogModel->user_id, 'profit', $order_id, ['money' => $ProfitModel->profit]);

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

        $UserModel=UserModel::query()->where('id',$user_id)->first();
        if(!$UserModel){
            return '客户不存在';
        }

        return [

            //已结算
            'settled_money'=>ProfitModel::getQueryWhere([
                ['created_at','>=','start_time'],
                ['created_at','<=','end_time'],
            ],$data)->whereIn('status',[2])->where('user_id',$user_id)->sum('profit'),

            //待结算
            'tobe_settled_money'=>ProfitModel::getQueryWhere([
                ['created_at','>=','start_time'],
                ['created_at','<=','end_time'],
            ],$data)->whereIn('status',[0,1])->where('user_id',$user_id)->sum('profit'),

            //不结算
            'unsettled_money'=>ProfitModel::getQueryWhere([
                ['created_at','>=','start_time'],
                ['created_at','<=','end_time'],
            ],$data)->whereIn('status',[3])->where('user_id',$user_id)->sum('profit'),

            //不结算数量
            'unsettled_count'=>ProfitModel::getQueryWhere([
                ['created_at','>=','start_time'],
                ['created_at','<=','end_time'],
            ],$data)->whereIn('status',[3])->where('user_id',$user_id)->count(),

            //待结算数量
            'tobe_settled_count'=>ProfitModel::getQueryWhere([
                ['created_at','>=','start_time'],
                ['created_at','<=','end_time'],
            ],$data)->whereIn('status',[0,1])->where('user_id',$user_id)->count(),

            //已结算数量
            'settled_count'=>ProfitModel::getQueryWhere([
                ['created_at','>=','start_time'],
                ['created_at','<=','end_time'],
            ],$data)->whereIn('status',[2])->where('user_id',$user_id)->count(),

            //订单数量
            'order_count'=>OrderModel::getQueryWhere([
                ['pay_time','>=','start_time'],
                ['pay_time','<=','end_time'],
            ],$data)->whereIn('status',[1])->where('user_id',$user_id)->count(),

            //客户数量
            'customer_count'=>VipUserBindModel::getQueryWhere([
                ['begin_at','>=','start_time'],
                ['begin_at','<=','end_time'],
            ],$data)->whereIn('status',[1])->where('parent',$UserModel->phone)->count(),

            //合伙人数量
            'partner_count'=>VipUserBindModel::getQueryWhere([
                ['VipUserBindModel.begin_at','>=','start_time'],
                ['VipUserBindModel.begin_at','<=','end_time'],
            ],$data)->from(VipUserBindModel::DB_TABLE.' as VipUserBindModel')
                ->leftJoin(UserModel::DB_TABLE.' as UserModel','UserModel.phone','=','VipUserBindModel.son')
                ->leftJoin(VipUserModel::DB_TABLE.' as VipUserModel','VipUserModel.user_id','=','UserModel.id')
                ->whereIn('VipUserBindModel.status',[1])->where('VipUserModel.status',1)->where('VipUserBindModel.parent',$UserModel->phone)->count(),

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
