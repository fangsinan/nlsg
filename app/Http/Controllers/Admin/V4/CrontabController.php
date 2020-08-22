<?php


namespace App\Http\Controllers\Admin\V4;


use App\Http\Controllers\Controller;
use App\Servers\MallRefundJob;

class CrontabController extends Controller
{

    public function mallRefund()
    {
        $servers = new MallRefundJob();
        $servers->mallRefund();
    }

    public function mallRefundCheck()
    {
        $servers = new MallRefundJob();
        $servers->mallRefundCheck();
    }
}
