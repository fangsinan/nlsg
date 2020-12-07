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
                $line = GetPriceTools::PriceCalc('+', $line, $money);

                $money = DB::table('nlsg_order as o')
                    ->join('nlsg_pay_record as p', 'o.ordernum', '=', 'p.ordernum')
                    ->where('p.user_id', '=', $user_id)
                    ->where('o.type', '=', 9)
                    ->where('o.activity_tag', '=', 'cytx')
                    ->where('o.status', '=', 1)
                    ->where('o.is_shill', '=', 0)
                    ->whereRaw('DATE_FORMAT(p.created_at,\'%Y%m\') = DATE_FORMAT(CURDATE(),"%Y%m")')
                    ->sum('p.price');

                if (intval($money ?? 0) <= intval($line)) {
                    return 0;
                } else {
                    return 1;
                }
            default:
                return 1;
        }


    }
}
