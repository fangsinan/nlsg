<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class Node extends Base
{
    protected $table = 'nlsg_node';


    public function getList()
    {
        $res = [];
        $this->getListFromDb($res);
        return $res;
    }

    public function getListFromDb(&$res, $pid = 0, $is_menu = 2)
    {
        $res = Node::query()
            ->where('pid', '=', $pid)
            ->where('status', '=', 1)
            ->where('is_menu', '=', $is_menu)
            ->where('app_project_type','=',APP_PROJECT_TYPE)
            ->select(['id', 'pid', 'name', 'path', 'is_menu', 'status', 'rank', DB::raw('0 as checked')])
            ->orderBy('rank')
            ->orderBy('id')
            ->get();

        if ($res->isNotEmpty()) {
            $res = $res->toArray();
            foreach ($res as &$v) {
                $v['menu'] = [];
                $v['api'] = [];
                $this->getListFromDb($v['menu'], $v['id'], 2);
                $this->getListFromDb($v['api'], $v['id'], 1);
            }
        } else {
            return true;
        }
    }

    public static function getMenuTree($role_id)
    {
        $roleModel = new Role();

        $query = DB::table('nlsg_role_node as rn')
                   ->where('rn.app_project_type','=',APP_PROJECT_TYPE)
            ->join('nlsg_node as n', 'rn.node_id', '=', 'n.id');

        $role_list = $roleModel->getAllRoleId(0, $role_id);

        $role_node_list = RoleNode::whereIn('role_id',$role_list)
            ->pluck('node_id');

        if ($role_id > 1) {
            $query->whereIn('rn.role_id', $role_list);
        }

        $tmp = $query->where('n.pid', '=', 0)
            ->where('n.is_menu', '=', 2)
            ->where('n.status', '=', 1)
            ->groupBy('n.id')
            ->orderBy('n.rank')
            ->orderBy('n.id')
            ->select(['n.id', 'n.pid', 'n.name', 'n.path'])
            ->get();

        if ($tmp->isEmpty()) {
            return [];
        }
        $tmp = $tmp->toArray();
        foreach ($tmp as &$v) {
            $v->menu = self::getMenuTreeRec($v->id, $role_node_list);
        }

        return $tmp;
    }

    private static function getMenuTreeRec($pid, $role_node_list)
    {
        $query = Node::where('pid', '=', $pid);

        if (!empty($role_node_list)) {
            $query->whereIn('id', $role_node_list);
        }

        $list = $query->where('is_menu', '=', 2)
            ->where('status', '=', 1)
            ->where('app_project_type','=',APP_PROJECT_TYPE)
            ->orderBy('rank')
            ->orderBy('id')
            ->select(['id', 'pid', 'name', 'path'])
            ->get();
        if ($list->isNotEmpty()) {
            foreach ($list as &$v) {
                $v->menu = self::getMenuTreeRec($v->id, $role_node_list);
            }
            return $list;
        } else {
            return [];
        }
    }

}
