<?php

namespace App\Servers\V5;


use Illuminate\Support\Facades\DB;

class UserServers
{
    public function settings($params, $uid): array
    {
        $flag  = $params['flag'] ?? '';
        $value = $params['value'] ?? '';

        if ($flag === 'ad_switch') {
            $value = (int)$value;
            DB::table('nlsg_user')->where('id', $uid)->update(['ad_switch' => $value === 1 ? 1 : 2]);
            return ['code' => true, 'msg' => '成功'];
        } else {
            return ['code' => false, 'msg' => '参数错误'];
        }

    }
}
