<?php

namespace App\Console\Commands;

use App\Models\PayRecordDetail;
use App\Models\User;
use App\Servers\V5\TempLiveExcelServers;
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

        if (0) {
            //根据w_temp_360表  批量添加396收益

            /**
             * 补全数据部分
             */
            if (0) {
                $list = DB::table('w_temp_396')
                    ->where('is_done', '=', 0)
                    ->get();

                if ($list->isEmpty()) {
                    exit('没有数据');
                }

                $this_time_remark = '220715导入';

                foreach ($list as $key => $value) {

                    $update_data = [];

                    //过滤掉ordernum中不是数字的字符
                    if ($value->ordernum === null) {
                        $update_data['ordernum'] = '';
                    } else {
                        $update_data['ordernum'] = preg_replace('/[^0-9]/', '', $value->ordernum);
                    }

                    $update_data['remark'] = $this_time_remark;

                    if (empty($value->user_id)) {
                        $check_user             = User::query()
                            ->firstOrCreate([
                                'phone' => (string)$value->phone,
                            ], [
                                'nickname' => substr_replace($value->phone, '****', 3, 4),
                            ]);
                        $update_data['user_id'] = $check_user->id;
                    }

                    if (empty($update_data)) {
                        continue;
                    }

                    DB::table('w_temp_396')->where('id', '=', $value->id)->update($update_data);

                }
            }


            /**
             * 写入收益部分
             */
            if (1) {
                $list = DB::table('w_temp_396')
                    ->where('is_done', '=', 0)
                    ->get();

                if ($list->isEmpty()) {
                    exit('没有数据');
                }

                $now = time();

                foreach ($list as $v) {
                    $temp_insert_data = [
                        'type'     => 13,
                        'ordernum' => $v->ordernum,
                        'user_id'  => $v->user_id,
                        'price'    => $v->price,
                        'ctime'    => $now,
                        'remark'   => $v->remark,
                    ];


                    DB::beginTransaction();

                    $insert_res = PayRecordDetail::query()
                        ->insert($temp_insert_data);
                    if (!$insert_res) {
                        DB::rollBack();
                        continue;
                    }

                    $update_res = DB::table('w_temp_396')
                        ->where('id', '=', $v->id)
                        ->update(['is_done' => 1]);
                    if (!$update_res) {
                        DB::rollBack();
                        continue;
                    }

                    DB::commit();
                }

            }


        }


        if (0) {
            //批量开通360部分
            $list = [
                '18624078563',
            ];

            $vip_temp_res = [];
            $vs           = new VipServers();
            foreach ($list as $v) {
                $p              = [
                    "flag"       => 1,
                    "parent"     => '',
                    "phone"      => $v,
                    "send_money" => 0
                ];
                $vip_temp_res[] = $vs->createVip_1($p, 1);
            }

            dd($vip_temp_res);
        }


        if (0) {
            //创业天下号码更改
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
                foreach ($list as $p) {
                    $check_p1 = User::query()->where('phone', '=', $p[0])->first();

                    if (empty($check_p1)) {
                        continue;
                    }

                    $check_p2 = User::query()->where('phone', '=', $p[1])->first();

                    if (empty($check_p2)) {
                        $check_p1->phone = $p[1];
                        $check_p1->save();
                    } else {
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

        if (0) {
            //临时调试用
            $r = (new TempLiveExcelServers())->qiYeWeiXin([
                'live_id'    => 617,
                'is_bind'    => 0,
                'begin_time' => '2022-08-22 00:00:00',
                'end_time'   => '2022-08-22 23:59:59'
            ], 168934);
            dd($r);
        }

    }
}
