<?php

namespace App\Console\Commands;

use App\Servers\V5\XiaoeTechServers;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;


class TestJob extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'TestJob {batch} {page}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'TestJob';

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
        $this->unbind();
    }

    public function unbind(){

        $page = (int)$this->argument('page');
        if(empty($page)){
            $page=1;
        }

        $batch = (int)$this->argument('batch');
        if(empty($batch)){
            $batch=1;
        }

        $XiaoeTechServers=new XiaoeTechServers();

        $list=DB::table('nlsg_xe_unbind')->where('batch_number',$batch)->orderBy('id','asc')->forPage($page,10000)->get();

        foreach ($list as $k=>$user){
            if($user->status !=1){
                $res = $XiaoeTechServers->distributor_member_change($user->sub_user_id);
                if(checkRes($res)){
                    DB::table('nlsg_xe_unbind')->where('id',$user->id)->update(['status'=>1]);
                }else{
                    DB::table('nlsg_xe_unbind')->where('id',$user->id)->update(['status'=>2,'remark'=>$res]);
                }
                var_dump($res);
            }

            var_dump($k);
        }
    }

    function again_bind(){
        $XiaoeTechServers=new XiaoeTechServers();
        $list=DB::table('nlsg_xe_distributor_customer')->where('status',0)
            ->where('bind_time','>=','2022-12-27 12:00:00')
            ->orderBy('xe_user_id','desc')->forPage(1,2000)->get();
        foreach ($list as $k=>$user){
            var_dump($k);
            var_dump($user->xe_user_id,$user->sub_user_id);
            $res=$XiaoeTechServers->distributor_member_bind($user->xe_user_id,$user->sub_user_id);
            var_dump($res);
        }
    }
}
