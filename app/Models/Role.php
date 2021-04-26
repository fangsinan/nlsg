<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Base
{
    protected $table = 'nlsg_role';

//    /**
//     * 获取角色的权限节点数组
//     * @param $roleId
//     * @return array
//     */
//    public function getRoleAuthNodeMap($roleId)
//    {
//        $role = $this->where('id', $roleId)->first();
//        if ( !empty($role)) {
//            $res = $this->getRoleNodeMap($role->node, $roleId);
//        }
//        return $res;
//    }
//
//    /**
//     * 获取角色节点信息
//     * @param $roleNode
//     * @param $roleId
//     * @return array
//     */
//    public function getRoleNodeMap($roleNode, $roleId)
//    {
//        $nodeModel = new Node();
//        $nodeInfo = $nodeModel->whereIn('id', $ids)->get()->toArray();
//
//        $map = [];
//        if ( ! empty($nodeInfo['data'])) {
//            foreach ($nodeInfo['data'] as $vo) {
//                if (empty($vo['path']) || '#' == $vo['path']) {
//                    continue;
//                }
//
//                $map[$vo['path']] = $vo['node_id'];
//            }
//        }
//
//        return $map;
//    }
}
