<?php

namespace App\Servers;

use App\Models\ImMsg;

class ImMsgServers
{
    public function getMsgList($params, $user_id)
    {
        $type = $params['type'] ?? -1;
        if (!in_array($type, [0, 1])) {
            return ['code' => false, 'msg' => '参数错误', 'ps' => 'type'];
        }

        $query = ImMsg::query()->where('type', '=', $type);

        $from_account = $params['from_account'] ?? 0;
        $to_account = $params['to_account'] ?? 0;
        $group_id = $params['group_id'] ?? 0;
        $size = $params['size'] ?? 10;
        $begin_date = $params['begin_date'] ?? 0;
        $end_date = $params['end_date'] ?? 0;


        switch (intval($type)) {
            case 0://个人
                if (empty($from_account ?? '') || empty($to_account)) {
                    return ['code' => false, 'msg' => '参数错误', 'ps' => 'from_account,to_account'];
                }
                break;
            case 1://群聊
                if (empty($group_id)) {
                    return ['code' => false, 'msg' => '参数错误', 'ps' => 'group_id'];
                }
                break;
        }
        $query->with(['content']);

        //TIMTextElem(文本消息)，TIMLocationElem(位置消息)，TIMFaceElem(表情消息)，
        //TIMCustomElem(自定义消息)，TIMSoundElem(语音消息)，TIMImageElem(图像消息)
        //TIMFileElem(文件消息)，TIMVideoFileElem(视频消息)',
        if (!empty($params['msg_type'] ?? '')) {
            $msg_type = $params['msg_type'];
            $query->whereHas('content.msg_type', function ($q) use ($msg_type) {
                $q->where('msg_type', '=', $msg_type);
            });
        }

        if (!empty($begin_date)) {
            $query->where('msg_time', '>=', strtotime($begin_date));
        }
        if (!empty($end_date)) {
            $query->where('msg_time', '<=', strtotime($end_date));
        }
        if (!empty($from_account)) {
            $query->where('from_account', '=', $params['from_account']);
        }
        if (!empty($to_account)) {
            $query->where('to_account', '=', $params['to_account']);
        }
        if (!empty($group_id)) {
            $query->where('group_id', '=', $params['group_id']);
        }

        return $query->orderBy('msg_time', 'desc')
            ->orderBy('msg_seq', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($size);

    }
}
