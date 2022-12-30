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
     * @return void
     */
    public function handle()
    {
        //慧鱼提交经销商保护表格处理
        if (0) {
            //处理待处理数据重复的问题
            $check_cf_sql = "SELECT * from (
SELECT parent,son,count(*) as counts from w_linshi_huiyu_vip_bind where `status` = 1 GROUP BY CONCAT(parent,'-',son)
) as a where counts > 1";
            $check_cf     = DB::select($check_cf_sql);
            if ($check_cf) {
                foreach ($check_cf as $v) {
                    $temp_v     = DB::table('w_linshi_huiyu_vip_bind')
                        ->where('parent', '=', $v->parent)
                        ->where('son', '=', $v->son)
                        ->where('status', '=', 1)
                        ->select('id')
                        ->get()
                        ->toArray();
                    $temp_v_id  = array_column($temp_v, 'id');
                    $bao_liu_id = $temp_v_id[0];
                    unset($temp_v_id[0]);
                    DB::table('w_linshi_huiyu_vip_bind')
                        ->whereIn('id', $temp_v_id)
                        ->update([
                            'status' => 4,
                            'msg'    => '和' . $bao_liu_id . '重复',
                        ]);

                }
            }

            //处理待处理中的冲突数据
            $check_data_sql = "SELECT son from (
    SELECT son,count(*) as counts from w_linshi_huiyu_vip_bind where status = 1 GROUP BY son
    ) as gs where counts > 1";
            $check_data_res = DB::select($check_data_sql);

            if (!empty($check_data_res)) {
                $check_data_res = array_column($check_data_res, 'son');

                foreach ($check_data_res as $cdr_son) {
                    $check_cdr_son = DB::table('w_linshi_huiyu_vip_bind')
                        ->where('son', '=', $cdr_son)
                        ->where('status', '=', 1)
                        ->select(['id', 'parent', 'son'])
                        ->get()
                        ->toArray();

                    $check_id_list = array_column($check_cdr_son, 'id');

                    DB::table('w_linshi_huiyu_vip_bind')
                        ->whereIn('id', $check_id_list)
                        ->update([
                            'status' => 5,
                            'msg'    => '上级冲突:' . implode(',', $check_id_list),
                        ]);
                }
            }


            //处理绑定
            $while_flag = true;
            $page       = 1;
            $size       = 50;
            $today      = date('Y-m-d H:i:s');

            while ($while_flag) {
                $temp_list = DB::table('w_linshi_huiyu_vip_bind')
                    ->where('id', '<=', 15)
                    ->where('status', '=', 1)
                    ->limit($size)
                    ->offset(($page - 1) * $size)
                    ->select(['id', 'parent', 'son', 'life_type'])
                    ->get()
                    ->toArray();
                if (empty($temp_list)) {
                    $while_flag = false;
                }
                $page++;


                foreach ($temp_list as $v) {

                    if ($v->life_type === '终身') {
                        $life     = 1;
                        $begin_at = $today;
                        $end_at   = '2030-12-31 23:59:59';

                    } elseif (is_numeric($v->life_type) && $v->life_type > 0 && $v->life_type < 120) {
                        $life     = 2;
                        $begin_at = $today;
                        $end_at   = date('Y-m-d 23:59:59', strtotime("+ " . $v->life_type . ' months'));
                    } else {
                        DB::table('w_linshi_huiyu_vip_bind')
                            ->where('id', '=', $v->id)
                            ->update([
                                'status' => 6,
                                'msg'    => '保护期限配置错误',
                            ]);
                        continue;
                    }

                    $get_vip_bind = DB::table('nlsg_vip_user_bind')
                        ->where('son', '=', $v->son)
                        ->where('status', '=', 1)
                        ->select(['id', 'parent', 'son', 'remark'])
                        ->first();

                    if (empty($get_vip_bind)) {

                        DB::beginTransaction();
                        $t_res = true;

                        //直接绑定
                        $temp_res = DB::table('nlsg_vip_user_bind')
                            ->insert([
                                'parent'   => $v->parent,
                                'son'      => $v->son,
                                'life'     => $life,
                                'begin_at' => $begin_at,
                                'end_at'   => $end_at,
                                'status'   => 1,
                                'remark'   => '慧宇表单提交:' . $v->id,
                            ]);
                        if (!$temp_res) {
                            $t_res = false;
                        }

                        //修改任务状态
                        $temp_res = DB::table('w_linshi_huiyu_vip_bind')
                            ->where('id', '=', $v->id)
                            ->update([
                                'status' => 2
                            ]);
                        if (!$temp_res) {
                            $t_res = false;
                        }

                        if ($t_res) {
                            DB::rollBack();
//                            DB::commit();
                            break;
                        }

                        DB::rollBack();
                        DB::table('w_linshi_huiyu_vip_bind')
                            ->where('id', '=', $v->id)
                            ->update([
                                'status' => 3,
                                'err'    => '任务处理失败,请重试',
                            ]);

                    } else {
                        if ($get_vip_bind->parent === '18512378959') {
                            DB::beginTransaction();
                            $t_res = true;

                            //修改原来的
                            $temp_res = DB::table('nlsg_vip_user_bind')
                                ->where('id', '=', $get_vip_bind->id)
                                ->update([
                                    'status' => 2,
                                    'remark' => '慧宇表单提交取消' . $v->id . ';' . $get_vip_bind->remark,
                                ]);
                            if (!$temp_res) {
                                $t_res = false;
                            }

                            //写入
                            $temp_res = DB::table('nlsg_vip_user_bind')
                                ->insert([
                                    'parent'   => $v->parent,
                                    'son'      => $v->son,
                                    'life'     => $life,
                                    'begin_at' => $begin_at,
                                    'end_at'   => $end_at,
                                    'status'   => 1,
                                    'remark'   => '慧宇表单提交:' . $v->id,
                                ]);
                            if (!$temp_res) {
                                $t_res = false;
                            }

                            //修改任务状态
                            $temp_res = DB::table('w_linshi_huiyu_vip_bind')
                                ->where('id', '=', $v->id)
                                ->update([
                                    'status' => 2
                                ]);
                            if (!$temp_res) {
                                $t_res = false;
                            }

                            if ($t_res) {
                                DB::rollBack();
//                                DB::commit();
                                break;
                            }

                            DB::rollBack();
                            DB::table('w_linshi_huiyu_vip_bind')
                                ->where('id', '=', $v->id)
                                ->update([
                                    'status' => 3,
                                    'err'    => '任务处理失败,请重试',
                                ]);

                        } else {

                            DB::table('w_linshi_huiyu_vip_bind')
                                ->where('id', '=', $v->id)
                                ->update([
                                    'status' => 3,
                                    'err'    => '和现有绑定冲突:' . $get_vip_bind->id, ':', $get_vip_bind->parent,
                                ]);

                        }
                    }
                }

            }

        }

        //抖音订单转入公司保护
        if (0) {

            $phone_list = DB::select("SELECT post_tel from nlsg_dou_dian_order as o
join nlsg_dou_dian_order_list as ol on o.order_id = ol.order_id
join nlsg_dou_dian_product_list as pl on ol.product_id = pl.product_id
where o.post_tel <> '' and pl.product_type = 3
GROUP BY post_tel");

            foreach ($phone_list as $v) {
                $check_phone = DB::table('nlsg_vip_user_bind')
                    ->where('son', '=', $v->post_tel)
                    ->where('status', '=', 1)
                    ->first();
                if (!empty($check_phone)) {
                    echo $v->post_tel, '跳过', $check_phone->parent, PHP_EOL;
                    continue;
                }

                DB::table('nlsg_vip_user_bind')
                    ->insert([
                        'parent'   => '18966893687',
                        'son'      => $v->post_tel,
                        'life'     => 2,
                        'begin_at' => '2022-12-30 14:00:00',
                        'end_at'   => '2023-12-20 23:59:59',
                        'status'   => 1,
                        'remark'   => '22.12.30由抖店虚拟订单导入',
                    ]);
                echo $v->post_tel, '绑定成功', PHP_EOL;
            }

        }

        //创业天下号码更改
        if (0) {
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
    }
}
