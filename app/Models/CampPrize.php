<?php

namespace App\Models;

class CampPrize extends Base
{
    protected $table = 'crm_camp_prize';

    protected $fillable = [
        'title', 'cover_pic',  'column_id', 'works_info_id', 'info_ids','relation_id','type','source_type','status','period_num'
    ];


    static function prizeToRes($query){
        $prize = $query->select('id','type','relation_id','info_ids','title','cover_pic','period_num_name')->get();
        if(empty($prize)){
            return [];
        }
        $prize = $prize->toArray();
        foreach($prize as &$prize_val){
            switch($prize_val['type']){
                case 1:
                    // 课程
                    $res=Works::select("title as prize_title","cover_img as cover_image")->find($prize_val['relation_id']);
                    $types = FuncType(config('web.GlobalType.INPUT_TYPE.WorksType'));
                     break;
                case 2:
                    // 讲座
                    $types = FuncType(config('web.GlobalType.INPUT_TYPE.LectureType'));
                    $res=Column::select("name as prize_title","cover_pic as cover_image")->find($prize_val['relation_id']);
                    break;
                case 3:
                    $res=["prize_title"=>$prize['title'],"cover_image"=>$prize['cover_pic'],];
                    break;
                default:
                    $res=["prize_title"=>"","cover_image"=>"",];
                    break;
            }
            $prize_val['prize_title'] = $res['prize_title']??'';
            $prize_val['prize_pic']  = $res['cover_image']??'';
            $prize_val['sub_type']  = $types['sub_type']??0;
        }
        return $prize;
    }

     /**
     * 根据父类获取奖品信息
     * @param  int $col_id 训练营id
     * @return string[] prize_title,cover_image
     * */
    static function getPrizeByclassifyId($classifyId){
        $query = CampPrize::where(['column_id'=>$classifyId,'status'=>1,'source_type'=>1]);
        // $prize = CampPrize::select('id','type','relation_id','info_ids','title','cover_pic','period_num_name')
        // ->where(['column_id'=>$classifyId,'status'=>1,'source_type'=>1])->get()->toArray();
        // $prize = array_column($prize,null,"id");

        return self::prizeToRes($query);
    }


    /**
     * 根据具体周获取奖品信息
     * @param  array $weekIds 周ids
     * @return string[] prize_title,cover_image
     * */
    static function getPrizeByWeekId($weekIds){
        $prize_ids = ColumnWeekModel::whereIn('id',$weekIds)->pluck("prize_id")->toArray();
        // $prize = CampPrize::select('id','type','relation_id','info_ids','title','cover_pic','period_num_name')
        // ->whereIn('id',$prize_ids)->get()->toArray();
        $query = CampPrize::whereIn('id',$prize_ids);
        return self::prizeToRes($query);
    }
}
