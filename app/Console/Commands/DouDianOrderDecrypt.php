<?php

namespace App\Console\Commands;

use App\Models\CommandJobLog;
use App\Servers\V5\DouDianServers;
use Illuminate\Console\Command;

class DouDianOrderDecrypt extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:DouDianOrderDecrypt';

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

        (new DouDianServers())->runDecryptNew();

        return 0;
    }
}
