<?php

namespace App\Servers\V5;

use App\Models\WechatOpenid;
use App\Models\WorksInfo;
use App\Servers\LiveConsoleServers;
use Predis\Client;
use EasyWeChat\Factory;

class WechatServersNew
{

    /**
     * WechatServers::SetAccessToken();
     * 获取token校验信息
     * access_token是公众号的全局唯一接口调用凭据
     * access_token的存储至少要保留512个字符空间。access_token的有效期目前为2个小时  刷新时公众平台后台会保证在5分钟内，新老access_token都可用
     * 此token和获取用户信息token不是同一个，此token用于调用其他接口如分享接口等   用户信息token用于处理支付
     * @return mixed
     */
    //未使用
    public static function SetAccessToken()
    {

        try {

            $redisConfig = config('database.redis.default');
            $Redis = new Client($redisConfig);
            $Redis->select(2);

            $app_id = 'wxe24a425adb5102f6';
            $app_secret = '2ded804b74f99ae2f342423dd7952620';
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $app_id . '&secret=' . $app_secret;

            $rst = WorksInfo::curlPost($url);

            if ($rst) {
                $rstJson = json_decode($rst);

                if (isset($rstJson->access_token)) {
                    //写入redis
                    $Redis->setex('crontab_wechat_access_token',7200,$rstJson->access_token);//设置redis缓存
                    LiveConsoleServers::LogIo('accesstoken','wechat','1');
                } else {
                    //写入报错日志
                    LiveConsoleServers::LogIo('accesstoken','wechat_error','WechatToken：access_token null');
                }
            }
        } catch (\Exception $e) {
            LiveConsoleServers::LogIo('accesstoken','wechat_error','WechatToken：' . $e->getMessage());
        }


    }


    //获取token
    public static function GetToken($flag=false){

        /*$redisConfig = config('database.redis.default');
        $Redis = new Client($redisConfig);
        $Redis->select(2);

        if($flag){ //强制刷新
            self::SetAccessToken();
        }

        $access_token = $Redis->get('crontab_wechat_access_token');

        return $access_token;*/

        $config = [
            'app_id' => 'wxe24a425adb5102f6',
            'secret' => '2ded804b74f99ae2f342423dd7952620',
            'response_type' => 'array',
            'cache' => "redis"
        ];

        $app = Factory::officialAccount($config);

        $accessToken = $app->access_token;

        if($flag){ //强制刷新

            $tokenArr = $accessToken->getToken(true);
            if(isset($tokenArr['access_token']) && !empty($tokenArr['access_token'])){
                $access_token=$tokenArr['access_token'];
                return $access_token;
            }
        }

        $tokenArr = $accessToken->getToken();
        $access_token='';
        if(isset($tokenArr['access_token']) && !empty($tokenArr['access_token'])){
            $access_token=$tokenArr['access_token'];
        }else{
            $tokenArr = $accessToken->getToken(true);
            if(isset($tokenArr['access_token']) && !empty($tokenArr['access_token'])){
                $access_token=$tokenArr['access_token'];
            }
        }
        return $access_token;

    }

    //拉取所有微信关注的用户
    //https://app.api.nlsgapp.com/index/GetOpenidList
    public static function GetOpenId(){

        $WechatInfo=WechatOpenid::query()->select(['id','open_id'])->orderByDesc('id')->first();
//        var_dump($WechatInfo);
        if(!empty($WechatInfo)){
            $next_openid=$WechatInfo->open_id;
        }else{
            $next_openid='';
        }
        $time=time();
        $day_time=date('Y-m-d H:i:s',$time);

        $Access_Token=self::GetToken(1); //获取token
        $url="https://api.weixin.qq.com/cgi-bin/user/get?access_token=$Access_Token&next_openid=".$next_openid;
//        echo $url;
        $data=WorksInfo::curlPost($url);
        $data=json_decode($data,true);
//        var_dump($data);
        /*{
            "total":2,
            "count":2,
            "data":{
                    "openid":["OPENID1","OPENID2"]},
            "next_openid":"NEXT_OPENID"
        }*/
        if(!empty($data['data']['openid'])){

            $redisConfig = config('database.redis.default');
            $Redis = new Client($redisConfig);
            $Redis->select(5);

            $map_all = [];
            foreach ($data['data']['openid'] as $fv) {
                $map_all_temp = [];
                $map_all_temp['open_id'] = $fv;
                $map_all_temp['created_at'] = $day_time;
                $map_all[] = $map_all_temp;
                //加入队列等待执行
                $Redis->rpush('push_wechat_openid_list', $fv);
            }

            //一次拉取10000条数据
            $rst = WechatOpenid::Add($map_all, true);
            LiveConsoleServers::LogIo('wechatopenid','laqu_',$rst.'----'.$next_openid);

        }

    }

    //测试发送
    public static function CeshiTemplate(){

        $redisConfig = config('database.redis.default');
        $Redis = new Client($redisConfig);
        $Redis->select(5);

        $time=time();
        $day_time=date('Y-m-d H:i:s',$time);

        $openidArr=['oVWHQwW2LP8Fp8TX8Auiyxze7PmA','oVWHQwa63mOAlXGnpyApkh1eS6oE','oVWHQwU-QmcTHePk6mQY8Xco7Vxg'];
        $map_all = [];
        foreach ($openidArr as $fv) {
            $map_all_temp = [];
            $map_all_temp['open_id'] = $fv;
            $map_all_temp['created_at'] = $day_time;
            $map_all[] = $map_all_temp;
            //加入队列等待执行
            $Redis->rpush('push_wechat_openid_list_test', $fv);
        }

        $rst = WechatOpenid::Add($map_all, true);
    }


    //模板发送
    public static function TemplateLive($is_test=0){

        try {

            $redisConfig = config('database.redis.default');
            $Redis = new Client($redisConfig);
            $Redis->select(5);
            if($is_test==1){
                $key_name='push_wechat_openid_list_test';
            }else{
                $key_name='push_wechat_openid_list';
            }
            $flag = true;
            while ($flag) {
                $num = $Redis->llen($key_name);
                if ($num > 0) {

                    $open_id = $Redis->lPop($key_name);
                    if(!empty($open_id)) {
                        $time = time();
                        $day_time = date('Y-m-d H:i:s', $time);
                        $Access_Token = self::GetToken(); //获取token
                        $Url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . $Access_Token;
                        //课程更新提醒
                        $rst = self::sendBespeakClass($Url, $open_id);
                        if ($rst['code'] == 200) {
                            $map = [
                                'updated_at' => $day_time,
                                'is_send' => 1 //已推送
                            ];
                            WechatOpenid::where(['open_id' => $open_id])->update($map);
                        } else {
                            if ($rst['code'] == 43004 || $rst['code'] == 43019) { //取消关注  拉黑
                                $map = [
                                    'updated_at' => $day_time,
                                    'status' => 1 //取消关注
                                ];
                                WechatOpenid::where(['open_id' => $open_id])->update($map);
                            } else if ($rst['code'] == 40001) {//access_token 失效 重新生成
                                self::GetToken(1);
                                $Redis->rpush($key_name, $open_id); //重新加入队列
                            }
                            LiveConsoleServers::LogIo('wechatopenid', 'send_error_', $open_id . '：' . $rst['msg']);
                        }
                    }

                }else{
                    break;
                }
            }

        }catch (\Exception $e){
            LiveConsoleServers::LogIo('wechatopenid','send_error_',$e->getMessage());
        }
    }

    //模板配置
    public  static function sendBespeakClass($Url,$open_id){

//        $hrefurl='https://wechat.nlsgapp.com/appv4/liveBroadcast?live_info_id=749&time=1669023081250&inviter=211370';
        $hrefurl='https://a.nlsgapp.net/a/n9x';
		$hello='如何提高孩子记忆力，高效记忆轻松学习？';
		$title='《轻松提高孩子记忆力》直播课';
		$teacher='姬广亮老师';
		$time='2023年4月20日 19:30';
		$remarks="点击下面详情按钮保存直播链接，以免错过课程~";

        $data=[
            'touser'=>$open_id,
            'template_id'=>'MOKu-moZ9zHh2HrLYaUuBFywPDYFathcLZa1BjUS0eU',
            'url'=>$hrefurl, #抖音直播不需要跳转
            'topcolor'=>"#FF0000",
            'data'=>[
                "first"=>[
                    "value"=>$hello,
                    "color"=>"#FF0000",
                ],
                "keyword1"=>[ //课程名称
                    "value"=>$title,
                    "color"=>"#FF0000",
                ],
                "keyword2"=>[
                    "value"=>$teacher,
                    "color"=>"#FF0000",
                ],
                "keyword3"=>[
                    "value"=>$time,
                    "color"=>"#FF0000",
                ],
                "remark"=>[
                    "value"=>$remarks,
                    "color"=>"#FF0000",
                ],

            ],
        ];

        $content = WorksInfo::curlPost($Url,json_encode($data));
        $Rst=json_decode($content,true);
        if($Rst['errcode']==0){
            return ['code'=>200,'msg'=>'发送成功'];
        }else{
            switch($Rst['errcode']){
                case 40001:
                    $str='access_token 失效 重新生成即可';
                    break;
                case 40003:
                    $str='不合法的OpenID';
                    break;
                case 43004:
                    $str='用户未关注公众号 发送失败';
                    break;
                default:
                    $str=$Rst['errmsg'];
            }
            $content=$Rst['errcode']." | ".$str;
            return ['code'=>$Rst['errcode'],'msg'=>$content];
        }
    }


}
