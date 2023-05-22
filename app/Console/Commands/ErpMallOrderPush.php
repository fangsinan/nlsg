<?php

namespace App\Console\Commands;

use App\Models\CommandJobLog;
use App\Servers\ErpServers;
use Illuminate\Console\Command;

class ErpMallOrderPush extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:ErpMallOrderPush';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '商城订单推送ERP';

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
        CommandJobLog::add(__METHOD__,$this->arguments());
        $s = new ErpServers();
        $s->pushRun();
    }
}
