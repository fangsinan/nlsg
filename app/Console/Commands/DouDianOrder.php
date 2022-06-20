<?php

namespace App\Console\Commands;

use App\Servers\V5\DouDianServers;
use App\Servers\V5\DouDianXueXiJiServers;
use Illuminate\Console\Command;

class DouDianOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:DouDianOrder {type}';

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
        $type = $this->argument('type');
        (new DouDianServers())->getOrderJob($type);
        (new DouDianXueXiJiServers())->getOrderJob($type);
    }
}
