<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\V4\LiveController;
use Illuminate\Console\Command;

class liveOnlineUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:liveOnlineUser';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '直播间在线人数入库，方便调试';

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
        LiveController::CrontabOnlineUser();
    }
}
