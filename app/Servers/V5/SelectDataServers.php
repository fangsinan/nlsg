<?php

namespace App\Servers\V5;

use App\Models\Lists;
use App\Models\RecommendConfig;
use App\Models\User;
use App\Models\Works;

class SelectDataServers
{
    public function recommendTypeList($params): array {
        $rcModel = new RecommendConfig();
        return [
            'show_position' => $rcModel->show_position_array,
            'jump_type'     => $rcModel->jump_type_array,
            'modular_type'  => $rcModel->modular_type_array,
        ];
    }


    public function worksList($params) {
        return Works::query()
            ->where('type', '=', 2)
            ->where('status', '=', 4)
            ->where('is_audio_book','=',0)
            ->select(['id', 'title'])
            ->get();
    }

    public function worksListsList($params) {
        return Lists::query()
            ->where('status', '=', 1)
            ->select(['id', 'title', 'type'])
            ->get();
    }

    public function teacherList($params) {
        return User::query()
            ->where('is_author', '=', 1)
            ->where('status', '=', 1)
            ->where('id', '<>', 1)
            ->select(['id', 'nickname', 'nickname as title','honor'])
            ->get();
    }

}
