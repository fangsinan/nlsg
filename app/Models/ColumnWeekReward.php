<?php
namespace App\Models;


class ColumnWeekReward extends Base
{
    protected $table = 'nlsg_column_week_reward';


    protected  $fillable = ['relation_id','user_id','is_get','is_end','end_time','os_type','week_id','speed_status','camp_id','week_num'];



    // 学习进度 奖励发放机制
    static function CampStudy($camp_id,$user_id,$os_type,$info_id){

        $column_data = Column::select('id','classify_column_id', 'info_column_id','end_time','show_info_num','is_start')->find($camp_id);

        if (empty($column_data)) {
            return ;
        }
        // 结营后三天没有奖励
        if( $column_data['is_start'] == 2 &&
            strtotime("+3 day",strtotime($column_data['end_time'])) <= time() ){
            return ;
        }

        // 查询当前章节所在周的所有章节是否学完   学完后添加nlsg_column_week_reward 表记录

        // 查看当前章节所在奖励信息
        $prize=CampPrize::where(['status'=>1,"column_id"=>$column_data['classify_column_id']])->whereRaw('FIND_IN_SET('.$info_id.',`info_ids`)')->first();
        if(empty($prize)){
            return ;
        }

        // 查看当前训练营和奖励对应 周
        $weeks = ColumnWeekModel::where(["relation_id" => $camp_id,"prize_id"=>$prize['id'],'is_del'=>0,])->first();
        if( empty($weeks)){
            return ;
        }

        $info_ids = explode(',',$prize['info_ids']);

        // 查看章节所在周 历史记录
        $count = History::where([
            "user_id" => $user_id,"relation_type" => 5,"relation_id" => $camp_id,"is_end" => 1,
        ])->whereIn("info_id",$info_ids)->count();

        // 查询是否有记录
        $Reward = ColumnWeekReward::where([
            'relation_id' =>$camp_id, 'user_id' =>$user_id,'week_id' => $weeks['id'],])->first();

        $data = [
            'relation_id'   => $camp_id,
            'user_id'       => $user_id,
            'os_type'       => $os_type,
            'week_id'       => $weeks['id'],
            'prize_id'       => $prize['id'],
         ];


        $data['speed_status'] = 1; //未学完
        // 数量相等 说明学完了本周课程
        if($count == count($info_ids)){
            $data['speed_status'] = 2;
            if(is_null($Reward["end_time"])){
                $data['end_time'] = date("Y-m-d H:i:s");
            }
        }

        if(empty($Reward)){
            ColumnWeekReward::create($data);
        }else{
            ColumnWeekReward::where(['id'=>$Reward->id])->update($data);
        }
        return ;
    }
}
