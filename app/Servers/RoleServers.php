<?php


namespace App\Servers;


use App\Models\Node;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class RoleServers
{
    public function nodeList($params, $admin_id)
    {
        $nodeModel = new Node();
        return $nodeModel->getList();
    }

    public function nodeListCreate($params, $admin_id)
    {
        $id = $params['id'] ?? 0;
        $name = $params['name'] ?? '';
        $path = $params['path'] ?? '#';

        if (empty($name)) {
            return ['code' => false, 'msg' => '名称不能为空'];
        }
        if ($path != '#') {
            $tmp_path = explode('/', $path);
            if (count($tmp_path) != 2) {
                return ['code' => false, 'msg' => '路径格式错误'];
            }
        }

        if ($id) {
            //编辑只能修改name,path
            $check = Node::where('id', '=', $id)->first();
            if (($check->status ?? 0) == 1) {

                if ($path == '#' && $check->pid != 0) {
                    return ['code' => false, 'msg' => '路径错误'];
                }

                $check->name = $name;
                $check->path = $path;

                $res = $check->save();
                if ($res) {
                    return ['code' => true, 'msg' => '成功'];
                } else {
                    return ['code' => false, 'msg' => '失败'];
                }

            } else {
                return ['code' => false, 'msg' => 'id错误'];
            }

        } else {
            //目录下面能添加目录和api
            //接口下方不得添加目录
            $pid = $params['pid'] ?? 0;
            $is_menu = $params['is_menu'] ?? 0;

            if ($is_menu == 1 && $pid == 0) {
                return ['code' => false, 'msg' => 'api上级需是目录'];
            }

            if ($pid != 0) {
                $check_pid = Node::where('id', '=', $pid)->first();
                if (empty($check_pid)) {
                    return ['code' => false, 'msg' => '父类id错误'];
                }
                if ($check_pid->is_menu == 1 && $is_menu == 2) {
                    return ['code' => false, 'msg' => 'api不可添加目录下级'];
                }
                if ($check_pid->is_menu == 1 && $is_menu == 1) {
                    return ['code' => false, 'msg' => 'api上级需是目录'];
                }
            }

            $tmpModel = new Node();
            $tmpModel->pid = $pid;
            $tmpModel->name = $name;
            $tmpModel->path = $path;
            $tmpModel->is_menu = $is_menu;
            $res = $tmpModel->save();
            if ($res) {
                return ['code' => true, 'msg' => '成功'];
            } else {
                return ['code' => false, 'msg' => '失败'];
            }
        }


    }

    public function nodeListStatus($params, $admin_id)
    {
        $flag = $params['flag'] ?? '';
        $id = $params['id'] ?? 0;
        if (empty($id)) {
            return ['code' => false, 'msg' => 'id错误'];
        }
        $check = Node::where('id', '=', $id)->first();
        if (empty($check)) {
            return ['code' => false, 'msg' => 'id错误'];
        }

        switch ($flag) {
            case 'del':
                $check->status = 0;
                $res = $check->save();
                if ($res) {
                    return ['code' => true, 'msg' => '成功'];
                } else {
                    return ['code' => false, 'msg' => '失败'];
                }
            case 'rank':
                $rank = $params['rank'] ?? 0;
                if ($rank < 1 || $rank > 99) {
                    return ['code' => false, 'msg' => '排序范围需在1-99'];
                }

                $before_arr = Node::where('id', '<>', $id)
                    ->where('rank', '<=', $rank)
                    ->where('rank', '<>', 99)
                    ->limit($rank - 1)
                    ->orderBy('rank', 'asc')
                    ->orderBy('id', 'asc')
                    ->pluck('id')
                    ->toArray();

                $rank = $rank > (count($before_arr) + 1) ? (count($before_arr) + 1) : $rank;

                $after_arr = Node::where('id', '<>', $id)
                    ->where('rank', '<>', 99)
                    ->whereNotIn('id', $before_arr)
                    ->orderBy('rank', 'asc')
                    ->orderBy('id', 'asc')
                    ->pluck('id')
                    ->toArray();

                DB::beginTransaction();
                $r1 = Node::where('id', '=', $id)->update([
                    'rank' => $rank
                ]);
                if ($r1 === false) {
                    DB::rollBack();
                    return ['code' => false, 'msg' => '失败'];
                }


                $r2 = true;
                foreach ($before_arr as $k => $v) {
                    $temp_r2 = Node::where('id', '=', $v)
                        ->update([
                            'rank' => $k + 1
                        ]);
                    if ($temp_r2 === false) {
                        $r2 = false;
                    }
                }
                if ($r2 === false) {
                    DB::rollBack();
                    return ['code' => false, 'msg' => '失败'];
                }

                $r3 = true;
                foreach ($after_arr as $k => $v) {
                    $temp_r3 = Node::where('id', '=', $v)
                        ->update([
                            'rank' => $k + 1 + $rank
                        ]);
                    if ($temp_r3 === false) {
                        $r3 = false;
                    }
                }
                if ($r3 === false) {
                    DB::rollBack();
                    return ['code' => false, 'msg' => '失败'];
                }

                DB::commit();
                return ['code' => true, 'msg' => '成功'];

            default:
                return ['code' => false, 'msg' => '修改类型错误'];
        }
    }

    public function roleList($params,$admin_id){
        $roleModel = new Role();
        return $roleModel->getList();
    }

}
