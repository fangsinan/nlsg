<?php


namespace App\Servers\V5;


use App\Models\Talk;
use App\Models\TalkList;
use App\Models\TalkRemark;
use App\Models\TalkUserStatistics;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
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

//        if ($nickname) {
//            $query->whereHas('userInfo', function ($q) use ($nickname) {
//                $q->where('nickname', 'like', '%' . $nickname . '%');
//            });
//        }

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
        if (!is_array($id)) {
            $id = (string)$id;
        }
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


    public function talkList($params, $admin): array
    {
        $user_id = $params['user_id'] ?? 0;
        if (!$user_id) {
            return ['code' => false, 'msg' => '用户id错误'];
        }
        $user_info = User::query()->where('id', '=', $user_id)
            ->select(['id', 'phone', 'nickname', 'headimg'])
            ->first();
        if (!$user_info) {
            return ['code' => false, 'msg' => '用户id错误'];
        }

        $flag    = $params['flag'] ?? 'begin';//begin第一条  end最后一条
        $talk_id = (int)($params['talk_id'] ?? 0); //如果传了talk_id,则返回本次会话内容.否则从开始返回
        $page    = (int)($params['page'] ?? 0);//如果是0 表示可以根据返回的page替换
        $size    = $params['size'] ?? 10;

        $query = TalkList::query()
            ->with([
                'talkInfo:id,status',
                'adminInfo:id,username,user_remark'
            ])
            ->where('user_id', '=', $user_id)
            ->whereHas('talkInfo', function ($q) {
                $q->where('status', '=', 1);
            });

        if ($page < 1) {
            if ($talk_id) {
                if ($flag === 'begin') {
                    $begin_limit = $query
                        ->whereHas('talkInfo', function ($q) use ($talk_id) {
                            $q->where('id', '<', $talk_id);
                        })
                        ->count();
                } else {
                    $begin_limit = $query
                        ->whereHas('talkInfo', function ($q) use ($talk_id) {
                            $q->where('id', '<=', $talk_id);
                        })
                        ->count();
                }
                $page = ceil($begin_limit / $size);
            } else {
                if ($flag === 'begin') {
                    $page = 1;
                } else {
                    $begin_limit = $query->count();
                    $page        = ceil($begin_limit / $size);
                }
            }
        }

        $query->select(['id', 'talk_id', 'type', 'admin_id', 'content', 'created_at']);
        $query->orderBy('id');
        $query->limit($size)->offset(($page - 1) * $size);

        return [
            'user_info' => $user_info,
            'page'      => $page,
            'size'      => $size,
            'list'      => $query->get(),
        ];
    }

    public function talkListCreate($params, $admin): array
    {
        $time_limit = 300;//单位秒
        $now        = time();

        $validator = Validator::make($params,
            [
                'user_id' => 'bail|required|exists:nlsg_user,id',
                'content' => 'bail|required|string|max:250',
            ]
        );

        if ($validator->fails()) {
            return ['code' => false, 'msg' => $validator->messages()->first()];
        }

        DB::beginTransaction();

        //获取当前talk_id
        $talk = Talk::query()
            ->firstOrCreate([
                'user_id'   => $params['user_id'],
                'is_finish' => 1
            ]);

        if (!$talk) {
            DB::rollBack();
            return ['code' => false, 'msg' => '失败请重试'];
        }

        //查询上调时间间隔
        $last_talk_list = TalkList::query()
            ->where('user_id', '=', $params['user_id'])
            ->orderBy('id', 'desc')
            ->first();

        if ($last_talk_list->type !== 3 && strtotime($last_talk_list->created_at) <= ($now - $time_limit)) {
            //添加一条type = 3
            $res = TalkList::query()
                ->create([
                    'talk_id'  => $talk->id,
                    'type'     => 3,
                    'user_id'  => $params['user_id'],
                    'admin_id' => $admin['id'],
                    'content'  => date('Y-m-d H:i'),
                    'status'   => 1,
                ]);

            if (!$res) {
                DB::rollBack();
                return ['code' => false, 'msg' => '失败请重试'];
            }
        }

        $res = TalkList::query()
            ->create([
                'talk_id'  => $talk->id,
                'type'     => 2,
                'user_id'  => $params['user_id'],
                'admin_id' => $admin['id'],
                'content'  => $params['content'],
                'status'   => 1,
            ]);

        if (!$res) {
            DB::rollBack();
            return ['code' => false, 'msg' => '失败请重试'];
        }


        DB::commit();
        return ['code' => true, 'msg' => '成功'];
    }

    public function finish($params, $admin): array
    {
        $user_id = $params['user_id'] ?? 0;
        if (!$user_id) {
            return ['code' => false, 'msg' => '用户信息错误'];
        }

        Talk::query()
            ->where('user_id', '=', $user_id)
            ->where('is_finish', '=', 1)
            ->update([
                'is_finish'       => 2,
                'finish_admin_id' => $admin['id'],
                'finish_at'       => date('Y-m-d H:i:s'),
            ]);

        return ['code' => true, 'msg' => '成功'];
    }

    public function talkUserList($params, $admin): LengthAwarePaginator
    {
        return TalkUserStatistics::query()
            ->select([
                'user_id', 'msg_count'
            ])
            ->with([
                'userInfo:id,nickname,phone'
            ])
//            ->when($params['nickname'] ?? '', function ($q) use ($params) {
//                $q->wherehas('userInfo', function ($q) use ($params) {
//                    $q->where('nickname', 'like', '%' . $params['nickname'] . '%');
//                });
//            })
            ->when($params['phone'] ?? '', function ($q) use ($params) {
                $q->wherehas('userInfo', function ($q) use ($params) {
                    $q->where('phone', 'like', '%' . $params['phone'] . '%');
                });
            })
            ->paginate($params['size'] ?? 10);
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
