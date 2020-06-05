<?php

namespace App\Models;

use App\Models\Works;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\Live;

class Recommend extends Model
{
    protected $table = 'nlsg_recommend';

    public function getIndexRecommend($type = 1, $position = '1', $limit = 5)
    {
        if (!$type){
            return false;
        }
//        DB::enableQueryLog();
        $list = $this->where('position', $position)
            ->where('type', $type)
            ->value('relation_id');
//        $quries = DB::getQueryLog();
//        dd($quries);

        $ids = explode(',', $list);
        switch ($type) {
            case 1:
                $model = new Column();
                $result = $model->getIndexColumn($ids);
                break;
            case 2:
                $model = new Works();
                $result = $model->getIndexWorks($ids);
                break;
            case 7:
                $model  = new Live();
                $result = $model->getIndexLive($ids);
                break;

        }
        return $result;
    }
}
