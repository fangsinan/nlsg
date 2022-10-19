<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Relations\HasOne;

class HelpAnswerKeywordsBind extends Base
{
    const DB_TABLE = 'nlsg_help_answer_keywords_bind';
    protected $table = 'nlsg_help_answer_keywords_bind';

    protected $fillable = [
        'help_answer_id','keywords_id',
    ];

    public function keywords(): HasOne
    {
        return $this->hasOne(HelpAnswerKeywords::class,'id','keywords_id');
    }
}
