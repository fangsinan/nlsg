<?php

namespace App\Console\Commands;

use App\Servers\LiveConsoleServers;
use Illuminate\Console\Command;

class CrontabOnlineUserRedis extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:CrontabOnlineUserRedis';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '直播间在线人数存入redis';

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
        LiveConsoleServers::CrontabOnlineUserRedis();//直播间在线人数存入redis
    }
}
