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

        //获取所有公司保护数据
        //能量时光渠道推广码
        $company_array=DB::table('nlsg_backend_live_role')->where('status','=',1)->pluck('son')->toArray();
        //韩建以前代理商账号 以及公司新号
        $company_array=array_merge($company_array,['18512378959','18966893687']);
        //公司员工，企业微信导出
        $company_array=array_merge($company_array,['18957021066','18231555068','15031595139','18032560886','17731451324','13810704425','13673138036','18288600451','15166347191','13810322546','18371896993','18132613285','15387346213','13810329420','15872003857','19909723131','18663475768','15327812392','18397143752','18871009189','18301135302','15006346972','17332561114','13597608520','13810273426','15707057061','13387167269','13002057810','15532588235','15599359657','15311935111','15163488540','18769565290','15597266825','18627808237','13811482176','15991422486','17332560307','13012199802','13813670119','15033243652','15212160739','18212771855','17368705222','13850783147','13811725540','15263496977','13367977775','18097023027','13593685250','13720947916','15597455248','18507340489','15891118309','15257277186','15869864917','18207240391','15197925736','13022765668','13810294092','13834225794','13782815706','18963491819','15501261079','19979191298','13563472221','13217157181','15003643376','15036542728','18210823375','18086174840','17683875292','13835313107','18512378959','13716572082','17813010967','18310363538','18333243374','15321660797','17701209775','15128898617','17310788951','15226525517','13520649059','18811188244','17710399952','17778172860','15201545820','18981295235','18347737119','17791486576','15282775122','15732256996','15032933258','13811397190','13810746076','13811722194','13811396063','13811097835','13811684409','13810274250','13810694578','18230153851','13810496044','15830580569','13811075137','18532146670','13811371595','18832533919','13811097244','13811079803','13811917184','13810644059','13811090477','13810053293','13810849547','13810379062','17332560298','13612130023','13810274294','13810459544','13811065081','13810973251','15133986781','13811367790','13811217603','13811892410','18833356321','13810282161','13811673603','17332561154','17332560301','19831532556','15832501911','17332560308','17332560314','19831532585','18333885745','15830580950','18332258522','15830581033','15830571103','17332561126','18032587125','17332560312','15830580960','17332561125','19831532568','17332561098','17332560297','18333885741','17332561097','17332560302','13810063730','17332561096','18333886800','15830581068','17332561120','13810271374','15830581080','17332561127','19565025885','17332560306','13811378147','17332560292','13811547423','18564557336','13811760545','13811844579','18331503513','17334109952','18531774467','13810713642','18713829619','15726698240','16649861238','13811525865','15732085544','13811096317','13931585203','13811536932','15633635351','17753805145','17332561108','17332561124','13811657173','13811655607','18633308398','17332561109','13811242418','18331502155','18046586104','15033934301','17717781514','13811671385','18631570816','15833561744','18633445724','17301061089','13931496923','18916571903','18633970310','13811622193','13811694573','13810591267','13811095302','17725527953','18331534397','15116935127','15133908911','17332561160','15333159067','19930065557','17692524102','17399715652','18630550375','13811405184','17731542707','13810792902','18330446326','13811505361','17332561107','15136569113','15130569113','13269209666','18903379656','15836573035','13042499802','17332561168','15544672822','17336399894','18518910719','13811061317','13292432977','13811387083','17633097501','15369598366','13810711357','15650701817','18511820586','18513099963','15903477034','17636563316','18600179874','15238363522','18624078563','13120309779','18510689501','18810355387','18701352544','18611970519','18137394257','18911259933','13720073732','13933544745','13811005342','15210670430','18356689071','15801308637','15718848565','15928681736','18516885601','13552933603','13671268308','13263193055','13522223009','15737163293','18838991892','15137110750']);

        $data     = DB::table('nlsg_vip_user_bind')
            ->where('end_at', '<', $now_date)->whereIn('status', [0, 1])->where('life', '=', 2) //时效性
            ->whereIn('parent',$company_array) //公司保护客户
            ->select(['id', 'parent', 'son'])
            ->limit(50000)
            ->get();

        if ($data->isEmpty()) {
            $data = [];
        } else {
            $data = $data->toArray();
        }

        DB::beginTransaction();
        try {
            if (!empty($data)) {
                $end_at=date('Y-m-d 23:59:59',strtotime('+1 year'));
                foreach ($data as $key => $val) {
                    if (($key % 5000) == 0) {
                        DB::table('nlsg_vip_user_bind')->insert($map);
                        $map = [];
                    }
                    //确保数据开始时间随机
                    $RandNum=rand(32400, 86400); //上午九点至晚24点间随机时间
                    $DayTime= strtotime(date('Y-m-d'))+$RandNum; //当天随机时间戳
                    $begin_at=date('Y-m-d H:i:s', $DayTime);
                    $map[] = [
                        'parent'     => '18966893687', //保护到公司
                        'son'        => $val->son,
                        'life'       => 2, //有效期
                        'begin_at'   => $begin_at,
                        'end_at'     => $end_at,
                        'status'     => 1,
                        'is_manual'  => 1, //1 手动绑定 配合小鹅通
                        'channel'    => 2, //'来源渠道 1导入 2平台 3抖音 4直播渠道 5:哈佛订单绑定平台'
                        'remark'     => $val->id,
                        'created_at' => $begin_at,
                    ];
                }
                if (!empty($map)) {
                    DB::table('nlsg_vip_user_bind')->insert($map);
                }
            }

            $clear_sql = "update  nlsg_vip_user_bind set status = 2 where status in (0,1) and end_at <= SYSDATE() and life=2";
            DB::select($clear_sql);
            #会员过期状态更新
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
