<?php

namespace App\Console\Commands;

use App\Servers\LiveConsoleServers;
use Illuminate\Console\Command;
use App\Servers\V5\WechatServersNew;

class LiveOnlineConsole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:LiveOnline  {type}';

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
        $type = (int)$this->argument('type');
        switch ($type) {
            case 1:
                //获取群发消息公众号openid
                WechatServersNew::GetOpenId();
                break;

//                LiveConsoleServers::CrontabGiftRedis();//直播打赏入库
//                LiveConsoleServers::CrontabJoinRedis();//加入直播间记录入库
//                LiveConsoleServers::CrontabOnlineUser();//直播间在线人数入库，方便调试
//                LiveConsoleServers::CrontabOnlineUserRedis();//直播间在线人数存入redis

//                LiveConsoleServers::CrontabOnlineUserRedis();//直播间在线人数存入redis
        }
        return 0;
    }
}
