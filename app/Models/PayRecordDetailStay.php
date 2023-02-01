<?php


namespace App\Models;

use Illuminate\Support\Facades\DB;

class PayRecordDetailStay extends Base
{
    protected $table = 'nlsg_pay_record_detail_stay';

    protected $fillable = [
        'ordernum', 'user_id', 'order_detail_id', 'type',
    ];


    public static function remove()
    {
        $line = ConfigModel::getData(46);
        $line = date('Y-m-d 23:59:59', strtotime("- $line days"));
        $list = DB::table('nlsg_pay_record_detail_stay as s')
            ->join('nlsg_mall_order as o', 's.ordernum', '=', 'o.ordernum')
            ->join('nlsg_mall_order_detail as d', 's.order_detail_id', '=', 'd.id')
            ->where('s.type', '=', 5)
            ->where('s.app_project_type','=',APP_PROJECT_TYPE)
            ->where('o.status', '=', 30)
            ->where('receipt_at', '<=', $line)
            ->where('after_sale_used_num', '=', 0)
            ->select(['s.*'])
            ->get();

        foreach ($list as $v) {
            DB::beginTransaction();

            $pr = new PayRecordDetail();
            $pr->type = $v->type;
            $pr->ordernum = $v->ordernum;
            $pr->user_id = $v->user_id;
            $pr->price = $v->price;
            $pr->order_detail_id = $v->order_detail_id;

            $pr_res = $pr->save();
            if ($pr_res === false) {
                DB::rollBack();
                continue;
            }

            $del_res = self::whereId($v->id)->delete();
            if ($del_res === false) {
                DB::rollBack();
                continue;
            }

            DB::commit();
        }

    }

}
