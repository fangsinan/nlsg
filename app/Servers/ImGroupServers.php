<?php


namespace App\Servers;


use App\Models\ImGroup;

class ImGroupServers
{
    public function groupList($params, $user_id)
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

        if (!empty($params['name']??'')){
            $query->where('name','like','%'.$params['name'].'%');
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

    public function statistics($params, $user_id)
    {
        $m = new ImGroup();
        //群总数
        $res['total_counts'] = $m->statistics($user_id,0);
        //我创建的
        $res['owner_counts'] = $m->statistics($user_id,1);
        //我加入的
        $res['join_counts'] = $m->statistics($user_id,2);

        return $res;

    }
}
