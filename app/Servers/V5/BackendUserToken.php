<?php


namespace App\Servers\V5;


use Illuminate\Support\Facades\Cache;

class BackendUserToken
{
    const KeyPre = 'AdminToken:';
    const TokenLife = 120;

    public static function setToken(int $admin_id, string $token)
    {
        Cache::put(self::KeyPre . $admin_id, $token, self::TokenLife);
    }

    public static function refreshToken(int $admin_id)
    {
        $token = self::getToken($admin_id);
        if ($token) {
            self::setToken($admin_id, $token);
        }
    }

    public static function getToken(int $admin_id)
    {
        if (!$admin_id) {
            return '';
        }
        return Cache::get(self::KeyPre . $admin_id);
    }

    public static function errLockSet(int $admin_id)
    {
        $err_token_life = strtotime(date('Y-m-d 00:01:00', strtotime('+1 days'))) - time();

        $key_name = 'AdminLoginErr:' . date('Ymd') . '_' . $admin_id;

        $get = Cache::get($key_name);

        if ($get) {
            Cache::increment($key_name);
        } else {
            Cache::put($key_name, 1, $err_token_life);
        }

    }

    public static function errLockCheck(int $admin_id)
    {
        $key_name = 'AdminLoginErr:' . date('Ymd') . '_' . $admin_id;
        return Cache::get($key_name);
    }

    public static function passwordCheck(string $pwd): array
    {
        if (strlen($pwd) < 9) {
            return ['code' => false, 'msg' => '密码长度需大于9位'];
        }

        $source = 0;

        if (preg_match('/[A-Z]/', $pwd)) {
            $source++;
        }

        if (preg_match('/[a-z]/', $pwd)) {
            $source++;
        }

        if (preg_match('/[0-9]/', $pwd)) {
            $source++;
        }

        if (preg_match('/[!@#$%^&*().\-_]/', $pwd)) {
            $source++;
        }

        if ($source < 3) {
            return ['code' => false, 'msg' => '密码过于简单,请重新设置'];
        }

        return ['code' => true, 'msg' => '成功'];

    }

}
