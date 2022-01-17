<?php

namespace App\Servers\V5;

use App\Models\RecommendConfig;
use Illuminate\Support\Facades\Validator;

class RecommendConfigServers
{
    public function list($params) {
        $title         = $params['title'] ?? '';
        $show_position = $params['show_position'] ?? '';
        $jump_type     = $params['jump_type'] ?? '';
        $modular_type  = $params['modular_type'] ?? '';
        $is_show       = $params['is_show'] ?? -1;
        $size          = $params['size'] ?? 10;

        $query = RecommendConfig::query()
            ->where('show_position','=',3)
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
        }

        return $list;
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

    public function info($params){
        $id = $params['id'] ?? 0;
        if (empty($id)){
            return ['code'=>false,'msg'=>'id错误'];
        }


    }

    public function sort($params) {
        return [1, 2, 3];
    }

    public function selectList($params): array {
        $rcModel = new RecommendConfig();
        return [
            'show_position' => $rcModel->show_position_array,
            'jump_type'     => $rcModel->jump_type_array,
            'modular_type'  => $rcModel->modular_type_array,
        ];
    }

}
