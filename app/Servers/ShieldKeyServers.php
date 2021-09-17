<?php

namespace App\Servers;

use App\Models\ShieldKey;

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
        $max_len = 30;
        $key = $params['name'] ?? '';
        if (empty($key)) {
            return ['code' => false, 'msg' => '不能为空'];
        }

        $reg = "/[[:punct:]]/i";
        $key = preg_replace($reg, ' ', $key);
        $key = array_unique(array_filter(explode(' ', $key)));

//        DB::beginTransaction();
//        $res_flag = true;
//        $key_array = [];
        $error_list = [];

        foreach ($key as $v) {

//            $key_array[] = [
//                'name' => $v,
//                'status' => 1
//            ];
            if (mb_strlen($v) > $max_len) {
//                DB::rollBack();
//                return ['code' => false, 'msg' => '单个关键字长度不能大于' . $max_len];
                $error_list[] = $v . ' 单个关键字长度不能大于' . $max_len;
            }

            $temp_res = ShieldKey::updateOrCreate([
                'name' => $v,
            ], [
                'status' => 1
            ]);
            if ($temp_res === false) {
                $error_list[] = $v . ' 添加失败';
            }

        }
        return ['code' => true, 'msg' => '成功', 'error' => $error_list];


//        if ($res_flag) {
//            DB::commit();
//            return ['code' => true, 'msg' => '成功'];
//        }
//
//        DB::rollBack();
//        return ['code' => false, 'msg' => '失败'];

//        $res = DB::table('nlsg_shield_key')->insert($key_array);
//
//        if ($res) {
//            return ['code' => true, 'msg' => '成功'];
//        }
//        return ['code' => false, 'msg' => '失败'];

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
