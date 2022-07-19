<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Predis\Client;

/**
 * ShortLinkController
 * 短链接转发
 */
class ShortLinkController extends Controller
{

    //http://127.0.0.1:8000/api/v/a
    //https://a.nlsgapp.net/api/v/a/iS6
    public function Jump(Request $request,$arg1)
    {

        $num=strlen($arg1);
        if($num!=3){
            echo '系统异常';
            exit;
        }

        $redisConfig = config('database.redis.default');
        $Redis = new Client($redisConfig);
        $Redis->select(6);

        $flag=$Redis->EXISTS($arg1);

        if($flag!=1) { //存在返回1

        }


    }


}
