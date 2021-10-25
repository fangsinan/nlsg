<?php

namespace App\Console\Commands;

use App\Servers\ImDocFolderServers;
use Illuminate\Console\Command;

class imJob extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'imJob';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'im文案发送任务';

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
        $idfServer = new ImDocFolderServers();
        $idfServer->sendJob();
    }
}
