<?php

namespace App\Servers\V5;

use AccessTokenBuilder;
use GlobalConfig;
use OrderSearchListParam;
use OrderSearchListRequest;

class DouDianServers
{
    //App Key: 6857846430543906317
    //App Secret: 2f3af110-3aef-4bf0-8641-f00840b8e87f
    //Service Id: 2484


    public function test() {

        GlobalConfig::getGlobalConfig()->appKey = "6857846430543906317";
        GlobalConfig::getGlobalConfig()->appSecret = "2f3af110-3aef-4bf0-8641-f00840b8e87f";

        $accessToken = AccessTokenBuilder::build(4463798, ACCESS_TOKEN_SHOP_ID);
        dd($accessToken);
    }
}
