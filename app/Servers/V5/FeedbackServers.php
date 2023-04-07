<?php


namespace App\Servers\V5;


use App\Models\FeedbackNew;
use App\Models\FeedbackReplyTemplate;
use App\Models\FeedbackTarget;
use App\Models\FeedbackType;
use App\Models\HelpAnswer;
use App\Models\HelpAnswerKeywords;
use App\Models\HelpAnswerKeywordsBind;
use App\Models\Message\Message;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FeedbackServers
{
    public function list($params, $admin): LengthAwarePaginator
    {
        $id          = $params['id'] ?? 0;
        $nickname    = $params['nickname'] ?? '';
        $phone       = $params['phone'] ?? '';
        $is_reply    = (int)($params['is_reply'] ?? 0);//1回复 2没回复
        $reply_begin = $params['reply_begin'] ?? '';
        $reply_end   = $params['reply_end'] ?? '';
        $os_type     = $params['os_type'] ?? 0;
        $type        = $params['type'] ?? 0;
        $get_feedback_type       = $params['get_feedback_type'] ?? 1;  // 1反馈 2帮助问题类型 3直播举报

        $select_array = [
            '*', DB::raw('if(reply_admin_id>0,1,0) as is_reply')
        ];

        $query = FeedbackNew::query()
            ->with([
                'UserInfo:id,phone,nickname,headimg',
                'FeedbackType:id,name'
            ])
            ->select($select_array)
            ->where('status', '=', 1)
            ->where('app_project_type','=',APP_PROJECT_TYPE);

        if ($id) {
            $query->where('id', '=', $id);
        }

        if ($phone) {
            $query->whereHas('UserInfo', function ($q) use ($phone) {
                $q->where('phone', 'like', "$phone%");
            });
        }

        if ($is_reply) {
            $query->where('reply_admin_id', $is_reply === 1 ? '>' : '=', 0);
        }

        if ($reply_begin && date('Y-m-d H:i:s', strtotime($reply_begin)) == $reply_begin) {
            $query->where('reply_admin_id', '>', 0)
                ->where('reply_at', '>=', $reply_begin);
        }

        if ($reply_end && date('Y-m-d H:i:s', strtotime($reply_end)) == $reply_end) {
            $query->where('reply_admin_id', '>', 0)
                ->where('reply_at', '<=', $reply_end);
        }

        if ($os_type) {
            $query->where('os_type', '=', $os_type);
        }

        if ($type) {
            $query->where('type', '=', $type);
        }else{
            $types = FeedbackType::where("type",$get_feedback_type)->pluck("id")->toArray();
            $query->whereIn('type', $types);
        }


        $res = $query->orderBy('id', 'desc')
            ->paginate($params['size'] ?? 10);

        if($get_feedback_type == 3){
            // 获取额外的举报信息
            $target_ids = $query->pluck("target")->toArray();
            $targetArrs = FeedbackTarget::query()
                ->with([
                    'user:id,phone,nickname,headimg',
                    // 'liveComment:id,content',
                    'live:id,title'
                ])
                ->select("*")
                ->whereIn('id', $target_ids)->get()->toArray();
            $new_target =[];
            foreach ($targetArrs as $k=>$v){
                $new_target[$v['id']] =$v;
            }
        }

        foreach ($res as $v) {
            $v->picture = explode(',', $v->picture);

            //举报用
            $v->target_live     = $new_target[$v['target']]['live']??'';
            $v->target_comment  = $new_target[$v['target']]['comment']??'';
            $v->target_user     = $new_target[$v['target']]['user']??'';
        }




        return $res;
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

        $res = FeedbackNew::query()
            ->whereIn('id', $id)
            ->update([
                'status' => 2,
                'del_at' => date('Y-m-d H:i:s'),
                'del_by' => $admin['id'],
            ]);

        if ($res) {
            return ['code' => true, 'msg' => '成功'];
        }

        return ['code' => false, 'msg' => '失败,请重试.'];
    }

    public function toReply($params, $admin): array
    {
        $id = $params['id'] ?? '';
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

        if (empty($params['reply_content'] ?? '')) {
            return ['code' => false, 'msg' => '回复内容不能为空'];
        }

        $id_count = count($id);

        $check_count = FeedbackNew::query()
            ->whereIn('id', $id)
            ->where('status', '=', 1)
            ->where('reply_admin_id', '=', 0)
            ->count();

        if (!$check_count || $id_count !== $check_count) {
            return ['code' => false, 'msg' => '批量回复中不得包含已回复状态.'];
        }

        $res = FeedbackNew::query()
            ->whereIn('id', $id)
            ->update([
                'reply_admin_id'    => $admin['id'],
                'reply_template_id' => $params['reply_template_id'] ?? 0,
                'reply_content'     => $params['reply_content'],
                'reply_at'          => date('Y-m-d H:i:s'),
            ]);

        if ($res) {

            //写入推送部分
            $fb_list = FeedbackNew::query()
                ->whereIn('id', $id)
                ->with('UserInfo:id,phone,nickname')
                ->select(['id', 'type', 'user_id', 'content', 'created_at', 'reply_content', 'reply_at'])
                ->get()
                ->toArray();

            foreach ($fb_list as $fb_v) {
                $fb_v['open_type']     = 3;
                $fb_v['relation_type'] = 181;
                Message::pushMessage(
                    0,
                    $fb_v['user_id'],
                    'SYS_FEEDBACK_REPLY',
                    $fb_v
                );
            }

            return ['code' => true, 'msg' => '成功'];
        }

        return ['code' => false, 'msg' => '失败,请重试.'];
    }

    public function templateList($params, $admin): LengthAwarePaginator
    {
        $id = $params['id'] ?? 0;

        $query = FeedbackReplyTemplate::query()
            ->where('status', '<>', 3)
            ->select(['id', 'title', 'content', 'status', 'created_at', 'updated_at']);

        if ($id) {
            $query->where('id', '=', $id);
        }

        //名称,状态,创建时间范围
        $title         = $params['title'] ?? '';
        $status        = $params['status'] ?? 0;
        $created_begin = $params['created_begin'] ?? '';
        $created_end   = $params['created_end'] ?? '';

        if ($title) {
            $query->where('title', 'like', "%$title%");
        }
        if ($status) {
            $query->where('status', '=', $status);
        }
        if ($created_begin && date('Y-m-d H:i:s', strtotime($created_begin)) == $created_begin) {
            $query->where('created_at', '>=', $created_begin);
        }
        if ($created_end && date('Y-m-d H:i:s', strtotime($created_end)) == $created_end) {
            $query->where('created_at', '<=', $created_end);
        }

        $query->orderByRaw('`status` asc,case when status = 1 then updated_at  end DESC,
        case when status = 2 then created_at  end DESC,id DESC');

        return $query->paginate($params['size'] ?? 10);
    }

    public function templateCreate($params, $admin): array
    {
        $title   = $params['title'] ?? '';
        $content = $params['content'] ?? '';
        $status  = $params['status'] ?? 0;
        $id      = $params['id'] ?? 0;

        if (empty($title) || empty($content) || !in_array($status, [1, 2])) {
            return ['code' => false, 'msg' => '数据错误'];
        }

        if (mb_strlen($title) > 200 || mb_strlen($content) > 200) {
            return ['code' => false, 'msg' => '标题或内容字数超限'];
        }

        if ($id) {
            $model = FeedbackReplyTemplate::query()
                ->where('id', '=', $id)
                ->first();
            if (empty($model)) {
                return ['code' => false, 'msg' => 'id错误'];
            }
        } else {
            $model = new FeedbackReplyTemplate();
        }

        $model->title      = $title;
        $model->content    = $content;
        $model->status     = $status;
        $model->created_by = $admin['id'];

        $res = $model->save();

        if ($res) {
            return ['code' => true, 'msg' => '成功'];
        }

        return ['code' => false, 'msg' => '失败'];
    }

    public function templateChangeStatus($params, $admin): array
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

        if (!in_array($flag, ['del', 'on', 'off'])) {
            return ['code' => false, 'msg' => '操作类型错误'];
        }

        $res = FeedbackReplyTemplate::query()
            ->whereIn('id', $id)
            ->update([
                'status' => $flag === 'del' ? 3 : ($flag == 'on' ? 1 : 2),
            ]);

        if ($res) {
            return ['code' => true, 'msg' => '成功'];
        }

        return ['code' => false, 'msg' => '失败,请重试.'];
    }

    public function typeList($params, $admin)
    {
        if (!in_array($params['type'] ?? 0, [1, 2, 3])) {
            return ['code' => false, 'msg' => '类型错误'];
        }

        return FeedbackType::query()
            ->where('status', '=', 1)
            ->where('type', '=', $params['type'])
            ->select(['id', 'name'])
            ->get();
    }

    public function typeCreate($params, $admin): array
    {
        $type = $params['type'] ?? 0;
        $name = $params['name'] ?? '';

        if (empty($type) || empty($name) || !in_array($type, [1, 2, 3])) {
            return ['code' => false, 'msg' => '参数错误'];
        }

        $id = $params['id'] ?? 0;

        if ($id) {
            $model = FeedbackType::query()
                ->where('id', '=', $id)
                ->where('type', '=', $type)
                ->first();
            if (empty($model)) {
                return ['code' => false, 'msg' => 'id错误'];
            }
        } else {
            $model = new FeedbackType();
        }

        $model->type = $type;
        $model->name = $name;

        $res = $model->save();

        if ($res) {
            return ['code' => true, 'msg' => '成功'];
        }

        return ['code' => false, 'msg' => '失败'];
    }

    public function typeChangeStatus($params, $admin): array
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

        $res = FeedbackType::query()
            ->whereIn('id', $id)
            ->update([
                'status' => 2,
            ]);

        if ($res) {

            $type_list = FeedbackType::query()
                ->whereIn('id', $id)
                ->where('type', '=', 2)
                ->pluck('id')
                ->toArray();

            if ($type_list) {
                HelpAnswer::query()
                    ->whereIn('type', $type_list)
                    ->whereIn('status', [1, 3])
                    ->update(['status' => 2]);
            }


            return ['code' => true, 'msg' => '成功'];
        }

        return ['code' => false, 'msg' => '失败,请重试.'];
    }

    public function helpList($params, $admin): LengthAwarePaginator
    {
        $query = HelpAnswer::query()
            ->with([
                'typeInfo:id,name',
                'keywordsBind:id,help_answer_id,keywords_id',
                'keywordsBind.keywords:id,keywords',
            ])
            ->where('status', '<>', 2);

        //类型,标题,内容
        $type        = $params['type'] ?? 0;
        $question    = $params['question'] ?? '';
        $answer      = $params['answer'] ?? '';
        $keywords_id = $params['keywords_id'] ?? 0;
        $sort        = $params['sort'] ?? '';

        if ($type) {
            $query->where('type', '=', $type);
        }

        if ($question) {
            $query->where('question', 'like', "%$question%");
        }

        if ($answer) {
            $query->where('answer', 'like', "%$answer%");
        }

        if ($keywords_id) {
            $query->whereHas('keywordsBind', function ($q) use ($keywords_id) {
                $q->where('keywords_id', '=', $keywords_id);
            });
        }

        switch ($sort) {
            case 'time_asc':
                $query->orderBy('id');
                break;
            case 'time_desc':
                $query->orderBy('id', 'desc');
                break;
            default:
                $query->orderByRaw('`status` asc,case when status = 1 then updated_at  end DESC,
        case when status = 2 then created_at  end DESC,id DESC');
        }

        $query->select([
            'id', 'type', 'question', 'answer', 'qr_code', 'created_at', 'status',
        ]);
        return $query->paginate($params['size'] ?? 10);
    }

    public function helpCreate($params, $admin): array
    {
        $validator = Validator::make($params,
            [
                'type'       => 'bail|required|exists:nlsg_feedback_type,id',
                'question'   => 'bail|required|string|max:20',
                'answer'     => 'bail|required|string|max:200',
                'status'     => 'bail|required|in:1,3',
                'keywords'   => 'bail|required|array|max:5',
                'keywords.*' => 'bail|distinct|max:20',
                //                'qr_code'    => 'bail|string|max:100',
            ],
            [
                'type.exists'         => '类型id错误',
                'keywords.max'        => '关键字最多允许5个',
                'keywords.*.distinct' => '关键字内有重复值',
                'keywords.*.size'     => '关键字最多允许20个字',
                'question.max'        => '最多允许20个字',
                'answer.max'          => '最多允许200个字',
                'keywords.required'   => '未输入关键词',
            ]
        );

        if ($validator->fails()) {
            return ['code' => false, 'msg' => $validator->messages()->first()];
        }

        $id = $params['id'] ?? 0;

        DB::beginTransaction();

        //写入问答表
        if ($id) {
            $ha = HelpAnswer::query()->where('id', '=', $id)->where('status', '<>', 2)->first();
            if (!$ha) {
                DB::rollBack();
                return ['code' => false, 'msg' => 'id错误'];
            }
        } else {
            $ha = new HelpAnswer();
        }

        $ha->type     = $params['type'];
        $ha->question = $params['question'];
        $ha->answer   = $params['answer'];
        $ha->qr_code  = $params['qr_code'] ?? '';
        $ha->status   = $params['status'];

        $ha_res = $ha->save();
        if (!$ha_res) {
            DB::rollBack();
            return ['code' => false, 'msg' => '失败,请重试.'];
        }

        //存入关键字
        $keywords_id_list = [];
        foreach ($params['keywords'] as $v) {
            $temp_k             = HelpAnswerKeywords::query()
                ->firstOrCreate([
                    'keywords' => $v
                ]);
            $keywords_id_list[] = $temp_k->id;
        }


        //绑定关键词
        HelpAnswerKeywordsBind::query()
            ->where('help_answer_id', '=', $ha->id)
            ->whereNotIn('keywords_id', $keywords_id_list)
            ->delete();

        foreach ($keywords_id_list as $v) {
            HelpAnswerKeywordsBind::query()
                ->firstOrCreate([
                    'help_answer_id' => $ha->id,
                    'keywords_id'    => $v,
                ]);
        }


        DB::commit();
        return ['code' => true, 'msg' => '成功'];
    }

    public function helpChangeStatus($params, $admin): array
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

        if (!in_array($flag, ['del', 'on', 'off'])) {
            return ['code' => false, 'msg' => '操作类型错误'];
        }

        $res = HelpAnswer::query()
            ->whereIn('id', $id)
            ->update([
                'status' => $flag === 'del' ? 2 : ($flag == 'on' ? 1 : 3),
            ]);

        if ($res) {
            return ['code' => true, 'msg' => '成功'];
        }

        return ['code' => false, 'msg' => '失败,请重试.'];
    }

    public function statistics($params, $admin): array
    {
        $flag = (int)($params['flag'] ?? 1);
        switch ($flag) {
            case 1:
                $res = $this->statisticsF1();
                break;
            case 2:
                $res = $this->statisticsF2();
                break;
            case 3:
                $res = $this->statisticsF3($params['begin'] ?? '', $params['end'] ?? '');
                break;
            default:
                $res = [];
        }

        return $res;
    }


    private function statisticsF1(): array
    {
        $today = date('Y-m-d');
        return [
            'total'    => FeedbackNew::query()->count(),
            'reply'    => FeedbackNew::query()->where('reply_admin_id', '>', 0)->count(),
            'no_reply' => FeedbackNew::query()->where('reply_admin_id', '=', 0)->where('status', '=', 1)->count(),
            'today'    => FeedbackNew::query()
                ->whereBetween('created_at', [
                    $today . ' 00:00:00', $today . ' 23:59:59'
                ])->where('status', '=', 1)->count(),
        ];
    }

    private function statisticsF2(): array
    {
        $res = FeedbackType::query()
            ->where('type', '=', 1)
            ->select(['id', 'type', 'name'])
            ->withCount('feedbackList as value')
            ->get();

        if ($res->isEmpty()) {
            return [];
        }

        return $res->toArray();
    }

    private function statisticsF3($begin = '', $end = ''): array
    {
        if ($begin) {
            $begin = date('Y-m-d', strtotime($begin));
        } else {
            $begin = date('Y-m-d', strtotime('-6 days'));
        }

        if ($end) {
            $end = date('Y-m-d', strtotime($end));
        } else {
            $end = date('Y-m-d');
        }

        $end = min(date('Y-m-d'), $end);

        $day_list = [];

        $while_flag = 0;
        while (true) {
            $day_list[] = date('Y-m-d', strtotime($begin . " + $while_flag days"));
            $while_flag++;
            if (end($day_list) >= $end) {
                break;
            }
        }

        $count_query = FeedbackNew::query()
            ->whereBetween('created_at', [$begin . ' 00:00:00', $end . ' 23:59:59'])
            ->groupByRaw('left(created_at,10)')
            ->select([
                DB::raw('left(created_at,10) as day'),
                DB::raw('count(*) as counts')
            ])
            ->get();

        $count_list = [];

        foreach ($day_list as $dl_k => $dl_v) {
            $count_list[$dl_k] = 0;
            foreach ($count_query as $cq_k => $cq_v) {
                if ($dl_v === $cq_v->day) {
                    $count_list[$dl_k] = $cq_v->counts;
                    unset($count_query[$cq_k]);
                }
            }
        }

        return [
            'day_list'   => $day_list,
            'count_list' => $count_list,
        ];
    }
}
