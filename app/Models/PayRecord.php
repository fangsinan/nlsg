<?php


namespace App\Models;

use Illuminate\Support\Facades\DB;

class PayRecord extends Base
{
    protected $table = 'nlsg_pay_record';

    protected $fillable = ['ordernum', 'type', 'user_id', 'transaction_id', 'status', 'price', 'product_id', 'order_type', 'client', 'tax', 'live_id',];

    public static function thisMoneyCanSpendMoney($user_id, $flag, $money = 0)
    {
        switch ($flag) {
            case 'cytx':
                $line = ConfigModel::getData(36);
                $line = GetPriceTools::PriceCalc('-', $line, $money);
                if ($line < 0) {
                    return 0;
                }

                //是否推送直播预约订单
                $type_config = ConfigModel::getData(53, 1);
                if ($type_config == 1) {
                    $type_list = [9, 15, 10];
                } else {
                    $type_list = [9, 15];
                }

                $money = DB::table('nlsg_order as o')
                    ->join('nlsg_pay_record as p', 'o.ordernum', '=', 'p.ordernum')
                    ->where('p.user_id', '=', $user_id)
                    ->whereIn('o.type', $type_list)
                    ->where('o.activity_tag', '=', 'cytx')
                    ->where('o.status', '=', 1)
                    ->where('o.is_shill', '=', 0)
                    ->whereRaw('DATE_FORMAT(p.created_at,\'%Y%m\') = DATE_FORMAT(CURDATE(),"%Y%m")')
                    ->sum('o.price');

                if (intval($money ?? 0) <= intval($line)) {
                    return 1;
                } else {
                    return 0;
                }
            default:
                return 1;
        }


    }

    
    public static function PayLog($notice='',$msg=''){
        if(!empty($notice) && !empty($msg)){
            DB::table('nlsg_pay_log')->insert([
                "notice"    => $notice,
                "message"   => $msg,
                "created_at"=> date("Y-m-d H:i:s"),
            ]);
        }
    }
}
