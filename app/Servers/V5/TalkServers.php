<?php


namespace App\Servers\V5;


use App\Models\Talk;
use App\Models\TalkList;
use App\Models\TalkRemark;
use App\Models\TalkUserStatistics;
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

        $flag    = $params['flag'] ?? 'begin';
        $talk_id = $params['talk_id'] ?? 0;
        $page    = $params['page'] ?? 0;//如果是0 表示可以根据返回的page替换

        //如果传了talk_id,则返回本次会话内容.否则从开始返回
        $size = $params['size'] ?? 10;
        $page = 1;

        if ($talk_id) {
            $check_talk_id = Talk::query()
                ->where('id', '=', $talk_id)
                ->where('user_id', '=', $user_id)
                ->where('status', '=', 1)
                ->first();
            if (!$check_talk_id) {
                return ['code' => false, 'msg' => '会话id错误'];
            }
        }

        if ($page) {
            return [
                'page' => $page,
                'size' => $size,
                'list' => TalkList::query()
                    ->with([
                        'userInfo:id,phone,nickname,headimg',
                        'adminInfo:id,username,user_remark',
                        'talkInfo:id'
                    ])
                    ->whereHas('talkInfo', function ($q) {
                        $q->where('status', '=', 1);
                    })->select([
                        'id', 'talk_id', 'type', 'user_id', 'admin_id', 'content', 'created_at', 'status',
                    ])
                    ->limit($size)
                    ->offset(($page - 1) * $size)
                    ->get(),
            ];
        } else {

        }

        //指定会话
        //读出会话的第一条id,计算这条id所在那个page的第一条
        //末尾,读取末尾倒数size条是在那个page的第一条

        //不指定会话
        //第一条
        //末尾,读取末尾倒数size在那个page的第一条


//        $talk_id = $params['talk_id'] ?? 0;
//        if ($talk_id) {
//            $check_talk_id = Talk::query()
//                ->where('id', '=', $talk_id)
//                ->where('status', '=', 1)
//                ->first();
//            if ($check_talk_id) {
//                if ($ob === 'begin'){
//                    $begin_id = TalkList::query()
//                        ->where('talk_id', '=', $talk_id)
//                        ->value('id');
//
//                    $before_begin_count = TalkList::query()
//                        ->where('user_id', '=', $user_id)
//                        ->where('id', '<', $begin_id)
//                        ->count();
//
//                    if ($before_begin_count > 0 && $before_begin_count <= $size) {
//                        $page = 1;
//                    } elseif ($before_begin_count > $size) {
//                        $page = ceil($before_begin_count / $size);
//                    }
//                }else{
//
//                }
//
//            }
//        }else{
//            if ($ob !== 'begin'){
//                $total_count = TalkList::query()
//                    ->with([
//                        'talkInfo'
//                    ])
//                    ->whereHas('talkInfo',function ($q){
//                        $q->where('status','=',1);
//                    })
//                    ->count();
//
//                $offset = $total_count - $size;
//                $page = ceil($offset / $size) + 1;
//            }
//        }


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
