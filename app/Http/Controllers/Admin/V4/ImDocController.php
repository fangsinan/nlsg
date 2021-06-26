<?php


namespace App\Http\Controllers\Admin\V4;


use App\Http\Controllers\ControllerBackend;
use App\Servers\ImDocServers;
use Illuminate\Http\Request;

class ImDocController extends ControllerBackend
{
    public function add(Request $request)
    {
        $servers = new ImDocServers();
        $data = $servers->add($request->input());
        return $this->getRes($data);
    }

    public function list(Request $request)
    {
        $servers = new ImDocServers();
        $data = $servers->list($request->input());
        return $this->getRes($data);
    }

    public function changeStatus(Request $request)
    {
        $servers = new ImDocServers();
        $data = $servers->changeStatus($request->input());
        return $this->getRes($data);
    }

    public function addSendJob(Request $request)
    {
        $servers = new ImDocServers();
        $data = $servers->addSendJob($request->input());
        return $this->getRes($data);
    }

    public function sendJobList(Request $request)
    {
        $servers = new ImDocServers();
        $data = $servers->sendJobList($request->input());
        return $this->getRes($data);
    }

    public function changeJobStatus(Request $request)
    {
        $servers = new ImDocServers();
        $data = $servers->changeJobStatus($request->input());
        return $this->getRes($data);
    }
}
