<?php


namespace App\Http\Controllers\Admin\V4;


use App\Http\Controllers\Controller;
use App\Servers\removeDataServers;

class RemoveDataController extends Controller
{
    public function goods(){

        if(0){
            $servers = new removeDataServers();
            $servers->removeGoods();
        }

        if(1){
            $servers = new removeDataServers();
            $servers->updateGoodsSkuPrice();
        }



    }

    public function mallOrders(){
        $servers = new removeDataServers();
        $servers->removeMallOrders();
    }
}
