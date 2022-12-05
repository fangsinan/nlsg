<?php


namespace App\Servers;


use App\Models\Node;
use App\Models\Role;
use App\Models\RoleNode;
use Illuminate\Support\Facades\DB;

class RoleServers
{
    public function nodeList($params, $admin_id)
    {
        $nodeModel = new Node();
        $list      = $nodeModel->getList();

        //如果指定用户id或者角色id
        $user_id = $params['user_id'] ?? 0;
        $role_id = $params['role_id'] ?? 0;


        //获取用户或者角色下属的所有角色
        $roleModel = new Role();
        $role_list = $roleModel->getAllRoleId($user_id, $role_id);

        //获取所有对应权限
        if (!empty($role_list)) {
            $rn_list = RoleNode::whereIn('role_id', $role_list)
                ->pluck('node_id')
                ->toArray();

            if (!empty($rn_list)) {
                //有值,则遍历list 修改选择状态
                $this->nodeListChecked($list, $rn_list);
            }
        }

        return $list;
    }

    private function nodeListChecked(&$list, $rn)
    {
        foreach ($list as &$v) {
            if (isset($v['checked']) && $v['checked'] == 0) {
                if (in_array($v['id'], $rn)) {
                    $v['checked'] = 1;
                }
                if (!empty($v['menu'])) {
                    $this->nodeListChecked($v['menu'], $rn);
                }
                if (!empty($v['api'])) {
                    $this->nodeListChecked($v['api'], $rn);
                }
            } else {
                return true;
            }
        }
    }

    public function roleSelectList($params, $admin_id)
    {
        $list = Role::where('status', '=', 1)
            ->select(['id', 'pid', 'name', DB::raw('name as name_path')])
            ->orderBy('pid')
            ->orderBy('id')
            ->get();

        foreach ($list as &$v) {
            $tmp = [];
            $this->roleSelectListRec($tmp, $v->pid, $list);
            $tmp = implode('-', $tmp);
            if (!empty($tmp)) {
                $v->name_path = $tmp . '-' . $v->name;
            }
        }

        return $list;
    }

    private function roleSelectListRec(&$tmp, $pid, $list)
    {
        if ($pid > 0) {
            foreach ($list as $v) {
                if ($v->id == $pid) {
                    if ($v->pid > 0) {
                        $this->roleSelectListRec($tmp, $v->pid, $list);
                    }
                    array_push($tmp, $v->name);
                }
            }
        }
    }

    public function roleCreate($params, $admin_id)
    {
        $id     = $params['id'] ?? 0;
        $name   = $params['name'] ?? '';
        $status = $params['status'] ?? 1;

        if (empty($name) || !in_array($status, [1, 2])) {
            return ['code' => false, 'msg' => '参数错误'];
        }

        if ($id) {
            $check = Role::where('id', '=', $id)->first();
            if ($check) {
                $check->name   = $name;
                $check->status = $status;

                $res = $check->save();
            } else {
                return ['code' => false, 'msg' => '角色不存在'];
            }
        } else {
            $rm         = new Role();
            $rm->name   = $name;
            $rm->status = $status;
            $res        = $rm->save();
        }

        if ($res) {
            return ['code' => true, 'msg' => '成功'];
        } else {
            return ['code' => false, 'msg' => '失败'];
        }
    }

    public function nodeListCreate($params, $admin_id)
    {
        $id   = $params['id'] ?? 0;
        $name = $params['name'] ?? '';
        $path = $params['path'] ?? '#';

        if (empty($name)) {
            return ['code' => false, 'msg' => '名称不能为空'];
        }
//        if ($path != '#') {
//            $tmp_path = explode('/', $path);
//            if (count($tmp_path) > 2) {
//                return ['code' => false, 'msg' => '路径格式错误'];
//            }
//        }

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
            $pid     = $params['pid'] ?? 0;
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

            $tmpModel          = new Node();
            $tmpModel->pid     = $pid;
            $tmpModel->name    = $name;
            $tmpModel->path    = $path;
            $tmpModel->is_menu = $is_menu;
            $res               = $tmpModel->save();
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
        $id   = $params['id'] ?? 0;
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
                $res           = $check->save();
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

    public function roleList($params, $admin_id)
    {
        $roleModel = new Role();
        return $roleModel->getList();
    }

    public function roleNodeBind($params, $admin_id)
    {
        $node_id = $params['node_id'] ?? [];
        $role_id = $params['role_id'] ?? 0;

        if (empty($role_id)) {
            return ['code' => false, 'msg' => '角色id不能为空'];
        }

        if (!is_array($node_id)) {
            $node_id = explode(',', $node_id);
        }

        //最多3层
        $node_p1 = Node::query()
            ->whereIn('id', $node_id)
            ->where('status','=',1)
            ->pluck('pid')
            ->toArray();

        $node_p2 = Node::query()
            ->whereIn('id', $node_p1)
            ->where('status','=',1)
            ->pluck('pid')
            ->toArray();

        $node_id = array_unique(array_merge($node_id, $node_p1, $node_p2));

        //已有的
        $already_node = RoleNode::query()->where('role_id', '=', $role_id)->pluck('node_id')->toArray();

        //需要添加的
        $add_node = array_diff($node_id, $already_node);

        //需要删除的
        $del_node = array_diff($already_node, $node_id);


        DB::beginTransaction();
        if (!empty($add_node)) {
            $add_data = [];
            foreach ($add_node as $v) {
                $tmp            = [];
                $tmp['role_id'] = $role_id;
                $tmp['node_id'] = $v;
                $add_data[]     = $tmp;
            }
            $res_add = DB::table('nlsg_role_node')->insert($add_data);
            if (!$res_add) {
                DB::rollBack();
                return ['code' => false, 'msg' => '失败'];
            }
        }

        if (!empty($del_node)) {
            $res_del = RoleNode::query()
                ->where('role_id', '=', $role_id)
                ->whereIn('node_id', $del_node)
                ->delete();
            if (!$res_del) {
                DB::rollBack();
                return ['code' => false, 'msg' => '失败'];
            }
        }

        DB::commit();
        return ['code' => true, 'msg' => '成功'];

    }

}
