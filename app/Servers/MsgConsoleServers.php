<?php


namespace App\Servers;


use App\Models\Message\MessageRelationType;
use App\Models\Message\MessageType;
use App\Models\Message\MessageView;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;

class MsgConsoleServers
{

    //推送任务列表
    public function jobList($params, $admin)
    {
        return [__LINE__];
    }

    //创建推送任务
    public function createJob($params, $admin)
    {
        return [__LINE__];
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
