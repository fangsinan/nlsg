<?php
namespace App\Servers;

use App\Models\User;
use Predis\Client;

class PhoneRegionServers
{

    //抓取手机号地区
    public static function getPhoneRegion()
    {

        $redisConfig = config('database.redis.default');
        $Redis = new Client($redisConfig);
        $Redis->select(0);

        $time=time();
        $key_name='1111PhoneRegion'.date('YmdHi',$time);
        $flag=$Redis->EXISTS($key_name);
        if($flag==1) { //存在返回1
            return ;
        }
        $Redis->setex($key_name,60,1);//2分钟

        $host = "https://ali-mobile.showapi.com";
        $path = "/6-1";
        $method = "GET";
        $appcode = "cc703c76da5b4b15bb6fc4aa0c0febf9";
        $headers = array();
        array_push($headers, "Authorization:APPCODE " . $appcode);

        $query = User::query()->select(['id','phone','nickname','province','city','created_at'])
//            ->where('created_at', '>', '2015-09-01')->where('created_at', '<', '2021-12-01')
            ->where('created_at', '>', '2021-10-01')
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

}
