<?php

namespace App\Servers\V5;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TeacherServers
{
    public function list($params): LengthAwarePaginator
    {
        $nickname = $params['nickname'] ?? '';
        $phone    = $params['phone'] ?? 0;
        $query    = User::query()
            ->select([
                'id', 'phone', 'nickname', 'headimg', 'status', 'honor', 'intro'
            ])
            ->where('id', '>', 1)
            ->where('is_test_pay', '=', 0)
            ->where('is_author', '=', 1)
            ->where('status', '=', 1)
            ->when($nickname, function ($q) use ($nickname) {
                $q->where('nickname', 'like', "$nickname");
            })
            ->when($phone, function ($q) use ($phone) {
                $q->where('phone', 'like', "$phone");
            });

        $query->orderBy('id', 'desc');
        $query->orderBy('updated_at', 'desc');

        return $query->paginate($params['size'] ?? 10);
    }

    public function create($params): array
    {
        $id               = $params['id'] ?? 0;
        $data['phone']    = $params['phone'] ?? '';
        $data['nickname'] = $params['nickname'] ?? '';
        $data['headimg']  = $params['headimg'] ?? '';
        $data['honor']    = $params['honor'] ?? '';
        $data['intro']    = $params['intro'] ?? '';

        $validator = Validator::make(
            $data,
            [
                'nickname' => 'bail|required',
                'headimg'  => 'bail|required',
                'honor'    => 'bail|required',
                'intro'    => 'bail|required',
            ],
            [
                'nickname.required' => '老师名必须填写',
                'headimg.required'  => '头像必须填写',
                'honor.required'    => '头衔必须填写',
                'intro.required'    => '简介必须填写',
            ]
        );

        if ($validator->fails()) {
            return ['code' => false, 'msg' => $validator->messages()->first()];
        }

        if (empty($id) && empty($data['phone'])) {
            $data['phone'] = date('YmdHis') . rand(1, 9);
        }

        $data['is_author'] = 1;

        if (empty($id)) {
            //添加
            $res = DB::table('nlsg_user')->insertGetId($data);
            if (empty($params['phone'] ?? '')) {
                DB::table('nlsg_user')
                    ->where('id', '=', $res)
                    ->update([
                        'phone' => $res
                    ]);
            }
        } else {
            //编辑
            $res = DB::table('nlsg_user')
                ->where('id', '=', $id)
                ->update($data);
        }

        if ($res) {
            return ['code' => true, 'msg' => '成功'];
        }

        return ['code' => false, 'msg' => '失败'];
    }

    public function info($params)
    {
        return User::query()
            ->where('id', '=', $params['id'] ?? 0)
            ->where('is_author', '=', 1)
            ->select([
                'id', 'phone', 'nickname', 'headimg', 'status', 'honor', 'intro'
            ])
            ->first();
    }
}
