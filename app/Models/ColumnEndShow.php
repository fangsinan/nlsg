<?php


namespace App\Models;


class ColumnEndShow extends Base
{
    protected $table = 'nlsg_column_end_show';

    // 允许批量赋值
    protected  $fillable = ['relation_id','user_id',];


}
