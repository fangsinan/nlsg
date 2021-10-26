<?php

namespace App\Console\Commands;

use App\Models\MallOrder;
use App\Models\MallOrderFlashSale;
use App\Models\MallOrderGroupBuy;
use App\Servers\MallRefundJob;
use Illuminate\Console\Command;

class mallOrderClear extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:mallOrderClear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '商城订单清理';

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
        MallOrder::clear();//超时订单处理
        MallOrderGroupBuy::clear();//拼团超时订单处理和退款登记
        MallOrderFlashSale::clear();//秒杀订单处理
        MallRefundJob::refundJob(1);//商城订单退款处理
        MallRefundJob::refundJob(2);//商城订单退款查询

    }
}
