<?php


namespace App\Http\Controllers\Admin\V4;


use App\Http\Controllers\ControllerBackend;
use App\Servers\SubHelperServers;
use Illuminate\Http\Request;

class SubHelperController  extends ControllerBackend
{
    public function objList(){
        $servers = new SubHelperServers();
        $data = $servers->ojbList();
        return $this->getRes($data);
    }

    public function comObjList(){
        $servers = new SubHelperServers();
        $data = $servers->comObjList();
        return $this->getRes($data);
    }


    public function open(Request $request){
        $servers = new SubHelperServers();
        $data = $servers->addOpenList($request->input(),$this->user['id'] ?? 0);
        return $this->getRes($data);
    }

}
