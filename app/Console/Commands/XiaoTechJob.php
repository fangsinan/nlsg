<?php

namespace App\Console\Commands;

use App\Servers\V5\XiaoeTechServers;
use Illuminate\Console\Command;

class XiaoTechJob extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'XiaoTechJob {task} {is_init} {num}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'XiaoTechJob';

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
        $task = $this->argument('task')??'';
        $is_init = $this->argument('is_init')??0;
        $num = $this->argument('num')??0;

        $XiaoeTechServers=new XiaoeTechServers();

        switch ($task){
            case 'sync_distributor_customer_list':
                $XiaoeTechServers->sync_distributor_customer_list($is_init,$num);
                break;
        }
//        $XiaoeTechServers->sync_distributor_customer_list(0);

    }
}
