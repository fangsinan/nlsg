<?php

namespace App\Console\Commands;

use App\Servers\removeDataServers;
use Illuminate\Console\Command;

class SubListClose extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:subListClose';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'works_list_of_del_sub';

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
        $servers = new removeDataServers();
        $servers->worksListOfDelSub();
    }
}

