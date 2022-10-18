<?php


namespace App\Servers\V5;


use App\Models\FeedbackNew;
use App\Models\FeedbackReplyTemplate;
use App\Models\FeedbackType;
use App\Models\HelpAnswer;
use App\Models\HelpAnswerKeywords;
use App\Models\HelpAnswerKeywordsBind;
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

        $select_array = $id > 0 ? ['*'] : [
            'id', 'type', 'user_id', 'os_type', 'created_at', 'reply_admin_id'
        ];

        $query = FeedbackNew::query()
            ->with([
                'UserInfo:id,phone,nickname',
                'FeedbackType:id,name'
            ])
            ->select($select_array)
            ->where('status', '=', 1);

        if ($id) {
            $query->where('id', '=', $id);
        }

        if ($nickname) {
            $query->whereHas('UserInfo', function ($q) use ($nickname) {
                $q->where('nickname', 'like', "%$nickname%");
            });
        }

        if ($phone) {
            $query->whereHas('UserInfo', function ($q) use ($phone) {
                $q->where('phone', 'like', "%$phone%");
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
        }


        $res = $query->orderBy('id', 'desc')
            ->paginate($params['size'] ?? 10);

        if ($id) {
            foreach ($res as $v) {
                $v->picture = explode(',', $v->picture);
            }
        }

        return $res;
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
            return ['code' => false, 'msg' => '选中数据有误,请核查.'];
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
            return ['code' => true, 'msg' => '成功'];
        }

        return ['code' => false, 'msg' => '失败,请重试.'];
    }

    public function templateList($params, $admin): LengthAwarePaginator
    {
        $id = $params['id'] ?? 0;

        $query = FeedbackReplyTemplate::query()
            ->where('status', '<>', 3)
            ->select(['id', 'title', 'content', 'status', 'created_at']);

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
        if (!in_array($params['type'] ?? 0, [1, 2])) {
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

        if (empty($type) || empty($name) || !in_array($type, [1, 2])) {
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
                'qr_code'    => 'bail|string|max:100',
            ],
            [
                'type.exists'         => '类型id错误',
                'keywords.max'        => '关键字最多允许5个',
                'keywords.*.distinct' => '关键字内有重复值',
                'keywords.*.size'     => '关键字最多允许20个字',
                'question.max'        => '最多允许20个字',
                'answer.max'          => '最多允许200个字',
            ]
        );

        if ($validator->fails()) {
            return ['code' => false, 'msg' => $validator->messages()->first()];
        }

        $id = $params['id'] ?? 0;

        DB::beginTransaction();

        //写入问答表
        if ($id){
            $ha = HelpAnswer::query()->where('id','=',$id)->where('status','<>',2)->first();
            if (!$ha){
                DB::rollBack();
                return ['code'=>false,'msg'=>'id错误'];
            }
        }else{
            $ha = new HelpAnswer();
        }

        $ha->type = $params['type'];
        $ha->question = $params['question'];
        $ha->answer = $params['answer'];
        $ha->qr_code = $params['qr_code'];
        $ha->status = $params['status'];

        $ha_res = $ha->save();
        if (!$ha_res){
            DB::rollBack();
            return ['code'=>false,'msg'=>'失败,请重试.'];
        }

        //存入关键字
        $keywords_id_list = [];
        foreach ($params['keywords'] as $v){
            $temp_k = HelpAnswerKeywords::query()
                ->firstOrCreate([
                    'keywords'=>$v
                ]);
            $keywords_id_list[] = $temp_k->id;
        }


        //绑定关键词
        HelpAnswerKeywordsBind::query()
            ->where('help_answer_id','=',$ha->id)
            ->whereNotIn('keywords_id',$keywords_id_list)
            ->delete();

        foreach ($keywords_id_list as $v){
            HelpAnswerKeywordsBind::query()
                ->firstOrCreate([
                    'help_answer_id'=>$ha->id,
                    'keywords_id'=>$v,
                ]);
        }


        DB::commit();
        return ['code'=>true,'msg'=>'成功'];
    }

    public function helpChangeStatus($params, $admin): array
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


}
