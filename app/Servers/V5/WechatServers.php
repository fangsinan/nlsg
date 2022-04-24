<?php

namespace App\Servers\V5;

use App\Models\WechatOpenid;
use App\Models\WorksInfo;
use App\Servers\LiveConsoleServers;
use Illuminate\Support\Facades\DB;
use Predis\Client;
use EasyWeChat\Factory;

class WechatServers
{

    /**
     * WechatServers::SetAccessToken();
     * 获取token校验信息
     * access_token是公众号的全局唯一接口调用凭据
     * access_token的存储至少要保留512个字符空间。access_token的有效期目前为2个小时  刷新时公众平台后台会保证在5分钟内，新老access_token都可用
     * 此token和获取用户信息token不是同一个，此token用于调用其他接口如分享接口等   用户信息token用于处理支付
     * @return mixed
     */
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
            $map_all = [];
            foreach ($data['data']['openid'] as $fv) {
                $map_all_temp = [];
                $map_all_temp['open_id'] = $fv;
                $map_all_temp['created_at'] = $day_time;
                $map_all[] = $map_all_temp;
            }

            //一次拉取10000条数据
            $rst = WechatOpenid::Add($map_all, true);
            LiveConsoleServers::LogIo('wechatopenid','laqu_',$rst.'----'.$next_openid);

        }

    }

    //模板发送
    public static function TemplateLiveDesc(){

        try {
            $flag = true;
            while ($flag) {
                $query = WechatOpenid::query()->select(['id','open_id'])->where('is_send', 0)->where('status', 0);
//                $query->whereIn('open_id', ['oVWHQwW2LP8Fp8TX8Auiyxze7PmA','oVWHQwa63mOAlXGnpyApkh1eS6oE'])->orderBy('id','DESC');
                $query->orderBy('id','DESC');

                $dataAll=$query->limit(200)->get()->toArray() ?: [];

//                $flag = false; //防止出错停止
                if (empty($dataAll)) {
                    $flag = false;
                } else {
                    $time=time();
                    foreach ($dataAll as $key => $val) {
                        $day_time=date('Y-m-d H:i:s',$time);
//                        if($order==1 && $key==1){
//                            sleep(20);
//                        }
                        $Access_Token = self::GetToken(); //获取token
//                        LiveConsoleServers::LogIo('wechatopenid','send_error_','GetToken：' .$Access_Token);
                        $Url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . $Access_Token;
                        //课程更新提醒
                        $rst = self::sendBespeakClass($Url, $val);
//                        LiveConsoleServers::LogIo('wechatopenid','send_error_','sendBespeakClass：' .json_encode($rst).'|'.$rst['code'].'|'.$val['id']);
                        if ($rst['code'] == 200) {
                            $map = [
                                'updated_at' => $day_time,
                                'is_send' => 1 //已推送
                            ];
                            WechatOpenid::where(['id' => $val['id']])->update($map);
                        } else {
                            if($rst['code']==43004 || $rst['code']==43019){ //取消关注  拉黑
                                $map = [
                                    'updated_at' => $day_time,
                                    'status' => 1 //取消关注
                                ];
                                WechatOpenid::where(['id' => $val['id']])->update($map);
                            }else if($rst['code']==40001){
                                self::GetToken(1);
                            }
                            LiveConsoleServers::LogIo('wechatopenid','send_error_',$val['open_id'] . '：' . $rst['msg']);
                        }
                    }
                }
            }

        }catch (\Exception $e){
            LiveConsoleServers::LogIo('wechatopenid','send_error_',$e->getMessage());
        }
    }

    //模板发送
    public static function TemplateLiveAsc(){
//        return ;
        try {
            $flag = true;
            while ($flag) {
                $query = WechatOpenid::query()->select(['id','open_id'])->where('is_send', 0)->where('status', 0);
//                $query->whereIn('open_id', ['oVWHQwW2LP8Fp8TX8Auiyxze7PmA','oVWHQwU-QmcTHePk6mQY8Xco7Vxg'])->orderBy('id','asc');
                $query->orderBy('id','asc');

                $dataAll=$query->limit(200)->get()->toArray() ?: [];

//                $flag = false; //防止出错停止
                if (empty($dataAll)) {
                    $flag = false;
                } else {
                    $time=time();
                    foreach ($dataAll as $key => $val) {
                        $day_time=date('Y-m-d H:i:s',$time);
//                        if($order==1 && $key==1){
//                            sleep(20);
//                        }
                        $Access_Token = self::GetToken(); //获取token
//                        LiveConsoleServers::LogIo('wechatopenid','send_error_','GetToken：' .$Access_Token);
                        $Url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . $Access_Token;
                        //课程更新提醒
                        $rst = self::sendBespeakClass($Url, $val);
//                        LiveConsoleServers::LogIo('wechatopenid','send_error_','sendBespeakClass：' .json_encode($rst).'|'.$rst['code'].'|'.$val['id']);
                        if ($rst['code'] == 200) {
                            $map = [
                                'updated_at' => $day_time,
                                'is_send' => 1 //已推送
                            ];
                            WechatOpenid::where(['id' => $val['id']])->update($map);
                        } else {
                            if($rst['code']==43004 || $rst['code']==43019){ //取消关注  拉黑
                                $map = [
                                    'updated_at' => $day_time,
                                    'status' => 1 //取消关注
                                ];
                                WechatOpenid::where(['id' => $val['id']])->update($map);
                            }else if($rst['code']==40001){
                                self::GetToken(1);
                            }
                            LiveConsoleServers::LogIo('wechatopenid','send_error_',$val['open_id'] . '：' . $rst['msg']);
                        }
                    }
                }
            }

        }catch (\Exception $e){
            LiveConsoleServers::LogIo('wechatopenid','send_error_',$e->getMessage());
        }
    }

    //模板配置
    public  static function sendBespeakClass($Url,$val){
        //周三早7点  &tweeter_code=211370
        $hrefurl='https://wechat.nlsgapp.com/appv4/liveBroadcast?live_info_id=391&time=1649815037309&inviter=211370';
        $hello='你好，今晚有免费直播课程，讲孩子的学习~';
        $title='让孩子主动学习的“6个秘密”';
        $category='家庭教育直播课';
        $teacher='汤蓓老师';
        $time='2022年4月13日 19:30';
        $remarks="※针对主动学习，父母和孩子如何“使劲”？
※真正拉开孩子成绩的原因是什么，如何提升？
※孩子写作业，父母正确的陪伴方式是什么？
点击下面详情按钮，免费预约直播课学习~";

        $data=[
            'touser'=>$val['open_id'],
            'template_id'=>'EOEBu_V_bYvyl4YQZ_0Zgij5I_2TfJQMXlRUz0VZwr0',
            'url'=>$hrefurl, #抖音直播不需要跳转
            'topcolor'=>"#FF0000",
            'data'=>[
                "first"=>[
                    "value"=>$hello,
                ],
                "keyword1"=>[ //课程名称
                    "value"=>$title,
                ],
                "keyword2"=>[ //课程类别
                    "value"=>$category,
                ],
                "keyword3"=>[ //课程老师
                    "value"=>$teacher,
                ],
                "keyword4"=>[ //课程时间
                    "value"=>$time,
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
