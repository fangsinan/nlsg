<?php


namespace App\Servers\V5;


use App\Models\VipUserBind;
use App\Models\XiaoeTech\XeDistributor;
use App\Models\XiaoeTech\XeDistributorCustomer;
use App\Models\XiaoeTech\XeOrder;
use App\Models\XiaoeTech\XeOrderDistribute;
use App\Models\XiaoeTech\XeUser;
use App\Models\XiaoeTech\XeUserJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class XiaoETongServers
{
    public function vipList($params, $admin)
    {
        $query = XeDistributor::query()
            ->select([
                'id', 'xe_user_id', 'xe_parent_user_id', 'nickname', 'group_name', 'source',
                'underling_number', 'total_amount', 'status', 'expire_time', 'created_at',
            ]);

        $query->with([
            'XeUserInfo:user_id,xe_user_id,nickname,name,phone,is_seal',
            'XeUserInfo.vipInfo:id,user_id,nickname,username,level,source,source_vip_id',
            'XeUserInfo.vipInfo.sourceVipInfo:id,user_id,nickname,username,level',
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
                    'operator' => '=',
                    'model'    => 'XeUserParentInfo',
                ], [
                    'field'    => 'bind_parent_phone',
                    'alias'    => 'parent',
                    'operator' => '=',
                    'model'    => 'XeUserInfo.vipBindInfo',
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
                ],
                [
                    'field' => 'source'
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
            'phone'  => 'required|string|size:11',
            'source' => 'required|in:2,3,4'
        ], [
            'phone.required'  => '手机号不能为空',
            'phone.size'      => '手机号长度应为11',
            'source.required' => '来源不能为空',
            'source.in'       => '来源不正确',
        ]);

        if ($validator->fails()) {
            return $validator->messages()->first();
        }

        $xts = new XiaoeTechServers();
        $res = $xts->distributor_member_add($params['phone'], 0, [
            'admin_id' => $admin['id'],
            'source'   => $params['source']
        ]);

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

    public function vipUnbindUser($params, $admin)
    {
        $validator = Validator::make(
            $params,
            [
                'list' => 'required|array'
            ]
        );

        if ($validator->fails()) {
            return $validator->messages()->first();
        }

        $xts = new XiaoeTechServers();
        foreach ($params['list'] as $v) {
            $xts->distributor_member_change($v['sub_user_id'], $v['xe_user_id']);
        }

        return ['code' => true, 'msg' => '成功'];
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
            'parentList:xe_user_id,sub_user_id,bind_time',
            'parentList.xeParenUserInfo:xe_user_id,phone,nickname',
        ]);

        $query->withCount('sonList');

        $parent_xe_user_id = $params['parent_xe_user_id'] ?? '';
        $parent_phone      = $params['parent_phone'] ?? '';

        if ($parent_phone) {
            $temp_parent_xe_user_id = XeUser::query()
                ->where('phone', '=', $parent_phone)
                ->value('xe_user_id');

            if (empty($temp_parent_xe_user_id)) {
                return $query->where('id', '=', 0)->paginate($params['size'] ?? 10);
            } else {
                if (!empty($parent_xe_user_id) && $temp_parent_xe_user_id !== $parent_xe_user_id) {
                    return $query->where('id', '=', 0)->paginate($params['size'] ?? 10);
                }
            }
        }

        if ($parent_xe_user_id) {
            $query->whereExists(function ($q) use ($parent_xe_user_id) {
                $q->select(DB::raw(1))
                    ->from(XeDistributorCustomer::DB_TABLE)
                    ->where('status', '=', 1)
                    ->whereRaw(XeDistributorCustomer::DB_TABLE . '.sub_user_id = ' . XeUser::DB_TABLE . '.xe_user_id')
                    ->whereRaw(XeDistributorCustomer::DB_TABLE . '.xe_user_id = "' . $parent_xe_user_id . '"');
            });
        }

        HelperService::queryWhen(
            $query,
            $params,
            [
                [
                    'field' => 'xe_user_id',
                ],
                [
                    'field' => 'phone',
                ],
                [
                    'field'    => 'user_created_at_begin',
                    'alias'    => 'user_created_at',
                    'operator' => '>=',
                ],
                [
                    'field'    => 'user_created_at_end',
                    'alias'    => 'user_created_at',
                    'operator' => '<=',
                ],
            ]
        );

        return $query->paginate($params['size'] ?? 10);

    }

    public function orderDistributeList($params, $admin)
    {
        $query = XeOrderDistribute::query()
            ->select([
                'id', 'order_id', 'share_user_id', 'share_user_nickname', 'distribute_price', 'created_at'
            ]);

        $query->with([
            'shareUserInfo:id,user_id,xe_user_id,phone',
        ]);

        HelperService::queryWhen(
            $query,
            $params,
            [
                [
                    'field' => 'share_user_id',
                ],
                [
                    'field' => 'order_id',
                ],
                [
                    'field'    => 'share_user_nickname',
                    'operator' => 'like',
                ],
                [
                    'field'    => 'phone',
                    'model'    => 'shareUserInfo',
                    'operator' => '=',
                ],
                [
                    'field'    => 'share_user_phone',
                    'alias'    => 'phone',
                    'model'    => 'shareUserInfo',
                    'operator' => '=',
                ],
                [
                    'field'    => 'created_at_begin',
                    'alias'    => 'created_at',
                    'operator' => '>=',
                ],
                [
                    'field'    => 'created_at_end',
                    'alias'    => 'created_at',
                    'operator' => '<=',
                ],
            ]
        );

        $query->orderBy('id', 'desc');

        return $query->paginate($params['size'] ?? 10);
    }

    public function orderList($params, $admin)
    {
        $query = XeOrder::query()
            ->select([
                'id', 'xe_user_id', 'order_id',
                'wx_app_type', 'pay_type', 'channel_type',
                'actual_fee', 'actual_price',
                'order_type', 'xe_created_time',
                'pay_state', 'pay_state_time',
                'order_state', 'order_state_time',
            ])
            ->with([
                'xeUserInfo:id,xe_user_id,user_id,nickname,phone,name,wx_union_id,user_created_at',
                'xeUserInfo.vipInfo:id,user_id,username,source,source_vip_id',
                'xeUserInfo.vipInfo.sourceVipInfo:id,nickname,username,username as phone',
                'xeUserInfo.liveUserWaiterInfo:user_id,admin_id',
                'xeUserInfo.liveUserWaiterInfo.adminUserInfo:id,name',
                'xeUserInfo.vipBindInfo:parent,son,life,begin_at,end_at',
                'orderGoodsInfo:order_id,sku_id,goods_name,goods_image,buy_num',
                'distributeInfo:id,order_id,share_user_id,distribute_price',
                'distributeInfo.shareUserInfo:id,xe_user_id,nickname,phone',
            ]);

        HelperService::queryWhen(
            $query,
            $params,
            [
                [
                    'field' => 'xe_user_id',
                ],
                [
                    'field'    => 'goods_name',
                    'model'    => 'orderGoodsInfo',
                    'operator' => 'like',
                ],
                [
                    'field'    => 'share_user_phone',
                    'model'    => 'distributeInfo.shareUserInfo',
                    'alias'    => 'phone',
                    'operator' => '=',
                ],
                [
                    'field'    => 'admin_id',
                    'model'    => 'xeUserInfo.liveUserWaiterInfo',
                    'alias'    => 'admin_id',
                    'operator' => '=',
                ],
                [
                    'field'    => 'vip_bind_parent',
                    'model'    => 'xeUserInfo.vipBindInfo',
                    'alias'    => 'parent',
                    'operator' => '=',
                ],
                [
                    'field' => 'order_id',
                ],
                [
                    'field' => 'wx_app_type',
                ],
                [
                    'field' => 'pay_type',
                ],
                [
                    'field' => 'order_type',
                ],
                [
                    'field'    => 'order_state',
                    'can_zero' => true,
                ],
                [
                    'field'    => 'phone',
                    'model'    => 'xeUserInfo',
                    'operator' => 'like',
                ],
                [
                    'field'    => 'xe_user_phone',
                    'alias'    => 'phone',
                    'model'    => 'xeUserInfo',
                    'operator' => 'like',
                ],
                [
                    'field'    => 'sku_id',
                    'model'    => 'orderGoodsInfo',
                    'operator' => 'like',
                ],
                [
                    'field'    => 'xe_created_time_begin',
                    'alias'    => 'xe_created_time',
                    'operator' => '>=',
                ],
                [
                    'field'    => 'xe_created_time_end',
                    'alias'    => 'xe_created_time',
                    'operator' => '<=',
                ],
                [
                    'field'    => 'pay_state_time_begin',
                    'alias'    => 'pay_state_time',
                    'operator' => '>=',
                ],
                [
                    'field'    => 'pay_state_time_end',
                    'alias'    => 'pay_state_time',
                    'operator' => '<=',
                ],
                [
                    'field'    => 'order_state_time_begin',
                    'alias'    => 'order_state_time',
                    'operator' => '>=',
                ],
                [
                    'field'    => 'order_state_time_end',
                    'alias'    => 'order_state_time',
                    'operator' => '<=',
                ],
            ]
        );

        $query->orderBy('id', 'desc');

        $list = $query->paginate($params['size'] ?? 10);

        $xeo = new XeOrder();

        foreach ($list as $v) {
            $v->pay_type_desc    = $xeo->payType($v->pay_type);
            $v->order_type_desc  = $xeo->orderType($v->order_type);
            $v->pay_state_desc   = $xeo->payState($v->pay_state);
            $v->order_state_desc = $xeo->orderState($v->order_state);
            $v->wx_app_type_desc = $xeo->wx_app_type($v->wx_app_type);
        }

        return $list;
    }


    public function getOldBindWechatWaiter($unionid)
    {
        return DB::table('nlsg_user_wechat as uw')
            ->join(
                'crm_live_waiter_wechat as ww',
                'uw.follow_user_userid', '=', 'ww.follow_user_userid'
            )
            ->join(
                'crm_admin_user as cau',
                'ww.admin_id', '=', 'cau.id'
            )
            ->where('uw.unionid', '=', $unionid)
            ->select([
                'ww.follow_user_userid', 'ww.admin_id', 'cau.name',
                'uw.follow_user_createtime as bind_admin_time',
            ])
            ->first();
    }

    /**定时任务**/
    public function runUserJobParent(): bool
    {
        $xts = new XiaoeTechServers();

        while (true) {

            $list = XeUserJob::query()
                ->where('parent_job', '=', 1)
                ->select(['parent_phone'])
                ->groupBy('parent_phone')
                ->limit(100)
                ->get();

            if ($list->isEmpty()) {
                break;
            }

            $list = $list->toArray();

            foreach ($list as $v) {

                if (empty($v['parent_phone'])) {
                    XeUserJob::query()
                        ->where('parent_phone', '=', '')
                        ->update([
                            'parent_job' => 2,
                        ]);
                    continue;
                }

                $temp_res = $xts->distributor_member_add($v['parent_phone']);

                if (strlen($temp_res['user_id'] ?? '') > 0) {
                    XeUserJob::query()
                        ->where('parent_phone', '=', $v['parent_phone'])
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
                        ->where('parent_phone', '=', '')
                        ->update($err_array);
                }
            }
        }

        return true;
    }

    public function runUserJobSon($flag): bool
    {
        $xts = new XiaoeTechServers();
        //515529 813685   6分
        $begin_id = XeUserJob::query()
            ->where('son_phone', '<>', '')
            ->where('son_job', '=', 1)
            ->min('id');

        $end_id = XeUserJob::query()
                ->where('son_phone', '<>', '')
                ->where('son_job', '=', 1)
                ->max('id') + 200;

        $fen       = 8;
        $fen_limit = (int)ceil(($end_id - $begin_id) / $fen);

        while (true) {

            $query = XeUserJob::query()
                ->where('son_job', '=', 1)
                ->select(['id', 'son_phone', 'son_xe_user_id']);

            if ($flag) {
                $query->where('id', '>=', $begin_id + ($flag - 1) * $fen_limit);
                $query->where('id', '<=', $begin_id + $flag * $fen_limit);
            }

            $list = $query->limit(100)->get();

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
                echo $v['id'], '---', $v['son_phone'], PHP_EOL;
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
        }

        return true;

    }

    public function runUserJobBind($flag): bool
    {
        $xts = new XiaoeTechServers();

        $begin_id = XeUserJob::query()
            ->where('parent_xe_user_id', '<>', '')
            ->where('son_xe_user_id', '<>', '')
            ->where('bind_job', '=', 1)
            ->min('id');

        $end_id = XeUserJob::query()
                ->where('parent_xe_user_id', '<>', '')
                ->where('son_xe_user_id', '<>', '')
                ->where('bind_job', '=', 1)
                ->min('id') + 200;

        $fen       = 8;
        $fen_limit = (int)ceil(($end_id - $begin_id) / $fen);

        while (true) {
            $query = XeUserJob::query()
                ->where('bind_job', '=', 1)
                ->where('parent_xe_user_id', '<>', '')
                ->where('son_xe_user_id', '<>', '');

            if ($flag) {
                $query->where('id', '>=', $begin_id + ($flag - 1) * $fen_limit);
                $query->where('id', '<=', $begin_id + $flag * $fen_limit);
            }

            $list = $query
                ->select(['id', 'parent_phone', 'parent_xe_user_id', 'son_phone', 'son_xe_user_id'])
                ->limit(100)
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
                echo $v['id'], '---', $v['son_xe_user_id'], PHP_EOL;
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
        }

        return true;

    }

    public function vipBindToUserJob()
    {
        $page = 1;
        $size = 100;

        while (true) {
            $offset = ($page - 1) * $size;
            $page++;
            $sql = 'SELECT parent as parent_phone,son as son_phone from nlsg_vip_user_bind
where parent = "18512378959"
and (
	life = 1 or (life = 2 and end_at >= "2022-12-14 00:00:00" )
)
and `status` = 1  LIMIT ? OFFSET ?';

            $list = DB::select($sql, [$size, $offset]);
            if (empty($list)) {
                exit('没有数据了:' . $page);
            }

            foreach ($list as &$v) {
                $v = (array)$v;
            }

            echo $page, '--', PHP_EOL;

            XeUserJob::query()->insert($list);
        }

    }

    public function hjCheck()
    {
        if (0) {
            $sql  = "select son from nlsg_vip_user_bind where parent in (
'15811570751','18500401602','13311111111','13411111111','13211111111','15032798039','15811570751','13522223779','17316297021','18516970530','18512341111','18512341112','18512341113','18512341114','18512341115','18512341116','18512341117','18512341118','18512342221','18512342222','18512342223','18512342224','18512342225','18512342226','18512342227','18512342228','18512342229','18512342230','18512342231','18512342232','18512342233','18512342234','18512342235','18512342236','18512342237','18512342238','18512342239','18512342240','18624078563','18512341111','18512342222','12000000001','12000000001','18522222001','18522222002','18522222003','18522222004','18522222005','18522222006','18522222007','18522222008','18522222009','18522222010','18522222011','18522222012','18522222013','18522222014','18522222015','18522222016','18522222017','18522222018','18522222019','18522222020','18522222021','18522222022','18522222023','18522222024','18522222025','18522222026','18522222027','18522222028','18522222029','18522222030','18522222031','18522222032','18522222033','18522222034','18522222035','18522222036','18522222037','18522222038','18522222039','18522222040','18522222041','18522222042','18522222043','18522222044','18522222045','18522222046','18522222047','18522222048','18522222049','18522222050','11002174002','18522222051','18522222052','18522222053','18522222054','18522222055','18522222056','18522222057','18522222058','18522222059','18522222060','18522222061','18522222062','18522222063','18522222064','18522222065','18522222066','18522222067','18522222068','18522222069','18522222070','18522222071','18522222072','18522222073','18522222074','18522222075','18522222076','18522222077','18522222078','18522222079','18522222080','18522222081','18522222082','18522222083','18522222084','18522222085','18522222086','18522222087','18522222088','18522222089','18522222090','18522222091','18522222092','18522222093','18522222094','18522222095','18522222096','18522222097','18522222098','18522222099','18522222100','18522222101','18522222102','18522222103','18522222104','18522222105','18522222106','18522222107','18522222108','18522222109','18522222110','18522222301','18522222302','18522222303','18522222304','18522222305','18522222306','18522222307','18522222308','18522222309','18522222310','18522222101','18522222102','18522222103','18522222104','18522222105','18522222201','18522222202','18522222203','18522222204','18522222205','18522222206','18522222207','18522222208','18522222209','18522222210','18522222211','18522222212','18522222213','18522222214','18522222215','18522222216','18522222217','18522222218','18522222219','18522222220','18522222221','18522222222','18522222223','18522222224','18522222225','18522222226','18522222227','18522222228','18522222229','18522222230','18522222231','18522222232','18522222233','18522222234','18522222235','18522222236','18522222237','18522222238','18522222239','18522222240','18522222241','18522222242','18522222243','18522222244','18522222245','18522222246','18522222247','18522222248','18522222249','18522222250','18522222251','18522222252','18522222253','18522222254','18522222255','18522222256','18522222257','18522222258','18522222259','18522222260','18522222261','18522222262','18522222263','18522222264','18522222265','18522222266','18522222267','18522222268','18522222269','18522222270','18522222271','18522222272','18522222273','18522222274','18522222275','18522222276','18522222277','18522222278','18522222279','18522222280','18522222281','18522222282','18522222283','18522222284','18522222285','18522222286','18522222287','18522222288','18522222289','18522222290','18522222291','18522222292','18522222293','18522222294','18522222295','18522222296','18522222297','18522222298','18522222299','18522222300','18522222401','18522222402','18522222403','18522222404','18522222405','18522222406','18522222407','18522222408','18522222409','18522222410','18522222411','18522222412','18522222413','18522222414','18522222415','18522222416','18522222417','18522222418','18522222419','18522222420','18522222421','18522222422','18522222423','18522222424','18522222425','18522222426','18522222427','18522222428','18522222429','18522222430','18522222431','18522222432','18522222433','18522222434','18522222435','18522222436','18522222437','18522222438','18522222439','18522222440','18522222441','18522222442','18522222443','18522222444','18522222445','18522222446','18522222447','18522222448','18522222449','18522222450','18522222451','18522222452','18522222453','18522222454','18522222455','18522222456','18522222457','18522222458','18522222459','18522222460','18522222461','18522222462','18522222463','18522222464','18522222465','18522222466','18522222467','18522222468','18522222469','18522222470','18522222471','18522222472','18522222473','18522222474','18522222475','18522222476','18522222477','18522222478','18522222479','18522222480','18522222481','18522222482','18522222483','18522222484','18522222485','18522222486','18522222487','18522222488','18522222489','18522222490','18522222491','18522222492','18522222493','18522222494','18522222495','18522222496','18522222497','18522222498','18522222499','18522222500','18522222501','18522222502','18522222503','18522222504','18522222505','18522222506','18522222507','18522222508','18522222509','18522222510','18511111001','18511111002','18511111003','18511111004','18511111005','18511111006','18511111007','18511111008','18511111009','18511111010','18511111011','18511111012','18511111013','18511111014','18511111015','18511111016','18511111017','18511111018','18511111019','18511111020','18511111021','18511111022','18511111023','18511111024','18511111025','18511111026','18511111027','18511111028','18511111029','18511111030','18511111031','18511111032','18511111033','18511111034','18511111035','18511111036','18511111037','18511111038','18511111039','18511111040','18511111041','18511111042','18511111043','18511111044','18511111045','18511111046','18511111047','18511111048','18511111049','18511111050','18511111051','18511111052','18511111053','18511111054','18511111055','18511111056','18511111057','18511111058','18511111059','18511111060','18511111061','18511111062','18511111063','18511111064','18511111065','18511111066','18511111067','18511111068','18511111069','18511111070','18511111071','18511111072','18511111073','18511111074','18511111075','18511111076','18511111077','18511111078','18511111079','18511111080','18511111081','18511111082','18511111083','18511111084','18511111085','18511111086','18511111087','18511111088','18511111089','18511111090','18511111091','18511111092','18511111093','18511111094','18511111095','18511111096','18511111097','18511111098','18511111099','18511111100','18511111201','18511111202','18511111203','18511111204','18511111205','18511111206','18511111207','18511111208','18511111209','18511111210','18511111211','18511111212','18511111213','18511111214','18511111215','18511111216','18511111217','18511111218','18511111219','18511111220','18511111221','18511111222','18511111223','18511111224','18511111225','18511111226','18511111227','18511111228','18511111229','18511111230','18511111231','18511111232','18511111233','18511111234','18511111235','18511111236','18511111237','18511111238','18511111239','18511111240','18511111241','18511111242','18511111243','18511111244','18511111245','18511111246','18511111247','18511111248','18511111249','18511111250','18511111251','18511111252','18511111253','18511111254','18511111255','18511111256','18511111257','18511111258','18511111259','18511111260','18511111261','18511111262','18511111263','18511111264','18511111265','18511111266','18511111267','18511111268','18511111269','18511111270','18511111271','18511111272','18511111273','18511111274','18511111275','18511111276','18511111277','18511111278','18511111279','18511111280','18511111281','18511111282','18511111283','18511111284','18511111285','18511111286','18511111287','18511111288','18511111289','18511111290','18511111291','18511111292','18511111293','18511111294','18511111295','18511111296','18511111297','18511111298','18511111299','18511111300','18522222001','18522222002','18522222003','18522222004','18522222005','18522222006','18522222007','18522222008','18522222009','18522222010','18522222011','18522222012','18522222013','18522222014','18522222015','18522222016','18522222017','18522222018','18522222019','18522222020','18522222021','18522222022','18522222023','18522222024','18522222025','18522222026','18522222027','18522222028','18522222029','18522222030','18522222031','18522222032','18522222033','18522222034','18522222035','18522222036','18522222037','18522222038','18522222039','18522222040','18522222041','18522222042','18522222043','18522222044','18522222045','18522222046','18522222047','18522222048','18522222049','18522222050','18522222051','18522222052','18522222053','18522222054','18522222055','18522222056','18522222057','18522222058','18522222059','18522222060','18522222061','18522222062','18522222063','18522222064','18522222065','18522222066','18522222067','18522222068','18522222069','18522222070','18522222071','18522222072','18522222073','18522222074','18522222075','18522222076','18522222077','18522222078','18522222079','18522222080','18522222081','18522222082','18522222083','18522222084','18522222085','18522222086','18522222087','18522222088','18522222089','18522222090','18522222091','18522222092','18522222093','18522222094','18522222095','18522222096','18522222097','18522222098','18522222099','18522222100','18522222101','18522222102','18522222103','18522222104','18522222105','18522222106','18522222107','18522222108','18522222109','18522222110','18522222601','18522222602','18522222603','18522222604','18522222605','18522222606','18522222607','18522222608','18522222609','18522222610','18522222611','18522222612','18522222613','18522222614','18522222615','18522222616','18522222617','18522222618','18522222619','18522222620','18522222621','18522222622','18522222623','18522222624','18522222625','18522222626','18522222627','18522222628','18522222629','18522222630','18522222631','18522222632','18522222633','18522222634','18522222635','18522222636','18522222637','18522222638','18522222639','18522222640','18522222641','18522222642','18522222643','18522222644','18522222645','18522222646','18522222647','18522222648','18522222649','18522222650','18522222651','18522222652','18522222653','18522222654','18522222655','18522222656','18522222657','18522222658','18522222659','18522222660','18522222661','18522222662','18522222663','18522222664','18522222665','18522222666','18522222667','18522222668','18522222669','18522222670','18522222671','18522222672','18522222673','18522222674','18522222675','18522222676','18522222677','18522222678','18522222679','18522222680','18522222681','18522222682','18522222683','18522222684','18522222685','18522222686','18522222687','18522222688','18522222689','18522222690','18522222691','18522222692','18522222693','18522222694','18522222695','18522222696','18522222697','18522222698','18522222699','18522222700','18522222701','18522222702','18522222703','18522222704','18522222705','18522222706','18522222707','18522222708','18522222709','18522222710','18522222711','18522222712','18522222713','18522222714','18522222715','18522222716','18522222717','18522222718','18522222719','18522222720','18522222721','18522222722','18522222723','18522222724','18522222725','18522222726','18522222727','18522222728','18522222729','18522222730','18522222731','18522222732','18522222733','18522222734','18522222735','18522222736','18522222737','18522222738','18522222739','18522222740','18522222741','18522222742','18522222743','18522222744','18522222745','18522222746','18522222747','18522222748','18522222749','18522222750','18522222751','18522222752','18522222753','18522222754','18522222755','18522222756','18522222757','18522222758','18522222759','18522222760','18522222761','18522222762','18522222763','18522222764','18522222765','18522222766','18522222767','18522222768','18522222769','18522222770','18522222771','18522222772','18522222773','18522222774','18522222775','18522222776','18522222777','18522222778','18522222779','18522222780','18522222781','18522222782','18522222783','18522222784','18522222785','18522222786','18522222787','18522222788','18522222789','18522222790','18522222791','18522222792','18522222793','18522222794','18522222795','18522222796','18522222797','18522222798','18522222799','18522222800','18522222401','18511111101','18511111102','18511111103','18511111104','18511111105','18511111106','18511111107','18511111108','18511111109','18511111110','18522222801','18522222802','18522222803','18522222804','18522222805','18522222806','18522222807','18522222808','18522222809','18522222810','18522222811','18522222812','18522222813','18522222814','18522222815','18522222816','18522222817','18522222818','18522222819','18522222820','18522222821','18522222822','18522222823','18522222824','18522222825','18522222826','18522222827','18522222828','18522222829','18522222830','18522222831','18522222832','18522222833','18522222834','18522222835','18522222836','18522222837','18522222838','18522222839','18522222840','18522222841','18522222842','18522222843','18522222844','18522222845','18522222846','18522222847','18522222848','18522222849','18522222850','18522222851','18522222852','18522222853','18522222854','18522222855','18522222856','18522222857','18522222858','18522222859','18522222860','18522222861','18522222862','18522222863','18522222864','18522222865','18522222866','18522222867','18522222868','18522222869','18522222870','18522222871','18522222872','18522222873','18522222874','18522222875','18522222876','18522222877','18522222878','18522222879','18522222880','18522222881','18522222882','18522222883','18522222884','18522222885','18522222886','18522222887','18522222888','18522222889','18522222890','18522222891','18522222892','18522222893','18522222894','18522222895','18522222896','18522222897','18522222898','18522222899','18522222900','18522222001'
) and `status`=1 ;";
            $list = DB::select($sql);

            $list = array_chunk($list, 2000);

            $err_list = [];

            foreach ($list as $v) {
                $temp_list = [];
                foreach ($v as $vv) {
                    $temp_list[] = $vv->son;
                }

                $check = XeUserJob::query()
                    ->whereIn('son_phone', $temp_list)
                    ->pluck('son_phone')
                    ->toArray();

                $temp_err = array_diff($temp_list, $check);

                if ($temp_err) {
                    $err_list = array_merge($err_list, $temp_err);
                }
            }

            foreach ($err_list as $v) {
                XeUserJob::query()->insert([
                    'parent_phone'      => '18512378959',
                    'parent_xe_user_id' => 'u_5d538b27472fb_gbuhCZK6To',
                    'son_phone'         => $v,
                    'parent_job'        => 2,
                ]);
            }

            dd($err_list);

        }

        if (0) {
            $err_list = [];

            $page = 1;
            $size = 2000;

            while (true) {
                echo $page, '---', ($page - 1) * $size, PHP_EOL;
                $list = VipUserBind::query()
                    ->where('parent', '=', '18512378959')
                    ->where('status', '=', 1)
                    ->limit($size)
                    ->offset(($page - 1) * $size)
                    ->pluck('son')
                    ->toArray();

                if (empty($list)) {
                    break;
                }

                $page++;

                $check = XeUserJob::query()
                    ->whereIn('son_phone', $list)
                    ->pluck('son_phone')
                    ->toArray();

                $temp_err = array_diff($list, $check);

                if ($temp_err) {
                    $err_list = array_merge($err_list, $temp_err);
                }
            }

            foreach ($err_list as $v) {
                XeUserJob::query()->insert([
                    'parent_phone'      => '18512378959',
                    'parent_xe_user_id' => 'u_5d538b27472fb_gbuhCZK6To',
                    'son_phone'         => $v,
                    'parent_job'        => 2,
                ]);
            }

            dd($err_list);
        }

    }


}
