<?php


namespace App\Servers\V5;


use App\Models\XiaoeTech\XeDistributor;
use App\Models\XiaoeTech\XeUser;
use App\Models\XiaoeTech\XeUserJob;
use Illuminate\Support\Facades\Validator;

class XiaoETongServers
{
    public function vipList($params, $admin)
    {
        $query = XeDistributor::query()
            ->select([
                'id', 'xe_user_id', 'xe_parent_user_id', 'nickname',
                'underling_number', 'total_amount', 'status', 'expire_time', 'created_at',
            ]);

        $query->with([
            'XeUserInfo:user_id,xe_user_id,nickname,name,phone,is_seal',
            'XeUserInfo.vipInfo:id,user_id,nickname,username,level',
            'XeUserInfo.vipBindInfo:parent,son,life,begin_at,end_at',
            'XeUserParentInfo:user_id,xe_user_id,nickname,name,phone,is_seal',
        ]);

        HelperService::queryWhen(
            $query,
            $params,
            [
                [
                    'field'    => 'user_phone',
                    'alias'    => 'phone',
                    'operator' => 'like',
                    'model'    => 'XeUserInfo',
                ],
                [
                    'field'    => 'user_parent_phone',
                    'alias'    => 'phone',
                    'operator' => 'like',
                    'model'    => 'XeUserParentInfo',
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
                [
                    'field' => 'status',
                ],
                [
                    'field' => 'xe_user_id'
                ]
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
        $validator = Validator::make($params, [
            'phone' => 'required|string|size:11',
        ], [
            'phone.required' => '手机号不能为空',
            'phone.size'     => '手机号长度应为11',
        ]);

        if ($validator->fails()) {
            return $validator->messages()->first();
        }

        $xts = new XiaoeTechServers();
        $res = $xts->distributor_member_add($params['phone']);

        if (is_array($res)) {
            return ['code' => true, 'msg' => '成功'];
        } else {
            return ['code' => false, 'msg' => is_bool($res) ? '成功' : $res];
        }
    }

    public function vipBindUser($params, $admin)
    {
        $validator = Validator::make(
            $params,
            [
                'parent_phone' => 'required|string|size:11',
                'son_phone'    => 'required|array',
                'son_phone.*'  => 'required|distinct|string|size:11|'
            ],
            [
                'parent_phone.required' => '手机号不能为空',
                'parent_phone.size'     => '手机号长度应为11',
                'son_phone.required'    => '下级账号必须存在',
                'son_phone.array'       => '下级账号必须是数组格式',
                'son_phone.*.size'      => '下级手机号长度应为11',
                'son_phone.*.string'    => '下级手机号必须是字符串格式',
                'son_phone.*.distinct'  => '下级手机号内有重复项',
            ]
        );

        if ($validator->fails()) {
            return $validator->messages()->first();
        }

        $check_parent = XeUser::query()
            ->where('phone', '=', $params['parent_phone'])
            ->select(['id', 'phone', 'xe_user_id', 'user_created_at'])
            ->first();

        $parent_user_id  = $check_parent->xe_user_id ?? '';
        $parent_job      = $parent_user_id ? 2 : 1;
        $parent_job_time = $check_parent->user_created_at ?? null;

        $check_son = XeUser::query()
            ->whereIn('phone', $params['son_phone'])
            ->select(['id', 'xe_user_id', 'phone', 'user_created_at'])
            ->get();

        $add_data = [];
        foreach ($params['son_phone'] as $v) {
            $temp_add_data                      = [];
            $temp_add_data['parent_phone']      = $params['parent_phone'];
            $temp_add_data['parent_xe_user_id'] = $parent_user_id;
            $temp_add_data['parent_job']        = $parent_job;
            $temp_add_data['parent_job_time']   = $parent_job_time;
            $temp_add_data['son_phone']         = $v;
            $temp_add_data['son_xe_user_id']    = '';
            $temp_add_data['son_job']           = 1;
            $temp_add_data['son_job_time']      = null;

            foreach ($check_son as $sk => $sv) {
                if ($sv->phone === $v) {
                    $temp_add_data['son_xe_user_id'] = $sv->xe_user_id;
                    $temp_add_data['son_job']        = 2;
                    $temp_add_data['son_job_time']   = $sv->user_created_at ?? null;
                    unset($check_son[$sk]);
                }
            }
            $add_data[] = $temp_add_data;
        }

        $res = XeUserJob::query()->insert($add_data);

        if ($res) {
            return ['code' => true, 'msg' => '成功'];
        }

        return ['code' => false, 'msg' => '失败'];
    }

    public function vipInfo($params, $admin)
    {
        $validator = Validator::make(
            $params,
            [
                'xe_user_id' => 'required|string|exists:nlsg_xe_user',
            ],
            [
                'xe_user_id.required' => '用户id不能为空',
                'xe_user_id.exists'   => '用户不存在',
            ]
        );

        if ($validator->fails()) {
            return $validator->messages()->first();
        }

        return XeUser::query()
            ->where('xe_user_id', '=', $params['xe_user_id'])
            ->select([
                'id', 'user_id', 'xe_user_id', 'nickname', 'name', 'avatar', 'gender', 'city', 'province', 'country',
                'phone', 'birth', 'address', 'company', 'user_created_at'
            ])
            ->with([
                'nlsgUserInfo:id,phone,nickname,headimg,is_author,status,is_test_pay',
                'distributorInfo',
            ])
            ->first();
    }

    public function userList($params, $admin)
    {
        $query = XeUser::query()
            ->select(['id', 'user_id', 'xe_user_id', 'nickname', 'name', 'avatar', 'phone', 'user_created_at']);

        $query->with([
            'parentList:xe_user_id,sub_user_id',
            'sonList:xe_user_id,sub_user_id',
        ]);

//        $query->withCount('sonList');

        HelperService::queryWhen(
            $query,
            $params,
            [
                [
                    //根据pid查下面用户
                    'field'  => 'parent_xe_user_id',
                    'alias'  => 'xe_user_id',
                    'models' => 'sonList'
                ],
            ]
        );


//        $query = XeDistributorCustomer::query()
//            ->select([
//                'id', 'xe_user_id', 'sub_user_id', 'order_num', 'sum_price', 'bind_time', 'status', 'status_text',
//                'remain_days', 'expired_at', 'is_anonymous'
//            ])
//            ->with(['xeUserInfo:id,user_id,xe_user_id,nickname,name,avatar,phone,user_created_at']);
//
//        HelperService::queryWhen(
//            $query,
//            $params,
//            [
//                [
//                    'field' => 'parent_user_id',
//                    'alias' => 'xe_user_id',
//                ],
//                [
//                    'field' => 'son_user_id',
//                    'alias' => 'sub_user_id',
//                ],
//                [
//                    'field'    => 'bind_time_begin',
//                    'alias'    => 'bind_time',
//                    'operator' => '>=',
//                ],
//                [
//                    'field'    => 'bind_time_begin',
//                    'alias'    => 'bind_time',
//                    'operator' => '<=',
//                ],
//                [
//                    'field'    => 'phone',
//                    'model'    => 'xeUserInfo',
//                    'operator' => 'like',
//                ],
//                [
//                    'field'    => 'user_created_at_begin',
//                    'model'    => 'xeUserInfo',
//                    'alias'    => 'user_created_at',
//                    'operator' => '>=',
//                ],
//                [
//                    'field'    => 'user_created_at_end',
//                    'model'    => 'xeUserInfo',
//                    'alias'    => 'user_created_at',
//                    'operator' => '<=',
//                ],
//            ]
//        );


        return $query->paginate($params['size'] ?? 10);

    }

    public function orderList($params, $admin)
    {
        return [__LINE__];
    }


    /**定时任务**/
    public function runUserJobParent(): bool
    {
        $xts = new XiaoeTechServers();

        while (true) {

            $list = XeUserJob::query()
                ->where('parent_job', '=', 1)
                ->select(['id', 'parent_phone', 'parent_xe_user_id'])
                ->limit(100)
                ->get();

            if ($list->isEmpty()) {
                break;
            }

            $list = $list->toArray();

            foreach ($list as $v) {

                if (empty($v['parent_phone'])) {
                    XeUserJob::query()
                        ->where('id', '=', $v['id'])
                        ->update([
                            'parent_job' => 2,
                        ]);
                    continue;
                }

                $temp_res = $xts->distributor_member_add($v['parent_phone']);

                if (strlen($temp_res['user_id'] ?? '') > 0) {
                    XeUserJob::query()
                        ->where('id', '=', $v['id'])
                        ->update([
                            'parent_xe_user_id' => $temp_res['user_id'],
                            'parent_job'        => 2,
                            'parent_job_time'   => $temp_res['created_at'],
                        ]);
                } else {
                    $err_array = [
                        'parent_job' => 3,
                    ];
                    if (is_string($temp_res)) {
                        $err_array['parent_job_err'] = $temp_res;
                    }
                    XeUserJob::query()
                        ->where('id', '=', $v['id'])
                        ->update($err_array);
                }
            }

            sleep(1);
        }

        return true;
    }

    public function runUserJobSon(): bool
    {
        $xts = new XiaoeTechServers();

        while (true) {

            $list = XeUserJob::query()
                ->where('son_job', '=', 1)
                ->select(['id', 'son_phone', 'son_xe_user_id'])
                ->limit(100)
                ->get();

            if ($list->isEmpty()) {
                break;
            }

            $list = $list->toArray();

            foreach ($list as $v) {

                if (empty($v['son_phone'])) {
                    XeUserJob::query()
                        ->where('id', '=', $v['id'])
                        ->update([
                            'son_job' => 2,
                        ]);
                    continue;
                }

                $temp_res = $xts->user_register($v['son_phone']);
                if (strlen($temp_res['user_id'] ?? '') > 0) {
                    XeUserJob::query()
                        ->where('id', '=', $v['id'])
                        ->update([
                            'son_xe_user_id' => $temp_res['user_id'],
                            'son_job'        => 2,
                            'son_job_time'   => $temp_res['created_at'] ?? date('Y-m-d H:i:s'),
                        ]);
                } else {
                    $err_array = [
                        'son_job' => 3,
                    ];
                    if (is_string($temp_res)) {
                        $err_array['son_job_err'] = $temp_res;
                    }
                    XeUserJob::query()
                        ->where('id', '=', $v['id'])
                        ->update($err_array);
                }
            }

            sleep(1);
        }

        return true;

    }

    public function runUserJobBind(): bool
    {
        $xts = new XiaoeTechServers();

        while (true) {
            $list = XeUserJob::query()
                ->where('bind_job', '=', 1)
                ->where('parent_xe_user_id', '<>', '')
                ->where('son_xe_user_id', '<>', '')
                ->select(['id', 'parent_phone', 'parent_xe_user_id', 'son_phone', 'son_xe_user_id'])
                ->limit(1)
                ->get();

            if ($list->isEmpty()) {
                break;
            }

            $list = $list->toArray();

            foreach ($list as $v) {
                $temp_res = $xts->distributor_member_bind(
                    $v['parent_xe_user_id'],
                    $v['son_xe_user_id']
                );
                if ($temp_res['code'] === true) {
                    XeUserJob::query()
                        ->where('id', '=', $v['id'])
                        ->update([
                            'bind_job'      => 2,
                            'bind_job_time' => $temp_res['created_at'] ?? date('Y-m-d H:i:s'),
                        ]);
                } else {
                    $err_array = [
                        'bind_job'     => 3,
                        'bind_job_err' => $temp_res['msg'] ?? '',
                    ];
                    XeUserJob::query()
                        ->where('id', '=', $v['id'])
                        ->update($err_array);
                }
            }

            sleep(1);
        }

        return true;

    }


}
