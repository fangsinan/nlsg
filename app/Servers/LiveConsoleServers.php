<?php
namespace App\Servers;

use App\Models\LiveInfo;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Predis\Client;

class LiveConsoleServers
{

    //定时入库打赏
    public static function CrontabGiftRedis(){

        try {
            $redisConfig = config('database.redis.default');
            $Redis = new Client($redisConfig);
            $Redis->select(0);

            $time=time();
            $key_minute='11LiveConsole:live_gift'.date('Hi',$time);
            $flag=$Redis->EXISTS($key_minute);

            $list_key='11LiveConsole:live_gift';
            if($flag!=1){ //存在返回1
                $Redis->setex($key_minute,60,1);//1分钟

                $list=$Redis->lrange($list_key,0,-1);// 获取所有数据
                if (!empty($list)) {
                    $data=[];
                    $map = [];
                    $start=0;
                    foreach ($list as $k => $val) {
                        $start=$k;
                        $map[] =  json_decode($val, true);
                        if(($k+1)%10000==0){
                            $data[]=$map;
                            $map=[]; //初始化
                        }
                    }
                    $Redis->ltrim($list_key,$start+1,-1);//删除已取出数据

                    if(!empty($map)){ //取余剩下的数据
                        $data[]=$map;
                    }

                    if (!empty($data)) {
                        DB::beginTransaction();
                        try {
                            $inser_rst=0;
                            $rst=true;

                            foreach ($data as $k=>$v) {
                                $rst = DB::table('nlsg_live_comment')->insert($v);
                                if ($rst === false) {
                                    DB::rollBack();
                                    $inser_rst=1;
                                    break;
                                }
                            }
                            if($inser_rst==1){
                                //日志写入
                                self::LogIo('livegift','gift_error','写入失败'.$rst);
                                //回写数据
                                foreach ($list as $k => $val) {
                                    $Redis->rpush($list_key,$val);
                                }
                                return '写入失败';
                            }
                            DB::commit();
                            //日志写入
                            self::LogIo('livegift','gift','执行成功');
                            return  '写入成功';
                        }catch (\Exception $e) {
                            DB::rollBack();
                            //日志写入
                            self::LogIo('livegift','gift_error','写入异常'.$e->getMessage());
                            //回写数据
                            foreach ($list as $k => $val) {
                                $Redis->rpush($list_key,$val);
                            }
                            return  '写入异常'.$e->getMessage();
                        }
                    }
                }else{
                    return  '集合为空';
                }


            }else{
                self::LogIo('livegift','gift_redis','不执行'.$key_minute);
            }
        }catch (\Exception $e){
            self::LogIo('livegift','gift_redis_error','写入失败'.$e->getMessage());
        }

    }

    //定时入库评论
    public static function CrontabCommentRedis(){

        try {
            $redisConfig = config('database.redis.default');
            $Redis = new Client($redisConfig);
            $Redis->select(0);

            $time=time();
            $key_minute='11LiveConsole:live_comment'.date('Hi',$time);
            $flag=$Redis->EXISTS($key_minute);

            $list_key='11LiveConsole:live_comment';
            if($flag!=1){ //存在返回1
                $Redis->setex($key_minute,60,1);//1分钟

                $list=$Redis->lrange($list_key,0,-1);// 获取所有数据
                if (!empty($list)) {
                    $data=[];
                    $map = [];
                    $start=0;
                    foreach ($list as $k => $val) {
                        $start=$k;
                        $map[] =  json_decode($val, true);
                        if(($k+1)%10000==0){
                            $data[]=$map;
                            $map=[]; //初始化
                        }
                    }
                    $Redis->ltrim($list_key,$start+1,-1);//删除已取出数据

                    if(!empty($map)){ //取余剩下的数据
                        $data[]=$map;
                    }

                    if (!empty($data)) {
                        DB::beginTransaction();
                        try {
                            $inser_rst=0;
                            $rst=true;

                            foreach ($data as $k=>$v) {
                                $rst = DB::table('nlsg_live_comment')->insert($v);
                                if ($rst === false) {
                                    DB::rollBack();
                                    $inser_rst=1;
                                    break;
                                }
                            }
                            if($inser_rst==1){
                                //日志写入
                                self::LogIo('livecomment','comment_error','写入失败'.$rst);
                                //回写数据
                                foreach ($list as $k => $val) {
                                    $Redis->rpush($list_key,$val);
                                }
                                return '写入失败';
                            }
                            DB::commit();
                            //日志写入
                            self::LogIo('livecomment','comment','执行成功');
                            return  '写入成功';
                        }catch (\Exception $e) {
                            DB::rollBack();
                            //日志写入
                            self::LogIo('livecomment','comment_error','写入异常'.$e->getMessage());
                            //回写数据
                            foreach ($list as $k => $val) {
                                $Redis->rpush($list_key,$val);
                            }
                            return  '写入异常'.$e->getMessage();
                        }
                    }
                }else{
                    return  '集合为空';
                }


            }else{
                self::LogIo('livecomment','comment_redis','不执行'.$key_minute);
            }
        }catch (\Exception $e){
            self::LogIo('livecomment','comment_redis_error','写入失败'.$e->getMessage());
        }

    }

    //定时入库加入直播间
    public static function CrontabJoinRedis(){

        try {
            $redisConfig = config('database.redis.default');
            $Redis = new Client($redisConfig);
            $Redis->select(0);

            $time=time();
            $key_minute='11LiveConsole:live_join'.date('Hi',$time);
            $flag=$Redis->EXISTS($key_minute);

            $list_key='11LiveConsole:live_join';
            if($flag!=1){ //存在返回1
                $Redis->setex($key_minute,60,1);//1分钟

                $list=$Redis->lrange($list_key,0,-1);// 获取所有数据
                if (!empty($list)) {
                    $data=[];
                    $map = [];
                    $start=0;
                    foreach ($list as $k => $val) {
                        $start=$k;
                        $map[] =  json_decode($val, true);
                        if(($k+1)%10000==0){
                            $data[]=$map;
                            $map=[]; //初始化
                        }
                    }
                    $Redis->ltrim($list_key,$start+1,-1);//删除已取出数据

                    if(!empty($map)){ //取余剩下的数据
                        $data[]=$map;
                    }

                    if (!empty($data)) {
                        DB::beginTransaction();
                        try {
                            $inser_rst=0;
                            $rst=true;

                            foreach ($data as $k=>$v) {
                                $rst = DB::table("nlsg_live_login")->insert($v);
                                if ($rst === false) {
                                    DB::rollBack();
                                    $inser_rst=1;
                                    break;
                                }
                            }
                            if($inser_rst==1){
                                //日志写入
                                self::LogIo('livejoin','join_error','写入失败'.$rst);
                                //回写数据
                                foreach ($list as $k => $val) {
                                    $Redis->rpush($list_key,$val);
                                }
                                return '写入失败';
                            }
                            DB::commit();
                            //日志写入
                            self::LogIo('livejoin','join','执行成功');
                            return  '写入成功';
                        }catch (\Exception $e) {
                            DB::rollBack();
                            //日志写入
                            self::LogIo('livejoin','join_error','写入异常'.$e->getMessage());
                            //回写数据
                            foreach ($list as $k => $val) {
                                $Redis->rpush($list_key,$val);
                            }
                            return  '写入异常'.$e->getMessage();
                        }
                    }
                }else{
                    return  '集合为空';
                }


            }else{
                self::LogIo('livejoin','join_redis','不执行'.$key_minute);
            }
        }catch (\Exception $e){
            self::LogIo('livejoin','join_redis_error','写入失败'.$e->getMessage());
        }

    }

    //定时扫描执行在线人数批量入库
    public static function CrontabOnlineUser($key_name=''){

        $redisConfig = config('database.redis.default');
        $Redis = new Client($redisConfig);
        $Redis->select(0);
        $list_in_flag=0;
        $list_name='11LiveConsole:online_user_list_in';
        if(empty($key_name)){ //执行队列
            $num=$Redis->llen($list_name);
            if($num>0) {
                $list_in_flag=1;
                $key_name = $Redis->lPop($list_name); //获取可执行key
            }else{
                return  '没有可执行队列';
            }
        }
        $list = $Redis->sMembers($key_name);// 获取有序集合 console

        if (!empty($list)) {
            $data=[];
            $map = [];
            $all_login_counts = [];

            foreach ($list as $k => $val) {
                $map[] = $temp_val =  json_decode($val, true);

                $temp_v_key = $temp_val['live_id'].'_'.$temp_val['live_son_flag'];
                if (!isset($all_login_counts[$temp_v_key])){
                    $all_login_counts[$temp_v_key] = [
                        'live_id'=>$temp_val['live_id'],
                        'live_son_flag'=>$temp_val['live_son_flag'],
                        'online_time_str'=>$temp_val['online_time_str'],
                        'counts'=>1
                    ];
                }else{
                    $all_login_counts[$temp_v_key]['counts'] += 1;
                }

                if(($k+1)%10000==0){
                    $data[]=$map;
                    $map=[]; //初始化
                }
            }
            if(!empty($map)){ //取余剩下的数据
                $data[]=$map;
            }
            //模拟失败，检测事务
//            $map1[]=json_decode('{"live_id":"19","user_id":"10","live_son_flag":"0","online_time_str":"2021-09-25 22:53"}',true);
//            $map1[]=json_decode('{"live_id":"19","user_id":"12","live_son_flag":"0","online_time_str":"2021-09-25 22:53"}',true);
//            $map1[]=json_decode('{"live_id":"19","user_id":"111","live_son_flag":"7","online_time_str":"2021-09-25 22:53"}',true);
//            $map1[]=json_decode('{"live_id":"19","user_id":"6","live_son_flag":"0","online_time_str":"2021-09-25 22:55"}',true);
//            $data=$map1;

            if (!empty($data)) {
                DB::beginTransaction();
                try {
                    $inser_rst=0;
                    $rst=true;

                    foreach ($data as $k=>$v) {
                        $rst = DB::table('nlsg_live_online_user')->insert($v);
                        if ($rst === false) {
                            DB::rollBack();
                            if($list_in_flag==1) {
                                $Redis->rpush($list_name, $key_name); //加入队列等待执行
                            }
                            $inser_rst=1;
                            break;
                        }
                    }
                    if($inser_rst==1){
                        //日志写入
                        self::LogIo('liveonlineuser','online_error','写入失败'.$rst);
                        return '写入失败';
                    }
                    if (!empty($all_login_counts) && $inser_rst!=1){ //插入成功写入
                        DB::table('nlsg_live_online_user_counts')->insert($all_login_counts);
                    }
                    DB::commit();
                    $Redis->del($key_name); //执行成功删除
                    //日志写入
                    self::LogIo('liveonlineuser','online','执行成功');

//                    if (!empty($all_login_counts)){
//                        DB::table('nlsg_live_online_user_counts')->insert($all_login_counts);
//                    }

                    return  '写入成功';
                }catch (\Exception $e) {
                    DB::rollBack();
                    if($list_in_flag==1) {
                        $Redis->rpush($list_name, $key_name); //加入队列等待执行
                    }
                    //日志写入
                    self::LogIo('liveonlineuser','online_error','写入异常'.$e->getMessage());
                    return  '写入异常'.$e->getMessage();
                }
            }
        }else{
            return  '集合为空';
        }

    }

    //定时扫描执行在线人数记录缓存
    public static function CrontabOnlineUserRedis(){

        try {
            $redisConfig = config('database.redis.default');
            $Redis = new Client($redisConfig);
            $Redis->select(0);

            $time=time();
            $key_minute='11LiveConsole:OnlineUser'.date('Hi',$time);
            $flag=$Redis->EXISTS($key_minute);

            if($flag!=1){ //存在返回1
                $Redis->setex($key_minute,60,1);//1分钟
                //获取redis
                $live_id_key='live_key_';
                //获取所有在线直播id
                $listRst=$Redis->keys($live_id_key.'*'); //获取多个直播间
                if(!empty($listRst)){
                    $key_name='11LiveConsole:online_user_list_'.date('YmdHi');
                    $es_key_name='es:live_online_es_list'; //es扫描队列
                    $now_time=date('Y-m-d H:i:s');
                    $online_time_str=substr($now_time,0,16);
                    $flag=0;
                    foreach ($listRst as $val){
                        $arr = explode ('_', $val);
                        $live_id=$arr[2];
                        //获取直播间信息
                        $Liveinfo = LiveInfo::query()->where('id',$live_id)->select(['is_begin'])->first();
                        if(!empty($Liveinfo) && !empty($Liveinfo->is_begin)) { //直播中
                            $clients = $Redis->sMembers($live_id_key . $live_id); //获取直播间有序集合
                            if (!empty($clients)) {
                                $flag=1;
                                foreach ($clients as $k => $v) {
                                    $user_arr = explode (',', $v); //ip,user_id,fd,live_son_flag
                                    $OnlineUserArr=['live_id' => $live_id, 'user_id' => $user_arr[1], 'live_son_flag'=>$user_arr[3],'online_time_str'=>$online_time_str];
//                                    $Redis->sAdd ($key_name, json_encode($OnlineUserArr));
                                    $OnlineUserArrData=json_encode($OnlineUserArr);
                                    $Redis->sAdd ($key_name, $OnlineUserArrData);
                                    $Redis->sAdd($es_key_name, $OnlineUserArrData); //加入es队列等待执行
                                }
                            }
                        }
                    }
                    if($flag==1) {
                        //可执行入库列表   没直播间开播也会插入
                        $Redis->rpush('11LiveConsole:online_user_list_in', $key_name); //从队尾插入  先进先出
                        self::LogIo('liveonlineuser','online_redis','执行成功'.$key_minute);
                    }
                }

            }else{
                self::LogIo('liveonlineuser','online_redis','不执行'.$key_minute);
            }
        }catch (\Exception $e){
            self::LogIo('liveonlineuser','online_redis_error','写入失败'.$e->getMessage());
        }

    }

    //抓取手机号地区
    //#*/3 * * * * /usr/bin/curl https://app.v4.api.nlsgapp.com/api/v4/index/phoneRegion
    public static function getPhoneRegion($param=0)
    {

        $redisConfig = config('database.redis.default');
        $Redis = new Client($redisConfig);
        $Redis->select(2);

        $time=time();
        $key_name='11API_PhoneRegion:'.date('md_Hi',$time);
        $flag=$Redis->EXISTS($key_name);
        if($flag==1) { //存在返回1
            if($param!=1) {
                return [];
            }
        }
        $Redis->setex($key_name,60,1);//1分钟

        $host = "https://ali-mobile.showapi.com";
        $path = "/6-1";
        $method = "GET";
        $appcode = "cc703c76da5b4b15bb6fc4aa0c0febf9";
        $headers = array();
        array_push($headers, "Authorization:APPCODE " . $appcode);

        $day_time=date('Y-m-d');

        $redis_user_id_key='11API_PhoneRegion:UserId'.date('md',$time);
        $RedisUserId=$Redis->get($redis_user_id_key);
        if(empty($RedisUserId)){
            //获取当天未识别的id
            $userInfo = User::query()->select(['id'])->where('created_at','>',$day_time)->orderBy('id','asc')->first();
            if(empty($userInfo)){
                return [];
            }
            $RedisUserId=$userInfo['id'];
            $Redis->setex($redis_user_id_key,3600,$userInfo['id']);//1小时
        }

        $query = User::query()->select(['id','phone','nickname','province','city','created_at'])
//            ->where('created_at', '>', '2015-09-01')->where('created_at', '<', '2021-12-01')
//            ->where('created_at', '>', $day_time)
            ->where('id', '>', $RedisUserId)
            ->where('phone','like' , "1%")->where('ref',0)->where('status',1)->where('is_robot',0)->where('province','')->whereRaw(DB::raw('length(phone) =11'))
            ->orderBy('id','asc')->limit(300)
            ;
//        echo $query->toSql().PHP_EOL;
//        $query->dd(); //dd 阻断流程
//        $query->dump();
        $list=$query->get()->toArray() ?: [];
//        echo '<pre>';
//        var_dump($list);
//        exit;
        $dataArr=[];
        if (!empty($list)) {
            $UpUserId=0;
            foreach ($list as $key => $val) {
                //兼容手机号后带用户id情况
                $phone=substr($val['phone'],0,11);

                $querys = "num=$phone";
                $bodys = "";
                $url = $host . $path . "?" . $querys;

                $curl = curl_init();
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
                curl_setopt($curl, CURLOPT_URL, $url);
                curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

                curl_setopt($curl, CURLOPT_FAILONERROR, false);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_HEADER, false); //true
                if (1 == strpos("$" . $host, "https://")) {
                    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
                }

//                var_dump(curl_exec($curl));
                $result = curl_exec($curl);
                $result = json_decode($result, true);
                $time=date('Y-m-d H:i:s');
                if (!empty($result) && $result['showapi_res_body']['ret_code'] == 0) { //返回为json串  查询成功
                    $arr = [
                        'province' => empty($result['showapi_res_body']['prov']) ? '未知' : $result['showapi_res_body']['prov'],
                        'city' => empty($result['showapi_res_body']['city']) ? '未知' : $result['showapi_res_body']['city'],
                    ];
                    $data = [
                        'province' => $arr['province'],
                        'city' => $arr['city'],
                        'updated_at' => $time,
                    ];
                } else {
                    $data = [
                        'province' => $result['showapi_res_body']['remark'],
                        'city' => '-1',
                        'updated_at' => $time,
                    ];
                }
                $UserRst=User::query()->where('id', $val['id'])->update($data);
                $UpUserId=$val['id'];
                if($param==1){
                    $dataArr[$key]=[
                        'userId'=>$UpUserId,
                        'phone'=>$phone,
                        'rst'=>$UserRst,
                        'data'=>$data,
                    ];
                }else{
                    echo ($key+1).':'.$phone.' - '.$UserRst.'<br>';
                }
            }
            if(!empty($UpUserId)) {
                $Redis->setex($redis_user_id_key, 3600, $UpUserId);//1小时
            }
            if($param==1){
                return $dataArr;
            }
        }else{
            //防止当天没有未识别客户，但是免登录客户在当天之前注册，后面绑定手机号，注册时间在今天之前。
            $redis_user_id_one_key='11API_PhoneRegion:UserId_One_'.date('md',$time);
            $RedisUserIdOne=$Redis->get($redis_user_id_one_key);
            if(empty($RedisUserIdOne) || time()>$RedisUserIdOne){

                $userInfo = User::query()->select(['id','phone','nickname','province','city','created_at'])
                    ->where('created_at', '>', '2015-01-01')
                    ->where('phone','like' , "1%")->where('ref',0)->where('status',1)->where('is_robot',0)->where('province','')->whereRaw(DB::raw('length(phone) =11'))
                    ->orderBy('id','asc')->first()
                ;
                $Redis->setex($redis_user_id_one_key,7200,$time+3600);//1小时
                if(empty($userInfo)){
                    return [];
                }
                $Redis->setex($redis_user_id_key,3600,$userInfo['id']-1);//1天

            }

        }

    }

    //日志写入
    public static function LogIo($dir,$name,$content){
        //创建目录
        $dir=storage_path('logs/'.$dir);
        if ( !is_dir ($dir) ) {
            mkdir ($dir, 0777, true);
        }
        if(is_array($content)){
            $content=json_encode($content);
        }
        $time=time();
        $content = date('Y-m-d H:i:s',$time) . '  ' . $content. PHP_EOL;
        $name = $name.date('ymd');
        file_put_contents($dir . "/$name.log",$content,FILE_APPEND|LOCK_EX);
    }

    //直播在线听课记录写入
    /*public  static function LiveOnline()
    {

        $while_flag = true;
        while ($while_flag) {
            $his_table = 'nlsg_live_online_user';
            $onlineList = DB::table($his_table)->select("id", "live_id", "user_id", "live_son_flag", 'online_time', 'online_time_str')
                ->orderBy('id', 'asc')->limit(10000)->get()->toArray();
//            echo '<pre>';
//            var_dump($onlineList);
            if (empty($onlineList)) {
                $while_flag = false;
                LiveConsoleServers::LogIo('liveonlineanalysis', 'online', '数据为空');
                break;
            }

            if (!empty($onlineList)) {
                $data = [];
                $IdArr = [];
                foreach ($onlineList as $k => $val) {
                    $map[] = [
                        "live_id" => $val->live_id,
                        "user_id" => $val->user_id,
                        "live_son_flag" => $val->live_son_flag,
                        "online_time" => $val->online_time,
                        "online_time_str" => $val->online_time_str
                    ];
                    $IdArr[] = $val->id;
                    if(($k+1)%10000==0){
                        $data[]=$map;
                        $map=[]; //初始化
                    }
                }
                if(!empty($map)){ //取余剩下的数据
                    $data[]=$map;
                }
                $inser_rst = 0;
                DB::beginTransaction();
                try {
                    //插入数据
                    foreach ($data as $k => $v) {
                        $rst = DB::table('nlsg_live_online_user20220131')->insert($v);
                        if ($rst === false) {
                            $inser_rst = 1;
                            DB::rollBack();
                            break;
                        }
                    }
                    //删除数据
                    if($inser_rst !=1) {
                        $id_res = DB::table($his_table)->whereIn('id', $IdArr)->delete();
                        if (!$id_res) {
                            $inser_rst = 1;
                            DB::rollBack();
                        }
                    }
                    if ($inser_rst == 1) {
                        LiveConsoleServers::LogIo('liveonlineanalysis', 'online_error', '写入失败' . $rst);
                        $while_flag = false;
                    }else {
                        DB::commit();
                        LiveConsoleServers::LogIo('liveonlineanalysis', 'online', '执行成功');
                    }
                } catch (\Exception $e) {
                    DB::rollBack();
                    $while_flag = false;
                    LiveConsoleServers::LogIo('liveonlineanalysis', 'online_error', '写入异常' . $e->getMessage());
                }

            } else {
                LiveConsoleServers::LogIo('liveonlineanalysis', 'online', '数据为空');
            }

        }

    }*/

    //处理重复数据
    /*public  static function LiveOnline()
    {

        $while_flag = true;
        while ($while_flag) {
            $his_table = 'nlsg_live_online_user';
            $onlineList = DB::table($his_table)->select("id", "live_id", "user_id", "live_son_flag", 'online_time', 'online_time_str')->where('id','<=',30000000)
                ->orderBy('id', 'asc')->limit(10000)->get()->toArray();
//            echo '<pre>';
//            var_dump($onlineList);
            if (empty($onlineList)) {
                $while_flag = false;
                LiveConsoleServers::LogIo('liveonlineanalysis', 'online', '数据为空');
                break;
            }

            foreach ($onlineList as $k => $val) {
                $map = [
                    "live_id" => $val->live_id,
                    "user_id" => $val->user_id,
                    "live_son_flag" => $val->live_son_flag,
                    "online_time" => $val->online_time,
                    "online_time_str" => $val->online_time_str
                ];

                //插入数据
                try {
                    $rst = DB::table('nlsg_live_online_user22')->insert($map);
                } catch (\Exception $e) {
                    $rst=DB::table('nlsg_live_online_user22_bf')->insert($map);; //标记重复数据
                    LiveConsoleServers::LogIo('liveonlineanalysis', 'online_error', '写入异常' . $e->getMessage());
                }
                if($rst) {
                    DB::table($his_table)->where('id', $val->id)->delete();
                }
            }

            LiveConsoleServers::LogIo('liveonlineanalysis', 'online', '执行成功');

        }

    }*/

    //加入执行队列
    public static function OnlineRedisPush(){

        $redisConfig = config('database.redis.default');
        $Redis = new Client($redisConfig);
        $Redis->select(3);
        $list_name='online_id_list_in';
        $num=$Redis->llen($list_name);
        if($num<=0) {

            $start_time=date("Y-m-d H:i:s",time());
            $Redis->setex('id_start',60*60*48,$start_time);

            $while_flag = true;
            while ($while_flag) {

                $id_key_name = 'key_end_id';
                $end_id = $Redis->get($id_key_name);
                if (empty($end_id)) {
                    $end_id = 1;
                }
                if($end_id>=101000000){
                    $while_flag = false;
                    break;
                }
                $his_table = 'nlsg_live_online_user';
                $onlineList = DB::table($his_table)->select("id")->where('id', '>=', $end_id)
                    ->orderBy('id', 'asc')->limit(100000)->get()->toArray();
                if (!empty($onlineList)) {
                    $num = count($onlineList);
                    $id_arr = [];
                    foreach ($onlineList as $k => $val) {
                        $id_arr[] = $val->id;
                        if (($k + 1) % 100 == 0) {
                            $Redis->rpush('online_id_list_in', $id_arr); //从队尾插入  先进先出
                            $id_arr = [];
                        }
                        if (($k + 1) == $num) {
                            $Redis->setex($id_key_name, 60 * 60 * 48, $val->id); //设置最后一个元素id
                        }
                    }
                    if (!empty($id_arr)) { //处理剩余数据
                        $Redis->rpush('online_id_list_in', $id_arr); //从队尾插入  先进先出
                    }
                }
            }

            $end_time=date("Y-m-d H:i:s",time());
            $Redis->setex('id_end',60*60*48,$end_time);
            echo '执行成功';
        }else{
            echo '任务执行成功';
        }
    }

    //处理重复数据
    public  static function LiveOnline($flag=1)
    {

        $redisConfig = config('database.redis.default');
        $Redis = new Client($redisConfig);
        $Redis->select(3);
        $list_name='online_id_list_in';

        $start_time=date("Y-m-d H:i:s",time());
        $Redis->setex('lpop_start_15_'.$flag,60*60*48,$start_time);

        $while_flag = true;
        while ($while_flag) {

            $id = $Redis->lPop($list_name); //获取队列执行id
            if(empty($id)){
                $while_flag = false;
                LiveConsoleServers::LogIo('liveonlineanalysis', 'online', '数据为空');
                break;
            }
            $his_table = 'nlsg_live_online_user';
            $onlineObj = DB::table($his_table)->select("id", "live_id", "user_id", "live_son_flag", 'online_time', 'online_time_str')
                          ->where('id',$id)->first();
            if (!empty($onlineObj)) {
                $map = [
                    "id" => $onlineObj->id,
                    "live_id" => $onlineObj->live_id,
                    "user_id" => $onlineObj->user_id,
                    "online_time_str" => $onlineObj->online_time_str,
                    "live_son_flag" => $onlineObj->live_son_flag,
                    "online_time" => $onlineObj->online_time
                ];
                try {
                    $rst = DB::table('nlsg_live_online_user22')->insert($map); //插入数据
                } catch (\Exception $e) {
                    $message=$e->getMessage();
                    if(strpos($message,'live_user_online_flag') !== false){ //判断报错信息是否包含唯一索引
                        $rst = DB::table('nlsg_live_online_user22_bf')->insert($map);; //标记重复数据
                        LiveConsoleServers::LogIo('liveonlineanalysis', 'online_error_unique_', '抛出异常' . $message);
                    }else{
                        $Redis->rpush($list_name, $id); //未处理恢复队尾
                        $rst=false;
                        LiveConsoleServers::LogIo('liveonlineanalysis', 'online_error_key_', '抛出异常' . $message);
                    }
                }
                if ($rst) {
                    DB::table($his_table)->where('id', $onlineObj->id)->delete();
                }
            }

        }
        $start_time=date("Y-m-d H:i:s",time());
        $Redis->setex('lpop_end_15_'.$flag,60*60*48,$start_time);
        LiveConsoleServers::LogIo('liveonlineanalysis', 'online', '执行成功---结束循环');
    }

    public function clearLiveOnlineUser()
    {

        $redisConfig = config('database.redis.default');
        $Redis = new Client($redisConfig);
        $Redis->select(3);
        $key_begin_id = 'key_begin_id';
        $begin_id = $Redis->get($key_begin_id);
        if (empty($begin_id)) {
            $begin_id = 1;
        }

        for ($i = 1; $i < 100; $i++) {
            $size     = 10000;
            $end_id   = $begin_id + $size;

            $list = DB::table('nlsg_live_online_user')
                ->where('id', '>=', $begin_id)
                ->where('id', '<', $end_id)
                ->get();

            if ($list->isEmpty()) {
                exit('没数据了');
            }

            $list = $list->toArray();
            foreach ($list as &$v) {
                unset($v->id);
                $v = (array)$v;
            }
            //批量插入排除重复数据
            $rst=DB::table('nlsg_live_online_user22')->insertOrIgnore($list);
            if($rst){
                $Redis->setex($key_begin_id,60*60*48,$end_id);
            }

        }

    }

}
