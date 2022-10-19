<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class HelpAnswer extends Base
{
    protected $table = 'nlsg_help_answer';

    protected $fillable = [
        'question', 'answer','qr_code',
    ];

    /**
     * GetAnswersByQuestion  获取答案
     *
     * @param $question
     *
     * @return array list: 答案列表  is_show_qr: 是否需显示二维码
     */
    public static function GetAnswersByQuestion($question): array
    {
        $is_show_qr = 0;
        $res = ['list'=>[], 'is_show_qr'=>$is_show_qr,];
        if(empty($question)) return $res;

        if($question=="微信客服"){
            $is_show_qr = 1;   //只有微信客服才显示二维码
            $operator = "=";
        }else{
            $operator = "like";
            $question = "%".$question."%";
        }
        $list = self::where("question",$operator,$question)->where("status",1)->get();
        if(empty($list)) return $res;

        return [
            "list"      => $list->toArray(),
            "is_show_qr"=> $is_show_qr,
        ];
    }


    public function keywordsBind(): HasMany
    {
        return $this->hasMany(HelpAnswerKeywordsBind::class,'help_answer_id','id');
    }

    public function typeInfo(){
        return $this->hasOne(FeedbackType::class,'id','type');
    }

}
