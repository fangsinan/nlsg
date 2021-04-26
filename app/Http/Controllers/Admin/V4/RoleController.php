<?php


namespace App\Http\Controllers\Admin\V4;


use App\Http\Controllers\ControllerBackend;
use App\Servers\RedeemCodeServers;
use App\Servers\RoleServers;
use Illuminate\Http\Request;


class RoleController  extends ControllerBackend
{

    //菜单和所含接口列表
    public function nodeList(Request $request){
        $servers = new RoleServers();
        $data = $servers->nodeList($request->input(),$this->user['id']??0);
        return $this->getRes($data);
    }

    //添加修改接口或菜单

    //删除接口或菜单

    //后台用户列表

    //后台用户改变角色

    //后台用户重置密码

}
