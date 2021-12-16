<?php
/**
 * Created by PhpStorm.
 * User: nlsg2017
 * Date: 2019/6/17
 * Time: 2:01 PM
 */


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;


class LiveInfo extends Model
{
    protected $table = 'nlsg_live_info';


    public function live()
    {
        return $this->belongsTo(Live::class, 'live_pid', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * 直播首页回放列表
     * @param  int  $uid
     * @return array
     */
    public function getBackLists($uid = 0)
    {

        $cache_live_name = 'live_back_list';
        $lists = Cache::get($cache_live_name);
        if (empty($liveLists)) {
            $lists = LiveInfo::with('user:id,nickname',
                'live:id,title,describe,price,cover_img,begin_at,type,playback_price,is_free,password')
                ->select('id', 'live_pid', 'user_id')
                ->where('status', 1)
                ->where('playback_url', '!=', '')
                ->orderBy('begin_at', 'desc')
                ->limit(2)
                ->get()
                ->toArray();
            $expire_num = CacheTools::getExpire('live_back_list');
            Cache::put($cache_live_name, $lists, $expire_num);
        }

        if ( ! empty($lists)) {
            $backLists = [];
            foreach ($lists as &$v) {
                $isSub = Subscribe::isSubscribe($uid, $v['live_pid'], 3);
                $isAdmin = LiveConsole::isAdmininLive($uid, $v['live_pid']);
                $backLists[] = [
                    'id'             => $v['live']['id'],
                    'title'          => $v['live']['title'],
                    'is_password'    => $v['live']['password'] ? 1 : 0,
                    'describe'       => $v['live']['describe'],
                    'price'          => $v['live']['price'],
                    'cover_img'      => $v['live']['cover_img'],
                    'playback_price' => $v['live']['playback_price'],
                    'live_time'      => date('Y.m.d H:i', strtotime($v['live']['begin_at'])),
                    'is_free'        => $v['live']['is_free'],
                    'info_id'        => $v['id'],
                    'is_sub'         => $isSub ?? 0,
                    'is_admin'       => $isAdmin ? 1 : 0
                ];
            }
        }

        return $lists;


    }




    public static  function  liveUrlEdit($type,$live_info_id){

        if($type == "" || $live_info_id == '' ){
            return ['code'=>0,'msg'=>'error'];

        }
        //开始时间   结束时间    pushurl   callbackurl
        $info = LiveInfo::find($live_info_id);
        if($type == 'create' && !empty($info['task_id'])){

            return ['code'=>0,'msg'=>'已创建拉流任务','data'=>[]];
        }

        if($type == 'del' && empty($info['task_id'])){
            return ['code'=>0,'msg'=>'当前拉流任务不存在','data'=>[]];
        }
        
        $subject        = $info['push_live_url'];
        $back_video_url   = $info['back_video_url'];

        $str_time = strtotime($info['begin_at']) - 60;
        $end_time       = $str_time+3600*3+600;//  结束时间需要大于当前时间 目前有视频超过3小时

        if( $type == 'create' && $str_time <= time() ){
            return ['code'=>0,'msg'=>'直播开始时间必须大于当前时间','data'=>[]];
        }
        if( $type == 'create' && empty($back_video_url) ){
            return ['code'=>0,'msg'=>'源播放地址不存在','data'=>[]];
        }
        //老版
        $SecretId="AKIDrcCpIdlpgLo4A4LMj7MPFtKfolWeNHnC";
        $SECRET_KEY="MWXLwKVXMzPcrwrcDcrulPsAF7nIpCNM";


        //新版
        //$appId="1308168117";
        $SecretId="AKIDYv1qOfeMqCI8h03oTs0tWtymKcdNr40g";
        $SECRET_KEY="RghRu61f17ycz5uSjkV0EBsIRJyOZsug";


        //加密
        $rand = rand (100, 10000000); //9031868223070871051
        $time = time ();
        $Region = "ap-guangzhou";
        $data_key = [
            'Action' => 'DescribeLivePullStreamTasks',
            'Version' => "2018-08-01",
            'Region' => $Region,
            'SecretId' => $SecretId,
            'Timestamp' => $time,
            'Nonce' => $rand,
            'SignatureMethod' => 'HmacSHA256',
        ];

        $DomainName = config('env.LIVE_PUSH_URL');;
        switch ($type){
            case "create":

//                $pattern_1 = '/rtmp:\/\/push.live.nlsgapp.com\/live\/(.*?)\?txSecret=(.*?)/';
                $pattern_1 = '/rtmp:\/\/'.$DomainName.'\/live\/(.*?)\?txSecret=(.*?)/';
                $num1 = preg_match_all($pattern_1, $subject, $matches_1,PREG_PATTERN_ORDER);
                if( $num1 <= 0 ){
                    break;
                }
                $StreamName = $matches_1[1][0];
                $data_key['Action'] = 'CreateLivePullStreamTask';
                $data_key['Version'] = '2018-08-01';
                $data_key['SourceType'] = 'PullVodPushLive'; //点播 类型
                //$data_key['SourceUrls.0'] = 'http://1253639599.vod2.myqcloud.com/32a152b3vodgzp1253639599/d590feb55285890818716274924/Ja0YTxwJYVIA.mp4';
                $data_key['SourceUrls.0'] = $back_video_url;
//                $data_key['DomainName'] = 'push.live.nlsgapp.com'; //推流域名
                $data_key['DomainName'] = $DomainName; //推流域名
                $data_key['AppName'] = 'live';  //推流路径。
                $data_key['StreamName'] = $StreamName; //推流名称。。
                // 北京时间值 = UTC 时间值 + 8 小时

                $data_key['StartTime'] = date('Y-m-d\TH:i:s.0000\Z', $str_time-28800); //开始时间  使用 UTC 格式时间，北京时间值 = UTC 时间值 + 8 小时
                $data_key['EndTime'] = date('Y-m-d\TH:i:s.0000\Z', $end_time-28800); //结束时间  使用 UTC 格式时间结束时间 和 开始时间 间隔必须小于七天。 使用 UTC 格式时间，
                $data_key['Operator'] = 'admin'; //任务创建人。

                break;
            case "show":
                $data_key['Action'] = 'DescribeLivePullStreamTasks';
                break;
            case "del":
                if($info['task_id'] == ''){
                    return ['code'=>0,'msg'=>'当前拉流任务不存在','data'=>[]];
                }
                $data_key['Action'] = 'DeleteLivePullStreamTask';
                $data_key['TaskId'] = $info['task_id'];//任务 Id。  创建时返回的任务id
                $data_key['Operator'] = 'admin'; //任务创建人。
                break;
            default:
                $data_key['Action'] = 'DescribeLivePullStreamTasks';
                break;
        }
        ksort ($data_key); //排序
        // 计算签名
//        $srcStr = "POSTlive.tencentcloudapi.com/?" . http_build_query ($data_key);
        //使用这种方式 是为了避免特殊字符转义 导致签名失败
        $srcStr = "POSTlive.tencentcloudapi.com/?";
        foreach ( $data_key as $key => $value ) {
            $srcStr = $srcStr . $key . "=" . $value . "&";
        }
        $srcStr = substr($srcStr, 0, -1);


        $signature = base64_encode (hash_hmac ('sha256', $srcStr, $SECRET_KEY, true)); //SHA1  sha256
        $data_key['Signature'] = $signature;
        ksort ($data_key); //排序



        //拉取转码成功信息
        $url = "https://live.tencentcloudapi.com/";
        $info = WorksInfo::curlPost ($url, $data_key);  //post
        $raw_array = json_decode($info, true);

        if(empty($raw_array['Response']['Error']) ){

            switch ($type){
                case "create":
                    //TaskId 记录数据库
                    LiveInfo::where('id', $live_info_id)->update([
                        'task_id' => $raw_array['Response']['TaskId'],
                    ]);
                    $res = $raw_array['Response'];
                    break;
                case "show":
                    $res = $raw_array['Response']['TaskInfos'];
                    break;
                case "del":
                    LiveInfo::where('id', $live_info_id)->update([
                        'task_id' => 0,
                    ]);
                    $res = $raw_array['Response'];
                    break;
                default:
                    $res = [];
                    break;
            }
        }else{
            return ['code'=>0,'msg'=>'error','data'=>$raw_array['Response']['Error']];
        }
        //$res
        return ['code'=>200,'msg'=>'error','data'=>$res];
    }

}
