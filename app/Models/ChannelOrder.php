<?php


namespace App\Models;


class ChannelOrder extends Base
{

    protected $table = 'nlsg_channel_order';

    public function skuInfo() {
        return $this->hasOne(ChannelSku::class, 'sku', 'sku')
            ->select();
    }
}
