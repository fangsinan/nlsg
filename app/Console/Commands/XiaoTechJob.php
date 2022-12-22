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
    protected $signature = 'XiaoTechJob';

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
        $XiaoeTechServers=new XiaoeTechServers();
        $XiaoeTechServers->sync_distributor_customer_list(0);

    }
}
