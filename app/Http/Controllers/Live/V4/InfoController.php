<?php


namespace App\Http\Controllers\Live\V4;


use App\Http\Controllers\ControllerBackend;
use App\Servers\LiveInfoServers;
use Illuminate\Http\Request;

class InfoController extends ControllerBackend
{
    public function liveSubOrder(Request $request)
    {
        $s = new LiveInfoServers();
        $data = $s->liveSubOrder($request->input());
        return $this->getRes($data);
    }

    public function liveOrder(Request $request)
    {
        $s = new LiveInfoServers();
        $data = $s->liveOrder($request->input());
        return $this->getRes($data);
    }

    public function comment(Request $request)
    {
        $s = new LiveInfoServers();
        $data = $s->comment($request->input());
           return $this->getRes($data);
    }

    public function orderOnlineNum(Request $request)
    {
        $s = new LiveInfoServers();
        $data = $s->orderOnlineNum($request->input());
           return $this->getRes($data);
    }

    public function onlineNum(Request $request)
    {
        $s = new LiveInfoServers();
        $data = $s->onlineNum($request->input());
           return $this->getRes($data);
    }

}
