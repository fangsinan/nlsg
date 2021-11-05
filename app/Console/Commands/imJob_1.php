<?php

namespace App\Console\Commands;

use App\Servers\ImDocFolderServers;
use Illuminate\Console\Command;
use Libraries\ImClient;
use Illuminate\Support\Facades\DB;

class imJob_1 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'imJob_1';

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
        return true;
        if(1){
            $url = ImClient::get_im_url("https://console.tim.qq.com/v4/group_open_http_svc/send_group_msg");
            $end = strtotime(date('Y-m-d H:i:59', strtotime('+1 minute')));
            $servers = new ImDocFolderServers();

            while (time() < $end) {
                $job_res = $servers->sendJob($end, 1, $url);
                if ($job_res === false) {
                    sleep(1);
                }
            }
        }else{
            //测试方法
            $end = strtotime(date('Y-m-d H:i:59'));
            $value = time();
            while (time() <= $end) {
                DB::table('nlsg_command_test')->insert([
                    'value'=>$value
                ]);
               sleep(1);
            }
        }

    }
}
