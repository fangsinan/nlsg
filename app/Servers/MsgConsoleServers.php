<?php


namespace App\Servers;


use App\Models\Message\MessageType;
use App\Models\Message\MessageView;
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

    //消息模板列表
    public function templateList($params, $admin)
    {
        return [__LINE__];
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
