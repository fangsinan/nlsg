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
            ->select(['id', 'pid', 'name', 'path', 'is_menu', 'status', 'rank',DB::raw('0 as checked')])
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

}
