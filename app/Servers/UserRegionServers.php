<?php

namespace App\Servers;

use App\Models\UserPhoneRegion;
use Illuminate\Support\Facades\DB;
use Predis\Client;
use Illuminate\Support\Facades\Http;

class UserRegionServers
{
    const LIST_KEY = 'getUserRegionList';
    const APP_KEY  = 'cc703c76da5b4b15bb6fc4aa0c0febf9';

    protected $rc;

    public function __construct()
    {
        $this->rc = $this->getRedis();
    }

    public function getRedis(): Client
    {
        $redisConfig = config('database.redis.default');
        $Redis       = new Client($redisConfig);
        $Redis->select(0);
        return $Redis;
    }

    public function toRun()
    {
        $end = time() + 240;

        while (true) {
            $this->toDoJob();
            if (time() > $end) {
                break;
            }
        }
    }

    public function toDoJob()
    {
        $str = $this->getJob();
        if (empty($str)) {
            echo '没有数据了';
            return;
        }

        $temp_str = explode('@', $str);
        $phone    = $temp_str[1];

        $res = $this->api($phone);

        if (!$res['code']) {
            $this->returnJob($str);
        }

        UserPhoneRegion::query()
            ->insertOrIgnore([
                                 'user_id'   => $temp_str[0],
                                 'phone'     => $phone,
                                 'prov'      => $res['data']['prov'] ?? '',
                                 'city'      => $res['data']['city'] ?? '',
                                 'area_code' => $res['data']['areaCode'] ?? '',
                                 'post_code' => $res['data']['postCode'] ?? '',
                                 'type'      => $res['data']['type'] ?? 0
                             ]);
    }


    private function getJob(): string
    {
        return $this->rc->spop(self::LIST_KEY) ?? '';
    }

    private function returnJob($string)
    {
        $this->rc->sadd(self::LIST_KEY, [$string]);
    }

    public function toAddList()
    {
        $job_counts = $this->rc->scard(self::LIST_KEY);
        if ($job_counts > 10000) {
            return;
        }

        $sql = "SELECT CONCAT(u.id,'@',u.phone) as str from nlsg_user as u
where u.phone like '1%' and LENGTH(u.phone) = 11
and u.`status` = 1 and u.ref = 0 and u.is_robot = 0
and not EXISTS (SELECT * from nlsg_user_phone_region where user_id = u.id)
LIMIT 5000";

        $list = DB::select($sql);

        if ($list) {
            $this->rc->sadd(self::LIST_KEY, array_column($list, 'str'));
        }
    }

    public function api($phone)
    {
        $host      = "https://ali-mobile.showapi.com";
        $path      = "/6-1";
        $method    = "GET";
        $headers   = array();
        $headers[] = "Authorization:APPCODE " . self::APP_KEY;
        $querys    = "num=" . $phone;
        $url       = $host . $path . "?" . $querys;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        if (1 == strpos("$" . $host, "https://")) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }

        $res = curl_exec($curl);
        $res = json_decode($res, true);

        $ret_code = $res['showapi_res_body']['ret_code'] ?? 1;

        if ($ret_code === 0) {
            return ['code' => true, 'msg' => '', 'data' => $res['showapi_res_body']];
        }

        return ['code' => false, 'msg' => '错误', 'data' => []];

    }


}
