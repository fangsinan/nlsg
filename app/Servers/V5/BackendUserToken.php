<?php


namespace App\Servers\V5;


use App\Models\BackendUser;
use Predis\Client;

class BackendUserToken
{
    const KeyPre = 'AdminToken:';
    const TokenLife = 1800;//正式半小时

    public static function getClient(): Client
    {
        $redisConfig = config('database.redis.default');
        $redis       = new Client($redisConfig);
        $redis->select(8);
        return $redis;
    }

    public static function setToken(int $admin_id, string $token)
    {
        $check_admin = BackendUser::query()->where('id', '=', $admin_id)->select(['id', 'long_token'])->first();
        if ($check_admin->long_token > 0) {
            $token_life = ($check_admin->long_token > 7 ? 7 : $check_admin->long_token) * 86400;
        } else {
            $token_life = self::TokenLife;
        }
        self::getClient()->setex(
            self::KeyPre . $admin_id,
            $token_life,
            $token
        );
    }

    public static function delToken(int $admin)
    {
        self::getClient()->del([self::KeyPre . $admin]);
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
        return self::getClient()->get(self::KeyPre . $admin_id);
    }

    public static function errLockSet(int $admin_id)
    {
        $err_token_life = strtotime(date('Y-m-d 00:01:00', strtotime('+1 days'))) - time();

        $key_name = 'AdminLoginErr:' . date('Ymd') . '_' . $admin_id;

        $client = self::getClient();

        $get = $client->get($key_name);

        if ($get) {
            $client->incr($key_name);
        } else {
            $client->setex($key_name, $err_token_life, 1);
        }

    }

    public static function errLockCheck(int $admin_id)
    {
        $key_name = 'AdminLoginErr:' . date('Ymd') . '_' . $admin_id;
        return self::getClient()->get($key_name);
    }

    public static function errLockClean(int $admin_id)
    {
        $key_name = 'AdminLoginErr:' . date('Ymd') . '_' . $admin_id;
        self::getClient()->del([$key_name]);
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
