<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use App\Models\Order;

class LiveDeal extends Base
{

    const DB_ORDER_TABLE='nlsg_order';

    const DB_TABLE = 'nlsg_live_deal';
    protected $table = 'nlsg_live_deal';

    /**
     * @return string
     * 添加
     */
    public static function Add($data,$flag){
//        $data['app_project_type'] = APP_PROJECT_TYPE;
        if($flag){
            $rst = DB::table(self::DB_TABLE)->insert($data);
        }else{
            $rst = self::query()->insertGetId($data);
        }

        return $rst;
    }

    public function orderInfo()
    {
        return $this->hasOne(Order::class, 'ordernum', 'ordernum');
    }

}
