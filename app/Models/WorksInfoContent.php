<?php


namespace App\Models;


class WorksInfoContent extends Base
{
    protected $table = 'nlsg_works_info_content';

    protected $fillable = [
        'works_info_id', 'content'
    ];


}