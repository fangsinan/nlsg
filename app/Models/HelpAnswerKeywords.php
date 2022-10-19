<?php


namespace App\Models;


class HelpAnswerKeywords extends Base
{
    const DB_TABLE = 'nlsg_help_answer_keywords';
    protected $table = 'nlsg_help_answer_keywords';

    protected $fillable = [
        'keywords',
        'created_at',
        'updated_at',
    ];
}
