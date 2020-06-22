<?php

namespace App\Models;

use App\Models\Works;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\Wiki;
use App\Models\Lists;

class Recommend extends Base
{
    protected $table = 'nlsg_recommend';

    public function getIndexRecommend($type = 1, $position = '1', $limit = 5, $row=1)
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
            case 4:
                $model = new Lists();
                $result = $model->getIndexListenBook($ids);
                break;
            case 5:
                $model  = new Wiki();
                $result = $model->getIndexWiki($ids);
                break;
            case 7:
                $model  = new Live();
                $result = $model->getIndexLive($ids);
                break;
            case 8:
                $model = new MallGoods();
                $result  = $model->getIndexGoods($ids);
                break;
            case 9:
                //听书
                $model = new Works();
                $result = $model->getIndexWorks($ids, 1);
                break;

        }
        return $result;
    }
}
