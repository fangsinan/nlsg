<?php


namespace App\Models;
use Illuminate\Support\Facades\DB;

class ColumnEndShow extends Base
{
    protected $table = 'nlsg_column_end_show';

    // 允许批量赋值
    protected  $fillable = ['relation_id','user_id','is_letter','letter_at','is_cer','cer_at',];
    
    static function EndShow($uid,$col_id){

        $res = ["id"=>0,"is_letter"=>0,"is_cer"=>0,"cer_is_show"=>0];
        $col = Column::select('is_start','classify_column_id','info_column_id')->find($col_id);
        if(empty($col)){
            return $res;
        }
        // 查看领取状态
        $show = ColumnEndShow::select("id","is_letter","is_cer")->where([
            'user_id' =>$uid,
            'relation_id' =>$col_id,
            'app_project_type'=>APP_PROJECT_TYPE,
        ])->first();

        $res['id'] = $show->id??0;
        $res['is_cer'] = 0; //是否领取奖励  需要结营后 手动点击
        $res['is_letter'] = 0; //是否拆开信件 结营当天必弹  弹完点击就算拆开信件


        // cer_is_show
        if($col['is_start'] == 2){
            // 未结营 全部为待领取状态   结营后进行 状态处理
            if( !empty($show)){
                $res['is_letter']   = $show->is_letter;
                $res['is_cer']      = $show->is_cer;
            }

            /**
            // 是否有资格领取  学完 并且结营  如果状态为学完的周 与 总周数 相同则学完
            $res['cer_is_show'] = 0;
            $reward_count = ColumnWeekReward::where([
            'user_id' =>$uid,'relation_id' =>$col_id,'speed_status' =>2,'app_project_type'=>APP_PROJECT_TYPE,
            ])->count();
            $week_count = ColumnWeekModel::where([
            'relation_id' =>$col_id,'is_del' =>0,'app_project_type'=>APP_PROJECT_TYPE,
            ])->count();
            if($reward_count == $week_count && $reward_count > 0){
            $res['cer_is_show'] = 1;
            }
             **/


            // 是否有资格领取  学完 并且结营  如果状态为学完的周 与 总周数 相同则学完
            $res['cer_is_show'] = 0;
            $info_count = WorksInfo::where([
                "column_id"=>$col['info_column_id'],
                "status"=>4,
                'app_project_type'=>APP_PROJECT_TYPE,
            ])->count();

            $his_count = History::where([
                "relation_type" => 5,
                "relation_id" => $col_id,
                "user_id" => $uid,
                "is_end" => 1,
                'app_project_type'=>APP_PROJECT_TYPE,
            ])->count();

            if( $info_count == $his_count ){
                $res['cer_is_show'] = 1;
            }
        }
        return $res;
    }








    /**
     * 获取证书和信件模板
     * @param  int $classify_id 训练营父类id
     * @return string[] letter_img 信, cer_img 证书,
     * */
    static function GetShowLetter($classify_id){

        $img = DB::table("crm_camp_certificate")->select("img_url",'letter')->where([
            'column_id' => $classify_id,
            'status' => 1
        ])->first();


        return [
            "letter" => $img->letter??'',
            "cer_img" => $img->img_url??'',
        ];
    }

}
