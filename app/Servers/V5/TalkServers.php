<?php


namespace App\Servers\V5;


use App\Models\Talk;
use App\Models\TalkList;
use App\Models\TalkRemark;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;

class TalkServers
{

    public function list($params, $admin): LengthAwarePaginator
    {
        $query = Talk::query()
            ->where('status', '=', 1)
            ->with([
                'userInfo:id,nickname,phone',
                'adminInfo:id,username,user_remark',
                'remarkList' => function ($q) {
                    $q->orderBy('id', 'desc')->limit(1);
                }
            ])
            ->select([
                'id', 'user_id', 'created_at', 'is_finish', 'finish_at', 'finish_admin_id'
            ]);

        //昵称,账号,留言时间,解决时间,状态
        $nickname      = $params['nickname'] ?? '';
        $phone         = $params['phone'] ?? '';
        $created_begin = $params['created_begin'] ?? '';
        $created_end   = $params['created_end'] ?? '';
        $finish_begin  = $params['finish_begin'] ?? '';
        $finish_end    = $params['finish_end'] ?? '';
        $is_finish     = $params['is_finish'] ?? 0;

        if ($nickname) {
            $query->whereHas('userInfo', function ($q) use ($nickname) {
                $q->where('nickname', 'like', '%' . $nickname . '%');
            });
        }

        if ($phone) {
            $query->whereHas('userInfo', function ($q) use ($phone) {
                $q->where('phone', 'like', '%' . $phone . '%');
            });
        }

        if ($created_begin && date('Y-m-d H:i:s', strtotime($created_begin)) == $created_begin) {
            $query->where('created_at', '>=', $created_begin);
        }
        if ($created_end && date('Y-m-d H:i:s', strtotime($created_end)) == $created_end) {
            $query->where('created_at', '<=', $created_end);
        }

        if ($finish_begin && date('Y-m-d H:i:s', strtotime($finish_begin)) == $finish_begin) {
            $query->where('finish_at', '>=', $finish_begin)->where('is_finish', '=', 2);
        }
        if ($finish_end && date('Y-m-d H:i:s', strtotime($finish_end)) == $finish_end) {
            $query->where('finish_at', '<=', $finish_end)->where('is_finish', '=', 2);
        }

        if ($is_finish) {
            $query->where('is_finish', '=', $is_finish);
        }

        $query->where('status', '=', 1);
        $query->orderBy('is_finish')->orderBy('id', 'desc');

        return $query->paginate($params['size'] ?? 10);
    }


    public function changeStatus($params, $admin): array
    {

        $flag = $params['flag'] ?? '';
        $id   = $params['id'] ?? '';
        if (is_string($id)) {
            $id = explode(',', $id);
            $id = array_filter($id);
        }

        if (empty($id)) {
            return ['code' => false, 'msg' => 'id不能为空'];
        }

        if (!in_array($flag, ['del'])) {
            return ['code' => false, 'msg' => '操作类型错误'];
        }

        $res = Talk::query()
            ->whereIn('id', $id)
            ->update([
                'status' => 2,
            ]);

        if ($res) {
            return ['code' => true, 'msg' => '成功'];
        }

        return ['code' => false, 'msg' => '失败,请重试.'];
    }


    public function remarkCreate($params, $admin): array
    {
        $params['admin_id'] = $admin['id'] ?? 0;

        $validator = Validator::make($params,
            [
                'talk_id'  => 'bail|required|exists:nlsg_talk,id',
                'content'  => 'bail|required|string|max:200',
                'admin_id' => 'bail|required|exists:nlsg_backend_user,id',
            ]
        );

        if ($validator->fails()) {
            return ['code' => false, 'msg' => $validator->messages()->first()];
        }

        $res = TalkRemark::query()->create($params);
        if ($res) {
            return ['code' => true, 'msg' => '成功'];
        }

        return ['code' => false, 'msg' => '失败'];
    }

    public function remarkList($params, $admin)
    {
        $validator = Validator::make($params,
            [
                'talk_id' => 'bail|required|exists:nlsg_talk,id',
            ]
        );

        if ($validator->fails()) {
            return ['code' => false, 'msg' => $validator->messages()->first()];
        }

        return TalkRemark::query()
            ->where('talk_id', '=', $params['talk_id'])
            ->orderBy('id', 'desc')
            ->select(['id', 'created_at', 'content', 'admin_id'])
            ->with([
                'adminInfo:id,username,user_remark'
            ])
            ->paginate($params['size'] ?? 10);
    }


    public function talkList($params, $admin)
    {
        $user_id = $params['user_id'] ?? 0;
        if (!$user_id) {
            return ['code' => false, 'msg' => '用户id错误'];
        }
        //如果传了talk_id,则返回本次会话内容.否则从开始返回
        $page = 1;
        $size = $params['size'] ?? 10;

        $talk_id = $params['talk_id'] ?? 0;
        if ($talk_id) {
            $check_talk_id = Talk::query()
                ->where('id', '=', $talk_id)
                ->where('status', '=', 1)
                ->first();
            if ($check_talk_id) {
                $begin_id = TalkList::query()
                    ->where('talk_id', '=', $talk_id)
                    ->value('id');

                $before_begin_count = TalkList::query()
                    ->where('user_id', '=', $user_id)
                    ->where('id', '<', $begin_id)
                    ->count();

                if ($before_begin_count > 0 && $before_begin_count <= $size) {
                    $page = 1;
                } elseif ($before_begin_count > $size) {
                    $page = ceil($before_begin_count / $size);
                }
            }
        }

        $query = TalkList::query()
            ->with([
                'userInfo:id,phone,nickname,headimg',
                'adminInfo:id,username,user_remark',
                'talkInfo:id'
            ])
            ->limit($size)
            ->offset(($page - 1) * $size);

        $query->whereHas('talkInfo', function ($q) {
            $q->where('status', '=', 1);
        });

        $query->select([
            'id', 'talk_id', 'type', 'user_id', 'admin_id', 'content', 'created_at', 'status',
        ]);

        return [
            'page' => $page,
            'size' => $size,
            'data' => $query->get(),
        ];

    }


    public function finish($params, $admin)
    {

        return [__LINE__];
    }


    public function templateList($params, $admin)
    {

        return [__LINE__];
    }

    public function templateListCreate($params, $admin)
    {

        return [__LINE__];
    }

    public function templateListChangeStatus($params, $admin)
    {

        return [__LINE__];
    }
}
