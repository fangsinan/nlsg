<?php

namespace App\Servers;

use App\Models\ImDoc;
use App\Models\ImQuickReply;

class ImQuickReplyServers
{
    public function list($params,$user_id)
    {
        $size = $params['size'] ?? 10;

        $query = ImQuickReply::query()->where('user_id','=',$user_id);
        $query->select(['id','user_id','content']);
        return $query->orderBy('id','desc')->paginate($size);
    }

    public function add($params,$user_id)
    {
        if (empty($params['content'] ?? '')){
            return ['code'=>false,'msg'=>'回复内容不能为空'];
        }
        if (mb_strlen($params['content']) > 225){
            return ['code'=>false,'msg'=>'内容过多'];
        }

        $m = new ImQuickReply();
        $m->user_id = $user_id;
        $m->content = $params['content'];
        $m->status = 1;
        $res = $m->save();
        if ($res){
            return ['code'=>true,'msg'=>'成功'];
        }else{
            return ['code'=>false,'msg'=>'失败'];
        }
    }

    public function changeStatus($params,$user_id)
    {
        $id = $params['id'] ?? 0;
        $flag = $params['flag'] ?? '';
        $check = ImQuickReply::query()
            ->where('id', '=', $id)
            ->where('user_id','=',$user_id)
            ->first();

        if (empty($check)) {
            return ['code' => false, 'msg' => 'id错误'];
        }
        switch ($flag) {
            case 'del':
                $check->status = 2;
                break;
            default:
                return ['code' => false, 'msg' => '动作错误'];
        }

        $res = $check->save();
        if ($res) {
            return ['code' => true, 'msg' => '成功'];
        } else {
            return ['code' => false, 'msg' => '失败'];
        }
    }
}
