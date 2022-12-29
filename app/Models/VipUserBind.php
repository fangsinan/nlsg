<?php


namespace App\Models;


use App\Models\XiaoeTech\XeDistributor;
use App\Models\XiaoeTech\XeUserJob;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class VipUserBind extends Base
{
    protected $table = 'nlsg_vip_user_bind';

    protected $fillable = [
        'parent', 'son', 'life', 'begin_at', 'end_at', 'channel', 'status'
    ];

    public function SonUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'son', 'phone');
    }

    //0没绑定   -1绑定但是无效   其他父类用户id
    public static function getBindParent($phone = '')
    {
        if (empty($phone)) {
            return 0;
        }
        $res = DB::table('nlsg_vip_user_bind as vub')
            ->leftJoin('nlsg_user as u', 'vub.parent', '=', 'u.phone')
            ->leftJoin('nlsg_vip_user as vu', function ($join) {
                $join->on('vu.user_id', '=', 'u.id')
                    ->where('vu.is_default', '=', 1)
                    ->where('vu.status', '=', 1);
            })
            ->where('son', '=', $phone)
            ->where('vub.status', '=', 1)
            ->whereRaw('(life = 1 or (life = 2 AND FROM_UNIXTIME(UNIX_TIMESTAMP()) BETWEEN begin_at and end_at))')
            ->select(['parent', 'u.id as parent_user_id', 'vu.user_id as vuid'])
            ->first();

        if (empty($res)) {
            return 0;
        }
        if (empty($res->vuid ?? '')) {
            return -1;
        }
        return (int)$res->vuid;
    }

    public static function clear()
    {
        $now_date = date('Y-m-d H:i:s', time());
        $map      = [];
        $data     = DB::table('nlsg_vip_user_bind')
            ->where('end_at', '<', $now_date)
            ->whereIn('status', [0, 1])
            ->where('life', '=', 2) //时效性
            ->select(['id', 'parent', 'son'])
            ->limit(30000)
            ->get();

        if ($data->isEmpty()) {
            $data = [];
        } else {
            $data = $data->toArray();
        }

        DB::beginTransaction();
        try {
            if (!empty($data)) {

                foreach ($data as $key => $val) {
                    if (($key % 5000) == 0) {
                        DB::table('nlsg_vip_user_bind')->insert($map);
                        $map = [];
                    }
                    $map[] = [
                        'parent'     => '18512378959', //保护到公司
                        'son'        => $val->son,
                        'life'       => 2, //有效期
                        'begin_at'   => $now_date,
                        'end_at'     => '2030-12-31 23:59:59',
                        'status'     => 1,
                        'is_manual'  => 1, //1 手动绑定 配合小鹅通
                        'channel'    => 2, //'来源渠道 1导入 2平台 3抖音 4直播渠道 5:哈佛订单绑定平台'
                        'remark'     => '保护过期，自动保护：' . $val->id,
                        'created_at' => $now_date,
                    ];
                }
                if (!empty($map)) {
                    DB::table('nlsg_vip_user_bind')->insert($map);
                }
            }

            $clear_sql = "update  nlsg_vip_user_bind set status = 2 where status in (0,1) and end_at <= SYSDATE() and life=2";
            DB::select($clear_sql);

            $clear_vip_sql = "UPDATE nlsg_vip_user SET `status` = 0,is_default=0 where is_default = 1 AND `status` = 1 AND expire_time < now();";
            DB::select($clear_vip_sql);

            DB::commit();
            echo '执行成功';
        } catch (\Exception $e) {
            DB::rollBack();
            var_dump($e->getMessage());
        }

    }

    public function bindToXeUserJob()
    {
        $begin_at    = date('Y-m-d H:i:00', strtotime('-6 minutes'));
        $parent_list = self::query()
            ->where('created_at', '>=', $begin_at)
            ->where('parent', '<>', '18512378959')
            ->where('status', '=', 1)
            ->groupBy('parent')
            ->pluck('parent')
            ->toArray();

        if (!$parent_list) {
            return 0;
        }

        $check_parent_list = XeDistributor::query()
            ->select(['id', 'xe_user_id'])
            ->with(['XeUserInfo:xe_user_id,phone'])
            ->whereHas('XeUserInfo', function ($q) use ($parent_list) {
                $q->whereIn('phone', $parent_list);
            })
            ->where('status', '=', 1)
            ->get();

        if ($check_parent_list->isEmpty()) {
            return 0;
        }

        foreach ($check_parent_list as $v) {
            $temp_list = self::query()
                ->where('parent', '=', $v->XeUserInfo['phone'])
                ->where('status', '=', 1)
                ->where('created_at', '>=', $begin_at)
                ->pluck('son')
                ->toArray();

            $temp_data = [];
            foreach ($temp_list as $vv) {
                $temp_data[] = [
                    'parent_phone'      => $v->XeUserInfo['phone'],
                    'parent_xe_user_id' => $v->XeUserInfo['xe_user_id'],
                    'son_phone'         => $vv,
                    'parent_job'        => 2,
                    'parent_job_time'   => $begin_at,
                ];
            }

            XeUserJob::query()->insert($temp_data);
        }

        return 0;

    }

    public function bindToXeUserJobLiveRole()
    {
        $now      = date('Y-m-d H:i:s');
        $begin_at = date('Y-m-d H:i:00', strtotime('-6 minutes'));
        $page     = 1;
        $size     = 300;

        $total = 0;
        while (true) {

            $list = DB::table('nlsg_backend_live_role as lr')
                ->join('nlsg_vip_user_bind as ub', 'lr.son', '=', 'ub.parent')
                ->where('ub.created_at', '>=', $begin_at)
                ->limit($size)
                ->offset(($page - 1) * $size)
                ->select(['ub.son'])
                ->get();

            if ($list->isEmpty()) {
                break;
            }

            $page++;

            $add_data = [];

            foreach ($list as $v) {
                $total++;
                $add_data[] = [
                    'parent_phone'      => '18966893687',
                    'parent_xe_user_id' => 'u_api_63ad5c92e37d7_thK3yErlvp',
                    'parent_job'        => '2',
                    'parent_job_time'   => $now,
                    'son_phone'         => $v->son,
                ];
            }
            XeUserJob::query()->insert($add_data);
        }
    }

    //种子推广员的
    public function bindToXeUserZhongZi()
    {

        $begin_at = date('Y-m-d H:i:00', strtotime('-6 minutes'));
        $page     = 1;
        $size     = 300;

        while (true) {

            $list = DB::table('nlsg_xe_distributor as xd')
                ->join('nlsg_xe_user as xu', 'xd.xe_user_id', '=', 'xu.xe_user_id')
                ->join('nlsg_vip_user_bind as vub', 'xu.phone', '=', 'vub.parent')
                ->where('vub.created_at', '>=', $begin_at)
                ->where('xd.source', '=', 4)
                ->limit($size)
                ->offset(($page - 1) * $size)
                ->select(['vub.parent', 'vub.son'])
                ->get();

            if ($list->isEmpty()) {
                break;
            }

            $page++;

            $add_data = [];

            foreach ($list as $v) {
                $add_data[] = [
                    'parent_phone' => $v->parent,
                    'son_phone'    => $v->son,
                ];
            }
            XeUserJob::query()->insert($add_data);
        }
    }

}
