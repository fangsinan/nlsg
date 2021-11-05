<?php

namespace App\Console\Commands;

use App\Servers\LiveConsoleServers;
use Illuminate\Console\Command;

class liveComment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:liveComment';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '评论入库';

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
        LiveConsoleServers::CrontabCommentRedis();//评论入库
    }
}
