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
            $Redis->rpush('push_wechat_openid_list', $fv);
        }

        $rst = WechatOpenid::Add($map_all, true);
    }


    //模板发送
    public static function TemplateLive(){

        try {

            $redisConfig = config('database.redis.default');
            $Redis = new Client($redisConfig);
            $Redis->select(5);
            $key_name='push_wechat_openid_list';
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
        /*您好，您已预约成功
        直播名称：如何让孩子更加高效学习
        主讲人：教育专家 张三
        开始时间：2017年8月1日 20:00
        请留言直播课程开始时间，以免错过课程*/
        //周三早7点  &tweeter_code=211370
        $hrefurl='https://wechat.nlsgapp.com/appv4/liveBroadcast?live_info_id=613&time=1660025453639&inviter=211370';
        $hello='王琨老师2晚家庭教育直播课：';
        $title='卓越孩子必备的6大能力';
        $number='ZYHZ-0810-11';
        $abstract='培养孩子的健康观、人生观、财富观、价值观、爱情观、世界观，详见链接';
        $remarks="温馨提示：原价699元直播课，琨哥粉丝福利价1元领取>>>";

        $data=[
            'touser'=>$open_id,
            'template_id'=>'W4eXPP0iI3iNGHvyrCOTfUvPpNFXwgP0uULl_0ZyK7s',
            'url'=>$hrefurl, #抖音直播不需要跳转
            'topcolor'=>"#FF0000",
            'data'=>[
                "first"=>[
                    "value"=>$hello,
                ],
                "keyword1"=>[ //名称
                    "value"=>$title,
                ],
                "keyword2"=>[ //编号
                    "value"=>$number,
                ],
                "keyword3"=>[ //摘要
                    "value"=>$abstract,
                ],
                "remark"=>[
                    "value"=>$remarks,
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
