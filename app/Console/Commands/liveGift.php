<?php

namespace App\Console\Commands;

use App\Servers\LiveConsoleServers;
use Illuminate\Console\Command;

class liveGift extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:liveGift';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '直播打赏入库';

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
        LiveConsoleServers::CrontabGiftRedis();//直播打赏入库
    }
}
