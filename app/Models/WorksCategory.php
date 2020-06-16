<?php


namespace App\Models;

class WorksCategory extends Base
{
    protected $table = 'nlsg_works_category';




    public function getDateFormat()
    {
        return time();
    }

    public function CategoryRelation()
    {
        //一对多
        return $this->hasMany('App\Models\WorksCategoryRelation','category_id');
    }
}