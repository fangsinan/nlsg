<?php


namespace App\Servers;


use App\Models\ImDoc;
use App\Models\ImDocSendJob;
use App\Models\ImDocSendJobInfo;
use Illuminate\Support\Facades\DB;

class ImDocServers
{
    public function add($params,$user_id)
    {
        if (!empty($params['id'] ?? 0)) {
            $docModel = ImDoc::where('id', '=', $params['id'])
                ->where('status', '=', 1)
                ->select(['id'])->first();
            if (empty($docModel)) {
                return ['code' => false, 'msg' => 'id错误'];
            }
        } else {
            $docModel = new ImDoc();
        }

        $type = $params['type'] ?? 0;
        if (!in_array($type, [1, 2, 3])) {
            return ['code' => false, 'msg' => '内容类型错误'];
        }

        $type_info = $params['type_info'] ?? 0;
        $obj_id = $params['obj_id'] ?? 0;
        $content = $params['content'] ?? '';
        $cover_img = $params['cover_img'] ?? '';
        $status = $params['status'] ?? 1;
        $file_url = $params['file_url'] ?? '';
        if (!in_array($status, [1, 2])) {
            return ['code' => false, 'msg' => '状态错误'];
        }

        if ($type_info < ($type * 10) || $type_info > ($type * 10 + 9)) {
            return ['code' => false, 'msg' => '详细类型错误'];
        }


        //1商品 2附件 3文本
        switch (intval($params['type'])) {
            case 1:
                // 11:讲座 12课程 13商品 14会员 15直播 16训练营
                if (empty($obj_id)) {
                    return ['code' => false, 'msg' => '目标id错误'];
                }

                break;
            case 2:
                //21音频 22视频 23图片
                if (empty($content)) {
                    return ['code' => false, 'msg' => '内容不能为空'];
                }
                if (empty($file_url)) {
                    return ['code' => false, 'msg' => '附件地址不能为空'];
                }

                break;
            case 3:
                //31文本
                if (empty($content)) {
                    return ['code' => false, 'msg' => '内容不能为空'];
                }

                break;
        }

        $docModel->type = $type;
        $docModel->type_info = $type_info;
        $docModel->obj_id = $obj_id;
        $docModel->cover_img = $cover_img;
        $docModel->content = $content;
        $docModel->file_url = $file_url;
        $docModel->status = $status;
        $docModel->user_id = $user_id;

        $res = $docModel->save();

        if ($res) {
            return ['code' => true, 'msg' => '成功'];
        } else {
            return ['code' => false, 'msg' => '失败'];
        }
    }

    public function list($params)
    {
        $size = $params['size'] ?? 10;
        $query = ImDoc::query();
        if (!empty($params['id'] ?? 0)) {
            $query->where('id', '=', $params['id']);
        }
        if (!empty($params['type_info'] ?? 0)) {
            $query->where('type_info', '=', $params['type_info']);
        }
        $query->where('status', '=', 1)
            ->orderBy('id', 'desc')
            ->select([
                'id', 'type', 'type_info', 'obj_id', 'cover_img', 'content', 'file_url'
            ]);

        return $query->paginate($size);
    }

    public function changeStatus($params,$user_id)
    {
        $id = $params['id'] ?? 0;
        $flag = $params['flag'] ?? '';
        $check = ImDoc::where('id', '=', $id)->first();
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

    public function addSendJob($params)
    {
        if (!empty($params['id'] ?? 0)) {
            $jobModel = ImDocSendJob::where('id', '=', $params['id'])
                ->whereIn('status', [1, 2])
                ->select(['id'])->first();
            if (empty($jobModel)) {
                return ['code' => false, 'msg' => 'id错误'];
            } else {
                if (in_array($jobModel->is_done, [2, 3])) {
                    return ['code' => false, 'msg' => '发送中和已完成的任务无法编辑'];
                }
            }
        } else {
            $jobModel = new ImDocSendJob();
        }

        $doc_id = $params['doc_id'] ?? 0;
        $send_type = $params['send_type'] ?? 0;
        $send_at = $params['send_at'] ?? '';
        $info = $params['info'] ?? [];

        $check_doc = ImDoc::where('id', '=', $doc_id)->where('status', '=', 1)->first();
        if (empty($check_doc)) {
            return ['code' => false, 'msg' => '文案不存在'];
        }
        if (!in_array($send_type, [1, 2])) {
            return ['code' => false, 'msg' => '发送时间类型错误'];
        }
        if ($send_type == 2) {
            if (empty($send_at)) {
                return ['code' => false, 'msg' => '定时时间不能为空'];
            } else {
                $temp_line = date('Y-m-d H:i:00', strtotime('+1 minute'));
                if ($send_at <= $temp_line) {
                    return ['code' => false, 'msg' => '发送时间错误' . $temp_line];
                }
            }
        }else{
            $send_at = date('Y-m-d H:i:s');
        }

        $add_info_data = [];
        foreach ($info as $v) {
            $temp_type = $v['type'] ?? 0;
            if (!in_array($temp_type, [1, 2, 3])) {
                return ['code' => false, 'msg' => '发送对象类型错误'];
            }
            foreach ($v['list'] as $vv) {
                $temp_info_data = [];
                $temp_info_data['send_obj_type'] = $temp_type;
                $temp_info_data['send_obj_id'] = $vv;
                $add_info_data[] = $temp_info_data;
            }
        }

        if (empty($add_info_data)) {
            return ['code' => false, 'msg' => '发送目标数据不能为空'];
        }

        $jobModel->doc_id = $doc_id;
        $jobModel->status = 1;
        $jobModel->send_type = $send_type;
        $jobModel->send_at = $send_at;
        $jobModel->is_done = 1;

        DB::beginTransaction();

        $job_ers = $jobModel->save();
        if ($job_ers == false) {
            DB::rollBack();
            return ['code' => false, 'msg' => '失败' . __LINE__];
        }

        if (!empty($params['id'])) {
            $del_res = ImDocSendJobInfo::where('job_id', '=', $jobModel->id)->delete();
            if ($del_res == false) {
                DB::rollBack();
                return ['code' => false, 'msg' => '失败' . __LINE__];
            }
        }

        foreach ($add_info_data as &$v) {
            $v['job_id'] = $jobModel->id;
        }

        $add_res = DB::table('nlsg_im_doc_send_job_info')->insert($add_info_data);
        if ($add_res == false) {
            DB::rollBack();
            return ['code' => false, 'msg' => '失败' . __LINE__];
        }

        DB::commit();
        return ['code' => true, 'msg' => '成功'];
    }

    public function sendJobList($params)
    {
        return $params;
    }

    public function changeJobStatus($params)
    {

        return $params;
    }
}
