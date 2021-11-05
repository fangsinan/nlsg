<?php

namespace App\Console\Commands;

use App\Servers\LiveConsoleServers;
use Illuminate\Console\Command;

class liveJoin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:liveJoin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '加入直播间记录入库';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        LiveConsoleServers::CrontabJoinRedis();//加入直播间记录入库
    }
}
