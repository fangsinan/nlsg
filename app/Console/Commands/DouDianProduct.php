<?php

namespace App\Console\Commands;

use App\Models\CommandJobLog;
use App\Servers\V5\DouDianServers;
use Illuminate\Console\Command;

class DouDianProduct extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:DouDianProduct';

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
        CommandJobLog::add(__METHOD__,$this->arguments());
        (new DouDianServers())->productListJob();
        return 0;
    }
}
