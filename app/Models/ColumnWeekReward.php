<?php
namespace App\Models;


class ColumnWeekReward extends Base
{
    protected $table = 'nlsg_column_week_reward';


    protected  $fillable = ['relation_id','user_id','is_get','is_end','end_time','os_type','week_id'];
}