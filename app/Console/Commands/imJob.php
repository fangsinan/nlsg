<?php

namespace App\Console\Commands;

use App\Servers\ImDocFolderServers;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class imJob extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'imJob';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'im文案发送任务';

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
//        $end = strtotime(date('Y-m-d H:i:59',strtotime('+1 minute')));
//        $t = rand(1,9999);
//        DB::table('wwwww')->insert([
//            'vv'=>date('Y-m-d H:i:s'),
//            't'=>'j_0_'.$t
//        ]);
//        while (time()<$end){
//            var_dump(time());
//        }
//        DB::table('wwwww')->insert([
//            'vv'=>date('Y-m-d H:i:s'),
//            't'=>'j_0_'.$t
//        ]);

//        $idfServer = new ImDocFolderServers();
//        $idfServer->sendJob();
    }
}
