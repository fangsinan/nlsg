<?php

namespace App\Console\Commands;

use App\Models\PayRecordDetail;
use App\Models\User;
use App\Models\VipUser;
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

        //创业天下号码更改
        if (0) {
            $list = [
                ['之前', '之后'],
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
                                                              'live_id' => 617,
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   'is_bind' => 0,
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   'begin_time' => '2022-08-22 00:00:00',
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     'end_time' => '2022-08-22 23:59:59'
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

        //23.02.06 吴秀娟提交   绑定到
        if (0) {
            $list = DB::select('select * from w_linshi_huiyu_vip_bind where id between 35016 and 35464');

//            $vs = new VipServers();

            foreach ($list as $v) {
                //查询son是不是合伙人 不是开通
//                $check_son = VipUser::query()
//                    ->where('username', '=', $v->son)
//                    ->where('level', '=', 2)
//                    ->where('status', '=', 1)
//                    ->where('is_default', '=', 1)
//                    ->first();
//
//                if (!$check_son) {
//                    $vs->createVip_2(['phone' => $v->son], 1);
//                }

                //绑定
                $today = date('Y-m-d H:i:s');

                $life     = 1;
                $begin_at = $today;
                $end_at   = '2030-12-31 23:59:59';

                $get_vip_bind = DB::table('nlsg_vip_user_bind')
                    ->where('son', '=', $v->son)
                    ->where('status', '=', 1)
                    ->select(['id', 'parent', 'son', 'remark'])
                    ->first();
                echo $v->son.'--开始';
                if (empty($get_vip_bind)) {

                    DB::beginTransaction();
                    $t_res = true;

                    //直接绑定
                    $temp_res = DB::table('nlsg_vip_user_bind')
                        ->insert([
                                     'parent'       => $v->parent,
                                     'son'          => $v->son,
                                     'life'         => $life,
                                     'begin_at'     => $begin_at,
                                     'end_at'       => $end_at,
                                     'status'       => 1,
                                     'remark'       => '230213吴秀娟提交:' . $v->id,
                                     'column_name'  => $v->column_name,
                                     'channel_name' => 2,
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
                        DB::commit();
                        continue;
                    } else {
                        DB::rollBack();
                        DB::table('w_linshi_huiyu_vip_bind')
                            ->where('id', '=', $v->id)
                            ->update([
                                         'status' => 3,
                                         'err'    => '任务处理失败,请重试',
                                     ]);

                    }

                } else {

                    if ($get_vip_bind->parent === '18512378959' || $get_vip_bind->parent === '18966893687') {
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
                                         'parent'       => $v->parent,
                                         'son'          => $v->son,
                                         'life'         => $life,
                                         'begin_at'     => $begin_at,
                                         'end_at'       => $end_at,
                                         'status'       => 1,
                                         'remark'       => '慧宇表单提交:' . $v->id,
                                         'column_name'  => $v->column_name,
                                         'channel_name' => 2,
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
                            DB::commit();
                            continue;
                        } else {
                            DB::rollBack();
                            DB::table('w_linshi_huiyu_vip_bind')
                                ->where('id', '=', $v->id)
                                ->update([
                                             'status' => 3,
                                             'err'    => '任务处理失败,请重试',
                                         ]);

                        }

                    } else {

                        DB::table('w_linshi_huiyu_vip_bind')
                            ->where('id', '=', $v->id)
                            ->update([
                                         'status' => 3,
                                         'err'    => '和现有绑定冲突:' . $get_vip_bind->id . ':' . $get_vip_bind->parent,
                                     ]);

                    }
                }
            }
        }

        //慧鱼提交经销商保护表格处理
        if (0) {

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
            }


            //处理绑定
            $while_flag = true;
            $page       = 1;
            $size       = 50;
            $today      = date('Y-m-d H:i:s');

            while ($while_flag) {
                $temp_list = DB::table('w_linshi_huiyu_vip_bind')
                    ->where('status', '=', 1)
                    ->limit($size)
                    ->offset(($page - 1) * $size)
                    ->select(['id', 'parent', 'son', 'life_type', 'column_name'])
                    ->orderBy('id')
                    ->get();

                if ($temp_list->isEmpty()) {
                    $while_flag = false;
                }

                $temp_list = $temp_list->toArray();

                $page++;


                foreach ($temp_list as $v) {
                    echo $v->id, ':', $v->son, PHP_EOL;
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
                                         'parent'       => $v->parent,
                                         'son'          => $v->son,
                                         'life'         => $life,
                                         'begin_at'     => $begin_at,
                                         'end_at'       => $end_at,
                                         'status'       => 1,
                                         'remark'       => '230107慧宇表单提交:' . $v->id,
                                         'column_name'  => $v->column_name,
                                         'channel_name' => 2,
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
                            DB::commit();
                            continue;
                        } else {
                            DB::rollBack();
                            DB::table('w_linshi_huiyu_vip_bind')
                                ->where('id', '=', $v->id)
                                ->update([
                                             'status' => 3,
                                             'err'    => '任务处理失败,请重试',
                                         ]);

                        }

                    } else {

                        if (0) {
                            //第一次跑,把冲突的入库,现在废弃.
                            DB::table('w_linshi_huiyu_vip_bind')
                                ->where('id', '=', $v->id)
                                ->update([
                                             'status' => 3,
                                             'err'    => '和现有绑定冲突:' . $get_vip_bind->id . ':' . $get_vip_bind->parent,
                                         ]);

                            DB::table('w_linshi_huiyu_parent_check')
                                ->insert([
                                             'parent'     => $get_vip_bind->parent,
                                             'new_parent' => $v->parent,
                                             'son'        => $v->son,
                                             'old_id'     => $get_vip_bind->id,
                                             'new_id'     => $v->id,
                                         ]);
                        }


                        if ($get_vip_bind->parent === '18512378959' || $get_vip_bind->parent === '18966893687') {
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
                                             'parent'       => $v->parent,
                                             'son'          => $v->son,
                                             'life'         => $life,
                                             'begin_at'     => $begin_at,
                                             'end_at'       => $end_at,
                                             'status'       => 1,
                                             'remark'       => '慧宇表单提交:' . $v->id,
                                             'column_name'  => $v->column_name,
                                             'channel_name' => 2,
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
                                DB::commit();
                                continue;
                            } else {
                                DB::rollBack();
                                DB::table('w_linshi_huiyu_vip_bind')
                                    ->where('id', '=', $v->id)
                                    ->update([
                                                 'status' => 3,
                                                 'err'    => '任务处理失败,请重试',
                                             ]);

                            }

                        } else {

                            DB::table('w_linshi_huiyu_vip_bind')
                                ->where('id', '=', $v->id)
                                ->update([
                                             'status' => 3,
                                             'err'    => '和现有绑定冲突:' . $get_vip_bind->id . ':' . $get_vip_bind->parent,
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

        //大课绑定到韩建明显
        if (0) {
            $list = DB::select("select id,ordernum,live_id,pay_price,pay_time,type,user_id,phone,nickname,protect_user_id,protect_phone
from nlsg_live_deal where type in (6,7,12,15,16,17,18,19,20,21,25,29) and
(
 protect_user_id =158291 or protect_phone in (
   '15811570751','18500401602','13311111111','13411111111','13211111111','15032798039','15811570751','13522223779','17316297021','18516970530','18512341111','18512341112','18512341113','18512341114','18512341115','18512341116','18512341117','18512341118','18512342221','18512342222','18512342223','18512342224','18512342225','18512342226','18512342227','18512342228','18512342229','18512342230','18512342231','18512342232','18512342233','18512342234','18512342235','18512342236','18512342237','18512342238','18512342239','18512342240','18624078563','18512341111','18512342222','12000000001','12000000001','18522222001','18522222002','18522222003','18522222004','18522222005','18522222006','18522222007','18522222008','18522222009','18522222010','18522222011','18522222012','18522222013','18522222014','18522222015','18522222016','18522222017','18522222018','18522222019','18522222020','18522222021','18522222022','18522222023','18522222024','18522222025','18522222026','18522222027','18522222028','18522222029','18522222030','18522222031','18522222032','18522222033','18522222034','18522222035','18522222036','18522222037','18522222038','18522222039','18522222040','18522222041','18522222042','18522222043','18522222044','18522222045','18522222046','18522222047','18522222048','18522222049','18522222050','11002174002','18522222051','18522222052','18522222053','18522222054','18522222055','18522222056','18522222057','18522222058','18522222059','18522222060','18522222061','18522222062','18522222063','18522222064','18522222065','18522222066','18522222067','18522222068','18522222069','18522222070','18522222071','18522222072','18522222073','18522222074','18522222075','18522222076','18522222077','18522222078','18522222079','18522222080','18522222081','18522222082','18522222083','18522222084','18522222085','18522222086','18522222087','18522222088','18522222089','18522222090','18522222091','18522222092','18522222093','18522222094','18522222095','18522222096','18522222097','18522222098','18522222099','18522222100','18522222101','18522222102','18522222103','18522222104','18522222105','18522222106','18522222107','18522222108','18522222109','18522222110','18522222301','18522222302','18522222303','18522222304','18522222305','18522222306','18522222307','18522222308','18522222309','18522222310','18522222101','18522222102','18522222103','18522222104','18522222105','18522222201','18522222202','18522222203','18522222204','18522222205','18522222206','18522222207','18522222208','18522222209','18522222210','18522222211','18522222212','18522222213','18522222214','18522222215','18522222216','18522222217','18522222218','18522222219','18522222220','18522222221','18522222222','18522222223','18522222224','18522222225','18522222226','18522222227','18522222228','18522222229','18522222230','18522222231','18522222232','18522222233','18522222234','18522222235','18522222236','18522222237','18522222238','18522222239','18522222240','18522222241','18522222242','18522222243','18522222244','18522222245','18522222246','18522222247','18522222248','18522222249','18522222250','18522222251','18522222252','18522222253','18522222254','18522222255','18522222256','18522222257','18522222258','18522222259','18522222260','18522222261','18522222262','18522222263','18522222264','18522222265','18522222266','18522222267','18522222268','18522222269','18522222270','18522222271','18522222272','18522222273','18522222274','18522222275','18522222276','18522222277','18522222278','18522222279','18522222280','18522222281','18522222282','18522222283','18522222284','18522222285','18522222286','18522222287','18522222288','18522222289','18522222290','18522222291','18522222292','18522222293','18522222294','18522222295','18522222296','18522222297','18522222298','18522222299','18522222300','18522222401','18522222402','18522222403','18522222404','18522222405','18522222406','18522222407','18522222408','18522222409','18522222410','18522222411','18522222412','18522222413','18522222414','18522222415','18522222416','18522222417','18522222418','18522222419','18522222420','18522222421','18522222422','18522222423','18522222424','18522222425','18522222426','18522222427','18522222428','18522222429','18522222430','18522222431','18522222432','18522222433','18522222434','18522222435','18522222436','18522222437','18522222438','18522222439','18522222440','18522222441','18522222442','18522222443','18522222444','18522222445','18522222446','18522222447','18522222448','18522222449','18522222450','18522222451','18522222452','18522222453','18522222454','18522222455','18522222456','18522222457','18522222458','18522222459','18522222460','18522222461','18522222462','18522222463','18522222464','18522222465','18522222466','18522222467','18522222468','18522222469','18522222470','18522222471','18522222472','18522222473','18522222474','18522222475','18522222476','18522222477','18522222478','18522222479','18522222480','18522222481','18522222482','18522222483','18522222484','18522222485','18522222486','18522222487','18522222488','18522222489','18522222490','18522222491','18522222492','18522222493','18522222494','18522222495','18522222496','18522222497','18522222498','18522222499','18522222500','18522222501','18522222502','18522222503','18522222504','18522222505','18522222506','18522222507','18522222508','18522222509','18522222510','18511111001','18511111002','18511111003','18511111004','18511111005','18511111006','18511111007','18511111008','18511111009','18511111010','18511111011','18511111012','18511111013','18511111014','18511111015','18511111016','18511111017','18511111018','18511111019','18511111020','18511111021','18511111022','18511111023','18511111024','18511111025','18511111026','18511111027','18511111028','18511111029','18511111030','18511111031','18511111032','18511111033','18511111034','18511111035','18511111036','18511111037','18511111038','18511111039','18511111040','18511111041','18511111042','18511111043','18511111044','18511111045','18511111046','18511111047','18511111048','18511111049','18511111050','18511111051','18511111052','18511111053','18511111054','18511111055','18511111056','18511111057','18511111058','18511111059','18511111060','18511111061','18511111062','18511111063','18511111064','18511111065','18511111066','18511111067','18511111068','18511111069','18511111070','18511111071','18511111072','18511111073','18511111074','18511111075','18511111076','18511111077','18511111078','18511111079','18511111080','18511111081','18511111082','18511111083','18511111084','18511111085','18511111086','18511111087','18511111088','18511111089','18511111090','18511111091','18511111092','18511111093','18511111094','18511111095','18511111096','18511111097','18511111098','18511111099','18511111100','18511111201','18511111202','18511111203','18511111204','18511111205','18511111206','18511111207','18511111208','18511111209','18511111210','18511111211','18511111212','18511111213','18511111214','18511111215','18511111216','18511111217','18511111218','18511111219','18511111220','18511111221','18511111222','18511111223','18511111224','18511111225','18511111226','18511111227','18511111228','18511111229','18511111230','18511111231','18511111232','18511111233','18511111234','18511111235','18511111236','18511111237','18511111238','18511111239','18511111240','18511111241','18511111242','18511111243','18511111244','18511111245','18511111246','18511111247','18511111248','18511111249','18511111250','18511111251','18511111252','18511111253','18511111254','18511111255','18511111256','18511111257','18511111258','18511111259','18511111260','18511111261','18511111262','18511111263','18511111264','18511111265','18511111266','18511111267','18511111268','18511111269','18511111270','18511111271','18511111272','18511111273','18511111274','18511111275','18511111276','18511111277','18511111278','18511111279','18511111280','18511111281','18511111282','18511111283','18511111284','18511111285','18511111286','18511111287','18511111288','18511111289','18511111290','18511111291','18511111292','18511111293','18511111294','18511111295','18511111296','18511111297','18511111298','18511111299','18511111300','18522222001','18522222002','18522222003','18522222004','18522222005','18522222006','18522222007','18522222008','18522222009','18522222010','18522222011','18522222012','18522222013','18522222014','18522222015','18522222016','18522222017','18522222018','18522222019','18522222020','18522222021','18522222022','18522222023','18522222024','18522222025','18522222026','18522222027','18522222028','18522222029','18522222030','18522222031','18522222032','18522222033','18522222034','18522222035','18522222036','18522222037','18522222038','18522222039','18522222040','18522222041','18522222042','18522222043','18522222044','18522222045','18522222046','18522222047','18522222048','18522222049','18522222050','18522222051','18522222052','18522222053','18522222054','18522222055','18522222056','18522222057','18522222058','18522222059','18522222060','18522222061','18522222062','18522222063','18522222064','18522222065','18522222066','18522222067','18522222068','18522222069','18522222070','18522222071','18522222072','18522222073','18522222074','18522222075','18522222076','18522222077','18522222078','18522222079','18522222080','18522222081','18522222082','18522222083','18522222084','18522222085','18522222086','18522222087','18522222088','18522222089','18522222090','18522222091','18522222092','18522222093','18522222094','18522222095','18522222096','18522222097','18522222098','18522222099','18522222100','18522222101','18522222102','18522222103','18522222104','18522222105','18522222106','18522222107','18522222108','18522222109','18522222110','18522222601','18522222602','18522222603','18522222604','18522222605','18522222606','18522222607','18522222608','18522222609','18522222610','18522222611','18522222612','18522222613','18522222614','18522222615','18522222616','18522222617','18522222618','18522222619','18522222620','18522222621','18522222622','18522222623','18522222624','18522222625','18522222626','18522222627','18522222628','18522222629','18522222630','18522222631','18522222632','18522222633','18522222634','18522222635','18522222636','18522222637','18522222638','18522222639','18522222640','18522222641','18522222642','18522222643','18522222644','18522222645','18522222646','18522222647','18522222648','18522222649','18522222650','18522222651','18522222652','18522222653','18522222654','18522222655','18522222656','18522222657','18522222658','18522222659','18522222660','18522222661','18522222662','18522222663','18522222664','18522222665','18522222666','18522222667','18522222668','18522222669','18522222670','18522222671','18522222672','18522222673','18522222674','18522222675','18522222676','18522222677','18522222678','18522222679','18522222680','18522222681','18522222682','18522222683','18522222684','18522222685','18522222686','18522222687','18522222688','18522222689','18522222690','18522222691','18522222692','18522222693','18522222694','18522222695','18522222696','18522222697','18522222698','18522222699','18522222700','18522222701','18522222702','18522222703','18522222704','18522222705','18522222706','18522222707','18522222708','18522222709','18522222710','18522222711','18522222712','18522222713','18522222714','18522222715','18522222716','18522222717','18522222718','18522222719','18522222720','18522222721','18522222722','18522222723','18522222724','18522222725','18522222726','18522222727','18522222728','18522222729','18522222730','18522222731','18522222732','18522222733','18522222734','18522222735','18522222736','18522222737','18522222738','18522222739','18522222740','18522222741','18522222742','18522222743','18522222744','18522222745','18522222746','18522222747','18522222748','18522222749','18522222750','18522222751','18522222752','18522222753','18522222754','18522222755','18522222756','18522222757','18522222758','18522222759','18522222760','18522222761','18522222762','18522222763','18522222764','18522222765','18522222766','18522222767','18522222768','18522222769','18522222770','18522222771','18522222772','18522222773','18522222774','18522222775','18522222776','18522222777','18522222778','18522222779','18522222780','18522222781','18522222782','18522222783','18522222784','18522222785','18522222786','18522222787','18522222788','18522222789','18522222790','18522222791','18522222792','18522222793','18522222794','18522222795','18522222796','18522222797','18522222798','18522222799','18522222800','18522222401','18511111101','18511111102','18511111103','18511111104','18511111105','18511111106','18511111107','18511111108','18511111109','18511111110','18522222801','18522222802','18522222803','18522222804','18522222805','18522222806','18522222807','18522222808','18522222809','18522222810','18522222811','18522222812','18522222813','18522222814','18522222815','18522222816','18522222817','18522222818','18522222819','18522222820','18522222821','18522222822','18522222823','18522222824','18522222825','18522222826','18522222827','18522222828','18522222829','18522222830','18522222831','18522222832','18522222833','18522222834','18522222835','18522222836','18522222837','18522222838','18522222839','18522222840','18522222841','18522222842','18522222843','18522222844','18522222845','18522222846','18522222847','18522222848','18522222849','18522222850','18522222851','18522222852','18522222853','18522222854','18522222855','18522222856','18522222857','18522222858','18522222859','18522222860','18522222861','18522222862','18522222863','18522222864','18522222865','18522222866','18522222867','18522222868','18522222869','18522222870','18522222871','18522222872','18522222873','18522222874','18522222875','18522222876','18522222877','18522222878','18522222879','18522222880','18522222881','18522222882','18522222883','18522222884','18522222885','18522222886','18522222887','18522222888','18522222889','18522222890','18522222891','18522222892','18522222893','18522222894','18522222895','18522222896','18522222897','18522222898','18522222899','18522222900','18522222001'
 )
);");

            foreach ($list as $k => $v) {
                $check_phone = DB::table('nlsg_vip_user_bind')
                    ->where('son', '=', $v->phone)
                    ->where('status', '=', 1)
                    ->first();
                if (!empty($check_phone)) {
                    if ($check_phone->parent === '18512378959' && $check_phone->life === 2) {
                        DB::table('nlsg_vip_user_bind')
                            ->where('id', '=', $check_phone->id)
                            ->update([
                                         'life'     => 1,
                                         'begin_at' => '2022-12-30 14:00:00',
                                         'end_at'   => '2030-12-21 23:59:59',
                                         'remark'   => $check_phone->remark . '2980用户:22.12.30',
                                     ]);
                        echo $k, ':', $v->phone, '修改成功', PHP_EOL;
                    } else {
                        echo $k, ':', $v->phone, '跳过', $check_phone->parent, PHP_EOL;
                    }
                    continue;
                }

                DB::table('nlsg_vip_user_bind')
                    ->insert([
                                 'parent'   => '18966893687',
                                 'son'      => $v->phone,
                                 'life'     => 1,
                                 'begin_at' => '2022-12-30 14:00:00',
                                 'end_at'   => '2030-12-21 23:59:59',
                                 'status'   => 1,
                                 'remark'   => '22.12.30大课订单导入',
                             ]);
                echo $k, ':', $v->phone, '绑定成功', PHP_EOL;
            }

        }

        //修改备注
        if (0) {
            $list = DB::select('select * from nlsg_vip_user_bind where remark != "2980用户:22.12.30" and remark like  "%2980用户:22.12.30%"');

            foreach ($list as $k => $v) {
                $remark = $v->remark;
                $remark = str_replace('2980用户:22.12.30', '', $remark) . ';2980用户:22.12.30';
                DB::table('nlsg_vip_user_bind')
                    ->where('id', '=', $v->id)
                    ->update([
                                 'remark' => $remark
                             ]);
                echo $k, ':', $remark, PHP_EOL;
            }
        }
    }
}
