<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use App\Models\LiveComment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Predis\Client;

class DemoController extends Controller
{

    /**
    把用户输入的文本转义（主要针对特殊符号和emoji表情）
     */
    public static function textEncode($str){
        if(!is_string($str))return $str;
        if(!$str || $str=='undefined')return '';

        $text = json_encode($str); //暴露出unicode
        $content = preg_replace_callback("/(\\\u[ed][0-9a-f]{3})/i",function($str){

            return addslashes($str[0]);//加两次转义  插入数据库的时候会被过滤掉一个\
        },$text); //将emoji的unicode留下，其他不动，这里的正则比原答案增加了d，因为我发现我很多emoji实际上是\ud开头的，反而暂时没发现有\ue开头。

        return json_decode($content);
    }
    /**
    解码上面的转义
     */
    public static function textDecode($str){
        $text = json_encode($str); //暴露出unicode
        $content = preg_replace_callback('/\\\\\\\\/i',function($str){
            return '\\';
        },$text); //将两条斜杠变成一条，其他不动
        return json_decode($content);
    }

    public function ceshi()
    {

//        $redisConfig = config('database.redis.default');
//        $Redis = new Client($redisConfig);
//        $Redis->select(1);

        $query=DB::table('nlsg_user_tag as t')->where('t.status',0)->select(['id','phone','internal_remarks'])->limit(1000);
        $list=$query->get()->toArray() ?: [];
        if(!empty($list)){
            foreach ($list as $key=>$val){
                $userInfo = User::query()->where('phone','=',$val->phone)->select('id')->first();
                if(empty($userInfo)){
                    User::query()->create([
                        'phone' => $val->phone,
                        'inviter' => 0,
                        'login_flag' => 0,
                        'nickname' => substr_replace($val->phone, '****', 3, 4),
                        'ref' => 0,
                        'internal_remarks'=>$val->internal_remarks,
                        'created_at'=>date('Y-m-d H:i:s',time())
                    ]);
                }else{
                    User::query()->where('id', '=', $userInfo->id)->update(['internal_remarks' => $val->internal_remarks]);
                    DB::table('nlsg_user_tag')->where('id',$val->id)->update(['status'=>1]);
                }
            }
            echo '执行成功';
        }else{
            echo '数据为空';
        }

        return ;

        $info=LiveComment::query()->where('id',13843220)->first();
        var_dump($info->content);
        $content=$info->content;

        $content_json=json_encode($content); var_dump($content_json);
        $content_json = str_replace("\ud83d\udc4f",'#01#',$content_json); //鼓掌
        $content_json = str_replace("\ud83d\udc4d",'#02#',$content_json); //点赞
        $content_json = str_replace("\ud83c\udf39",'#03#',$content_json); //小红花
        $content=json_decode($content_json);

//        $content = preg_replace_callback('/\%ud.{3}/u', function (array $match) { //  '/./u'
//            return strlen($match[0]) >= 4 ? '' : $match[0];
//        }, $content);

        $regex='/[\xf0-\xf7].{3}/'; // 全局匹配
        $content = preg_replace($regex,'',$content);

        if(empty($content)){
            $replace_content=self::textDecode('\ud83d\udc4f');  //   \ud83d\udc4f  鼓掌
            $content=$replace_content.$replace_content.$replace_content;
        }
        $replace_gz=self::textDecode('\ud83d\udc4f');  // \ud83d\udc4f  鼓掌
        $replace_dz=self::textDecode('\ud83d\udc4d');  // \ud83d\udc4d  点赞
        $replace_xhh=self::textDecode('\ud83c\udf39');  // \ud83c\udf39  小红花
        $content = str_replace("#01#",$replace_gz,$content); //鼓掌
        $content = str_replace("#02#",$replace_dz,$content); //点赞
        $content = str_replace("#03#",$replace_xhh,$content); //小红花

        $time=date('Y-m-d H:i:s', time());
        $data = ['live_id' => 19, 'live_info_id' => 19, 'user_id' => 211370, 'content' => $content, 'live_son_flag' => 0, 'created_at' => $time];
        $rst = LiveComment::query()->insertGetId($data);
        var_dump($rst);

        exit;
    }

}
