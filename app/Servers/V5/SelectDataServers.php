<?php

namespace App\Servers\V5;

use App\Models\FeedbackType;
use App\Models\Lists;
use App\Models\Live;
use App\Models\LiveClassify;
use App\Models\LiveValidTime;
use App\Models\RecommendConfig;
use App\Models\User;
use App\Models\Works;
use Illuminate\Support\Facades\DB;

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


    public function worksList($params, $id = 0) {
        if ($id === 0) {
            return Works::query()
                ->where('type', '=', 2)
                ->where('status', '=', 4)
                ->where('is_audio_book', '=', 0)
                ->select(['id', 'title'])
                ->get();
        }

        $check = RecommendConfig::query()
            ->where('id', '=', $id)
            ->select(['id', 'lists_id'])
            ->first();

        if (empty($check['lists_id'])) {
            return [];
        }

        return DB::table('nlsg_works as w')
            ->join('nlsg_lists_work as l', 'w.id', '=', 'l.works_id')
            ->where('w.status', '=', 4)
            ->where('l.state', '=', 1)
            ->orderBy('l.sort')
            ->orderBy('l.id')
            ->select(['w.id', 'w.title'])
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
            ->select(['id', 'nickname', 'nickname as title', 'honor'])
            ->get();
    }


    public function liveList($params){
        $is_free = (int)($params['is_free'] ?? 0);
        return Live::query()
            ->where('id','>',350)
            ->where('status','=',4)
            ->where('is_del','=',0)
//            ->where('is_test','=',0)
            ->where('is_free','=',$is_free)
            ->select(['id','title'])
            ->orderBy('id','desc')
            ->get();
    }

    public function liveClassify($type = 1){
        return LiveClassify::query()
            ->where('type','=',$type)
            ->select([
                'id as key','name as value'
            ])
            ->get();
    }

    public function liveValidTimeList(){
        return LiveValidTime::query()
            ->where('status','=',1)
            ->select(['id','begin_at','end_at'])
            ->get();
    }

    public function feedbackTypeList(){
        return FeedbackType::query()
            ->select(['id','name'])
            ->where('status','=',1)
            ->get();
    }
}
