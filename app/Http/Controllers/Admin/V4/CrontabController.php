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
}
