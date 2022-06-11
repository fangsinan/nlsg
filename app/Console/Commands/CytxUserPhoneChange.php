<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Servers\VipServers;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CytxUserPhoneChange extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:CytxUserPhoneChange';

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
        if (true){
            $list = [
                '18624078563',
            ];

            $vip_temp_res = [];
            $vs = new VipServers();
            foreach ($list as $v){
                $p = [
                    "flag"=>1,
                    "parent"=>'',
                    "phone"=>$v,
                    "send_money"=>0
                ];
                $vip_temp_res[] = $vs->createVip_1($p,1);
            }

            dd($vip_temp_res);
        }
        return 0;

        $list = [
//            ['18198080359','13369423657'],
//            ['18877762860','13684118715'],
//            ['15945369583','18104537687'],
//            ['15595182687','13995193090'],
//            ['17752314931','13895002795'],
//            ['13995370916','15378964931'],
//            ['18260429996','13776046443'],
        ];

        DB::beginTransaction();
        try {
            foreach ($list as $p){
                $check_p1 = User::query()->where('phone','=',$p[0])->first();

                if (empty($check_p1)){
                    continue;
                }

                $check_p2 = User::query()->where('phone','=',$p[1])->first();

                if (empty($check_p2)){
                    $check_p1->phone = $p[1];
                    $check_p1->save();
                }else{
                    $check_p2->phone = $check_p2->id;
                    $check_p2->save();

                    $check_p1->phone = $p[1];
                    $check_p1->save();

                    $check_p2->phone = $p[0];
                    $check_p2->save();
                }

            }
            DB::commit();
            echo '成功';
        } catch (\Exception $e) {
            DB::rollBack();
            echo '失败';
        }
    }
}
