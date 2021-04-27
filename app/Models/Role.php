<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Base
{
    protected $table = 'nlsg_role';

    /**
     * 获取角色的权限节点数组
     * @param $roleId
     * @return array
     */
    public function getRoleAuthNodeMap($roleId)
    {
        $nodeIds = RoleNode::where('role_id', $roleId)->pluck('node_id')->toArray();
        if ($nodeIds) {
            $res =  $this->getRoleNode($nodeIds);
        }
        return $res;
    }

    /**
     * 获取角色节点信息
     * @param $roleNode
     * @param $roleId
     * @return array
     */
    public function getRoleNode($ids)
    {
        if (!$ids){
            return  false;
        }
        $nodeModel = new Node();
        $nodeInfo = $nodeModel->whereIn('id', $ids)->get()->toArray();

        $data = [];
        if (!empty($nodeInfo)) {
            foreach ($nodeInfo as $vo) {
                if (empty($vo['path']) || '#' == $vo['path']) {
                    continue;
                }
                $data[] = $vo['path'];
            }
        }
        return $data;
    }

    public function getList(){
        $res = [];
        $this->getListFromDb($res);
        return $res;
    }

    public function getListFromDb(&$res, $pid = 0)
    {
        $res = self::query()
            ->where('pid', '=', $pid)
            ->where('status', '=', 1)
            ->select(['id', 'pid', 'name'])
            ->orderBy('id')
            ->get();

        if ($res->isNotEmpty()) {
            $res = $res->toArray();
            foreach ($res as &$v) {
                $v['role'] = [];
                $this->getListFromDb($v['role'], $v['id']);
            }
        } else {
            return true;
        }
    }

    public function getAllRoleId($user_id = 0,$role_id = 0){
        $res = [];

        if ($user_id){
            $check_user = BackendUser::where('id','=',$user_id)->first();
            if (!empty($check_user)){
                if (!in_array($check_user->role_id,$res)){
                    array_push($res,$check_user->role_id);
                }

            }
            if (empty($role_id)){
                $role_id = $check_user->role_id;
            }
        }

        if ($role_id){
            $check_role = self::where('id','=',$role_id)->first();
            if (!empty($check_role)){
                if (!in_array($role_id,$res)){
                    array_push($res,$role_id);
                }
                $this->getAllRoleIdRec($res,$role_id);
            }
        }

        return $res;
    }

    private function getAllRoleIdRec(&$list,$pid=0){
        if (!empty($pid)){
            $tmp = self::where('pid','=',$pid)->where('status','=',1)->select(['id'])->get();
            if ($tmp->isNotEmpty()){
                foreach ($tmp as $v){
                    if (!in_array($v->id,$list)){
                        array_push($list,$v->id);
                    }
                    $this->getAllRoleIdRec($list,$v->id);
                }
            }else{
                return true;
            }
        }
    }

    public function roleNode()
    {
        return $this->hasMany(RoleNode::class, 'role_id', 'id')
            ->select(['id', 'role_id', 'node_id']);
    }

}
