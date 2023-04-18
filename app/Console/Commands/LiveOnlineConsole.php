<?php

namespace App\Console\Commands;

use App\Models\LiveConsole;
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
                LiveConsoleServers::CrontabOnlineUserRedis();//直播间在线人数存入redis
                break;
            case 2:
                LiveConsoleServers::CrontabOnlineUser();//直播间在线人数入库
                break;
            case 3:
                LiveConsoleServers::CrontabJoinRedis();//加入直播间记录入库
                break;
            case 4:
                LiveConsoleServers::CrontabCommentRedis();//评论入库
                break;
            case 5:
                LiveConsoleServers::CrontabGiftRedis();//直播打赏入库
                break;
            case 6:
                $m = new LiveConsole();
                $m->LiveAutoConfig();//直播自动开始结束和人数
                break;

            case 11:
                WechatServersNew::GetOpenId();//获取群发消息公众号openid
                break;
            case 21:
                LiveConsoleServers::getPhoneRegion();//识别手机号归属地
                break;
        }
        return 0;
    }
}
