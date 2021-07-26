<?php


namespace App\Servers;


use App\Models\ImGroup;
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
                'owner.nickname as owner_nickname',
                DB::raw('0 as group_count'),
                DB::raw('(case gt.id when gt.id > 0 then 1 else 0 end) as is_top')
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

        $query->orderBy('g.id', 'desc');

        return $query->paginate($size);
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

    public function changeStatus($params, $user_id)
    {

        //退出 Topping置顶 取消指定 转让 解散

    }
}
