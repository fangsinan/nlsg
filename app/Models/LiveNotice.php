<?php
/**
 * Created by PhpStorm.
 * User: nlsg2017
 * Date: 2019/6/25
 * Time: 2:04 PM
 */


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class LiveNotice extends Base
{
    protected $table = 'nlsg_live_notice';

    public function add($params, $user_id)
    {
        $now_date = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' +1 minutes'));
        $live_id = $params['live_id'] ?? 0;
        $live_info_id = $params['live_info_id'] ?? 0;
        $type = $params['type'] ?? 0;
        $content = $params['content'] ?? '';
        $content_type = $params['content_type'] ?? 1;
        $length = $params['length'] ?? 0;

        if (empty($params['send_at'] ?? '')) {
            $send_at = $now_date;
        } else {
            $send_at = date('Y-m-d H:i:s', strtotime($params['send_at']));
        }

        if (empty($live_id) || empty($live_info_id) || empty($content)) {
            return ['code' => false, 'msg' => '参数错误'];
        }

        if (!in_array($type, [1, 2])) {
            return ['code' => false, 'msg' => '类型错误'];
        }

        if ($type == 1) {
            //公告
            if (empty($length) || !is_numeric($length)) {
                return ['code' => false, 'msg' => '参数错误'];
            }
            if (empty($send_at) || strtotime($send_at) === false || $send_at < $now_date) {
                return ['code' => false, 'msg' => '时间错误'];
            }
        }

        $length_temp = $length + 5;

        $check_live_info = LiveInfo::whereId($live_info_id)->where('status', '=', 1)
            ->select(['id', 'is_begin', 'is_finish', 'user_id'])->first();

        if (empty($check_live_info)) {
            return ['code' => false, 'msg' => '直播不存在'];
        }
        /* 4月18号 电视直播 去除直播开始结束校验
        if ($check_live_info->is_begin == 0) {
            return ['code' => false, 'msg' => '直播未开始'];
        }

        if ($check_live_info->is_finish == 1) {
            return ['code' => false, 'msg' => '直播已结束'];
        }
        */
        $check_is_admin = LiveConsole::isAdmininLive($user_id, $live_id);
        if ($check_is_admin === false) {
            return ['code' => false, 'msg' => '需要管理员权限'];
        }

        if ($type == 1) {

//            $check_push = LiveNotice::where('live_id', '=', $live_id)
//                ->where('live_info_id', '=', $live_info_id)
//                ->where('is_del', '=', 0)
//                //->whereRaw("'$send_at' BETWEEN send_at and
//                // FROM_UNIXTIME( UNIX_TIMESTAMP( send_at) + CEILING( 80 / 60 ) * 60, '%Y-%m-%d %H:%i:00' ) ")
//                ->whereBetween('send_at', [
//                    date('Y-m-d H:i:s', strtotime("$send_at -$length_temp second")),
//                    date('Y-m-d H:i:s', strtotime("$send_at +$length_temp second"))
//                ])
//                ->select(['id'])
//                ->first();

$send_timestamp = strtotime($send_at) + 13;

            $check_sql = "SELECT * from (
SELECT
	id,
	user_id,
	length,
	send_at,
	UNIX_TIMESTAMP( send_at )+ length as s_end,
	UNIX_TIMESTAMP( send_at ) as s_begin
FROM
	nlsg_live_notice
WHERE
	live_id = $live_id
	AND live_info_id = $live_info_id and is_send = 1
	AND is_del = 0 ) as a where $send_timestamp > s_begin and $send_timestamp < s_end limit 1";
            $check_push = DB::select($check_sql);

            //是否对直播公告发送做出1分钟限制
            $check_send_time = ConfigModel::getData(23);
            if ($check_send_time) {
                if (empty($params['id'] ?? 0)) {
                    if (!empty($check_push)) {
                        return ['code' => false, 'msg' => '所选时间已有推送内容,请更换时间.'];
                    }
                } else {
                    if ($params['id'] != $check_push->id) {
                        return ['code' => false, 'msg' => '所选时间已有推送内容,请更换时间.'];
                    }
                }
            }
        }

        if (empty($params['id'] ?? 0)) {
            $model = new LiveNotice();
        } else {
            $model = LiveNotice::whereId($params['id'])->where('user_id', '=', $user_id)->first();
            if (empty($model)) {
                return ['code' => false, 'msg' => '需本人修改'];
            }
            if ($type == 1) {
                if ($model->is_done == 1) {
                    return ['code' => false, 'msg' => '已经发布,无法修改'];
                }
            }
        }

        $content = str_replace(
            "http://nlsgapp.oss-cn-beijing.aliyuncs.com",
            "https://image.nlsgapp.com",
            $content);
        $model->type = $type;
        $model->user_id = $user_id;
        $model->live_id = $live_id;
        $model->live_info_id = $live_info_id;
        $model->content = $content;
        $model->content_type = $content_type;
        $model->length = $length;
        $model->is_send = 1;
        $model->send_at = date('Y-m-d H:i:s',strtotime($send_at) + 13);
        $model->is_done = 0;

        $res = $model->save();

        if ($res) {
            return ['code' => true, 'msg' => '添加成功'];
        } else {
            return ['code' => false, 'msg' => '失败,请重试'];
        }

    }

    public function list($params, $user_id=0)
    {
        $live_id = $params['live_id'] ?? 0;
        $live_info_id = $params['live_info_id'] ?? 0;
        $page = (int)($params['page'] ?? 1);
        $size = (int)($params['size'] ?? 10);
        $type = $params['type'] ?? 1;
        if (!$user_id){
            $user_id = 0;
        }
        if (empty($live_id) || empty($live_info_id) || $live_id === 'undefined') {
            return ['code' => false, 'msg' => '参数错误'];
        }
//        $check_is_admin = LiveConsole::isAdmininLive($user_id, $live_id);
//        if ($check_is_admin === false) {
//            return ['code' => false, 'msg' => '需要管理员权限'];
//        }

        if (empty($params['id'] ?? 0)) {
            $query = self::query();
        } else {
            $query = self::whereId($params['id']);
        }

        $query->where('live_id', '=', $live_id)
            ->where('live_info_id', '=', $live_info_id)
            ->where('type', '=', $type)
            ->where('is_del', '=', 0)
            ->select([
                'id', 'live_id', 'live_info_id', 'content', 'length', 'send_at',
                'is_send', 'is_done', 'done_at', 'user_id', 'type','content_type',
                DB::raw("if(user_id=$user_id,1,0) as is_self")
            ])
            ->with(['userInfo']);

        switch ($params['ob'] ?? '') {
            case 't_asc':
                $query->orderBy('id', 'asc');
                break;
            default:
                $query->orderBy('id', 'desc');
        }

        $list = $query->limit($size)
            ->offset(($page - 1) * $size)
            ->get();

        return $list;

    }

    public function userInfo()
    {
        return $this->hasOne(User::class, 'id', 'user_id')
            ->select(['id', 'phone', 'nickname']);
    }

    public function changeState($params, $user_id)
    {
        $id = $params['id'] ?? 0;
        if (empty($id)) {
            return ['code' => false, 'msg' => '参数错误'];
        }
        $check = self::whereId($id)
            ->where('is_del', '=', 0)
            ->select(['id', 'is_send', 'type','user_id'])
            ->first();

        if (empty($check)){
            return ['code' => false, 'msg' => 'id错误'];
        }

        if (intval($check->user_id) !== intval($user_id)){
            return ['code'=>false,'msg'=>'非本人创建,不可删除.'];
        }

        switch ($params['flag'] ?? '') {
            case 'off':
                if ($check->is_done == 1) {
                    return ['code' => false, 'msg' => '已经推送,无法取消'];
                }
                $check->is_send = 0;
                break;
            case 'del':
                if ($check->type == 1) {
                    if ($check->is_done = 0 && $check->is_send == 1) {
                        return ['code' => false, 'msg' => '取消之后删除'];
                    }
                }
                $check->is_done = 0;
                $check->is_del = 1;
                break;
            default:
                return ['code' => false, 'msg' => '参数错误'];
        }

        $res = $check->save();
        if ($res) {
            return ['code' => true, 'msg' => '成功'];
        } else {
            return ['code' => false, 'msg' => '失败'];
        }
    }
}
