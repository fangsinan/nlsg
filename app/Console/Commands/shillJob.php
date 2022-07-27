<?php

namespace App\Console\Commands;

use App\Servers\MallRefundJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class shillJob extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:shillJob';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '虚拟订单退款任务';

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
//        MallRefundJob::shillJob(1);
//        MallRefundJob::shillJob(2);
    }
}
