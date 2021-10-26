<?php

namespace App\Console\Commands;

use App\Servers\ChannelServers;
use Illuminate\Console\Command;

class cytxJob extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:cytxJob';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '创业天下订单的推送任务';

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
        ChannelServers::cytxJob();//创业天下推送
    }
}
