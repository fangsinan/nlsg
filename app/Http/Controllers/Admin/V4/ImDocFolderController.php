<?php

namespace App\Http\Controllers\Admin\V4;

use App\Http\Controllers\ControllerBackend;
use App\Servers\ImDocFolderServers;
use Illuminate\Http\Request;

class ImDocFolderController extends ControllerBackend
{
    public function list(Request $request)
    {
        $servers = new ImDocFolderServers();
        $data = $servers->list($request->input(), $this->user['id']);
        return $this->getRes($data);
    }

    public function add(Request $request)
    {
        $servers = new ImDocFolderServers();
        $data = $servers->add($request->input(), $this->user['id']);
        return $this->getRes($data);
    }

    public function changeStatus(Request $request)
    {
        $servers = new ImDocFolderServers();
        $data = $servers->changeStatus($request->input(), $this->user['id']);
        return $this->getRes($data);
    }
}
