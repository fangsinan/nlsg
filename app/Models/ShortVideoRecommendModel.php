<?php


namespace App\Models;


class ShortVideoRecommendModel extends Base
{
    protected $table = 'nlsg_short_video_recommend';


    //获取短视频
    public function getRecommend ($id){
        //按照rand、创建时间排序
        $field = ["relation_type","relation_id","push_time"];
        $data = self::select($field)->where(['video_id'=>$id, 'status'=>1,])->OrderBy('push_time')->get()->toArray();

        $recomObj = new Recommend();
        foreach ($data as $key=>&$com_val){
            $com_val['info'] = $recomObj->getResult($com_val['relation_type'],[$com_val['relation_id']]);
        }

        return $data;
    }
}
