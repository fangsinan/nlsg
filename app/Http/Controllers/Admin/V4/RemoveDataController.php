<?php


namespace App\Http\Controllers\Admin\V4;


use App\Http\Controllers\Controller;
use App\Servers\removeDataServers;

class RemoveDataController extends Controller
{
    public function goods(){
        $servers = new removeDataServers();
        $servers->removeGoods();
    }
}
