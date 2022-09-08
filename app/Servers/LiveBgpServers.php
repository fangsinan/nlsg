<?php


namespace App\Servers;


use App\Models\LiveBgp;

class LiveBgpServers
{
    public function list($params)
    {
        return LiveBgp::query()
            ->select(['id', 'title', 'url', 'color'])
            ->get();
    }

}
