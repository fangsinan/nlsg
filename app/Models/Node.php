<?php

namespace App\Models;

class Node extends Base
{
    protected $table = 'nlsg_node';


    public function getList()
    {
        $res = [];
        $this->getListFromDb($res);
        return $res;
    }

    public function getListFromDb(&$res,$pid=0,$is_menu = 2)
    {
        if ($pid == 0){
            $res = Node::query()
                ->where('pid', '=', $pid)
                ->where('status', '=', 1)
                ->where('is_menu', '=', $is_menu)
                ->select(['id', 'pid', 'name', 'path', 'is_menu', 'status'])
                ->orderBy('rank')
                ->orderBy('id')
                ->get();
        }else{

        }


        if ($res->isNotEmpty()){
            foreach ($res as $v){

            }
        }


    }

    public function menuList()
    {
        return $this->hasMany(Node::class, 'pid', 'id')
            ->where('is_menu', '=', 2)
            ->where('status', '=', 1)
            ->select(['id', 'pid', 'name', 'path', 'is_menu', 'status']);
    }


    public function apiList()
    {
        return $this->hasMany(Node::class, 'pid', 'id')
            ->where('is_menu', '=', 1)
            ->where('status', '=', 1)
            ->select(['id', 'pid', 'name', 'path', 'is_menu', 'status']);
    }
}
