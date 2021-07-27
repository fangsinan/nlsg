<?php


namespace App\Servers;


use App\Models\Column;
use App\Models\ImGroup;
use App\Models\ImGroupTop;
use Illuminate\Support\Facades\DB;

class ImGroupServers
{
    public function groupList($params, $user_id)
    {
        $size = $params['size'] ?? 10;

        $query = DB::table('nlsg_im_group as g')
            ->leftJoin('nlsg_im_group_top as gt', function ($q) use ($user_id) {
                $q->whereRaw('g.id = gt.group_id')->where('gt.user_id', '=', $user_id);
            })
            ->leftJoin('nlsg_user as owner', 'g.owner_account', '=', 'owner.id')
            ->select([
                'g.id', 'g.group_id', 'g.operator_account', 'g.owner_account', 'g.type', 'g.name',
                'g.status', 'g.created_at', 'owner.phone as owner_phone', 'owner.id as owner_id',
                'owner.nickname as owner_nickname', 'g.member_num', 'works_id',
                DB::raw('(case gt.id when gt.id > 0 then 1 else 0 end) as is_top'),
                DB::raw('2000 as max_num')
            ])->orderBy('gt.id', 'desc');

        switch ($params['ob'] ?? '') {
            case 'time_asc':
                $query->orderBy('g.created_at');
                break;
            case 'time_desc':
                $query->orderBy('g.created_at', 'desc');
                break;
        }

        if (!empty($params['name'] ?? '')) {
            $query->where('g.name', 'like', '%' . $params['name'] . '%');
        }

        if (!empty($params['status'] ?? 0)) {
            $query->where('g.status', '=', $params['status']);
        }

        $query->orderBy('g.id', 'desc');


        $list = $query->paginate($size);

        foreach ($list as $v) {
            $v->admin = DB::table('nlsg_im_group_user as gu')
                ->join('nlsg_user as u', 'gu.group_account', '=', 'u.id')
                ->where('group_id', '=', $v->group_id)
                ->where('group_role', '<>', 0)
                ->orderBy('group_role')
                ->select(['group_account', 'u.phone', 'u.nickname', 'group_role'])
                ->get();
            $v->worksInfo = Column::query()
                ->where('id', '=', $v->works_id ?? 0)
                ->select(['id', 'name', 'subtitle'])
                ->first();
        }

        return $list;
    }


    public function groupList_del($params, $user_id)
    {
        $size = $params['size'] ?? 10;

        $query = ImGroup::query();

        switch ($params['ob'] ?? '') {
            case 'time_asc':
                $query->orderBy('created_at');
                break;
            case 'time_desc':
                $query->orderBy('created_at', 'desc');
                break;
        }

        if (!empty($params['name'] ?? '')) {
            $query->where('name', 'like', '%' . $params['name'] . '%');
        }

        $query->orderBy('id', 'desc');

        $query->with(['ownerInfo:id,phone,nickname']);

        $query->select([
            'id', 'group_id', 'operator_account', 'owner_account', 'type', 'name', 'status', 'created_at'
        ]);

        $res = $query->paginate($size);
        $iguModel = new ImGroup();
        foreach ($res as &$v) {
            $v->group_count = $iguModel->groupUserCount($v->group_id, 0);
        }
        return $res;
    }

    public function statistics($params, $user_id): array
    {
        $m = new ImGroup();
        //群总数
        $res['total_counts'] = $m->statistics($user_id, 0);
        //我创建的
        $res['owner_counts'] = $m->statistics($user_id, 1);
        //我加入的
        $res['join_counts'] = $m->statistics($user_id, 2);

        return $res;
    }

    public function changeTop($params, $user_id)
    {
        $group_id = $params['group_id'] ?? '';
        if (empty($group_id)) {
            return ['code' => false, 'msg' => '参数错误'];
        }
        $group_id = ImGroup::getId($group_id);
        $group_id = $group_id['id'];
        if (empty($group_id)) {
            return ['code' => false, 'msg' => '参数错误'];
        }
        $check = ImGroupTop::query()
            ->where('group_id', '=', $group_id)
            ->where('user_id', '=', $user_id)
            ->first();

        //退出 置顶 取消置顶 转让 解散
        $flag = $params['flag'] ?? '';
        switch ($flag) {
            case 'top':
                $res = ImGroupTop::firstOrCreate([
                    'user_id' => $user_id,
                    'group_id' => $group_id
                ]);
                break;
            case 'cancel_top':
                if ($check) {
                    $res = ImGroupTop::where('user_id', '=', $user_id)
                        ->where('group_id', '=', $group_id)
                        ->delete();
                } else {
                    $res = true;
                }
                break;
            default:
                return ['code' => false, 'msg' => '参数错误'];
        }
        if ($res == false) {
            return ['code' => false, 'msg' => '失败'];
        } else {
            return ['code' => true, 'msg' => '成功'];
        }
    }

    public function bindWorks($params, $user_id)
    {
        $group_id = $params['group_id'] ?? 0;
        $works_id = $params['works_id'] ?? 0;

        $group_id = ImGroup::getId($group_id);
        $group_id = $group_id['id'];
        if (empty($group_id)) {
            return ['code' => false, 'msg' => '群信息错误'];
        }

        $check_works = Column::where('id', '=', $works_id)->where('type', '=', 3)->first();
        if (empty($check_works)) {
            return ['code' => false, 'msg' => '课程错误'];
        }

        $res = ImGroup::where('id', '=', $group_id)->update([
            'works_id' => $works_id
        ]);

        if ($res === false) {
            return ['code' => false, 'msg' => '失败'];
        } else {
            return ['code' => true, 'msg' => '成功'];
        }

    }
}
