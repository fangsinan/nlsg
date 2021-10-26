<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class imJob_2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'imJob_2';

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
        $end = strtotime(date('Y-m-d H:i:59',strtotime('+1 minute')));
        $t = rand(1,9999);
        DB::table('wwwww')->insert([
            'vv'=>date('Y-m-d H:i:s'),
            't'=>'j_2_'.$t
        ]);
        while (time()<$end){
            var_dump(time());
        }
        DB::table('wwwww')->insert([
            'vv'=>date('Y-m-d H:i:s'),
            't'=>'j_2_'.$t
        ]);
    }
}
