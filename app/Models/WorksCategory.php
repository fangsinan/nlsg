<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class WorksCategory extends Model
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