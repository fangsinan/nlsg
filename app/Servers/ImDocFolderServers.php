<?php

namespace App\Servers;

use App\Models\ImDocFolder;
use App\Models\ImDocFolderBind;
use Illuminate\Support\Facades\DB;

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

    public function changeDocStatus($params, $user_id)
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


    private function folderIdList(&$tree, $id, $begin)
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

    public function folderDocList($params, $user_id)
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

    }

    public function addJob($params, $user_id)
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
        if (empty($list)){
            return ['code'=>false,'msg'=>'数据错误'];
        }

        $now = time();
        $job_begin_at = date('Y-m-d H:i:s',$now);

        $folder_job_data = [];
        $folder_job_info_data = [];

        foreach ($list as $v){
            $temp_info_data = [];
            $temp_info_data['job_id'] = 0;
            $temp_info_data['folder_id'] = $v['folder_id'];
            $temp_info_data['doc_id'] = $v['doc_id'];
            $temp_info_data['job_time'] = $v['job_time'];
            $temp_info_data['job_timestamp'] = strtotime($v['job_time']);
            if ($job_type === 1 && $v['job_time'] < $job_begin_at){
                $job_begin_at = $v['job_time'];
            }
            $temp_info_data['status'] = 1;
            $temp_info_data['job_status'] = 1;
            $folder_job_info_data[] = $temp_info_data;
        }

        foreach ($group_id as $giv){
            $temp_giv = [];
            $temp_giv['folder_id'] = $folder_id;
            $temp_giv['group_id'] = $giv;
            $temp_giv['user_id'] = $user_id;
            $temp_giv['job_begin_at'] = $job_begin_at;
            $temp_giv['status'] = 1;
            $temp_giv['job_type'] = $job_type;

        }







        return [$params,$folder_job_data,$folder_job_info_data];
    }

    public function changeJobStatus($params, $user_id)
    {

    }

}
