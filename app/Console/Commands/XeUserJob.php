<?php

namespace App\Console\Commands;

use App\Models\VipUserBind;
use App\Servers\V5\XiaoETongServers;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;

class XeUserJob extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:XeUserJob  {type}';

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
        $xes  = new XiaoETongServers();
        switch ($type) {
            case 1:
                $xes->runUserJobParent();
                break;

            case 10:
                $xes->runUserJobBind(1);
                break;
            case 11:
                $xes->runUserJobBind(2);
                break;
            case 12:
                $xes->runUserJobBind(3);
                break;
            case 13:
                $xes->runUserJobBind(4);
                break;
            case 14:
                $xes->runUserJobBind(5);
                break;
            case 15:
                $xes->runUserJobBind(6);
                break;
            case 16:
                $xes->runUserJobBind(7);
                break;
            case 17:
                $xes->runUserJobBind(8);
                break;
            case 19:
                $xes->runUserJobBind(0);
                break;


            case 20:
                $xes->runUserJobSon(1);
                break;
            case 21:
                $xes->runUserJobSon(2);
                break;
            case 22:
                $xes->runUserJobSon(3);
                break;
            case 23:
                $xes->runUserJobSon(4);
                break;
            case 24:
                $xes->runUserJobSon(5);
                break;
            case 25:
                $xes->runUserJobSon(6);
                break;
            case 26:
                $xes->runUserJobSon(7);
                break;
            case 27:
                $xes->runUserJobSon(8);
                break;
            case 29:
                $xes->runUserJobSon(0);
                break;

            case 30:
                $vub = new VipUserBind();
                $vub->bindToXeUserJob();
                break;

            case 99:
                $xes->hjCheck();
                break;
        }
        return 0;
    }
}
