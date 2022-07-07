<?php


namespace App\Models;
use Illuminate\Support\Facades\DB;

class ColumnEndShow extends Base
{
    protected $table = 'nlsg_column_end_show';

    // 允许批量赋值
    protected  $fillable = ['relation_id','user_id','is_letter','letter_at','is_cer','cer_at',];


    static function EndShow($uid,$col_id){
        
        $res = ["id"=>0,"is_letter"=>0,"is_cer"=>0,];
        $col = Column::select('is_start')->find($col_id);
        if(empty($col)){
            return $res;
        }
        // 查看领取状态
        $show = ColumnEndShow::select("id","is_letter","is_cer")->where([
                'user_id' =>$uid,
                'relation_id' =>$col_id,
            ])->first();

        $res['id'] = $show->id??0;
        $res['is_cer'] = 0; //是否领取奖励  需要结营后 手动点击
        $res['is_letter'] = 0; //是否拆开信件 结营当天必弹  弹完点击就算拆开信件
        // 未结营全部为待领取状态   结营后进行处理
        if($col['is_start'] == 2 && !empty($show)){
            $res['is_letter']   = $show->is_letter;
            $res['is_cer']      = $show->is_cer;
        }
        return $res;
    }


    
    /**
     * 获取证书和信件模板
     * @param  int $col_id 训练营id
     * @return string[] letter_img 信, cer_img 证书,
     * */
    static function GetShowLetter($col_id){

        $img = DB::table("crm_camp_certificate")->select("img_url")->where([
            'column_id' => $col_id,
            'status' => 1
        ])->first();

        
        return [
            "letter" => [
                "第一段",
                "第二段",
                "第三段",
            ],
            "cer_img" => $img->img_url??'',
        ];
    }

}
