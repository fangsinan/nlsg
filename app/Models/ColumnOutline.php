<?php


namespace App\Models;


class ColumnOutline extends Base
{
    protected $table = 'nlsg_column_outline';
    public $timestamps = false;



    public function getDateFormat()
    {
        return time();
    }

}