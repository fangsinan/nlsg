<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\V4\LiveController;
use Illuminate\Console\Command;

class liveOnlineUserRedis extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:liveOnlineUserRedis';

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
        LiveController::CrontabOnlineUserRedis();
    }
}
