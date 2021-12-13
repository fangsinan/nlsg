<?php


namespace App\Models;


class ShortVideoRecommedModel extends Base
{
    protected $table = 'nlsg_short_video_recomment';


    //获取短视频
    public function getRecomment ($id){
        //按照rand、创建时间排序
        $field = ["relation_type","relation_id","push_time"];
        $data = self::select($field)->where(['status'=>1,'video_id'=>$id])->OrderBy('push_time')->get()->toArray();

        $recomObj = new Recommend();
        foreach ($data as $key=>&$com_val){
            $com_val['info'] = $recomObj->getResult($com_val['relation_type'],[$com_val['relation_id']]);
        }

        return $data;
    }
}
