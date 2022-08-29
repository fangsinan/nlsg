<?php


namespace App\Servers;


use App\Models\Column;
use App\Models\Lists;
use App\Models\Live;
use App\Models\MallGoods;
use App\Models\Message\Message;
use App\Models\Message\MessageRelationType;
use App\Models\Message\MessageType;
use App\Models\Message\MessageUser;
use App\Models\Message\MessageView;
use App\Models\User;
use App\Models\Works;
use App\Models\WorksInfo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MsgConsoleServers
{
    const GROUP_SIZE = 1000;

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
            'id', 'title', 'message', 'receive_type', 'relation_type', 'relation_id',
            'relation_info_id', 'plan_time', 'status', 'is_timing', 'timing_send_time',
            'created_at', 'open_type', 'url', 'rich_text',
            'send_count', 'get_count', 'read_count',
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
                'title'            => 'bail|required|string|max:20',
                'message'          => 'bail|required|string|max:50',
                'receive_type'     => 'bail|required|in:1,2',
                'phone_list'       => 'exclude_unless:receive_type,1|required|array|max:20000',
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
                'title.max'             => '最多允许20个字',
                'message.max'           => '最多允许50个字',
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

        if ($params['open_type'] == 4) {
            $params['type'] = 23;
        }

        if (isset($params['timing_send_time'])){
            $params['timing_send_time'] = date('Y-m-d H:i:00', strtotime($params['timing_send_time']));
        }else{
            $params['timing_send_time'] = date('Y-m-d H:i:00');
        }


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

        $send_count = 0;
        if ($params['receive_type'] == 1) {

            //删掉之前的记录
            MessageUser::query()
                ->where('message_id', '=', $msg_id)
                ->delete();

            $phone_list = array_chunk($params['phone_list'], self::GROUP_SIZE);
            foreach ($phone_list as $pl_k => $pl_v) {
                $user_list = User::query()
                    ->whereIn('phone', $pl_v)
                    ->pluck('id')
                    ->toArray();
                if (empty($user_list)) {
                    continue;
                }
                $group_id  = $msg_id . '-' . $pl_k;
                $user_data = [];
                foreach ($user_list as $ul_v) {
                    $send_count++;
                    $user_data[] = [
                        'send_user'    => 0,
                        'receive_user' => $ul_v,
                        'message_id'   => $msg_id,
                        'group_id'     => $group_id,
                    ];
                }

                $res = MessageUser::query()->insert($user_data);

                if ($res === false) {
                    DB::rollBack();
                    return ['code' => false, 'msg' => '添加用户信息失败'];
                }
            }

            if (0) {
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

        }

        if ($send_count > 0) {
            Message::query()->where('id', '=', $msg_id)
                ->update([
                    'send_count' => $send_count
                ]);
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

        foreach ($list as $k => $v) {
            if (!isset($temp[$v->group_name])) {
                $temp[$v->group_name] = [
                    'title'=>$v->group_name,
                    'id'=>$k,
                    'child_list'=>[]
                ];
            }
            $temp[$v->group_name]['child_list'][] = $v;
        }

        return Array_values($temp);
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

            //不允许修改type
            if ($check_id->type != $params['type']) {
                return ['code' => false, 'msg' => '不允许修改类型'];
            }

            $mv = MessageView::query()->find($params['id']);
        } else {
            $mv = new MessageView();

            //新建,需要校验是否已经存在该type
            $check_type = MessageView::query()->where('type', '=', $params['type'])->first();
            if ($check_type) {
                return ['code' => false, 'msg' => '该类型已经存在模板,不允许创建'];
            }
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


    public function msgTypeList($params)
    {
        $model = new MessageType();
        return $model->getTypeList($params['flag'] ?? 1);
    }

    public function msgRelationTypeSearchData($params): array
    {
        $validator = Validator::make($params, [
                'id' => 'bail|required|integer|exists:nlsg_message_relation_type',
            ]
        );

        if ($validator->fails()) {
            return ['code' => false, 'msg' => $validator->messages()->first()];
        }

        switch ($params['id']) {
            //课程
            case '101':
                $res = [
                    'have_info' => 0,
                    'data'      => $this->getWorksList()
                ];
                break;
            case '102':
                if ($params['relation_id'] ?? 0) {
                    $res = [
                        'have_info' => 0,
                        'data'      => $this->getWorksInfoList($params['relation_id']),
                    ];
                } else {
                    $res = [
                        'have_info' => 1,
                        'data'      => $this->getWorksList(),
                    ];
                }
                break;
            //讲座
            case '111':
                $res = [
                    'have_info' => 0,
                    'data'      => $this->getColumnList()
                ];
                break;
            case '112':
                if ($params['relation_id'] ?? 0) {
                    $res = [
                        'have_info' => 0,
                        'data'      => $this->getColumnInfoList($params['relation_id']),
                    ];
                } else {
                    $res = [
                        'have_info' => 1,
                        'data'      => $this->getColumnList()
                    ];
                }
                break;
            //商品
            case '122':
                $res = [
                    'have_info' => 0,
                    'data'      => $this->getGoodsList()
                ];
                break;
            //直播间
            case '131':
                $res = [
                    'have_info' => 0,
                    'data'      => $this->getLiveList()
                ];
                break;
            //训练营
            case '141':
                $res = [
                    'have_info' => 0,
                    'data'      => $this->getCampList()
                ];
                break;
            case '142':
                $res = [
                    'have_info' => 0,
                    'data'      => $this->getCampInfoList()
                ];
                break;
            //大咖讲书
            case '171':
                $res = [
                    'have_info' => 0,
                    'data'      => $this->getExplainBookList()
                ];
                break;
            case '172':
                if ($params['relation_id'] ?? 0) {
                    $res = [
                        'have_info' => 0,
                        'data'      => $this->getExplainBookWorksInfoList($params['relation_id']),
                    ];
                } else {
                    $res = [
                        'have_info' => 1,
                        'data'      => $this->getExplainBookWorksList()
                    ];
                }
                break;
            default:
                $res = [
                    'have_info' => 0,
                    'data'      => []
                ];
        }


        return $res;

    }

    public function getWorksList()
    {
        return Works::query()->where('type', '=', 2)
            ->where('status', '=', 4)
            ->select(['id as relation_id', 'title'])
            ->get();
    }

    public function getWorksInfoList($id = 0)
    {
        if (empty($id)) {
            return [];
        }
        return WorksInfo::query()->where('pid', '=', $id)
            ->where('status', '=', 4)
            ->select(['id as relation_info_id', 'title'])
            ->get();

    }

    public function getColumnList()
    {
        return Column::query()
            ->where('type', '=', 2)
            ->where('status', '=', 1)
            ->select(['id as relation_id', 'name as title'])
            ->get();
    }

    public function getColumnInfoList($id = 0)
    {
        if (empty($id)) {
            return [];
        }

        return WorksInfo::query()->where('column_id', '=', $id)
            ->where('status', '=', 4)
            ->select(['id as relation_info_id', 'title'])
            ->get();
    }

    public function getGoodsList()
    {
        return MallGoods::query()
            ->where('status', '=', 2)
            ->select(['id as relation_id', 'name as title'])
            ->get();
    }

    public function getLiveList()
    {
        return Live::query()
            ->where('is_finish', '=', 0)
            ->where('is_del', '=', 0)
            ->where('begin_at', '>=', date('Y-m-d H:i:s'))
            ->select(['id as relation_id', 'title'])
            ->get();
    }

    public function getCampList()
    {
        return Column::query()
            ->where('type', '=', 4)
            ->where('status', '=', 1)
            ->select(['id as relation_id', 'name as title'])
            ->get();
    }

    public function getCampInfoList()
    {
        return Column::query()
            ->where('type', '=', 3)
            ->where('status', '=', 1)
            ->whereIn('is_start', [0, 1])
            ->select(['id as relation_id', 'name as title'])
            ->get();
    }

    public function getExplainBookList()
    {
        return Lists::query()
            ->where('type', '=', 10)
            ->where('status', '=', 1)
            ->select(['id as relation_id', 'title'])
            ->get();
    }

    public function getExplainBookWorksList()
    {
        return Works::query()->where('type', '=', 2)
            ->where('is_show', '=', 0)
            ->where('status', '=', 4)
            ->select(['id as relation_id', 'title'])
            ->get();
    }

    public function getExplainBookWorksInfoList($id = 0)
    {
        if (empty($id)) {
            return [];
        }
        return WorksInfo::query()->where('pid', '=', $id)
            ->where('status', '=', 4)
            ->select(['id as relation_info_id', 'title'])
            ->get();
    }
}
