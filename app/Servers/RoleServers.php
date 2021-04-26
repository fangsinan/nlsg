<?php


namespace App\Servers;


use App\Models\Node;
use Illuminate\Support\Facades\DB;

class RoleServers
{
    public function nodeList($params, $admin_id){
//        DB::connection()->enableQueryLog();
//        $list = Node::query()
//            ->where('pid','=',0)
//            ->where('status','=',1)
//            ->where('is_menu','=',2)
//            ->select(['id','pid','name','path','is_menu','status'])
//            ->with(['menuList','apiList','menuList.apiList'])
//            ->orderBy('rank')
//            ->orderBy('id')
//            ->get();
//        dd(DB::getQueryLog());
        $nodeModel = new Node();
        $list = $nodeModel->getList();
        return $list;


    }
}
