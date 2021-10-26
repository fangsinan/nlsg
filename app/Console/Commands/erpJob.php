<?php

namespace App\Console\Commands;

use App\Servers\ErpServers;
use Illuminate\Console\Command;

class erpJob extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:erpJob';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'erp推送';

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
        $s = new ErpServers();
        $s->logisticsSync();
        $s->pushRun();
    }
}
