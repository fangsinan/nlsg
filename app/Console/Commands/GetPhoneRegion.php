<?php

namespace App\Console\Commands;

use App\Servers\UserRegionServers;
use Illuminate\Console\Command;

class GetPhoneRegion extends Command
{

    protected $signature = 'command:GetPhoneRegion  {type}';

    protected $description = 'Command description';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $type = $this->argument('type') ?? '';

        $s = new UserRegionServers();
        if ($type === '1') {
            $s->toAddListNew();
        } else {
            $s->toRun();
        }
        return 0;
    }
}
