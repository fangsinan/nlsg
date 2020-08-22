<?php


namespace App\Http\Controllers\Admin\V4;


use App\Http\Controllers\Controller;
use App\Servers\SpecialPriceServers;
use Illuminate\Http\Request;

class SpecialPriceController extends Controller
{
    public function list(Request $request)
    {
        $servers = new SpecialPriceServers();
        $data = $servers->list($request->input());
        return $this->getRes($data);
    }

    public function add(Request $request)
    {
        $servers = new SpecialPriceServers();
        $data = $servers->add($request->input());
        return $this->getRes($data);
    }

    public function statusChange(Request $request)
    {
        $servers = new SpecialPriceServers();
        $data = $servers->statusChange($request->input());
        return $this->getRes($data);
    }
}
