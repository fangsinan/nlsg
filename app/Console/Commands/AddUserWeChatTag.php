<?php

namespace App\Console\Commands;

use App\Servers\UserRegionServers;
use App\Servers\UserWechatServers;
use Illuminate\Console\Command;

class AddUserWeChatTag extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:AddUserWeChatTag {type}';

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
        $type = $this->argument('type') ?? '';
        if ($type === '1') {
            (new UserRegionServers())->addUserWechatIdList();
        } else {
            $this->runAddUserPhoneRegionTag();
        }
    }

    public function runAddUserPhoneRegionTag()
    {
        $end = time() + 240;
        $s   = new UserWechatServers();
        while (true) {
            $s->runAddUserPhoneRegionTag();
            if (time() > $end) {
                break;
            }
        }
    }

}
