<?php


namespace App\Servers\V5;


use App\Models\Subscribe;
use App\Models\User;
use App\Servers\MsgConsoleServers;

class SubCheckServers
{
    public function relationTypeList(): array
    {
        return [
            1 => ['value' => '课程', 'key' => 1],
            2 => ['value' => '讲座', 'key' => 2],
            3 => ['value' => '直播', 'key' => 3],
            4 => ['value' => '训练营分期', 'key' => 4],
        ];
    }

    public function relationInfoList($type)
    {
        $mcs = new MsgConsoleServers();
        switch ((int)$type) {
            case 1:
                $res = $mcs->getWorksList();
                break;
            case 2:
                $res = $mcs->getColumnList();
                break;
            case 3:
                $res = $mcs->getLiveListByDate();
                break;
            case 4:
                $res = $mcs->getCampInfoList();
                break;
            default:
                $res = [];
        }

        return $res;

    }

    public function toCheck($params): array
    {
        $type        = (int)($params['type'] ?? 0);
        $relation_id = $params['relation_id'] ?? 0;
        $phone_list  = $params['phone_list'] ?? '';
        $now_date    = date('Y-m-d H:i:s');

        if (empty($type) || empty($relation_id || empty($phone_list) || in_array($type, [1, 2, 3, 4]))) {
            return ['code' => false, 'msg' => '参数错误' . __LINE__];
        }

        $phone_list = preg_replace('/[^0-9]/i', ',', $phone_list);
        $phone_list = array_unique(array_filter(explode(',', $phone_list)));
        if (empty($phone_list)) {
            return ['code' => false, 'msg' => '参数错误' . __LINE__];
        }

        $phone_list = array_chunk($phone_list, 100);
        //2作品 3直播  6讲座  7训练营
        $not_sub_list = [];

        foreach ($phone_list as $v) {

            $query = Subscribe::query()
                ->with([
                    'user:id,phone'
                ]);

            $query->where('status', '=', 1)
                ->where('is_del', '=', 0)
                ->whereHas('user', function ($q) use ($v) {
                    $q->whereIn('phone', $v);
                });

            switch ($type) {
                case 1:
                    $query->where('type', '=', 2)->where('end_time', '>', $now_date);
                    break;
                case 2:
                    $query->where('type', '=', 6)->where('end_time', '>', $now_date);
                    break;
                case 3:
                    $query->where('type', '=', 3);
                    break;
                case 4:
                    $query->where('type', '=', 7)->where('created_at', '>', '2022-06-01 00:00:00');
                    break;
            }

            $query->where('relation_id', '=', $relation_id);
            $query->select(['id', 'user_id']);

            $temp_res = $query->get();

            if ($temp_res->isEmpty()) {
                $not_sub_list = array_merge($not_sub_list, $v);
                continue;
            }

            $temp_v = array_flip($v);
            foreach ($temp_res as $tr_v) {
                if (in_array($tr_v->user['phone'], $v)) {
                    unset($temp_v[$tr_v->user['phone']]);
                }
            }
            $temp_v       = array_flip($temp_v);
            $not_sub_list = array_merge($not_sub_list, $temp_v);
        }

        return $not_sub_list;

    }

    public function toCheckByPhone($params)
    {
        $phone = $params['phone'] ?? 0;
        if (!$phone) {
            return ['code' => false, 'msg' => '账号不能为空'];
        }
        $user_id = User::query()->where('phone', '=', $phone)->first();
        if (!$user_id) {
            return ['code' => false, 'msg' => '账号错误'];
        }

        //1专栏  2作品 3直播 5线下产品  6讲座  7训练营
        $query = Subscribe::query()
            ->where('user_id', '=', $user_id->id)
            ->where('status', '=', 1)
            ->where('is_del', '=', 0)
            ->whereIn('type', [1, 2, 3, 5, 6, 7]);

        $query->with([
            'subWorksInfo:id,title',
            'subColumnInfo:id,name as title',
            'subOfflineInfo:id,title',
            'subLiveInfo:id,title',
        ]);

        $query->select([
            'id', 'type', 'user_id', 'relation_id', 'order_id', 'start_time', 'end_time', 'give', 'created_at',
        ]);
        $list = $query->orderBy('id', 'desc')->get();

        foreach ($list as $v) {
            $v->open_flag = '后台开通';
            if ($v->order_id) {
                $v->open_flag = '订单购买';
            }
            $v->relation_title = '--';
            switch ($v->type) {
                case 1:
                    $v->type_name      = '专栏';
                    $v->relation_title = $v->subColumnInfo->title;
                    break;
                case 2:
                    $v->type_name      = '作品';
                    $v->relation_title = $v->subWorksInfo->title;
                    break;
                case 3:
                    $v->type_name      = '直播';
                    $v->relation_title = $v->subLiveInfo->title;
                    break;
                case 5:
                    $v->type_name      = '线下产品';
                    $v->relation_title = $v->subOfflineInfo->title;
                    break;
                case 6:
                    $v->type_name      = '讲座';
                    $v->relation_title = $v->subColumnInfo->title;
                    break;
                case 7:
                    $v->type_name      = '训练营';
                    $v->relation_title = $v->subColumnInfo->title;
                    break;
            }
            unset(
                $v->subColumnInfo,
                $v->subLiveInfo,
                $v->subOfflineInfo,
                $v->subWorksInfo
            );
        }

        return $list;

    }

}
