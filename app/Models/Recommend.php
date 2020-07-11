<?php

namespace App\Models;

use App\Models\Lists;
use App\Models\Works;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\Wiki;

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
                $result = $model->getIndexListWorks($ids, 3);
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
            case 10:
                $model = new Lists();
                $result = $model->getIndexListWorks($ids, 1);
                break;
            case 11:
                $model = new Lists();
                $result = $model->getIndexListWorks($ids, 2);
                break;

        }
        return $result;
    }


    public  function  getEditorWorks()
    {
        $lists = Recommend::with(['works'=>function($query){
                    $query->select('id','user_id','title','subtitle','cover_img','price','chapter_num','subscribe_num');
                 },
                 'works.user' => function($query){
                    $query->select('id','nickname');
                 }])
                 ->select('id', 'relation_id','reason')
                 ->where('position', 1)
                 ->where('type', 12)
                 ->orderBy('created_at', 'desc')
                 ->get();
        return $lists;
    }

    public function  works()
    {
        return $this->belongsTo('App\Models\Works', 'relation_id', 'id');
    }
}
