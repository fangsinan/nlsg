<?php


namespace App\Servers;


use App\Models\Message\Message;
use App\Models\Message\MessageRelationType;
use App\Models\Message\MessageType;
use App\Models\Message\MessageUser;
use App\Models\Message\MessageView;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MsgConsoleServers
{

    //推送任务列表
    public function jobList($params, $admin): LengthAwarePaginator
    {
        $query = Message::query();

        if ($params['id'] ?? 0) {
            $query->where('id', '=', $params['id']);
        }

        if (in_array($params['status'] ?? 0, [1, 2, 3, 4])) {
            $query->where('status', '=', $params['status']);
        }

        if ($params['begin_time'] ?? '') {
            $query->where(
                'timing_send_time',
                '>=',
                date('Y-m-d H:i:00', strtotime($params['begin_time']))
            );
        }

        if ($params['end_time'] ?? '') {
            $query->where(
                'timing_send_time',
                '<=',
                date('Y-m-d H:i:59', strtotime($params['end_time']))
            );
        }

        $query->orderBy('status');
        $query->orderBy('timing_send_time');
        $query->orderBy('id', 'desc');
        $query->select([
            'id','title','message','receive_type','relation_type','relation_id',
            'relation_info_id','plan_time','status','is_timing','timing_send_time',
            'created_at','open_type','url','rich_text',
            'send_count','get_count','read_count',
        ]);


        return $query->paginate($params['size'] ?? 10);
    }

    public function jobStatus($params, $admin): array
    {
        $validator = Validator::make($params, [
                'id'   => 'bail|required|integer',
                'flag' => 'bail|required|in:cancel',
            ]
        );

        if ($validator->fails()) {
            return ['code' => false, 'msg' => $validator->messages()->first()];
        }

        $check = Message::query()->find($params['id']);
        if (!$check) {
            return ['code' => false, 'msg' => 'id错误'];
        }

        if ($check->status !== 1) {
            return ['code' => false, 'msg' => '只有待发送的任务能够取消'];
        }

        if (strtotime($check->timing_send_time) < (time() + 120)) {
            return ['code' => false, 'msg' => '两分钟内的任务无法取消'];
        }

        $check->status = 4;
        $res           = $check->save();

        if (!$res) {
            return ['code' => false, 'msg' => '失败,请重试.'];
        }

        return ['code' => true, 'msg' => '成功'];
    }

    //创建推送任务
    public function createJob($params, $admin): array
    {
        $params['is_jpush'] = 1;

        $validator = Validator::make($params,
            [
                'title'            => 'bail|required|string|max:255',
                'message'          => 'bail|required|string|max:255',
                'receive_type'     => 'bail|required|in:1,2',
                'phone_list'       => 'exclude_unless:receive_type,1|required|array|max:1000',
                'phone_list.*'     => 'distinct|size:11',
                'open_type'        => 'bail|required|in:1,2,3,4',
                'url'              => 'exclude_unless:open_type,2|required|url|max:255',
                'rich_text'        => 'exclude_unless:open_type,4|required|string',
                'relation_type'    => 'exclude_unless:open_type,3|required|integer|exists:nlsg_message_relation_type,id',
                'relation_id'      => 'exclude_unless:open_type,3|required|integer|min:1',
                'is_timing'        => 'bail|required|in:1,2',
                'timing_send_time' => 'exclude_unless:is_timing,1|required|date',
            ],
            [
                'phone_list.*.distinct' => '手机号内有重复值',
                'phone_list.*.size'     => '手机号长度有误',
            ]
        );

        if ($validator->fails()) {
            return ['code' => false, 'msg' => $validator->messages()->first()];
        }

        if ($params['open_type'] != 3) {
            $params['relation_type']    = 0;
            $params['relation_id']      = 0;
            $params['relation_info_id'] = 0;
        }

        $params['timing_send_time'] = date('Y-m-d H:i:00', strtotime($params['timing_send_time']));

        $is_old = $params['id'] ?? 0;

        DB::beginTransaction();

        //写入message
        if ($is_old) {
            //编辑
            $msg = Message::query()->find($params['id']);
            if (!$msg) {
                return ['code' => false, 'msg' => 'id错误'];
            }

            if ($msg->status !== 4) {
                return ['code' => false, 'msg' => '请先取消任务,只有未生效状态的任务可以进行编辑'];
            }

            $old_receive_type = $msg->receive_type;

            $res = $msg->update($params);

            if (!$res) {
                DB::rollBack();
                return ['code' => false, 'msg' => '失败请重试'];
            }
            $msg_id = $params['id'];
        } else {
            //创建
            $res    = Message::query()->create($params);
            $msg_id = $res->id;

            if (!$res) {
                DB::rollBack();
                return ['code' => false, 'msg' => '失败请重试'];
            }
        }

        if ($is_old) {
            //如果发送目标类型改变,删除原有message_user记录
            if ($old_receive_type == 1 && $params['receive_type'] == 2) {
                MessageUser::query()
                    ->where('message_id', '=', $msg_id)
                    ->delete();
            }
        }

        if ($params['receive_type'] == 1) {
            //写入message_user
            $user_list = User::query()
                ->whereIn('phone', $params['phone_list'])
                ->pluck('id')
                ->toArray();

            if (empty($user_list)) {
                DB::rollBack();
                return ['code' => false, 'msg' => '提供的手机号都未注册'];
            }

            $send_count = count($user_list);
            Message::query()->where('id', '=', $msg_id)->update(['send_count' => $send_count]);

            $msg_user_ist = MessageUser::query()
                ->where('message_id', '=', $msg_id)
                ->pluck('receive_user')
                ->toArray();


            $del_array = array_diff($msg_user_ist, $user_list);
            $add_array = array_diff($user_list, $msg_user_ist);

            if ($del_array) {
                MessageUser::query()
                    ->where('message_id', '=', $msg_id)
                    ->whereIn('receive_user', $del_array)
                    ->delete();
            }

            if ($add_array) {
                $user_data = [];

                foreach ($add_array as $ul_v) {
                    $user_data[] = [
                        'send_user'    => 0,
                        'receive_user' => $ul_v,
                        'message_id'   => $msg_id
                    ];
                }

                if ($user_data) {
                    $res = MessageUser::query()->insert($user_data);

                    if ($res === false) {
                        DB::rollBack();
                        return ['code' => false, 'msg' => '失败请重试'];
                    }
                }
            }
        }

        DB::commit();
        return ['code' => true, 'msg' => '成功'];

    }

    public function jPushMsgTypeList(): array
    {
        $list = MessageRelationType::query()
            ->where('status', '=', 1)
            ->select(['id', 'title', 'group_name'])
            ->get();

        $temp = [];

        foreach ($list as $v) {
            if (!isset($temp[$v->group_name])) {
                $temp[$v->group_name] = [];
            }
            $temp[$v->group_name][] = $v;
        }

        return $temp;
    }

    public function templateStatus($params, $admin): array
    {
        $validator = Validator::make($params, [
                'id'   => 'bail|required|integer|exists:nlsg_message_view',
                'flag' => 'bail|required|in:delete',
            ]
        );

        if ($validator->fails()) {
            return ['code' => false, 'msg' => $validator->messages()->first()];
        }


        $res = MessageView::query()->where('id', '=', $params['id'])
            ->update([
                'status' => 2
            ]);

        if (!$res) {
            return ['code' => false, 'msg' => '失败,请重试.'];
        }

        return ['code' => true, 'msg' => '成功'];

    }

    //消息模板列表
    public function templateList($params, $admin): LengthAwarePaginator
    {
        return MessageView::query()
            ->where('status', '=', 1)
            ->with(['typeInfo:id,title'])
            ->select(['id', 'title', 'message', 'created_at', 'type'])
            ->orderBy('id', 'desc')
            ->paginate($params['size'] ?? 10);
    }

    //创建模板
    public function createTemplate($params, $admin): array
    {
        $validator = Validator::make($params, [
                'type'    => 'bail|required|integer|min:1',
                'title'   => 'bail|string|max:255',
                'message' => 'bail|required|string|max:255',
            ]
        );

        if ($validator->fails()) {
            return ['code' => false, 'msg' => $validator->messages()->first()];
        }

        $mt_model   = new MessageType();
        $check_type = $mt_model->checkUsableById($params['type']);
        if ($check_type['code'] === false) {
            return $check_type;
        }

        if (($params['id'] ?? 0) > 0) {
            $check_id = MessageView::query()->where('id', '=', $params['id'])->first();
            if (!$check_id) {
                return ['code' => false, 'msg' => '模板id不存在'];
            }
            $mv = MessageView::query()->find($params['id']);
        } else {
            $mv = new MessageView();
        }

        $mv->title           = $params['title'];
        $mv->message         = $params['message'];
        $mv->type            = $params['type'];
        $mv->create_admin_id = $admin['id'];

        $res = $mv->save();
        if (!$res) {
            return ['code' => false, 'msg' => '失败'];
        }

        return ['code' => true, 'msg' => '成功'];
    }


    public function msgTypeList()
    {
        $model = new MessageType();
        return $model->getTypeList(1);
    }
}
