<?php


namespace App\Http\Controllers\Admin\V4;


use App\Http\Controllers\ControllerBackend;
use App\Servers\ChannelServers;
use App\Servers\MallOrderServers;
use Illuminate\Http\Request;

class ChannelController extends ControllerBackend
{
    public function list(Request $request)
    {
        $servers = new ChannelServers();
        $data = $servers->getList($request->input());
        return $this->getRes($data);
    }

    public function rank(Request $request){
        $servers = new ChannelServers();
        $data = $servers->rank($request->input());
        return $this->getRes($data);
    }
}
