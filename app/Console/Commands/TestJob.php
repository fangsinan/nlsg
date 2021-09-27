<?php

namespace App\Console\Commands;

use App\Servers\OrderRefundServers;
use Illuminate\Console\Command;

class TestJob extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'testJob:test {type}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'test job';

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
        //
        OrderRefundServers::test($this->argument('type'));
    }
}
