<?php

namespace App\Console\Commands;

use App\Servers\V5\XiaoeTechServers;
use Illuminate\Console\Command;

class XiaoTechJob extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'XiaoTechJob {task} {is_init} {num}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'XiaoTechJob';

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
        $task = $this->argument('task')??'';
        $is_init = $this->argument('is_init')??0;
        $num = $this->argument('num')??0;

        $XiaoeTechServers=new XiaoeTechServers();

        switch ($task){
            case 'sync_distributor_customer_list':
                //获取推广员客户
                $XiaoeTechServers->sync_distributor_customer_list($is_init,$num);
                break;
            case 'sync_fast_distributor_customer_list':
                //查询最新的推广员客户
                $XiaoeTechServers->sync_fast_distributor_customer_list();
                break;

            case 'sync_xe_xfxs':

                //小鹅通合伙人同步幸福学社
                XiaoeTechServers::rpush_add_vip_user();
                XiaoeTechServers::lpop_add_vip_user();
                XiaoeTechServers::rpush_add_vip_user_inviter();
                XiaoeTechServers::lpop_add_vip_user_inviter();
                break;
        }
//        $XiaoeTechServers->sync_distributor_customer_list(0);

    }
}
