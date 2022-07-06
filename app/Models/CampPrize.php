<?php

namespace App\Models;

class CampPrize extends Base
{
    protected $table = 'crm_camp_prize';

    protected $fillable = [
        'camp_id', 'type',  'title', 'status', 'cover_pic','week_id',
    ];

     /**
     * 获取奖品信息
     * @param  int $col_id 训练营id
     * @return string[] prize_title,cover_image
     * */
    static function getPrize($col_id){
        $prize = CampPrize::select('id','type','relation_id','info_ids','title','cover_pic')->where(['column_id'=>$col_id,'status'=>1,'source_type'=>1])->get()->toArray();
        // $prize = array_column($prize,null,"id");

        foreach($prize as &$prize_val){
            switch($prize_val['type']){
                case 1:
                    $res=Works::select("title as prize_title","cover_img as cover_image")->find($prize_val['relation_id']);
                     break;
                case 2:
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


        }
        return $prize;
    }
}
