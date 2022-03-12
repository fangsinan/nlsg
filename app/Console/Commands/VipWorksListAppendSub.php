<?php

namespace App\Console\Commands;

use App\Servers\VipWorksListServers;
use Illuminate\Console\Command;

class VipWorksListAppendSub extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:VipWorksListAppendSub';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'VipWorksListAppendSub';

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
        $s = new VipWorksListServers();
        $s->appendSub();
    }
}
