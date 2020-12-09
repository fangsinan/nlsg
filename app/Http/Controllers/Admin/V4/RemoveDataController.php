<?php


namespace App\Http\Controllers\Admin\V4;


use App\Http\Controllers\Controller;
use App\Servers\removeDataServers;

class RemoveDataController extends Controller
{
    public function goods(){
        return true;
        $servers = new removeDataServers();
        $servers->removeGoods();
    }

    public function mallOrders(){
        $servers = new removeDataServers();
        $servers->removeMallOrders();
    }
}
