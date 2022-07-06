<?php
namespace App\Models;


class ColumnWeekModel extends Base
{
    protected $table = 'nlsg_column_week';


    protected  $fillable = ['relation_id','title','start_at','end_at','reward_id','is_del'];
}