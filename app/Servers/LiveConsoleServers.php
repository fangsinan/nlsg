<?php
namespace App\Servers;

use App\Models\LiveInfo;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Predis\Client;

class LiveConsoleServers
{

    //定时入库加入直播间
    public static function CrontabJoinRedis(){

        try {
            $redisConfig = config('database.redis.default');
            $Redis = new Client($redisConfig);
            $Redis->select(0);

            $time=time();
            $key_minute='111_live_join'.date('Hi',$time);
            $flag=$Redis->EXISTS($key_minute);

            $list_key='111_live_join';
            if($flag!=1){ //存在返回1
                $Redis->setex($key_minute,60,1);//1分钟

                $list = $Redis->sMembers($list_key);// 获取有序集合 console
                if (!empty($list)) {
                    $data=[];
                    $map = [];
                    foreach ($list as $k => $val) {
                        $map[] =  json_decode($val, true);
                        if(($k+1)%10000==0){
                            $data[]=$map;
                            $map=[]; //初始化
                        }
                    }
                    if(!empty($map)){ //取余剩下的数据
                        $data[]=$map;
                    }

                    if (!empty($data)) {
                        DB::beginTransaction();
                        try {
                            $inser_rst=0;
                            $rst=true;

                            foreach ($data as $k=>$v) {
                                $rst = DB::table('nlsg_live_login')->insert($v);
                                if ($rst === false) {
                                    DB::rollBack();
                                    $inser_rst=1;
                                    break;
                                }
                            }
                            if($inser_rst==1){
                                //日志写入
                                self::LogIo('livejoin','online_error','写入失败'.$rst);
                                //回写数据
                                foreach ($list as $k => $val) {
                                    $Redis->sadd($list_key,$val);
                                }
                                return '写入失败';
                            }
                            DB::commit();
                            //日志写入
                            self::LogIo('livejoin','online','执行成功');
                            return  '写入成功';
                        }catch (\Exception $e) {
                            DB::rollBack();
                            //日志写入
                            self::LogIo('livejoin','join_error','写入异常'.$e->getMessage());
                            //回写数据
                            foreach ($list as $k => $val) {
                                $Redis->sadd($list_key,$val);
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
        $list_name='111online_user_list_in';
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
                    DB::commit();
                    $Redis->del($key_name); //执行成功删除
                    //日志写入
                    self::LogIo('liveonlineuser','online','执行成功');

                    if (!empty($all_login_counts)){
                        DB::table('nlsg_live_online_user_counts')->insert($all_login_counts);
                    }

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
            $key_minute='111OnlineUser'.date('Hi',$time);
            $flag=$Redis->EXISTS($key_minute);

            if($flag!=1){ //存在返回1
                $Redis->setex($key_minute,60,1);//1分钟
                //获取redis
                $live_id_key='live_key_';
                //获取所有在线直播id
                $listRst=$Redis->keys($live_id_key.'*'); //获取多个直播间
                if(!empty($listRst)){
                    $key_name='111online_user_list_'.date('YmdHi');
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
                                    $Redis->sAdd ($key_name, json_encode($OnlineUserArr));
                                }
                            }
                        }
                    }
                    if($flag==1) {
                        //可执行入库列表   没直播间开播也会插入
                        $Redis->rpush('111online_user_list_in', $key_name); //从队尾插入  先进先出
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
    public static function getPhoneRegion()
    {

        $redisConfig = config('database.redis.default');
        $Redis = new Client($redisConfig);
        $Redis->select(0);

        $time=time();
        $key_name='111PhoneRegion'.date('Hi',$time);
        $flag=$Redis->EXISTS($key_name);
        if($flag==1) { //存在返回1
            return ;
        }
        $Redis->setex($key_name,60,1);//1分钟

        $host = "https://ali-mobile.showapi.com";
        $path = "/6-1";
        $method = "GET";
        $appcode = "cc703c76da5b4b15bb6fc4aa0c0febf9";
        $headers = array();
        array_push($headers, "Authorization:APPCODE " . $appcode);

        $day_time=date('Y-m-d');
        $query = User::query()->select(['id','phone','nickname','province','city','created_at'])
//            ->where('created_at', '>', '2015-09-01')->where('created_at', '<', '2021-12-01')
            ->where('created_at', '>', $day_time)
            ->where('phone','like' , "1%")->where('ref',0)->where('province','')
            ->orderBy('id','asc')->limit(300)
            ;
//        echo $query->toSql().PHP_EOL;
//        $query->dd(); //dd 阻断流程
//        $query->dump();
        $list=$query->get()->toArray() ?: [];
        echo '<pre>';
//        var_dump($list);
//        exit;
        if (!empty($list)) {
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
                echo ($key+1).':'.$phone.' - '.$UserRst.'<br>';
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

}
