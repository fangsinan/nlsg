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

    //http://127.0.0.1:8000/a/iS6
    //https://a.nlsgapp.net/a/iS6
    public function Jump(Request $request,$arg1)
    {

        $num=strlen($arg1);
        if($num!=3){
            echo '数据异常'; exit;
        }
        $redisConfig = config('database.redis.default');
        $Redis = new Client($redisConfig);
        $Redis->select(6);

        $flag=$Redis->EXISTS($arg1);

        $table_name='a_short_link';
        if($flag==1) {
            $url=$Redis->get($arg1);
        }else{
            $info = DB::table($table_name)->select("id","code", "url")->where('code',$arg1)->first();
            if(empty($info)){
                echo '数据异常'; exit;
            }
            $key_name=$info->code;
            $url=$info->url;
            $Redis->setex($key_name,60*60*2,$url);//2小时
        }

        header("Location:$url");
        die;

    }


}
