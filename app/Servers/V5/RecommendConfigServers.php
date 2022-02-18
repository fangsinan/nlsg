<?php

namespace App\Servers\V5;

use App\Models\Recommend;
use App\Models\RecommendConfig;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;

class RecommendConfigServers
{

    public function list($params) {
        $title         = $params['title'] ?? '';
        $show_position = $params['show_position'] ?? '';
        $jump_type     = $params['jump_type'] ?? '';
        $modular_type  = $params['modular_type'] ?? '';
        $is_show       = $params['is_show'] ?? -1;
        $size          = $params['size'] ?? 10;

        $rcModel = new RecommendConfig();

        /*
         * 3  每日琨说, 4  直播, 5  精品课程, 6  短视频, 7  大咖主讲人, 8  1-3岁父母  主题课程, 9  精品专题, 10 热门榜单, 11 亲子专题
         *
         *
         * 3,4,6,10只能参与排序
         *
         * */
        $query = RecommendConfig::query()
            ->where('show_position', '=', 3)
            ->whereIn('jump_type', $rcModel->jump_type_array_keys)
            ->whereIn('modular_type', $rcModel->modular_type_array_keys)
            ->when($title, function ($q, $title) {
                $q->where('title', 'like', "%$title%");
            })
            ->when($show_position, function ($q, $show_position) {
                $q->where('show_position', '=', $show_position);
            })
            ->when($jump_type, function ($q, $jump_type) {
                $q->where('jump_type', '=', $jump_type);
            })
            ->when($modular_type, function ($q, $modular_type) {
                $q->where('modular_type', '=', $modular_type);
            });

        if ($is_show !== -1) {
            $query->where('is_show', '=', $is_show);
        }

        $query->select([
            'id', 'title', 'icon_pic', 'icon_mark', 'icon_mark_rang', 'show_position',
            'jump_type', 'modular_type', 'is_show', 'sort', 'jump_url', 'lists_id', 'created_at'
        ]);

        $query->withCount('recommendInfo')->orderBy('sort')->orderBy('id', 'desc');

        $list = $query->paginate($size);

        foreach ($list as $v) {
            $v->show_position_name = ($v->show_position_array)[$v->show_position] ?? '';
            $v->jump_type_name     = ($v->jump_type_array)[$v->jump_type] ?? '';
            $v->modular_type_name  = ($v->modular_type_array)[$v->modular_type] ?? '';
            if (in_array($v->modular_type, [3, 4, 6])) {
                $v->can_bind = 0;
            } else {
                $v->can_bind = 1;
            }
        }

        return $list;
    }

    public function changeStatus($data) {
        $params          = [];
        $params['id']    = (int)($data['id'] ?? 0);
        $params['flag']  = $data['flag'] ?? '';
        $params['value'] = (int)($data['value'] ?? 0);

        $validator = Validator::make($params, [
                'id'    => 'bail|required|integer|min:0',
                'flag'  => 'bail|required|in:sort,is_show',
                'value' => 'bail|required|integer|max:9999|min:0',
            ]
        );

        if ($validator->fails()) {
            return ['code' => false, 'msg' => $validator->messages()->first()];
        }

        $check_id = RecommendConfig::query()->where('id', '=', $params['id'])->first();
        if (!$check_id) {
            return ['code' => false, 'msg' => 'id错误'];
        }
        $rcModel = new RecommendConfig();
        switch ($params['flag']) {
            case 'is_show':
                $check_id->is_show = $params['value'] === 1 ? 1 : 0;
                break;
            case 'sort':
                $inc = RecommendConfig::query()
                    ->where('show_position', '=', 3)
                    ->whereIn('jump_type', $rcModel->jump_type_array_keys)
                    ->whereIn('modular_type', $rcModel->modular_type_array_keys);
                if ($params['value'] === 1) {
                    $inc->where('sort', '>=', $params['value']);
                } else {
                    $inc->where('sort', '>', $params['value']);
                }
                $inc->increment('sort');

                $check_id->sort = $params['value'];
                break;
        }

        $res = $check_id->save();
        if ($params['flag'] === 'sort') {
            $this->rc2Rank();
        }

        if (!$res) {
            return ['code' => false, 'msg' => '失败请重试'];
        }

        return ['code' => true, 'msg' => '成功'];
    }

    public function rc2Rank() {
        $rcModel   = new RecommendConfig();
        $temp_line = 11;
        $temp_list = RecommendConfig::query()
            ->where('show_position', '=', 3)
            ->whereIn('jump_type', $rcModel->jump_type_array_keys)
            ->whereIn('modular_type', $rcModel->modular_type_array_keys)
            ->select(['id'])
            ->orderBy('sort')
            ->get();
        foreach ($temp_list as $v) {
            $v->sort = $temp_line;
            $v->save();
            $temp_line++;
        }
    }


    public function add($data): array {
        $params             = [];
        $params['title']    = $data['title'] ?? '';
        $params['icon_pic'] = $data['icon_pic'] ?? '';

        $params['show_position'] = (int)($data['show_position'] ?? 0);
        $params['jump_type']     = (int)($data['jump_type'] ?? 0);
        $params['modular_type']  = (int)($data['modular_type'] ?? 0);

        $params['is_show']  = $data['is_show'] ?? -1;
        $params['jump_url'] = $data['jump_url'] ?? '';

        $validator = Validator::make($params, [
                'title'         => 'bail|required',
                'show_position' => 'bail|required|min:1',
                'jump_type'     => 'bail|required|min:1',
                'modular_type'  => 'bail|required|min:1',
                'is_show'       => 'bail|required|min:0|max:1',
            ]
        );

        if ($validator->fails()) {
            return ['code' => false, 'msg' => $validator->messages()->first()];
        }

        $res = RecommendConfig::query()->updateOrCreate(['id' => $data['id'] ?? 0], $params);

        if (!$res) {
            return ['code' => false, 'msg' => '失败,请重试.'];
        }

        return ['code' => true, 'msg' => '成功'];

    }

    public function info($params) {
        $id = $params['id'] ?? 0;
        if (empty($id)) {
            return ['code' => false, 'msg' => 'id错误'];
        }

        $check_id = RecommendConfig::query()
            ->where('id', '=', $id)
            ->select(['id', 'modular_type'])
            ->first();
        if (empty($check_id)) {
            return ['code' => false, 'msg' => 'id错误'];
        }

        switch ($check_id->modular_type) {
            case 5:
            case 8:
            case 11:
                $with_str = 'recommendInfo.works:id,title,cover_img';
                break;
            case 7:
                $with_str = 'recommendInfo.teacher:id,nickname,headimg';
                break;
            default:
                $with_str = 'recommendInfo.lists:id,title,cover';
                break;
        }

        $query = RecommendConfig::query()
            ->where('id', '=', $id)
            ->select([
                'id', 'id as recommend_config_id', 'title', 'icon_pic', 'icon_mark', 'icon_mark_rang', 'show_position',
                'jump_type', 'modular_type', 'is_show', 'sort', 'jump_url', 'lists_id', 'created_at'
            ])->with([
                'recommendInfo' => function ($q) {
                    $q->where('status', '=', 1);
                }, $with_str
            ]);

        $res = $query->first();

        foreach ($res->recommendInfo as $v) {
            switch ($check_id->modular_type) {
                case 5:
                case 8:
                case 11:
                    $v->info_id    = $v->works->id;
                    $v->info_title = $v->works->title;
                    $v->info_img   = $v->works->cover_img;
                    break;
                case 7:
                    $v->info_id    = $v->teacher->id;
                    $v->info_title = $v->teacher->nickname;
                    $v->info_img   = $v->teacher->headimg;
                    break;
                default:
                    $v->info_id    = $v->lists->id;
                    $v->info_title = $v->lists->title;
                    $v->info_img   = $v->lists->cover;
                    break;
            }
        }

        return $res;
    }

    public function sort($params) {
        return [1, 2, 3];
    }

    public function infoSelectList($params) {
        $modular_type = (int)($params['modular_type'] ?? 0);
        if (empty($modular_type)) {
            return [];
        }

        $sds = new SelectDataServers();
        switch ($modular_type) {
            case 5:
            case 8:
            case 11:
                return $sds->worksList($params);
            case 7:
                return $sds->teacherList($params);
            default:
                return $sds->worksListsList($params);
        }
    }

    public function selectList($params): array {
        $rcModel = new RecommendConfig();
        return [
            'show_position' => $rcModel->show_position_array,
            'jump_type'     => $rcModel->jump_type_array,
            'modular_type'  => $rcModel->modular_type_array,
        ];
    }

    public function delInfoBind($data): array {
        $params                        = [];
        $params['recommend_config_id'] = (int)($data['recommend_config_id'] ?? 0);
        $params['recommend_info_id']   = (int)($data['recommend_info_id'] ?? 0);

        $validator = Validator::make($params, [
                'recommend_config_id' => 'bail|required|min:1',
                'recommend_info_id'   => 'bail|required|min:1',
            ]
        );

        if ($validator->fails()) {
            return ['code' => false, 'msg' => $validator->messages()->first()];
        }

        DB::beginTransaction();
//        $res = Recommend::query()->where('id', '=', $params['recommend_info_id'])
//            ->where('position', '=', $params['recommend_config_id'])
//            ->update(['status' => 0]);

        $check = Recommend::query()->where('id', '=', $params['recommend_info_id'])
            ->where('position', '=', $params['recommend_config_id'])
            ->select(['id','type','position'])
            ->first();

        $check->status = 0;
        $res = $check->save();

        if (!$res) {
            DB::rollBack();
            return ['code' => false, 'msg' => '失败'];
        }
        DB::commit();

        $this->delRecommendCache($check['type'],$check['position']);

        return ['code' => true, 'msg' => '成功'];
    }

    public function infoBind($data): array {
        $params                        = [];
        $params['recommend_config_id'] = (int)($data['recommend_config_id'] ?? 0);
        $params['show_position']       = (int)($data['show_position'] ?? 0);
        $params['jump_type']           = (int)($data['jump_type'] ?? 0);
        $params['modular_type']        = (int)($data['modular_type'] ?? 0);
        $params['obj_id']              = (int)($data['obj_id'] ?? 0);

        $validator = Validator::make($params, [
                'recommend_config_id' => 'bail|required',
                'show_position'       => 'bail|required|in:3',
                'jump_type'           => 'bail|required|in:4,11,13',
                'modular_type'        => 'bail|required|in:5,7,8,9,11',
            ]
        );

        if ($validator->fails()) {
            return ['code' => false, 'msg' => $validator->messages()->first()];
        }

        DB::beginTransaction();
        if (!empty($data['recommend_info_id'])) {
            $res = Recommend::query()->where('id', '=', $data['recommend_info_id'])
                ->where('position', '=', $params['recommend_config_id'])
                ->update(['status' => 0]);
            if (!$res) {
                DB::rollBack();
                return ['code' => false, 'msg' => '失败'];
            }
        }

        $d                  = [];
        $d['relation_id']   = $params['obj_id'];
        $d['relation_type'] = 0;
        $d['position']      = $params['recommend_config_id'];
        $d['type']          = 0;
        $d['status']        = 1;

        switch ($params['modular_type']) {
            case 5:
            case 8:
            case 11:
                $d['type'] = 2;
                break;
            case 7:
                $d['type'] = 14;
                break;
            case 9:
                $d['type'] = 15;
                break;
        }

        $now_date = date('Y-m-d H:i:s');
        $res      = Recommend::query()->firstOrCreate(
            $d,
            [
                'created_at' => $now_date,
                'nickname'   => $now_date,
            ]
        );

        if (!$res) {
            DB::rollBack();
            return ['code' => false, 'msg' => '失败'];
        }

        DB::commit();
        $this->delRecommendCache($d['type'],$d['position']);

        return ['code' => true, 'msg' => '成功'];
    }

    public function delRecommendCache($type=0,$position=0){
        Cache::forget('index_recommend_'.$type .'_'.$position);
    }

}
