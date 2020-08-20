<?php


namespace App\Http\Controllers\Admin\V4;


use App\Http\Controllers\Controller;
use App\Servers\CrontabServers;

class CrontabController extends Controller
{

    public function mallRefund()
    {
        $servers = new CrontabServers();
        $servers->mallRefund();
    }
}
