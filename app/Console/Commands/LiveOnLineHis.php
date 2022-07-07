<?php

namespace App\Console\Commands;

use App\Servers\LiveConsoleServers;
use Illuminate\Console\Command;

class LiveOnLineHis extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:LiveOnLineHis';

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
        $s = new LiveConsoleServers();
        $s->LiveOnline();
    }
}
