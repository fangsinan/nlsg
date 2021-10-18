<?php

namespace App\Servers;

use App\Models\ImDocFolder;
use App\Models\ImDocFolderBind;
use Illuminate\Support\Facades\DB;

class ImDocFolderServers
{
    public function list($params, $user_id)
    {

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
        return ['code' => true, 'msg' => '成功'];

    }

    public function changeStatus($params, $user_id): array
    {
        $id = $params['id'] ?? 0;
        $flag = $params['flag'] ?? '';
        if (empty($id) || empty($flag)) {
            return ['code' => false, 'msg' => '参数错误'];
        }

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
                break;
            case 'remove':
                //移动
                $pid = (int)($params['pid'] ?? -1);
                if ($pid < 0) {
                    return ['code' => false, 'msg' => '目标参数错误'];
                }
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
                //todo 复制
                break;
            default:
                return ['code' => false, 'msg' => '动作错误'];
        }

        $res = $check->save();
        if ($res) {
            return ['code' => true, 'msg' => '成功'];
        }
        return ['code' => false, 'msg' => '失败'];

    }
}
