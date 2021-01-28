<?php
/**
 * Created by PhpStorm.
 * User: nlsg2017
 * Date: 2019/6/25
 * Time: 11:36 AM
 */

namespace App\Models;

use App\Servers\JobServers;


class LiveForbiddenWords extends Base
{
    protected $table = 'nlsg_live_forbidden_words';

    public function add($params, $user_id)
    {
        $live_id = $params['live_id'] ?? 0;
        $live_info_id = $params['live_info_id'] ?? 0;
        $flag = $params['flag'] ?? 0;
        $obj_id = $params['user_id'] ?? 0;

        if (!empty($obj_id) && !empty($user_id)) {
            if ($obj_id == $user_id) {
                return ['code' => false, 'msg' => '对象是管理员'];
            }
        }

        if (empty($live_id) || empty($live_info_id)) {
            return ['code' => false, 'msg' => '参数错误'];
        }
        if (!in_array($flag, ['on', 'off'])) {
            return ['code' => false, 'msg' => '参数错误'];
        }

        $check_is_admin = LiveConsole::isAdmininLive($user_id, $live_id);
        if ($check_is_admin === false) {
            return ['code' => false, 'msg' => '需要管理员权限'];
        }

        $check = self::where('live_id', '=', $live_id)
            ->where('live_info_id', '=', $live_info_id)
            ->where('user_id', '=', $obj_id)
            ->first();

        if (empty($check)) {
            $check = new self();
        }

        $check->user_id = $obj_id;
        $check->admin_id = $user_id;
        $check->live_id = $live_id;
        $check->live_info_id = $live_info_id;
        $check->forbid_at = date('Y-m-d H:i:s');
        if ($flag == 'on') {
            $check->is_forbid = 1;
        } else {
            $check->is_forbid = 2;
        }
        if ($obj_id) {
            $check->length = 300;
        } else {
            $check->length = 0;
        }

        $res = $check->save();
        if ($res === false) {
            return ['code' => false, 'msg' => '失败'];
        } else {
            JobServers::pushToSocket($live_id, $live_info_id, 9);
            return ['code' => true, 'msg' => '成功'];
        }

    }

}
