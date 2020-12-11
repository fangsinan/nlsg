<?php


namespace App\Http\Controllers\Admin\V4;


use App\Http\Controllers\Controller;
use App\Servers\removeDataServers;

class RemoveDataController extends Controller
{
    public function goods()
    {

        if (0) {
            //迁移商品,规格,图片,评论
            $servers = new removeDataServers();
            $servers->removeGoods();
        }

        if (1) {
            //校验商品和规格的价格是否冲突
            $servers = new removeDataServers();
            $servers->updateGoodsSkuPrice();
        }

    }

    public function mallOrders()
    {
        $servers = new removeDataServers();
        $servers->removeMallOrders();
    }
}
