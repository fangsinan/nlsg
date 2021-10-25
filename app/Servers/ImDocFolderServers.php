<?php

namespace App\Servers;

use App\Models\ImBeatWord;
use App\Models\ImDocFolder;
use App\Models\ImDocFolderBind;
use App\Models\ImDocFolderJob;
use App\Models\ImDocFolderJobInfo;
use App\Models\ImMedia;
use Illuminate\Support\Facades\DB;
use Libraries\ImClient;

class ImDocFolderServers
{
    public function addDoc($params, $user_id)
    {
        $folder_id = $params['folder_id'] ?? 0;
        if (empty($folder_id)) {
            return ['code' => true, 'msg' => '文件夹错误'];
        }

        $check_folder = ImDocFolder::query()
            ->where('id', '=', $folder_id)
            ->where('status', '=', 1)
            ->first();
        if (empty($check_folder)) {
            return ['code' => false, 'msg' => '文件夹错误'];
        }

        $params['add_flag'] = 'new';
        $id_s = new ImDocServers();
        $add_res = $id_s->add($params, $user_id);
        if (!$add_res['res']) {
            return ['code' => false, 'msg' => '添加文案失败'];
        }

        $fbModel = new ImDocFolderBind();
        $fbModel->folder_id = $folder_id;
        $fbModel->doc_id = $add_res['doc_id'];
        $fbModel->status = 1;
        $fbModel->sort = ($this->getDocLastSort() + 1);
        $res = $fbModel->save();
        if ($res === false) {
            return ['code' => false, 'msg' => '添加失败'];
        }

        $log_data = [];
        $log_data['admin_id'] = $user_id;
        $log_data['type'] = 4;
        $log_data['folder_id'] = $folder_id;
        $log_data['remark'] = '文件夹(' . $folder_id . ')添加文案(' . $add_res['doc_id'] . ')';
        DB::table('nlsg_im_doc_folder_log')->insert($log_data);

        $this->docSortNowList($folder_id);

        return ['code' => true, 'msg' => '成功'];
    }

    //文案最大排序
    public function getDocLastSort(int $folder_id = 0)
    {
        if (empty($folder_id)) {
            return 0;
        }

        return ImDocFolderBind::query()
                ->where('folder_id', '=', $folder_id)
                ->where('status', '=', 1)
                ->max('sort') ?? 0;
    }

    //已经存在的文案数据重新规范排序
    public function docSortNowList(int $folder_id = 0)
    {
        if (empty($folder_id)) {
            return ['code' => false, 'msg' => '文件夹错误'];
        }
        $sort_sql = "UPDATE nlsg_im_doc_folder_bind AS fb
                JOIN (
                    SELECT
                        id,
                        folder_id,
                        sort,
                        @line := @line + 1 AS line_num
                    FROM
                        nlsg_im_doc_folder_bind,(
                        SELECT
                            @line := 0
                        ) a
                    WHERE
                        folder_id = $folder_id
                        AND STATUS = 1
                    ORDER BY
                        sort ASC,
                        id ASC
                    ) AS a ON fb.id = a.id
                    SET fb.sort = a.line_num
                WHERE
                    a.sort <> a.line_num";
        DB::select($sort_sql);
        return ['code' => true, 'msg' => '成功'];
    }

    public function list($params, $user_id)
    {
        $tree = [];
        $this->listTree($tree);
        return $tree;
    }

    private function listTree(&$tree, $pid = 0)
    {
        $tree = ImDocFolder::query()
            ->where('pid', '=', $pid)
            ->where('status', '=', 1)
            ->with(['docList', 'docList.docInfo:id,type,type_info,obj_id,cover_img,content'])
            ->select(['id', 'folder_name', 'pid', 'sort'])
            ->orderBy('sort')
            ->orderBy('id')
            ->get();
        if ($tree->isNotEmpty()) {
            $tree = $tree->toArray();
            foreach ($tree as &$v) {
                $this->listTree($v['tree'], $v['id']);
            }
        }
    }

    public function add($params, $user_id)
    {
        $log_data = [];
        $log_data['admin_id'] = $user_id;

        if (!empty($params['id'] ?? 0)) {
            $model = ImDocFolder::query()
                ->where('id', '=', $params['id'])
                ->where('status', '=', 1)
                ->first();
            if (empty($model)) {
                return ['code' => false, 'msg' => 'id错误'];
            }
            $log_data['type'] = 2;

            $log_data['remark'] = '';
            if ($model->folder_name !== $params['folder_name']) {
                $log_data['remark'] .= '文件夹名称:' . $model->folder_name . '->' . $params['folder_name'] . ';';
            }
            if ((int)$model->pid !== (int)$params['pid']) {
                $log_data['remark'] .= '归属:' . $model->pid . '->' . $params['pid'];
            }

        } else {
            $model = new ImDocFolder();
            $log_data['type'] = 1;
        }

        if (empty($params['folder_name'] ?? '')) {
            return ['code' => false, 'msg' => '文件夹名称不能为空'];
        }

        $check_folder_name = ImDocFolder::query()
            ->where('folder_name', 'like', '%' . $params['folder_name'] . '%')
            ->where('status', '=', 1);
        if (!empty($params['id'] ?? 0)) {
            $check_folder_name->where('id', '<>', $params['id']);
        }
        $check_folder_name = $check_folder_name->first();
        if (!empty($check_folder_name)) {
            return ['code' => false, 'msg' => '文件夹名称重复'];
        }

        if (!empty($params['pid'] ?? 0)) {
            $check_pid = ImDocFolder::query()
                ->where('id', '=', $params['pid'])
                ->where('status', '=', 1)
                ->first();
            if (empty($check_pid)) {
                return ['code' => false, 'msg' => '上级文件夹不存在'];
            }
        }

        $model->folder_name = $params['folder_name'] ?? '';
        $model->pid = $params['pid'] ?? 0;
        $model->status = 1;
        $model->sort = ($this->getFolderLastSort() + 1);

        DB::beginTransaction();

        $model_res = $model->save();
        if (!$model_res) {
            DB::rollBack();
            return ['code' => false, 'msg' => '失败'];
        }

        $log_data['folder_id'] = $model->id;
        $log_res = DB::table('nlsg_im_doc_folder_log')->insert($log_data);
        if (!$log_res) {
            DB::rollBack();
            return ['code' => false, 'msg' => '失败'];
        }

        DB::commit();
        $this->folderSortNowList($params['pid']);

        return ['code' => true, 'msg' => '成功'];
    }

    public function getFolderLastSort(int $folder_id = 0)
    {
        if (empty($folder_id)) {
            return 0;
        }

        return ImDocFolder::query()
                ->where('pid', '=', $folder_id)
                ->where('status', '=', 1)
                ->max('sort') ?? 0;
    }

    public function folderSortNowList(int $folder_id = 0): array
    {

        $sort_sql = "UPDATE nlsg_im_doc_folder AS fb
            JOIN (
                SELECT
                    id,
                    sort,
                    @line := @line + 1 AS line_num
                FROM
                    nlsg_im_doc_folder,(
                    SELECT
                        @line := 0
                    ) a
                WHERE
                    pid = $folder_id
                    AND `status` = 1
                ORDER BY
                    sort ASC,
                    id ASC
                ) AS a ON fb.id = a.id
                SET fb.sort = a.line_num
            WHERE
                a.sort <> a.line_num";
        DB::select($sort_sql);
        return ['code' => true, 'msg' => '成功'];

    }

    public function changeStatus($params, $user_id): array
    {
        $id = $params['id'] ?? 0;
        $flag = $params['flag'] ?? '';
        if (empty($id) || empty($flag)) {
            return ['code' => false, 'msg' => '参数错误'];
        }

        $log_data = [];
        $log_data['admin_id'] = $user_id;
        $log_data['type'] = 2;
        $log_data['folder_id'] = $id;

        $check = ImDocFolder::query()
            ->where('id', '=', $id)
            ->first();

        if (empty($check)) {
            return ['code' => false, 'msg' => 'id错误'];
        }

        switch ($flag) {
            case 'del':
                //删除
                if ($check->status === 1) {
                    return ['code' => true, 'msg' => '成功'];
                }
                $check_bind = ImDocFolderBind::query()
                    ->where('folder_id', '=', $id)
                    ->where('status', '=', 1)
                    ->first();
                if (!empty($check_bind)) {
                    return ['code' => false, 'msg' => '清空文件夹后重试'];
                }
                $check->status = 2;
                $log_data['remark'] = '删除';
                break;
            case 'remove':
                //移动
                $pid = (int)($params['pid'] ?? -1);
                if ($pid < 0) {
                    return ['code' => false, 'msg' => '目标参数错误'];
                }

                $log_data['remark'] = '移动' . $check->pid . '->' . $pid;

                if ($pid > 0) {
                    $check_pid = ImDocFolder::query()->where('id', '=', $pid)
                        ->where('status', '=', 1)
                        ->first();
                    if (!empty($check_pid)) {
                        return ['code' => false, 'msg' => '目标错误'];
                    }
                }
                $check->pid = $pid;

                break;
            case 'copy':
                //复制
                $new_data = [];

                $temp_folder_name_list = ImDocFolder::query()
                    ->where('pid', '=', $check->pid)
                    ->where('status', '=', 1)
                    ->where('folder_name', 'like', '%' . $check->folder_name . '_%')
                    ->pluck('folder_name');
                $folder_num = 1;
                foreach ($temp_folder_name_list as $fnv) {
                    $temp_fnv = str_replace($check->folder_name . '_', '', $fnv);
                    if (is_numeric($temp_fnv) && $folder_num <= $temp_fnv) {
                        $folder_num++;
                    }
                }
                $new_data['folder_name'] = $check->folder_name . '_' . $folder_num;

                $new_data['pid'] = $check->pid;
                $new_data['status'] = 1;
                $new_data['sort'] = $check->sort;

                $bind_data = ImDocFolderBind::query()
                    ->where('folder_id', '=', $id)
                    ->where('status', '=', 1)
                    ->get();

                DB::beginTransaction();
                $new_folder_id = DB::table('nlsg_im_doc_folder')->insertGetId($new_data);
                if (!$new_folder_id) {
                    DB::rollBack();
                    return ['code' => false, 'msg' => '复制失败' . __LINE__];
                }

                $new_bind_data = [];
                if ($bind_data->isNotEmpty()) {
                    foreach ($bind_data as $bdv) {
                        $temp_bind_data = [];
                        $temp_bind_data['folder_id'] = $new_folder_id;
                        $temp_bind_data['doc_id'] = $bdv->doc_id;
                        $temp_bind_data['status'] = 1;
                        $temp_bind_data['sort'] = $bdv->sort;
                        $new_bind_data[] = $temp_bind_data;
                    }
                }
                if (!empty($new_bind_data)) {
                    $add_bind_res = DB::table('nlsg_im_doc_folder_bind')->insert($new_bind_data);
                    if (!$add_bind_res) {
                        DB::rollBack();
                        return ['code' => false, 'msg' => '复制失败' . __LINE__];
                    }
                }
                DB::commit();

                $log_data['remark'] = '复制' . $check->id . '->' . $new_folder_id;
                DB::table('nlsg_im_doc_folder_log')->insert($log_data);

                $this->folderSortNowList($check->pid);
                return ['code' => true, 'msg' => '成功'];
            default:
                return ['code' => false, 'msg' => '动作错误'];
        }

        $res = $check->save();
        DB::table('nlsg_im_doc_folder_log')->insert($log_data);
        if ($res) {
            return ['code' => true, 'msg' => '成功'];
        }
        return ['code' => false, 'msg' => '失败'];
    }

    public function changeDocStatus($params, $user_id): array
    {
        $id = $params['id'] ?? 0;
        $flag = $params['flag'] ?? '';
        $folder_id = $params['folder_id'] ?? 0;
        if (empty($id) || empty($flag) || empty($folder_id)) {
            return ['code' => false, 'msg' => '参数错误'];
        }

        $log_data = [];
        $log_data['admin_id'] = $user_id;
        $log_data['type'] = 5;
        $log_data['folder_id'] = $id;

        $sort_id_list = [];
        $sort_id_list[] = $id;

        $check = ImDocFolderBind::query()
            ->where('doc_id', '=', $id)
            ->where('folder_id', '=', $folder_id)
            ->where('status', '=', 1)
            ->first();

        if (empty($check)) {
            return ['code' => false, 'msg' => 'id错误'];
        }

        switch ($flag) {
            case 'del':
                $check->status = 2;
                $log_data['remark'] = '删除';
                break;
            case 'remove':
                $pid = $params['pid'] ?? 0;
                if (empty($pid)) {
                    return ['code' => false, 'msg' => '目标id错误'];
                }
                $check_pid = ImDocFolder::query()
                    ->where('id', '=', $pid)
                    ->where('status', '=', 1)
                    ->first();
                if (empty($check_pid)) {
                    return ['code' => false, 'msg' => '目标不存在'];
                }
                $check_unique = ImDocFolderBind::query()
                    ->where('folder_id', '=', $pid)
                    ->where('doc_id', '=', $id)
                    ->where('status', '=', 1)
                    ->first();
                if (!empty($check_unique)) {
                    return ['code' => false, 'msg' => '目标文件夹已有相同文案'];
                }

                $log_data['remark'] = '移动' . $check->folder_id . '->' . $pid;
                $check->folder_id = $pid;
                $sort_id_list[] = $pid;
                break;
            default:
                return ['code' => false, 'msg' => '动作错误'];
        }

        DB::beginTransaction();
        $res = $check->save();
        if (!$res) {
            DB::rollBack();
            return ['code' => false, 'msg' => '失败'];
        }

        $res = DB::table('nlsg_im_doc_folder_log')->insert($log_data);
        if (!$res) {
            DB::rollBack();
            return ['code' => false, 'msg' => '失败'];
        }

        DB::commit();

        foreach ($sort_id_list as $v) {
            $this->docSortNowList($v);
        }

        return ['code' => true, 'msg' => '成功'];

    }


    private function folderIdList(&$tree, $id, $begin): void
    {
        $query = ImDocFolder::query();

        if ($begin === 1 && $id !== 0) {
            $query->where('id', '=', $id);
        } else {
            $query->where('pid', '=', $id);
        }

        $temp_tree = $query->where('status', '=', 1)
            ->orderBy('sort')
            ->orderBy('id')
            ->pluck('id')
            ->toArray();
        $tree = array_merge($tree, $temp_tree);

        if (!empty($temp_tree)) {
            foreach ($temp_tree as $v) {
                $this->folderIdList($tree, $v, 2);
            }
        }
    }

    public function folderDocList($params, $user_id): array
    {
        $folder_id = $params['folder_id'] ?? 0;
        if (empty($folder_id)) {
            return ['code' => false, 'msg' => '文件夹不能为空'];
        }

        $tree = [];

        $this->folderIdList($tree, $folder_id, 1);

        if (empty($tree)) {
            return [];
        }

        $doc_list = DB::table('nlsg_im_doc_folder_bind as dfb')
            ->join('nlsg_im_doc as d', 'dfb.doc_id', '=', 'd.id')
            ->join('nlsg_im_doc_folder as df', 'dfb.folder_id', '=', 'df.id')
            ->whereIn('dfb.folder_id', $tree)
            ->select(['dfb.folder_id', 'df.folder_name', 'dfb.doc_id', 'dfb.sort as doc_sort', 'd.type',
                'd.type_info', 'd.obj_id', 'd.cover_img', 'd.content', 'd.subtitle'])
            ->orderBy('dfb.sort')
            ->get();

        $res = [];
        foreach ($tree as $v) {
            foreach ($doc_list as $vv) {
                $vv->top_folder_id = $folder_id;
                if ($v === $vv->folder_id) {
                    $res[] = $vv;
                }
            }
        }

        return $res;
    }


    public function jobList($params, $user_id)
    {
        $size = $params['size'] ?? 10;

        $query = ImDocFolderJob::query()->where('status', '=', 1);

        $query->with([
            'folderInfo:id,folder_name',
            'jobInfo:id,id as job_info_id,job_id,folder_id,doc_id,job_time,job_timestamp,job_status',
            'jobInfo.docInfo:id,type,type_info,obj_id,cover_img,content',
            'groupInfo:id,group_id,type,name,status'
        ]);

        $query->select(['id', 'id as job_id', 'folder_id', 'group_id', 'user_id', 'job_begin_at', 'job_type', 'job_status'])
            ->where('status', '<>', 3)
            ->orderBy('job_status', 'desc')
            ->orderBy('job_begin_at', 'desc')
            ->orderBy('id', 'desc');

        return $query->paginate($size);

    }

    public function addJob($params, $user_id): array
    {
        $folder_id = $params['folder_id'] ?? 0;
        $group_id = $params['group_id'] ?? '';
        if (empty($folder_id) || empty($group_id)) {
            return ['code' => false, 'msg' => '参数错误'];
        }
        if (!is_array($group_id)) {
            $group_id = explode(',', $group_id);
        }
        $job_type = (int)($params['job_type'] ?? 0);
        if (!in_array($job_type, [1, 2])) {
            return ['code' => false, 'msg' => '参数错误'];
        }
        $list = $params['list'] ?? [];
        if (empty($list)) {
            return ['code' => false, 'msg' => '数据错误'];
        }

        $now = time();
        $job_begin_at = date('Y-m-d H:i:s', $now);

        $folder_job_data = [];
        $folder_job_info_data = [];

        foreach ($list as $v) {
            $temp_info_data = [];
            $temp_info_data['job_id'] = 0;
            $temp_info_data['folder_id'] = $v['folder_id'];
            $temp_info_data['doc_id'] = $v['doc_id'];
            $temp_info_data['job_time'] = $v['job_time'];
            $temp_info_data['job_timestamp'] = strtotime($v['job_time']);
            if ($job_type === 1 && $v['job_time'] < $job_begin_at) {
                $job_begin_at = $v['job_time'];
            }
            $temp_info_data['status'] = 1;
            $temp_info_data['job_status'] = 1;
            $folder_job_info_data[] = $temp_info_data;
        }

        foreach ($group_id as $giv) {
            $temp_giv = [];
            $temp_giv['folder_id'] = $folder_id;
            $temp_giv['group_id'] = $giv;
            $temp_giv['user_id'] = $user_id;
            $temp_giv['job_begin_at'] = $job_begin_at;
            $temp_giv['status'] = 1;
            $temp_giv['job_type'] = $job_type;
            $temp_giv['job_status'] = 1;
            $folder_job_data[] = $temp_giv;
        }

        DB::beginTransaction();

        $id_list = [];
        foreach ($folder_job_data as $k => $v) {
            $id_list[$k] = DB::table('nlsg_im_doc_folder_job')->insertGetId($v);
            if (!$id_list[$k]) {
                DB::rollBack();
                return ['code' => false, 'msg' => '失败'];
            }
        }

        $folder_job_info = [];
        foreach ($id_list as $v) {
            foreach ($folder_job_info_data as $vv) {
                $vv['job_id'] = $v;
                $folder_job_info[] = $vv;
            }
        }

        $temp_res = DB::table('nlsg_im_doc_folder_job_info')->insert($folder_job_info);
        if (!$temp_res) {
            DB::rollBack();
            return ['code' => false, 'msg' => '失败'];
        }

        DB::commit();
        return ['code' => true, 'msg' => '成功'];
    }

    public function changeJobStatus($params, $user_id): array
    {
        $job_flag = $params['job_flag'] ?? '';
        if (in_array($job_flag, ['job', 'job_info'])) {
            return ['code' => true, 'msg' => '参数错误'];
        }

        $flag = $params['flag'] ?? '';
        if (empty($flag) || !in_array($flag, ['off', 'del'])) {
            return ['code' => true, 'msg' => '参数错误'];
        }
        $job_id = $params['job_id'] ?? 0;
        if (empty($job_id)) {
            return ['code' => false, 'msg' => '参数错误'];
        }
        $check = ImDocFolderJob::query()->where('id', '=', $job_id)->first();
        if (empty($check)) {
            return ['code' => false, 'msg' => 'id错误'];
        }

        if ($job_flag === 'job') {
            switch ($flag) {
                case 'on':
                    $check->status = 1;
                    break;
                case 'off':
                    $check->status = 2;
                    break;
                case 'del':
                    $check->status = 3;
                    break;
            }

            $res = $check->save();
            if ($res) {
                return ['code' => true, 'msg' => '成功'];
            }
            return ['code' => false, 'msg' => '失败'];
        }

        $job_info_id = $params['job_info_id'] ?? 0;
        if (empty($job_info_id)) {
            return ['code' => false, 'msg' => '参数错误'];
        }
        $check_info = ImDocFolderJobInfo::query()->where('id', '=', $job_info_id)->first();
        if (empty($check_info)) {
            return ['code' => false, 'msg' => '任务文案错误'];
        }
        switch ($flag) {
            case 'on':
                if ($check->job_type === 1) {
                    $job_time = $params['job_time'] ?? '';
                    if (empty($job_time)) {
                        return ['code' => false, 'msg' => '时间不能为空'];
                    }
                }
                $check_info->job_time = $job_time;
                $check_info->job_timestamp = strtotime($job_time);
                $check_info->status = 1;
                break;
            case 'off':
                $check_info->status = 2;
                break;
            case 'del':
                $check_info->status = 3;
                break;
        }
        $res = $check_info->save();
        if ($res) {
            return ['code' => true, 'msg' => '成功'];
        }
        return ['code' => false, 'msg' => '失败'];

    }

    public function sendJob($limit = 2)
    {
        $limit = $limit < 2 ? 2 : $limit;
        $end_time = strtotime(date('Y-m-d H:i:59', strtotime('+' . ($limit - 1) . ' minute')));

        $list = ImDocFolderJobInfo::query()
            ->with(['jobTop:id,status,group_id,user_id', 'docInfo', 'jobTop.groupInfo:id,group_id'])
            ->where('status', '=', 1)
            ->where('job_status', '=', 1)
            ->where('job_timestamp', '<=', $end_time)
            ->whereHas('jobTop', function ($q) {
                $q->where('status', '=', 1);
            })
            ->orderBy('job_timestamp')
            ->orderBy('id')
            ->select(['id', 'job_id', 'doc_id', 'job_time', 'job_timestamp', 'job_status'])
            ->get();
        if ($list->isEmpty()) {
            return true;
        }

        $list = $list->toArray();

        //获取发送任务
        foreach ($list as $k => &$v) {
            $v['send_times'] = 0;
            $v['sendData'] = [];
            $group_id = $v['job_top']['group_info']['group_id'] ?? '';
            $send_user_id = $v['job_top']['user_id'] ?? 0;
            if (empty($group_id) || empty($send_user_id)) {
                ImDocFolderJob::query()->where('id', '=', $v['job_id'])
                    ->update([
                        'status' => 2, 'remark' => '信息错误' . $group_id . '->' . $send_user_id
                    ]);
                ImDocFolderJobInfo::query()->where('id', '=', $v['id'])
                    ->update([
                        'status' => 2, 'remark' => '信息错误' . $group_id . '->' . $send_user_id
                    ]);
                unset($list[$k]);
                continue;
            }
            $v['sendData'] = $this->getSendGroupMsgPostData($v['doc_info'], $group_id, $send_user_id);
            unset($v['doc_info']);
        }

        $url = ImClient::get_im_url("https://console.tim.qq.com/v4/group_open_http_svc/send_group_msg");

        $while_flag = true;
        $beat_word = [];

        while ($while_flag) {
            if (empty($list)) {
                $while_flag = false;
            }
            if (time() > $end_time) {
                $while_flag = false;
            }

            foreach ($list as $k => $v) {
                if ($v['job_timestamp'] <= time()) {
                    $temp_res = ImClient::curlPost($url, json_encode($v['sendData']));
                    $temp_res = json_decode($temp_res, true);

                    if ($temp_res['ErrorCode'] === 0) {
                        ImDocFolderJobInfo::query()
                            ->where('id', '=', $v['id'])
                            ->update([
                                'job_status' => 3
                            ]);


                        DB::select("UPDATE nlsg_im_doc_folder_job AS j SET j.job_status = 3
                            WHERE
                                j.id = " . $v['job_id'] . "
                                AND NOT EXISTS (
                                SELECT * FROM nlsg_im_doc_folder_job_info
                                WHERE job_id = j.id AND STATUS = 1 AND job_status = 1
                        )");

                    } else {
                        if ($temp_res['ErrorCode'] === 80001) {
                            $str_1 = strpos($temp_res['ErrorInfo'], 'beat word:');
                            $str_2 = strpos($temp_res['ErrorInfo'], '|requestid');
                            $str = substr($temp_res['ErrorInfo'], $str_1 + 10, $str_2 - $str_1 - 10);
                            $beat_word[] = trim($str);
                        }

                        ImDocFolderJobInfo::query()
                            ->where('id', '=', $v['id'])
                            ->update([
                                'status' => 2,
                                'remark' => $temp_res['ErrorInfo'] ?? ''
                            ]);

                    }
                    unset($list[$k]);
                }
            }
        }

        if (!empty($beat_word)) {
            foreach ($beat_word as $bwv) {
                ImBeatWord::query()->updateOrCreate(
                    array('beat_word' => $bwv),
                    array('times' => DB::raw('times+1'))
                );
            }
        }

        return true;
    }

    //拼接消息体
    public function getSendGroupMsgPostData($doc_info, $to, $from)
    {
        try {
            $idServers = new ImDocServers();
            $temp_post_data = [];
            $temp_post_data['GroupId'] = $to;
            $temp_post_data['From_Account'] = (string)$from;
            $temp_post_data['Random'] = $idServers->getMsgRandom();
            $temp_post_data['MsgBody'] = [];
            $temp_msg_type = 0;
            switch ((int)$doc_info['type_info']) {
                case 11:
                    if (empty($temp_msg_type)) {
                        $temp_msg_type = 7;
                    }
                case 12:
                    if (empty($temp_msg_type)) {
                        $temp_msg_type = 2;
                    }
                case 13:
                    if (empty($temp_msg_type)) {
                        $temp_msg_type = 3;
                    }
                case 14:
                    if (empty($temp_msg_type)) {
                        $temp_msg_type = 6;
                    }
                case 15:
                    if (empty($temp_msg_type)) {
                        $temp_msg_type = 9;
                    }
                case 17:
                    if (empty($temp_msg_type)) {
                        $temp_msg_type = 10;
                    }
                case 16:
                    if (empty($temp_msg_type)) {
                        $temp_msg_type = 11;
                    }
                case 18:
                    if (empty($temp_msg_type)) {
                        $temp_msg_type = 4;
                    }
                case 19:
                    if (empty($temp_msg_type)) {
                        $temp_msg_type = 8;
                    }
                    //类型 11:讲座 12课程 13商品 14会员 15直播 16训练营 17外链 18线下课 19听书
                    $custom_elem_body = [
                        "goodsID" => $temp_msg_type === 10 ? $doc_info['subtitle'] : (string)$doc_info['obj_id'],
                        "cover_pic" => $doc_info['cover_img'],
                        "titleName" => $doc_info['content'],
                        "subtitle" => $doc_info['subtitle'],
                        "type" => (string)$temp_msg_type,
                    ];

                    $custom_elem_body = json_encode($custom_elem_body);
                    $temp_post_data['MsgBody'][] = [
                        "MsgType" => "TIMCustomElem", // 自定义,不成功
                        "MsgContent" => [
                            "Data" => $custom_elem_body,
                            "Desc" => '',
                            'Ext' => '',
                            'Sound' => '',
                        ]
                    ];
                    $post_data_array[] = $temp_post_data;
                    break;
                case 211://音频
                    $temp_post_data['MsgBody'][] = [
                        "MsgType" => "TIMSoundElem",//音频
                        "MsgContent" => [
                            "Url" => $doc_info['file_url'],
                            "Size" => $doc_info['file_size'],
                            "Second" => $doc_info['second'],
                            "Download_Flag" => 2
                        ]
                    ];
                    $post_data_array[] = $temp_post_data;
                    break;
                case 22://视频
                    $temp_media_id = $doc_info['media_id'];
                    $temp_media_info = ImMedia::query()->where('media_id', '=', $temp_media_id)->first();
                    if (!empty($temp_media_info->second ?? 0)) {
                        $temp_video_second = (int)(round($temp_media_info->second));
                    } else {
                        $temp_video_second = $doc_info['second'];
                    }

                    $temp_post_data['MsgBody'][] = [
                        "MsgType" => "TIMVideoFileElem",//视频
                        "MsgContent" => [
                            "VideoUrl" => str_replace('https:', 'http:', $doc_info['file_url']),
                            "VideoUUID" => $idServers->getMsgRandom(),
                            "videouuid" => $idServers->getMsgRandom(),
                            "VideoSize" => $doc_info['file_size'],
                            "VideoSecond" => $temp_video_second,
                            "VideoFormat" => $doc_info['format'],
                            "VideoDownloadFlag" => 2,
                            "ThumbUrl" => str_replace('https:', 'http:', $doc_info['cover_img']),
                            "ThumbUUID" => $idServers->getMsgRandom(),
                            "ThumbSize" => $doc_info['img_size'],
                            "ThumbWidth" => $doc_info['img_width'],
                            "ThumbHeight" => $doc_info['img_height'],
                            "ThumbFormat" => $doc_info['img_format'],
                            "ThumbDownloadFlag" => 2
                        ]
                    ];
                    $post_data_array[] = $temp_post_data;
                    break;
                case 23:
                    //图片
                    $file_url = explode(';', $doc_info['file_url']);
                    $file_url = array_filter($file_url);
                    foreach ($file_url as $fuv) {
                        $temp_format = pathinfo($fuv[0])['extension'] ?? 'jpg';
                        $temp_format_num = 255;
                        switch (strtolower($temp_format)) {
                            case 'jpg':
                                $temp_format_num = 1;
                                break;
                            case 'gif':
                                $temp_format_num = 2;
                                break;
                            case 'png':
                                $temp_format_num = 3;
                                break;
                            case 'bmp':
                                $temp_format_num = 4;
                                break;
                        }
                        $temp_post_data['Random'] = $idServers->getMsgRandom();
                        $temp_post_data['MsgBody'] = [];
                        $fuv = explode(',', $fuv);
                        $temp_fuv_width = (int)$fuv[2];
                        $temp_fuv_height = (int)$fuv[3];
                        $temp_wh_str = '';
                        $img_url = $fuv[0];
                        $img_url = str_replace('https:', 'http:', $img_url);

                        if ($temp_fuv_height > 396 && $temp_fuv_width > 396) {
                            $temp_w_c = floor($temp_fuv_width / 198);
                            $temp_h_c = floor($temp_fuv_height / 198);

                            $temp_wh = $temp_h_c > $temp_w_c ? $temp_w_c : $temp_h_c;
                            $temp_wh = floor(100 / $temp_wh);
                            $temp_wh = $temp_wh > 50 ? 50 : $temp_wh;
                            $temp_wh_str = '?x-oss-process=image/resize,p_' . $temp_wh;
                        }

                        $temp_post_data['MsgBody'][] = [
                            "MsgType" => "TIMImageElem",
                            "MsgContent" => [
                                "UUID" => 'ali_rest_image_' . $idServers->getMsgRandom() . '.' . $temp_format,
                                "ImageFormat" => $temp_format_num,
                                "ImageInfoArray" => [
                                    [
                                        "Type" => 1,
                                        "Size" => (int)$fuv[1],
                                        "Width" => (int)$fuv[2],
                                        "Height" => (int)$fuv[3],
                                        "URL" => $img_url,
                                    ],
                                    [
                                        "Type" => 2,
                                        "Size" => (int)$fuv[1],
                                        "Width" => 0,
                                        "Height" => 0,
                                        "URL" => $img_url,
                                    ],
                                    [
                                        "Type" => 3,
                                        "Size" => (int)$fuv[1],
                                        "Width" => 0,
                                        "Height" => 0,
                                        "URL" => $img_url . $temp_wh_str,
                                    ],
                                ]
                            ]
                        ];
                        $post_data_array[] = $temp_post_data;
                    }
                    break;
                case 21:
                    //音频
                    $temp_post_data['MsgBody'][] = [
                        "MsgType" => "TIMFileElem",
                        "MsgContent" => [
                            "Url" => str_replace('https:', 'http:', $doc_info['file_url']),
                            "FileSize" => $doc_info['file_size'],
                            "FileName" => $doc_info['content'],
                            "Download_Flag" => 2,
                        ]
                    ];
                    $post_data_array[] = $temp_post_data;
                    break;
                case 24:
                    //文件
                    $temp_post_data['MsgBody'][] = [
                        "MsgType" => "TIMFileElem",
                        "MsgContent" => [
                            "Url" => str_replace('https:', 'http:', $doc_info['file_url']),
                            "FileSize" => $doc_info['file_size'],
                            "FileName" => $doc_info['content'],
                            "Download_Flag" => 2,
                        ]
                    ];
                    $post_data_array[] = $temp_post_data;
                    break;
                case 31:
                    //文本
                    $temp_post_data['MsgBody'][] = [
                        "MsgType" => "TIMTextElem",
                        "MsgContent" => [
                            "Text" => $doc_info['content'],
                        ]
                    ];
                    $post_data_array[] = $temp_post_data;
                    break;
            }

            return $temp_post_data;
        } catch (\Exception $e) {
            return [];
        }
    }

}
