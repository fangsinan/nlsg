<?php


namespace App\Http\Controllers\Admin\V4;


use App\Http\Controllers\ControllerBackend;
use App\Servers\removeDataServers;

class RemoveDataController extends ControllerBackend
{
    public function goods()
    {

        if (0) {
            //迁移商品,规格,图片,评论
            $servers = new removeDataServers();
            $servers->removeGoods();
        }

        if (0) {
            //校验商品和规格的价格是否冲突
            $servers = new removeDataServers();
            $servers->updateGoodsSkuPrice();
        }

        if(0){
            //临时 批量添加机器人
            $servers = new removeDataServers();
            $servers->addRobot();
        }

    }

    public function mallOrders()
    {
        $servers = new removeDataServers();
        $servers->removeMallOrders();
    }

    public function addressExpress(){
        $servers = new removeDataServers();
        $servers->addressExpress();
    }

    //补全vip表新加字段
    public function vip(){
        $servers = new removeDataServers();
        $servers->vip();
    }

    public function redeemCode(){
        $servers = new removeDataServers();
        $servers->redeemCode();
    }
}
