<?php

namespace App\Http\Controllers\Admin\V5;

use App\Http\Controllers\ControllerBackend;
use App\Servers\V5\RecommendConfigServers as rcs;
use Illuminate\Http\Request;

class RecommendConfigController extends ControllerBackend
{

    public function list(Request $request) {
        return $this->getRes((new rcs())->list($request->input()));
    }

    public function sort(Request $request){
        return $this->getRes((new rcs())->sort($request->input()));
    }

    public function selectList(Request $request) {
        return $this->getRes((new rcs())->selectList($request->input()));
    }

    public function add(Request $request) {
        return $this->getRes((new rcs())->add($request->input()));
    }

}
