<?php


namespace App\Http\Controllers\Admin\V4;


use App\Http\Controllers\Controller;
use App\Servers\MallRefundJob;

class CrontabController extends Controller
{

    //售后订单退款
    public function mallRefund()
    {
        $servers = new MallRefundJob();
        $servers->mallRefund();
    }

    //售后订单退款查询
    public function mallRefundCheck()
    {
        $servers = new MallRefundJob();
        $servers->mallRefundCheck();
    }

    //todo 清除失效拼团订单
    public function delGroupBuyOrder(){

    }

    //todo 清除失效秒杀订单
    public function delFlashSaleOrder(){

    }
}
