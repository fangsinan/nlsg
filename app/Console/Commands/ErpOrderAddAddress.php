<?php

namespace App\Console\Commands;

use App\Models\CommandJobLog;
use App\Servers\ErpServers;
use App\Servers\ErpXfxsServers;
use Illuminate\Console\Command;

class ErpOrderAddAddress extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:ErpOrderAddAddress';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '用户完善地址后同步推送任务';

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
        $s->orderUpdateAddressId();
        (new ErpXfxsServers())->orderUpdateAddressId();
        return 0;
    }
}
