<?php

namespace App\Console\Commands;

use App\Servers\VipServers;
use Illuminate\Console\Command;

class VipUserBindSource extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:VipUserBindSource';

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
        $s = new VipServers();
        $s->vipUpateBindSource();
        $s->vipUserBindSource();
        return 0;
    }
}
