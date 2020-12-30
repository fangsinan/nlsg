<?php


namespace App\Servers;


use App\Models\VipRedeemAssign;
use App\Models\VipUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class VipServers
{
    public function list($params)
    {
        $size = $params['size'] ?? 10;
        $query = VipUser::query()
            ->orderBy('created_at', 'asc')
            ->groupBy('user_id')
            ->select(['id', 'user_id', 'nickname', 'username']);

        $query->with(['nowLevel']);

        if (!empty($params['id'] ?? 0)) {
            $query->with(['assignCount', 'assignHistory']);
        }

        if (!empty($params['id'] ?? '')) {
            $query->where('id', '=', $params['id']);
        }

        if (!empty($params['username'] ?? '')) {
            $query->where('username', 'like', '%' . trim($params['username']) . '%');
        }

        switch (intval($params['level'] ?? '')) {
            case 1:
                $query->whereHas('nowLevel', function (Builder $q) {
                    $q->where('level', '=', 1);
                });
                break;
            case 2:
                $query->whereHas('nowLevel', function (Builder $q) {
                    $q->where('level', '=', 2);
                });
                break;
        }

        $list = $query->paginate($size);

        $vModel = new VipUser();
        foreach ($list as $v) {
            if (!empty($params['id'] ?? 0)) {
                $v->open_history = $vModel->openHistory($v->user_id);
            }
        }

        return $list;
    }

    public function assign($params, $admin_id = 0)
    {
        if (empty($admin_id)) {
            return ['code' => false, 'msg' => '用户错误'];
        }

        $vip_id = $params['vip_id'] ?? 0;
        $user_id = $params['user_id'] ?? 0;

        if (empty($user_id) || empty($vip_id) || !isset($params['num']) || !isset($params['status'])) {
            return ['code' => false, 'msg' => '参数错误'];
        }
        if (in_array($params['status'], [1, 2])) {
            return ['code' => false, 'msg' => '状态错误'];
        }


        switch ($params['flag'] ?? '') {
            case 'add':
                break;
            case 'edit':
                $id = $params['id'] ?? 0;
                $receive_vip_id = $params['receive_vip_id'] ?? 0;

//                $check = VipRedeemAssign::where('id','=',$id)
//                    ->where('receive_vip_id','=',$receive_vip_id)
//                    ->first();
//                if (empty($check)){
//                    return ['code'=>false,'msg'=>'参数错误'];
//                }


                break;
            default:
                return ['code' => false, 'msg' => '参数错误'];
        }


    }

}
