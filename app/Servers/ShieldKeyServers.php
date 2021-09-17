<?php

namespace App\Servers;

use App\Models\ShieldKey;
use Illuminate\Support\Facades\DB;

class ShieldKeyServers
{
    public function list($params, $user_id)
    {
        $size = $params['size'] ?? 10;
        $key = $params['name'] ?? '';
        $query = ShieldKey::query();
        if (!empty($key)) {
            $query->where('name', 'like', "%$key%");
        }
        return $query->where('status', '=', 1)->paginate($size);
    }

    public function add($params, $user_id)
    {
        $key = $params['name'] ?? '';
        if (empty($key)) {
            return ['code' => false, 'msg' => '不能为空'];
        }

        $reg = "/[[:punct:]]/i";
        $key = preg_replace($reg, ' ', $key);
        $key = array_unique(array_filter(explode(' ', $key)));

        $key_array = [];
        foreach ($key as $v) {
            $key_array[] = [
                'name' => $v,
                'status' => 1
            ];
        }

        $res = DB::table('nlsg_shield_key')->insert($key_array);

        if ($res) {
            return ['code' => true, 'msg' => '成功'];
        }
        return ['code' => false, 'msg' => '失败'];

    }

    public function changeStatus($params, $user_id)
    {
        $id = $params['id'] ?? 0;
        if (empty($id)) {
            return ['code' => false, 'msg' => 'id错误'];
        }
        $check = ShieldKey::query()->where('id', '=', $id)->first();
        $flag = $params['flag'] ?? '';
        switch ($flag) {
            case 'del':
                $check->status = 0;
                break;
            case 'on':
                $check->status = 1;
                break;
            default:
                return ['code' => false, 'msg' => '参数错误'];
        }
        $res = $check->save();
        if ($res) {
            return ['code' => true, 'msg' => '成功'];
        }
        return ['code' => false, 'msg' => '失败'];

    }
}
