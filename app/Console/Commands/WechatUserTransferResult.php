<?php

namespace App\Console\Commands;

use App\Servers\UserWechatServers;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class WechatUserTransferResult extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:wechatUserTransferResult';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '企业微信客户转移结果查询';

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

        (new UserWechatServers())->transfer_result();
    }
}
