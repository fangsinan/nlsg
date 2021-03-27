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
                ->whereIn('type', [2,5,6,7,8,9,10,11])->sum('price');

            $cash = PayRecord::where('user_id', $user_id)->whereIn('status', [1,2])
                ->whereIn('order_type', [7,8,9,12])->sum('price');
            return round($income - $cash, 2);
        }else{
            $first_day=(date('Y-m-01')); //本月第一天
            $query = PayRecordDetail::where('user_id',$user_id)
                ->whereIn('type',[2,5,6,7,8,9,10,11]);

            if($type==2){ //累计结算 电商 专栏 精品课
                $query->where('created_at', '<',$first_day);
            }elseif($type==3){//本月当前收益
                $query->where('created_at', '>=',$first_day);
            }elseif($type==4){ //上月收益
                $Last_monthtime = date('Y-m-d',strtotime(date('Y-m-01').' -1 month'));
                //$Last_monthtime=(date('Y-m-01').' -1 month'); //上月第一天

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


    //计算当前提现税额
    static function cal_tax($user_id, $money)
    {

        $cash_data_info = CashData::where(['user_id'=>$user_id])->first();
        if (empty($cash_data_info) ) {
            return 0;
        }
        //这个月提现的总额
        $sum = PayRecord::where(['user_id' => $user_id,])
            ->whereIn('status', [1, 2])
            ->whereIn('order_type', [7, 8])
            ->where('created_at','>',date('Y-m-1', time()))
            ->sum('price');
        //$sum=(empty($sum['price']) || $sum['price']<0)?0:$sum['price'];

        $sum += $money;
        //应该缴纳的税

        $shui = Withdrawals::cal_tax($sum);
        //这个月已经缴纳的税
        $tax_sum = PayRecord::where(['user_id' => $user_id,])
            ->whereIn('status', [1, 2])
            ->whereIn('order_type', [7, 8])
            ->where('created_at','>',date('Y-m-1', time()))->sum('tax');
        return round($shui - $tax_sum, 2);
    }



    /**
     * 计算个税
     * @return [type] [description]
     */
    public static function  getIncomeTax($user_id, $money)
    {
        $sum = PayRecord::where(['user_id' => $user_id,])
            ->whereIn('status', [1, 2])
            ->whereIn('order_type', [7, 8])
            ->where('created_at', date('Y-m-1', time()), '>')
            ->sum('price');
        $pay_total = $sum ? $sum : 0;
        $total      = $sum + $money ;
        $income_tax = Withdrawals::cal_tax($total);
        $remain_money = round($income_tax, 2);

        return $remain_money ?: 0;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
