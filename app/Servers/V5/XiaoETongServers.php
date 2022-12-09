<?php


namespace App\Servers\V5;


use App\Models\XiaoeTech\XeDistributor;

class XiaoETongServers
{
    public function vipList($params, $admin)
    {
        $query = XeDistributor::query()
            ->select([
                'id', 'xe_user_id', 'xe_parent_user_id', 'nickname',
                'underling_number', 'total_amount', 'status', 'expire_time', 'created_at',
            ]);

        $query->where('status', '=', 1);

        $query->with([
            'userInfo:user_id,xe_user_id,nickname,name,phone,is_seal',
            'userInfo.vipInfo:id,user_id,nickname,username,level',
            'userInfo.vipBindInfo:parent,son,life,begin_at,end_at',
            'userParentInfo:user_id,xe_user_id,nickname,name,phone,is_seal',
        ]);

        HelperService::queryWhen(
            $query,
            $params,
            [
                [
                    'field'    => 'user_phone',
                    'alias'    => 'phone',
                    'operator' => 'like',
                    'model'    => 'userInfo',
                ],
                [
                    'field'    => 'user_parent_phone',
                    'alias'    => 'phone',
                    'operator' => 'like',
                    'model'    => 'userParentInfo',
                ],
                [
                    'field'    => 'created_begin',
                    'alias'    => 'created_at',
                    'operator' => '>=',
                ],
                [
                    'field'    => 'created_end',
                    'alias'    => 'created_at',
                    'operator' => '<=',
                ],
                [
                    'field'    => 'expire_time_begin',
                    'alias'    => 'expire_time',
                    'operator' => '>=',
                ],
                [
                    'field'    => 'expire_time_end',
                    'alias'    => 'expire_time',
                    'operator' => '<=',
                ],
            ]
        );

        switch ($params['ob'] ?? '') {
            case 'time_asc':
                $query->orderBy('created_at');
                break;
            case 'time_desc':
                $query->orderBy('created_at', 'desc');
                break;
            case 'expire_time_asc':
                $query->orderBy('expire_time');
                break;
            case 'expire_time_desc':
                $query->orderBy('expire_time', 'desc');
                break;
        }

        $query->orderBy('id', 'desc');

        return $query->paginate($params['size'] ?? 10);
    }

    public function vipAdd($params, $admin)
    {
        return [__LINE__];
    }

    public function vipBindUser($params, $admin)
    {
        return [__LINE__];
    }

    public function vipInfo($params, $admin)
    {
        return [__LINE__];
    }

    public function orderList($params, $admin)
    {
        return [__LINE__];
    }

}
