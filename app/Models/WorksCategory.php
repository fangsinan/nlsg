<?php


namespace App\Models;

class WorksCategory extends Base
{
    protected $table = 'nlsg_works_category';




    public function getDateFormat()
    {
        return time();
    }

//    public function CategoryRelation()
//    {
//        //一对多
//        return $this->hasMany('App\Models\WorksCategoryRelation','category_id');
//    }

    static function getCategory($arr,$id,$level)
    {
        $list =array();
        foreach ($arr as $k=>$v){
            if ($v['pid'] == $id){
                $v['level']=$level;
                $v['son'] = self::getCategory($arr,$v['id'],$level+1);
                $list[] = $v;
            }
        }
        return $list;
    }



}