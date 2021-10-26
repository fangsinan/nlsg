<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Works;
use App\Models\WorksInfo;
use Illuminate\Console\Command;

class orderClear extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:orderClear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '虚拟订单清理';

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
        Order::clear(); //线下课超时处理
        WorksInfo::covertVideo(); //转换音频视频
        Works::deal(); //自动上架
    }
}
