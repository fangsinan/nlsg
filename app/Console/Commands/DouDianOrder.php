<?php

namespace App\Console\Commands;

use App\Servers\V5\DouDianServers;
use Illuminate\Console\Command;

class DouDianOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:DouDianOrder {type}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $type = $this->argument('type');
        if ($type == 3) {
            (new DouDianServers())->tempGetOrderDetails();//根据单号获取订单详情
//        (new DouDianServers())->tempExcelAddOrder();//临时导入订单号
        } else {
            (new DouDianServers())->getOrderJob($type);
        }
        return 0;
    }
}
