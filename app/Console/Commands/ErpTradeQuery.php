<?php

namespace App\Console\Commands;

use App\Models\CommandJobLog;
use App\Models\ConfigModel;
use App\Servers\ErpServers;
use Illuminate\Console\Command;

class ErpTradeQuery extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:ErpTradeQuery';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '主动查询ERP订单详情同步物流信息';

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
        $s->tradeQuery();
    }
}
