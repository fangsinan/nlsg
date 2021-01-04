<?php


namespace App\Http\Controllers\Admin\V4;


use App\Http\Controllers\ControllerBackend;
use App\Servers\removeDataServers;

class RemoveDataController extends ControllerBackend
{
    public function goods()
    {
        set_time_limit(0);

        $servers = new removeDataServers();
        if (0) {
            //迁移商品,规格,图片,评论
            $servers->removeGoods();
        }

//        if (0) {
//            //校验商品和规格的价格是否冲突
//            $servers->updateGoodsSkuPrice();
//        }

        if (0) {
            //临时 批量添加机器人
            $servers->addRobot();
        }

//        if(0){
//            //临时 计算用户的关注和历史数量
//            $servers->countUserData();
//        }

    }

    public function mallOrders()
    {
        if (0) {
            //需要先执行 addressExpress
            $servers = new removeDataServers();
            $servers->removeMallOrders();
        }
    }

    public function addressExpress()
    {
        if (0) {
            $servers = new removeDataServers();
            $servers->addressExpress();
        }
    }

    //补全vip表新加字段
    public function vip()
    {
        if (0) {
            $servers = new removeDataServers();
            $servers->vip();
        }
    }

//    public function redeemCode(){
//        $servers = new removeDataServers();
//        $servers->redeemCode();
//    }
}
