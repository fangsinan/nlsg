<?php

namespace App\Console\Commands;

use App\Servers\OrderRefundServers;
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
    protected $description = 'im job test';

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
        $begin = time();
        $end_time = strtotime(date('Y-m-d H:i:58',strtotime('+4 minute')));
        $type_name = 'im_'.date('Y-m-d H:i:s');
        while ($begin < $end_time){
            OrderRefundServers::test($type_name);
            $begin = time();
            sleep(5);
        }

    }
}
