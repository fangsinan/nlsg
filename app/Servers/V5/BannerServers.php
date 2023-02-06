<?php


namespace App\Servers\V5;


use App\Models\Banner;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;

class BannerServers
{
    public function list($params): LengthAwarePaginator
    {
        $query = Banner::query()
                       ->whereIn('status', [1, 2])
                       ->where('app_project_type', '=', APP_PROJECT_TYPE)
                       ->select([
                           'id', 'title', 'pic', 'url', 'h5_url', 'type', 'start_time', 'end_time',
                           'created_at', 'status', 'jump_type', 'obj_id', 'info_id'
                       ]);

        HelperService::queryWhen(
            $query,
            $params,
            [
                ['field' => 'type'],
                ['field' => 'jump_type'],
                ['field' => 'status'],
                ['field' => 'title', 'operator' => 'like'],
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
                [
                    'field'    => 'start_time_begin',
                    'alias'    => 'start_time',
                    'operator' => '>=',
                ],
                [
                    'field'    => 'start_time_end',
                    'alias'    => 'start_time',
                    'operator' => '<=',
                ],
                [
                    'field'    => 'end_time_begin',
                    'alias'    => 'end_time',
                    'operator' => '>=',
                ],
                [
                    'field'    => 'end_time_end',
                    'alias'    => 'end_time',
                    'operator' => '<=',
                ],
            ]
        );

        $query->orderBy('status');

        if ((int)($params['type'] ?? 0) === 61) {
            $query->orderBy('start_time');
        }

        $query->orderBy('id', 'desc');

        $jump_type = $this->selectData(['flag' => 'jump_type', 'only_key' => false]);

        $list = $query->paginate($params['size'] ?? 10);

        foreach ($list as &$v) {
            $v->jump_type_desc = '-';

            foreach ($jump_type as $vv) {
                if ($vv['key'] === $v->jump_type) {
                    $v->jump_type_desc = $vv['value'];
                }
            }

        }

        return $list;
    }

    public function add($params): array
    {
        $type_list                  = $this->selectData(['flag' => 'type', 'only_key' => true]);
        $jump_type_list             = $this->selectData(['flag' => 'jump_type', 'only_key' => true]);
        $jump_type_list             = array_merge($jump_type_list, [1]);
        $params['h5_url']           = $params['url'] ?? '';
        $params['app_project_type'] = APP_PROJECT_TYPE;
        $params['version']          = '5.0.0';
        $validator                  = Validator::make(
            $params,
            [
                'title'      => 'bail|required',
                'pic'        => 'bail|required|url',
                'type'       => 'bail|required|in:' . implode(',', $type_list),
                'jump_type'  => 'bail|required|in:' . implode(',', $jump_type_list),
                'start_time' => 'exclude_unless:type,61|required|date|size:19',
                'end_time'   => 'exclude_unless:type,61|required|date|size:19|gt:start_time',
                'obj_id'     => [
                    function ($attribute, $value, $fail) use ($params) {
                        if (!in_array($params['jump_type'], [1, 13]) && empty($value)) {
                            $fail($attribute . ' 不能为空.');
                        }
                    }
                ],
                'id'         => [
                    function ($attribute, $value, $fail) {
                        if ($value > 0) {
                            $check = Banner::query()->where('id', '=', $value)->first();
                            if (!$check) {
                                $fail('id不存在.');
                            }
                        }
                    }
                ],
                'url'        => [
                    'bail',
                    'present',
                    function ($attribute, $value, $fail) use ($params) {
                        if (in_array($params['jump_type'], [1, 13]) && $value == '') {
                            $fail('请填写URL');
                        }
                    }
                ],
            ],
            [
                'flag.in'         => '类型错误:type,jump_type',
                'start_time.size' => '时间格式为 2022-01-01 01:00:00',
                'end_time.size'   => '时间格式为 2022-01-01 01:00:00',
            ]
        );

        if ($validator->fails()) {
            return ['code' => false, 'msg' => $validator->messages()->first()];
        }

        if ($params['type'] == 61) {
            $params['start_time'] = date('Y-m-d H:i:00', strtotime($params['start_time']));
            $params['end_time']   = date('Y-m-d H:i:59', strtotime($params['end_time']));
        }

        $params['pic'] = str_replace('https://image.nlsgapp.com/','',$params['pic']);

        $params['status'] = 1;

        if ($params['id'] ?? 0) {
            $res = Banner::query()
                         ->find($params['id'])
                         ->update($params);

        } else {
            $res = Banner::query()
                         ->insert($params);
        }

        if ($res) {
            return ['code' => true, 'msg' => '成功'];
        }

        return ['code' => false, 'msg' => '失败'];
    }

    public function changeStatus($params): array
    {
        $validator = Validator::make(
            $params,
            [
                'flag' => 'bail|required|string|in:on,off,del',
                'id'   => 'bail|required|exists:nlsg_banner,id'
            ],
            [
                'flag.in' => '操作类型错误',
            ]
        );

        if ($validator->fails()) {
            return ['code' => false, 'msg' => $validator->messages()->first()];
        }

        $check = Banner::query()->find($params['id']);

        switch ($params['flag']) {
            case 'on':
                $check->status = 1;
                break;
            case 'off':
                $check->status = 2;
                break;
            case 'del':
                $check->status = 3;
                break;
        }

        $res = $check->save();

        if ($res) {
            return ['code' => true, 'msg' => '成功'];
        }

        return ['code' => false, 'msg' => '失败'];

    }

    public function selectData($params): array
    {

        $validator = Validator::make(
            $params,
            [
                'flag' => 'bail|required|string|in:type,jump_type',
            ],
            [
                'flag.in' => '类型错误:type,jump_type',
            ]
        );

        if ($validator->fails()) {
            return ['code' => false, 'msg' => $validator->messages()->first()];
        }

        if ($params['flag'] === 'type') {
            $type_array = [
                ['key' => 1, 'value' => '首页'],
                ['key' => 51, 'value' => '商城首页轮播'],
                ['key' => 52, 'value' => '分类下方推荐位'],
                ['key' => 53, 'value' => '爆款推荐'],
                ['key' => 60, 'value' => '开屏图'],
                ['key' => 61, 'value' => '闪屏图'],
                ['key' => 81, 'value' => '直播列表'],
                ['key' => 82, 'value' => '大咖讲书'],
                ['key' => 83, 'value' => '课程首页'],
            ];

            if ($params['only_key'] ?? false) {
                return array_column($type_array, 'key');
            }

            return $type_array;
        }


        $jump_type_array = [
//            ['key' => 1, 'value' => 'H5', 'mrt_search_data' => 0],
['key' => 2, 'value' => '商品', 'mrt_search_data' => 122],
['key' => 4, 'value' => '课程', 'mrt_search_data' => 101],
['key' => 5, 'value' => '讲座', 'mrt_search_data' => 111],
['key' => 8, 'value' => '直播', 'mrt_search_data' => 131],
//            ['key' => 13, 'value' => 'APP内部H5', 'mrt_search_data' => 0],
        ];

        if ($params['only_key'] ?? false) {
            return array_column($jump_type_array, 'key');
        }

        return $jump_type_array;

    }


    public function info($params): array
    {
        return [__LINE__];
    }

}
