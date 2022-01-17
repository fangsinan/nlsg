<?php

namespace App\Servers\V5;

use App\Models\RecommendConfig;

class RecommendConfigServers
{
    public function list($params) {
        $rcModel = new RecommendConfig();
        return [1, 2, 3];
    }

    public function add($params) {
        return [1, 2, 3];
    }

    public function sort($params) {
        return [1, 2, 3];
    }

    public function selectList($params): array {
        $rcModel = new RecommendConfig();
        return [
            'show_position' => $rcModel->show_position,
            'jump_type'     => $rcModel->jump_type,
            'modular_type'  => $rcModel->modular_type,
        ];
    }

}
