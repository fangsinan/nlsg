<?php


namespace App\Models;


class PayRecordDetail extends Base
{
    protected $table = 'nlsg_pay_record_detail';

    protected $fillable = ['ordernum' , 'type' , 'user_id', 'price' , 'order_detail_id'  , 'source_id' , 'subsidy_type' ,];

    static function getSumProfit($user_id,$type){

        if($type==1){ //待到账
            $money = PayRecordDetailStay::where(['user_id'=>$user_id])->sum('price');
            return $money;
        }elseif($type==5) { //可提现余额
            $first_day=(date('Y-m-01')); //本月第一天
            $income = PayRecordDetail::where('user_id', $user_id)->where('created_at', '<', $first_day)
                ->whereIn('type', [2,5,6,7,8,9])->sum('price');

            $cash = PayRecord::where('user_id', $user_id)->whereIn('status', [1,2])
                ->whereIn('order_type', [7,8,9,12])->sum('price');
            return round($income - $cash, 2);
        }else{
            $first_day=(date('Y-m-01')); //本月第一天
            $query = PayRecordDetail::where('user_id',$user_id)
                ->whereIn('type',[2,5,6,7,8,9]);

            if($type==2){ //累计结算 电商 专栏 精品课
                $query->where('created_at', '<',$first_day);
            }elseif($type==3){//本月当前收益
                $query->where('created_at', '>=',$first_day);
            }elseif($type==4){ //上月收益
                $Last_monthtime=(date('Y-m-01').' -1 month'); //上月第一天
                $query->where('created_at', '<',$first_day);
                $query->where('created_at', '>=',$Last_monthtime);

            } elseif($type==11){ //今日收益
                $Last_monthtime=(date('Y-m-d',time())); //今日
                $query->where('created_at', '<',date('Y-m-d H:i:s',time()));
                $query->where('created_at', '>=',$Last_monthtime);
            } elseif($type==12){ //昨天收益
                $Last_monthtime=(date('Y-m-d').' -1 day'); //上月第一天
                $query->where('created_at', '<', date('Y-m-d',time()));
                $query->where('created_at', '>=',$Last_monthtime);
            }

            $money = $query->sum('price');
            return $money;
        }
    }

}