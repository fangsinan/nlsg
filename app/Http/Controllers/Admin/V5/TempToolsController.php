<?php

namespace App\Http\Controllers\Admin\V5;

use App\Http\Controllers\ControllerBackend;
use App\Servers\V5\TempToolsServers;
use Illuminate\Http\Request;

class TempToolsController extends ControllerBackend
{
    public function liveTools(Request $request){
        set_time_limit(0);
        $tool = $request->input('flag','');
        switch ($tool){
            case 'meikan':
                $res = (new TempToolsServers())->meiKan();
                return $this->getRes($res);
            default:
                exit('error');
        }
    }

    public function insertOnlineUserTest(){
        $res = (new TempToolsServers())->insertOnlineUserTest();
        return $this->getRes($res);
    }

}
